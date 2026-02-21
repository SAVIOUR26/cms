import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import '../../theme/kn_theme.dart';

/// Full-screen edition reader using WebView
/// Loads the self-contained HTML5 flipbook directly
class ReaderScreen extends StatefulWidget {
  final String url;
  final String title;

  const ReaderScreen({super.key, required this.url, required this.title});

  @override
  State<ReaderScreen> createState() => _ReaderScreenState();
}

class _ReaderScreenState extends State<ReaderScreen> {
  double _progress = 0;
  bool _isLoading = true;
  InAppWebViewController? _webController;

  @override
  void initState() {
    super.initState();
    // Enter immersive mode for reading
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
  }

  @override
  void dispose() {
    // Restore system UI
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1E2B42),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1E2B42),
        foregroundColor: Colors.white,
        elevation: 0,
        title: Text(
          widget.title,
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.fullscreen),
            onPressed: () {
              SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => _webController?.reload(),
          ),
        ],
      ),
      body: Stack(
        children: [
          InAppWebView(
            initialUrlRequest: URLRequest(url: WebUri(widget.url)),
            initialSettings: InAppWebViewSettings(
              javaScriptEnabled: true,
              mediaPlaybackRequiresUserGesture: false,
              allowsInlineMediaPlayback: true,
              supportZoom: false,
              useWideViewPort: true,
              loadWithOverviewMode: true,
              builtInZoomControls: false,
              transparentBackground: true,
            ),
            onWebViewCreated: (controller) {
              _webController = controller;
            },
            onProgressChanged: (controller, progress) {
              setState(() {
                _progress = progress / 100;
                if (progress >= 100) _isLoading = false;
              });
            },
            onLoadStop: (controller, url) {
              setState(() => _isLoading = false);
            },
            onReceivedError: (controller, request, error) {
              setState(() => _isLoading = false);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Failed to load edition: ${error.description}'),
                  action: SnackBarAction(
                    label: 'Retry',
                    onPressed: () => controller.reload(),
                  ),
                ),
              );
            },
          ),

          // Loading indicator
          if (_isLoading)
            Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const CircularProgressIndicator(
                    color: KnColors.orange,
                    strokeWidth: 3,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Loading edition... ${(_progress * 100).toInt()}%',
                    style: const TextStyle(
                      color: Colors.white70,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),

          // Progress bar at top
          if (_isLoading)
            Positioned(
              top: 0,
              left: 0,
              right: 0,
              child: LinearProgressIndicator(
                value: _progress,
                backgroundColor: Colors.transparent,
                valueColor: const AlwaysStoppedAnimation<Color>(KnColors.orange),
                minHeight: 3,
              ),
            ),
        ],
      ),
    );
  }
}
