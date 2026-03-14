import 'package:flutter/material.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../theme/kn_theme.dart';

/// KandaNews in-app browser.
///
/// All web content (kandanews.africa pages, ads portal, careers, etc.)
/// opens here — never in the system browser. Only system-level protocols
/// (mailto:, tel:, https://wa.me/) are allowed to leave the app.
class KnWebViewScreen extends StatefulWidget {
  final String url;
  final String title;

  const KnWebViewScreen({super.key, required this.url, required this.title});

  /// Push the in-app browser from anywhere.
  static void push(BuildContext context, String url, {String title = ''}) {
    Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => KnWebViewScreen(url: url, title: title),
    ));
  }

  @override
  State<KnWebViewScreen> createState() => _KnWebViewScreenState();
}

class _KnWebViewScreenState extends State<KnWebViewScreen> {
  InAppWebViewController? _controller;
  double _progress = 0;
  String? _error;
  late String _currentTitle;

  @override
  void initState() {
    super.initState();
    _currentTitle = widget.title;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: KnColors.navy,
      appBar: AppBar(
        backgroundColor: KnColors.navy,
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.close_rounded),
          onPressed: () => Navigator.pop(context),
        ),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              _currentTitle.isEmpty ? 'KandaNews' : _currentTitle,
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            if (_progress < 1.0)
              const Text(
                'Loading…',
                style: TextStyle(fontSize: 11, color: Colors.white54),
              ),
          ],
        ),
        actions: [
          if (_controller != null)
            IconButton(
              icon: const Icon(Icons.refresh_rounded),
              onPressed: () => _controller!.reload(),
            ),
        ],
        bottom: _progress < 1.0
            ? PreferredSize(
                preferredSize: const Size.fromHeight(3),
                child: LinearProgressIndicator(
                  value: _progress,
                  backgroundColor: Colors.white12,
                  valueColor:
                      const AlwaysStoppedAnimation<Color>(KnColors.orange),
                ),
              )
            : null,
      ),
      body: _error != null ? _buildError() : _buildWebView(),
    );
  }

  Widget _buildWebView() {
    return InAppWebView(
      initialUrlRequest: URLRequest(url: WebUri(widget.url)),
      initialSettings: InAppWebViewSettings(
        useShouldOverrideUrlLoading: true,
        javaScriptEnabled: true,
        supportZoom: true,
        useWideViewPort: true,
        loadWithOverviewMode: true,
        domStorageEnabled: true,
        databaseEnabled: true,
        mediaPlaybackRequiresUserGesture: false,
      ),
      onWebViewCreated: (c) => setState(() => _controller = c),
      onProgressChanged: (_, p) => setState(() => _progress = p / 100.0),
      onTitleChanged: (_, title) {
        if (title != null && title.isNotEmpty) {
          setState(() => _currentTitle = title);
        }
      },
      onLoadError: (_, __, code, message) {
        setState(() => _error = message);
      },
      onLoadHttpError: (_, __, statusCode, url) {
        if (statusCode >= 500) {
          setState(() => _error = 'Server error ($statusCode)');
        }
      },
      // Allow mailto:, tel:, wa.me to escape to system apps.
      // All other URLs stay inside this WebView.
      shouldOverrideUrlLoading: (c, action) async {
        final uri = action.request.url;
        if (uri == null) return NavigationActionPolicy.ALLOW;
        final scheme = uri.scheme.toLowerCase();
        if (scheme == 'mailto' ||
            scheme == 'tel' ||
            scheme == 'sms' ||
            (scheme == 'https' && uri.host.contains('wa.me'))) {
          await launchUrl(Uri.parse(uri.toString()),
              mode: LaunchMode.externalApplication);
          return NavigationActionPolicy.CANCEL;
        }
        return NavigationActionPolicy.ALLOW;
      },
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off_rounded, size: 56, color: Colors.white38),
            const SizedBox(height: 20),
            const Text(
              'Could not load page',
              style: TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text(
              _error ?? 'Check your connection and try again.',
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.white54, fontSize: 13),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () {
                setState(() => _error = null);
                _controller?.reload();
              },
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: KnColors.orange,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                padding:
                    const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
