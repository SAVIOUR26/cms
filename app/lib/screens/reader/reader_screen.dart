import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import '../../theme/kn_theme.dart';

/// Immersive full-screen edition reader.
///
/// The moment this screen opens, system UI is hidden and the WebView
/// owns 100% of the physical display — no AppBar, no bottom bar, no frames.
/// A floating close button is the only chrome.
class ReaderScreen extends StatefulWidget {
  final String url;
  final String title;

  const ReaderScreen({super.key, required this.url, required this.title});

  @override
  State<ReaderScreen> createState() => _ReaderScreenState();
}

class _ReaderScreenState extends State<ReaderScreen>
    with SingleTickerProviderStateMixin {
  double _progress = 0;
  bool _isLoading = true;
  bool _hasError = false;
  InAppWebViewController? _webController;

  late final AnimationController _loadingFade;
  late final Animation<double> _loadingOpacity;

  @override
  void initState() {
    super.initState();

    // Take over the full screen immediately — before anything else renders
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);

    _loadingFade = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
      value: 1.0,
    );
    _loadingOpacity = _loadingFade;
  }

  @override
  void dispose() {
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    _loadingFade.dispose();
    super.dispose();
  }

  // ── Injected after page load ─────────────────────────────────────────────
  // Forces Swiper and every page-wrapper to fill 100vw × 100vh,
  // hides the CMS-generated header/footer chrome, and overrides
  // the fixed 461×600px canvas the templates were authored with.
  static const _fullscreenCSS = '''
    html, body {
      margin: 0 !important;
      padding: 0 !important;
      width: 100vw !important;
      height: 100vh !important;
      overflow: hidden !important;
      background: #1e2b42 !important;
      display: block !important;
    }
    /* The .container flex wrapper — pin it to fill the full screen
       so header/controls outside it cannot steal vertical space */
    .container, .viewer {
      position: fixed !important;
      inset: 0 !important;
      width: 100vw !important;
      height: 100vh !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    /* Swiper containers */
    .swiper, .main-swiper, .swiper-wrapper, .swiper-slide {
      width: 100vw !important;
      height: 100vh !important;
      max-width: 100vw !important;
      max-height: 100vh !important;
    }
    /* Individual page canvas — override the fixed 461×600px */
    .page-wrapper {
      width: 100vw !important;
      height: 100vh !important;
      max-width: 100vw !important;
      max-height: 100vh !important;
      box-sizing: border-box !important;
    }
    /* Hide all CMS chrome — app uses native swipe + floating close button */
    .header, .thumbs, .page-counter-bar, .edition-header,
    .controls, .sidebar, .kn-indicator,
    #fs-prompt, .fullscreen-prompt {
      display: none !important;
    }
    /* Ensure animated backgrounds fill the new size */
    .particles, .shine, .bg-overlay {
      width: 100% !important;
      height: 100% !important;
    }
  ''';

  Future<void> _injectFullscreen() async {
    if (_webController == null) return;

    // Fix the viewport meta tag
    await _webController!.evaluateJavascript(source: '''
      (function() {
        var meta = document.querySelector('meta[name="viewport"]');
        if (meta) {
          meta.setAttribute('content',
            'width=device-width, initial-scale=1.0, maximum-scale=1.0, '
            'user-scalable=no, viewport-fit=cover');
        } else {
          var m = document.createElement('meta');
          m.name = 'viewport';
          m.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, '
            + 'user-scalable=no, viewport-fit=cover';
          document.head.prepend(m);
        }

        // Inject fullscreen CSS
        var style = document.createElement('style');
        style.id = 'kn-fullscreen';
        style.textContent = `${_fullscreenCSS.replaceAll('`', r'\`')}`;
        document.head.appendChild(style);

        // Force Swiper to recalculate layout after CSS change
        if (window.mainSwiper && window.mainSwiper.update) {
          window.mainSwiper.update();
        }
        window.dispatchEvent(new Event('resize'));
      })();
    ''');
  }

  void _onLoadStop() {
    _injectFullscreen().then((_) {
      // Fade out the loading splash after a short settle delay
      Future.delayed(const Duration(milliseconds: 150), () {
        if (mounted) {
          _loadingFade.reverse().then((_) {
            if (mounted) setState(() => _isLoading = false);
          });
        }
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light,
      child: Scaffold(
        backgroundColor: KnColors.navy,
        // No AppBar — zero chrome
        extendBody: true,
        extendBodyBehindAppBar: true,
        body: Stack(
          fit: StackFit.expand,
          children: [
            // ── WebView — fills 100% of display ───────────────────────────
            InAppWebView(
              initialUrlRequest: URLRequest(url: WebUri(widget.url)),
              initialSettings: InAppWebViewSettings(
                javaScriptEnabled: true,
                mediaPlaybackRequiresUserGesture: false,
                allowsInlineMediaPlayback: true,
                supportZoom: false,
                // Do NOT use wideViewPort or overviewMode —
                // these zoom the page out and break the full-screen layout
                useWideViewPort: false,
                loadWithOverviewMode: false,
                builtInZoomControls: false,
                transparentBackground: true,
                // Allow audio autoplay for podcast pages
                allowsAirPlayForMediaPlayback: true,
              ),
              onWebViewCreated: (c) => _webController = c,
              onProgressChanged: (_, progress) {
                if (mounted) setState(() => _progress = progress / 100);
              },
              onLoadStop: (_, __) => _onLoadStop(),
              onReceivedError: (_, __, ___) {
                if (mounted) setState(() {
                  _isLoading = false;
                  _hasError = true;
                });
              },
            ),

            // ── Full-screen loading splash ─────────────────────────────────
            if (_isLoading)
              FadeTransition(
                opacity: _loadingOpacity,
                child: _LoadingSplash(
                  title: widget.title,
                  progress: _progress,
                ),
              ),

            // ── Error state ────────────────────────────────────────────────
            if (_hasError && !_isLoading)
              _ErrorState(
                onRetry: () {
                  setState(() {
                    _hasError = false;
                    _isLoading = true;
                    _progress = 0;
                    _loadingFade.value = 1.0;
                  });
                  _webController?.reload();
                },
              ),

            // ── Floating close button ──────────────────────────────────────
            // Always visible, lives in the safe area top-left.
            // Small and unobtrusive so it doesn't distract from reading.
            SafeArea(
              child: Padding(
                padding: const EdgeInsets.only(left: 14, top: 10),
                child: GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: Colors.black.withAlpha(120),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: Colors.white.withAlpha(40),
                        width: 1,
                      ),
                    ),
                    child: const Icon(
                      Icons.arrow_back_ios_new_rounded,
                      color: Colors.white,
                      size: 16,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Loading splash — shown over the full screen while the HTML loads.
// Fades out once the edition is ready, creating a seamless reveal.
// ─────────────────────────────────────────────────────────────────────────────

class _LoadingSplash extends StatelessWidget {
  final String title;
  final double progress;

  const _LoadingSplash({required this.title, required this.progress});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: KnColors.navy,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Logo mark
          Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              gradient: KnColors.orangeGradient,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: KnColors.orange.withAlpha(80),
                  blurRadius: 24,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: const Center(
              child: Text(
                'KN',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 26,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.5,
                ),
              ),
            ),
          ),

          const SizedBox(height: 28),

          Text(
            title,
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.w700,
              letterSpacing: 0.2,
            ),
          ),

          const SizedBox(height: 8),

          const Text(
            'Opening edition…',
            style: TextStyle(
              color: Colors.white38,
              fontSize: 13,
            ),
          ),

          const SizedBox(height: 32),

          // Progress bar
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 64),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: progress > 0 ? progress : null,
                minHeight: 3,
                backgroundColor: Colors.white12,
                valueColor:
                    const AlwaysStoppedAnimation<Color>(KnColors.orange),
              ),
            ),
          ),

          if (progress > 0) ...[
            const SizedBox(height: 10),
            Text(
              '${(progress * 100).toInt()}%',
              style: const TextStyle(
                color: Colors.white24,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Error state
// ─────────────────────────────────────────────────────────────────────────────

class _ErrorState extends StatelessWidget {
  final VoidCallback onRetry;
  const _ErrorState({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: KnColors.navy,
      padding: const EdgeInsets.all(32),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.signal_wifi_off_outlined,
              size: 56, color: Colors.white.withAlpha(80)),
          const SizedBox(height: 20),
          const Text(
            'Could not load edition',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Check your connection and try again.',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.white54, fontSize: 13),
          ),
          const SizedBox(height: 28),
          ElevatedButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh, size: 18),
            label: const Text('Try Again'),
            style: ElevatedButton.styleFrom(
              backgroundColor: KnColors.orange,
              foregroundColor: Colors.white,
              padding:
                  const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
