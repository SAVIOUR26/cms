<?php
/**
 * KandaNews API v1 — Polls & Voting Routes
 *
 * GET  /polls?country=ug&status=active   List polls with options + vote counts
 * POST /polls/{id}/vote                  Cast a vote  {"option_id": 3}  [auth]
 * GET  /polls/{id}/results               Get live results for one poll
 */

function route_polls(string $action, string $method, string $extra = ''): void {
    // List polls
    if ($action === '' && $method === 'GET') {
        polls_list();
        return;
    }

    // Single poll operations: /polls/{id}/vote  or  /polls/{id}/results
    if (is_numeric($action)) {
        $id = (int) $action;
        if ($extra === 'vote'    && $method === 'POST') { polls_vote($id);    return; }
        if ($extra === 'results' && $method === 'GET')  { polls_results($id); return; }
    }

    json_error('Not found', 404);
}

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Build the vote-count + percentage array for a poll's options.
 * Also returns total_votes.
 */
function _poll_options_with_counts(PDO $pdo, int $pollId): array {
    // Fetch options
    $stmt = $pdo->prepare("
        SELECT id, option_text, image_url, sort_order
        FROM   poll_options
        WHERE  poll_id = ?
        ORDER  BY sort_order ASC, id ASC
    ");
    $stmt->execute([$pollId]);
    $options = $stmt->fetchAll();

    // Fetch counts per option in one query
    $stmt = $pdo->prepare("
        SELECT option_id, COUNT(*) AS cnt
        FROM   poll_votes
        WHERE  poll_id = ?
        GROUP  BY option_id
    ");
    $stmt->execute([$pollId]);
    $counts = [];
    foreach ($stmt->fetchAll() as $row) {
        $counts[(int)$row['option_id']] = (int)$row['cnt'];
    }

    $total = array_sum($counts);

    $result = [];
    foreach ($options as $opt) {
        $oid   = (int) $opt['id'];
        $votes = $counts[$oid] ?? 0;
        $result[] = [
            'id'         => $oid,
            'text'       => $opt['option_text'],
            'image_url'  => $opt['image_url'],
            'votes'      => $votes,
            'percentage' => $total > 0 ? round(($votes / $total) * 100, 1) : 0.0,
        ];
    }

    return ['options' => $result, 'total_votes' => $total];
}

// ── Route handlers ────────────────────────────────────────────────────────────

/**
 * GET /polls?country=ug&status=active
 */
function polls_list(): void {
    $user    = optional_auth();
    $country = $_GET['country'] ?? ($user['country'] ?? 'ug');
    $status  = $_GET['status']  ?? 'active';
    $country = strtolower(trim($country));

    // Validate status
    if (!in_array($status, ['active', 'closed', 'all'], true)) $status = 'active';

    $pdo = db();

    $where  = "country = ?";
    $params = [$country];

    if ($status !== 'all') {
        $where   .= " AND status = ?";
        $params[] = $status;
    }

    // Also exclude polls that haven't started yet
    $where .= " AND starts_at <= NOW()";

    $stmt = $pdo->prepare("
        SELECT id, question, description, cover_image_url,
               country, status, starts_at, ends_at
        FROM   polls
        WHERE  $where
        ORDER  BY sort_order ASC, id DESC
    ");
    $stmt->execute($params);
    $polls = $stmt->fetchAll();

    // Attach options + counts, and user vote state
    foreach ($polls as &$poll) {
        $poll['id'] = (int) $poll['id'];

        $data = _poll_options_with_counts($pdo, $poll['id']);
        $poll['options']     = $data['options'];
        $poll['total_votes'] = $data['total_votes'];

        // User vote state (requires auth)
        $poll['user_has_voted']      = false;
        $poll['user_vote_option_id'] = null;

        if ($user) {
            $vs = $pdo->prepare("
                SELECT option_id FROM poll_votes
                WHERE poll_id = ? AND user_id = ?
            ");
            $vs->execute([$poll['id'], $user['id']]);
            $vote = $vs->fetch();
            if ($vote) {
                $poll['user_has_voted']      = true;
                $poll['user_vote_option_id'] = (int) $vote['option_id'];
            }
        }
    }
    unset($poll);

    json_success(['polls' => $polls]);
}

/**
 * POST /polls/{id}/vote
 * Body: {"option_id": 3}
 * Requires authentication.
 */
function polls_vote(int $pollId): void {
    $user = require_auth();

    $input    = json_decode(file_get_contents('php://input'), true) ?? [];
    $optionId = isset($input['option_id']) ? (int) $input['option_id'] : 0;
    if (!$optionId) json_error('option_id is required');

    $pdo = db();

    // Verify poll exists and is active
    $stmt = $pdo->prepare("
        SELECT id, status, ends_at FROM polls WHERE id = ?
    ");
    $stmt->execute([$pollId]);
    $poll = $stmt->fetch();
    if (!$poll)                         json_error('Poll not found', 404);
    if ($poll['status'] !== 'active')   json_error('This poll is not active');
    if ($poll['ends_at'] && strtotime($poll['ends_at']) < time()) {
        json_error('This poll has ended');
    }

    // Verify option belongs to this poll
    $stmt = $pdo->prepare("
        SELECT id FROM poll_options WHERE id = ? AND poll_id = ?
    ");
    $stmt->execute([$optionId, $pollId]);
    if (!$stmt->fetch()) json_error('Invalid option for this poll');

    // Cast vote (UNIQUE KEY prevents double voting)
    try {
        $stmt = $pdo->prepare("
            INSERT INTO poll_votes (poll_id, option_id, user_id, voted_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$pollId, $optionId, $user['id']]);
    } catch (PDOException $e) {
        // Duplicate entry = already voted
        if (str_contains($e->getMessage(), '1062')) {
            json_error('You have already voted in this poll');
        }
        throw $e;
    }

    // Return updated results
    $data = _poll_options_with_counts($pdo, $pollId);

    json_success([
        'voted'              => true,
        'user_vote_option_id'=> $optionId,
        'total_votes'        => $data['total_votes'],
        'options'            => $data['options'],
    ]);
}

/**
 * GET /polls/{id}/results
 */
function polls_results(int $pollId): void {
    $pdo  = db();
    $stmt = $pdo->prepare("
        SELECT id, question, status, ends_at FROM polls WHERE id = ?
    ");
    $stmt->execute([$pollId]);
    $poll = $stmt->fetch();
    if (!$poll) json_error('Poll not found', 404);

    $data = _poll_options_with_counts($pdo, $pollId);

    json_success([
        'poll_id'     => (int) $poll['id'],
        'question'    => $poll['question'],
        'status'      => $poll['status'],
        'ends_at'     => $poll['ends_at'],
        'total_votes' => $data['total_votes'],
        'options'     => $data['options'],
    ]);
}
