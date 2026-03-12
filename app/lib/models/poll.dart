/// A single voting option within a poll.
/// image_url is optional — used when the poll features candidate profile pics.
class PollOption {
  final int id;
  final String text;
  final String? imageUrl;
  final int votes;
  final double percentage;

  const PollOption({
    required this.id,
    required this.text,
    this.imageUrl,
    required this.votes,
    required this.percentage,
  });

  factory PollOption.fromJson(Map<String, dynamic> json) {
    return PollOption(
      id:         json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      text:       json['text'] ?? '',
      imageUrl:   json['image_url'],
      votes:      json['votes'] is int
                      ? json['votes']
                      : int.tryParse(json['votes']?.toString() ?? '0') ?? 0,
      percentage: (json['percentage'] is num)
                      ? (json['percentage'] as num).toDouble()
                      : double.tryParse(json['percentage']?.toString() ?? '0') ?? 0.0,
    );
  }
}

/// A poll / voting campaign fetched from the server.
class Poll {
  final int id;
  final String question;
  final String? description;
  final String? coverImageUrl;
  final String country;
  final String status;
  final String? endsAt;
  final int totalVotes;
  final bool userHasVoted;
  final int? userVoteOptionId;
  final List<PollOption> options;

  const Poll({
    required this.id,
    required this.question,
    this.description,
    this.coverImageUrl,
    required this.country,
    required this.status,
    this.endsAt,
    required this.totalVotes,
    required this.userHasVoted,
    this.userVoteOptionId,
    required this.options,
  });

  factory Poll.fromJson(Map<String, dynamic> json) {
    return Poll(
      id:               json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      question:         json['question'] ?? '',
      description:      json['description'],
      coverImageUrl:    json['cover_image_url'],
      country:          json['country'] ?? 'ug',
      status:           json['status'] ?? 'active',
      endsAt:           json['ends_at'],
      totalVotes:       json['total_votes'] is int
                            ? json['total_votes']
                            : int.tryParse(json['total_votes']?.toString() ?? '0') ?? 0,
      userHasVoted:     json['user_has_voted'] == true,
      userVoteOptionId: json['user_vote_option_id'] is int
                            ? json['user_vote_option_id']
                            : int.tryParse(json['user_vote_option_id']?.toString() ?? ''),
      options: (json['options'] as List? ?? [])
          .map((o) => PollOption.fromJson(o as Map<String, dynamic>))
          .toList(),
    );
  }

  /// Returns a copy of this poll with updated vote data after submission.
  Poll withVoteResult(Map<String, dynamic> result) {
    return Poll(
      id:               id,
      question:         question,
      description:      description,
      coverImageUrl:    coverImageUrl,
      country:          country,
      status:           status,
      endsAt:           endsAt,
      totalVotes:       result['total_votes'] is int
                            ? result['total_votes']
                            : int.tryParse(result['total_votes']?.toString() ?? '0') ?? totalVotes,
      userHasVoted:     true,
      userVoteOptionId: result['user_vote_option_id'] is int
                            ? result['user_vote_option_id']
                            : int.tryParse(result['user_vote_option_id']?.toString() ?? ''),
      options: (result['options'] as List? ?? [])
          .map((o) => PollOption.fromJson(o as Map<String, dynamic>))
          .toList(),
    );
  }
}
