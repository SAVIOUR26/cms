import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../providers/edition_provider.dart';
import '../../providers/subscription_provider.dart';
import '../../theme/kn_theme.dart';
import '../../widgets/dashboard_tile.dart';
import '../../widgets/kn_drawer.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _waveController;

  @override
  void initState() {
    super.initState();
    _waveController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 3),
    )..repeat();
  }

  @override
  void dispose() {
    _waveController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final user = authState.user;
    final country = user?.country ?? 'ug';
    final quote = ref.watch(quoteProvider);
    final todayEdition = ref.watch(todayEditionProvider(country));
    final subscription = ref.watch(subscriptionStatusProvider);

    final firstName = user?.firstName ?? 'Reader';
    final isSubscribed = subscription.whenOrNull(
          data: (sub) => sub?.isActive ?? false,
        ) ??
        false;

    final screenWidth = MediaQuery.of(context).size.width;
    final isDesktop = screenWidth > 800;

    final dashboardContent = _buildDashboardContent(
      context: context,
      firstName: firstName,
      isSubscribed: isSubscribed,
      todayEdition: todayEdition,
      quote: quote,
      user: user,
      country: country,
      isDesktop: isDesktop,
    );

    // Desktop layout: permanent sidebar + main content
    if (isDesktop) {
      return Scaffold(
        body: Row(
          children: [
            // Permanent sidebar
            SizedBox(
              width: 280,
              child: KnDrawer(embedded: true),
            ),
            // Main content area
            Expanded(
              child: Scaffold(
                appBar: _buildAppBar(context, user, isDesktop),
                body: dashboardContent,
              ),
            ),
          ],
        ),
      );
    }

    // Mobile layout: drawer-based sidebar
    return Scaffold(
      drawer: const KnDrawer(),
      appBar: _buildAppBar(context, user, isDesktop),
      body: dashboardContent,
    );
  }

  PreferredSizeWidget _buildAppBar(BuildContext context, dynamic user, bool isDesktop) {
    return AppBar(
      automaticallyImplyLeading: !isDesktop,
      title: const Text('KandaNews Africa'),
      actions: [
        // Initials as a real orange button
        GestureDetector(
          onTap: () => context.push('/profile'),
          child: Container(
            margin: const EdgeInsets.only(right: 16),
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: KnColors.orange,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: KnColors.orange.withAlpha(80),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Center(
              child: Text(
                user?.initials ?? '?',
                style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                  fontSize: 14,
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildDashboardContent({
    required BuildContext context,
    required String firstName,
    required bool isSubscribed,
    required AsyncValue todayEdition,
    required AsyncValue<Map<String, dynamic>?> quote,
    required dynamic user,
    required String country,
    required bool isDesktop,
  }) {
    return Stack(
      children: [
        // Fixed grid wallpaper background
        Positioned.fill(
          child: CustomPaint(
            painter: _DashboardGridPainter(
              darkColor: KnColors.navy.withAlpha(8),
              gridColor: KnColors.orange.withAlpha(12),
            ),
          ),
        ),

        // Wave shimmer animation overlay
        Positioned.fill(
          child: AnimatedBuilder(
            animation: _waveController,
            builder: (context, _) => CustomPaint(
              painter: _WaveShimmerPainter(
                progress: _waveController.value,
                color: KnColors.orange.withAlpha(10),
              ),
            ),
          ),
        ),

        // Scrollable content
        RefreshIndicator(
          onRefresh: () async {
            ref.invalidate(todayEditionProvider(country));
            ref.invalidate(quoteProvider);
            ref.invalidate(subscriptionStatusProvider);
          },
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.symmetric(
              horizontal: isDesktop ? 32 : 20,
              vertical: 20,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Horizontal scrolling marquee text
                const _MarqueeWidget(),
                const SizedBox(height: 20),

                // Welcome text (centered)
                Text(
                  'Welcome, $firstName!',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: KnColors.navy,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  isSubscribed
                      ? 'Your subscription is active'
                      : 'Subscribe to read all editions',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: KnColors.textSecondary,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 24),

                // Dashboard Tiles Grid (centered, responsive)
                LayoutBuilder(
                  builder: (context, constraints) {
                    final crossAxisCount = isDesktop ? 3 : 2;
                    final aspectRatio = isDesktop ? 1.4 : 1.1;
                    return GridView.count(
                      crossAxisCount: crossAxisCount,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      mainAxisSpacing: 16,
                      crossAxisSpacing: 16,
                      childAspectRatio: aspectRatio,
                      children: [
                        DashboardTile(
                          icon: Icons.today,
                          label: "Today's\nEdition",
                          color: KnColors.orange,
                          badge: todayEdition.whenOrNull(
                              data: (e) => e != null ? 'NEW' : null),
                          onTap: () {
                            final edition = todayEdition.valueOrNull;
                            if (edition != null && edition.htmlUrl != null) {
                              context.push('/reader', extra: {
                                'url': edition.htmlUrl,
                                'title': edition.title,
                              });
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                    content:
                                        Text('No edition available today')),
                              );
                            }
                          },
                        ),
                        DashboardTile(
                          icon: Icons.library_books,
                          label: 'Archives',
                          color: const Color(0xFF3B82F6),
                          onTap: () => context.push('/archives'),
                        ),
                        DashboardTile(
                          icon: Icons.trending_up,
                          label: 'Trending in\n${_countryName(country)} ${_countryFlag(country)}',
                          color: const Color(0xFF8B5CF6),
                          onTap: () => context.push('/archives',
                              extra: {'type': 'daily'}),
                        ),
                        DashboardTile(
                          icon: Icons.star,
                          label: 'Subscribe',
                          color: const Color(0xFF10B981),
                          badge: isSubscribed ? 'ACTIVE' : null,
                          onTap: () => context.push('/subscribe'),
                        ),
                        DashboardTile(
                          icon: Icons.auto_awesome,
                          label: 'Special\nEditions',
                          color: const Color(0xFFF59E0B),
                          onTap: () => context.push('/special-editions'),
                        ),
                        DashboardTile(
                          icon: Icons.campaign,
                          label: 'Advertise',
                          color: const Color(0xFFEF4444),
                          onTap: () => _showAdvertise(context),
                        ),
                      ],
                    );
                  },
                ),

                const SizedBox(height: 24),

                // Quote card (centered)
                quote.when(
                  data: (q) {
                    if (q == null) return const SizedBox.shrink();
                    return Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: KnColors.primaryGradient,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          const Text(
                            'QUOTE OF THE DAY',
                            style: TextStyle(
                              color: KnColors.orange,
                              fontWeight: FontWeight.w700,
                              fontSize: 13,
                              letterSpacing: 1,
                            ),
                          ),
                          const SizedBox(height: 12),
                          const Icon(Icons.format_quote,
                              color: KnColors.orange, size: 32),
                          const SizedBox(height: 8),
                          Text(
                            q['quote'] ?? '',
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontStyle: FontStyle.italic,
                              height: 1.5,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            '— ${q['author'] ?? 'Unknown'}',
                            style: TextStyle(
                              color: Colors.white.withAlpha(179),
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                  loading: () => const SizedBox.shrink(),
                  error: (_, __) => const SizedBox.shrink(),
                ),

                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ],
    );
  }

  String _countryName(String code) {
    const names = {
      'ug': 'Uganda',
      'ke': 'Kenya',
      'ng': 'Nigeria',
      'za': 'South Africa',
    };
    return names[code.toLowerCase()] ?? 'Africa';
  }

  String _countryFlag(String code) {
    const flags = {
      'ug': '\u{1F1FA}\u{1F1EC}',
      'ke': '\u{1F1F0}\u{1F1EA}',
      'ng': '\u{1F1F3}\u{1F1EC}',
      'za': '\u{1F1FF}\u{1F1E6}',
    };
    return flags[code.toLowerCase()] ?? '\u{1F30D}';
  }

  void _showAdvertise(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Advertise with Us'),
        content: const Text(
          'Reach thousands of readers across Africa.\n\n'
          'Contact: ads@kandanews.africa\n'
          'WhatsApp: +256 XXX XXX XXX',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }
}

/// Horizontal scrolling marquee for the target audience tagline.
/// Uses a continuous translate animation so the text scrolls smoothly
/// from right to left. The text is duplicated so that the second copy
/// enters from the right only after the first copy has fully left the
/// viewport on the left, creating a seamless loop.
class _MarqueeWidget extends StatefulWidget {
  const _MarqueeWidget();

  @override
  State<_MarqueeWidget> createState() => _MarqueeWidgetState();
}

class _MarqueeWidgetState extends State<_MarqueeWidget>
    with SingleTickerProviderStateMixin {
  late final AnimationController _animController;
  final GlobalKey _textKey = GlobalKey();
  double _textWidth = 0;

  static const _text =
      'Designed for Professionals, Entrepreneurs and University Students Across Africa';

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      // Slower speed: 25 seconds for one full cycle
      duration: const Duration(seconds: 25),
    );
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _measureAndStart();
    });
  }

  void _measureAndStart() {
    if (!mounted) return;
    final renderBox =
        _textKey.currentContext?.findRenderObject() as RenderBox?;
    if (renderBox != null) {
      setState(() {
        _textWidth = renderBox.size.width;
      });
    }
    _animController.repeat();
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 36,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            KnColors.navy.withAlpha(15),
            KnColors.orange.withAlpha(15),
            KnColors.navy.withAlpha(15),
          ],
        ),
        borderRadius: BorderRadius.circular(18),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(18),
        child: LayoutBuilder(
          builder: (context, constraints) {
            final viewportWidth = constraints.maxWidth;
            // Total travel: the text starts fully off-screen right,
            // scrolls until it fully disappears off-screen left.
            // Then the second copy (spaced by viewportWidth) does the same.
            final totalTravel = _textWidth + viewportWidth;

            return AnimatedBuilder(
              animation: _animController,
              builder: (context, child) {
                // Position: starts at +viewportWidth (off-screen right),
                // ends at -textWidth (fully off-screen left)
                final offset =
                    viewportWidth - (_animController.value * totalTravel);

                return Stack(
                  children: [
                    Positioned(
                      left: offset,
                      top: 0,
                      bottom: 0,
                      child: _buildMarqueeText(),
                    ),
                  ],
                );
              },
            );
          },
        ),
      ),
    );
  }

  Widget _buildMarqueeText() {
    return Row(
      key: _textKey,
      mainAxisSize: MainAxisSize.min,
      children: [
        const SizedBox(width: 24),
        Icon(Icons.star, color: KnColors.orange, size: 14),
        const SizedBox(width: 8),
        Text(
          _text,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: KnColors.navy,
            letterSpacing: 0.3,
          ),
        ),
        const SizedBox(width: 8),
        Icon(Icons.star, color: KnColors.orange, size: 14),
        const SizedBox(width: 24),
      ],
    );
  }
}

/// Paints the fixed dark grid with light orange lines wallpaper
class _DashboardGridPainter extends CustomPainter {
  final Color darkColor;
  final Color gridColor;

  _DashboardGridPainter({required this.darkColor, required this.gridColor});

  @override
  void paint(Canvas canvas, Size size) {
    // Background tint
    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      Paint()..color = darkColor,
    );

    // Grid lines
    final gridPaint = Paint()
      ..color = gridColor
      ..strokeWidth = 0.5;

    const spacing = 32.0;
    for (double x = 0; x < size.width; x += spacing) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), gridPaint);
    }
    for (double y = 0; y < size.height; y += spacing) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), gridPaint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

/// Paints a diagonal wave shimmer that sweeps across the dashboard
class _WaveShimmerPainter extends CustomPainter {
  final double progress;
  final Color color;

  _WaveShimmerPainter({required this.progress, required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final shaderPaint = Paint()
      ..shader = LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [
          Colors.transparent,
          color,
          Colors.transparent,
        ],
        stops: [
          (progress - 0.15).clamp(0.0, 1.0),
          progress,
          (progress + 0.15).clamp(0.0, 1.0),
        ],
      ).createShader(Rect.fromLTWH(0, 0, size.width, size.height));

    canvas.drawRect(
      Rect.fromLTWH(0, 0, size.width, size.height),
      shaderPaint,
    );
  }

  @override
  bool shouldRepaint(_WaveShimmerPainter oldDelegate) =>
      oldDelegate.progress != progress;
}
