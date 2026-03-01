<?php
/**
 * KandaNews — Seed test editions into the API database.
 *
 * Run once from the CMS root:
 *   php api/v1/schema/seed_editions.php
 *
 * This registers the existing output/ editions so they can be
 * published via the portal and appear in the Flutter app.
 */

// Load .env
$envFile = dirname(__DIR__, 2) . '/.env';
$env = [];
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
}

$cmsUrl = rtrim($env['CMS_URL'] ?? $env['APP_URL'] ?? 'https://cms.kandanews.africa', '/');

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s',
            $env['DB_HOST'] ?? 'localhost',
            $env['DB_NAME'] ?? 'kandan_api',
            $env['DB_CHARSET'] ?? 'utf8mb4'),
        $env['DB_USER'] ?? 'kandan_api',
        $env['DB_PASS'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "Connected to database.\n";

// Editions to seed
$editions = [
    [
        'title'        => 'Daily Edition',
        'slug'         => 'daily-edition-2025-11-03',
        'country'      => 'ug',
        'edition_date' => '2025-11-03',
        'edition_type' => 'daily',
        'category'     => null,
        'html_url'     => $cmsUrl . '/output/2025-11-03/index.html',
        'zip_url'      => $cmsUrl . '/output/2025-11-03/Daily_Edition_2025-11-03.zip',
        'page_count'   => 8,
        'is_free'      => 1,
        'theme'        => null,
        'description'  => 'KandaNews Daily Edition — November 3, 2025',
        'status'       => 'published',
    ],
    [
        'title'        => 'Rate Card 2025',
        'slug'         => 'rate-card-2025',
        'country'      => 'ug',
        'edition_date' => '2025-01-01',
        'edition_type' => 'rate_card',
        'category'     => null,
        'html_url'     => $cmsUrl . '/output/rate_card_2025/index.html',
        'zip_url'      => $cmsUrl . '/output/rate_card_2025/Rate_Card_2025.zip',
        'page_count'   => 4,
        'is_free'      => 1,
        'theme'        => null,
        'description'  => 'KandaNews Advertising Rate Card 2025',
        'status'       => 'published',
    ],
    [
        'title'        => 'Special Edition',
        'slug'         => 'special-edition-sample',
        'country'      => 'ug',
        'edition_date' => '2025-01-01',
        'edition_type' => 'special',
        'category'     => 'university',
        'html_url'     => $cmsUrl . '/output/special_edition/index.html',
        'zip_url'      => $cmsUrl . '/output/special_edition/Special_Edition.zip',
        'page_count'   => 4,
        'is_free'      => 1,
        'theme'        => null,
        'description'  => 'KandaNews Special Edition — University',
        'status'       => 'published',
    ],
];

$stmt = $pdo->prepare("
    INSERT INTO editions
        (title, slug, country, edition_date, edition_type, category,
         html_url, zip_url, page_count, is_free, theme, description, status, created_at)
    VALUES
        (:title, :slug, :country, :edition_date, :edition_type, :category,
         :html_url, :zip_url, :page_count, :is_free, :theme, :description, :status, NOW())
    ON DUPLICATE KEY UPDATE
        html_url = VALUES(html_url),
        zip_url  = VALUES(zip_url),
        status   = VALUES(status)
");

$count = 0;
foreach ($editions as $ed) {
    try {
        $stmt->execute([
            ':title'        => $ed['title'],
            ':slug'         => $ed['slug'],
            ':country'      => $ed['country'],
            ':edition_date' => $ed['edition_date'],
            ':edition_type' => $ed['edition_type'],
            ':category'     => $ed['category'],
            ':html_url'     => $ed['html_url'],
            ':zip_url'      => $ed['zip_url'],
            ':page_count'   => $ed['page_count'],
            ':is_free'      => $ed['is_free'],
            ':theme'        => $ed['theme'],
            ':description'  => $ed['description'],
            ':status'       => $ed['status'],
        ]);
        $count++;
        echo "  + {$ed['title']} ({$ed['edition_type']}) — {$ed['status']}\n";
    } catch (PDOException $e) {
        echo "  ! Failed: {$ed['title']} — {$e->getMessage()}\n";
    }
}

echo "\nDone. Seeded $count edition(s).\n";
echo "These are published and should now appear in the app.\n";
echo "You can manage them at: portal/editions.php\n";
