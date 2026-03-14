import 'dart:io';

import 'package:archive/archive_io.dart';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';

/// Manages offline KEP (KandaNews Edition Package) downloads.
///
/// Downloads the ZIP bundle for an edition, extracts it to the app's
/// documents directory, and serves the local index.html via file:// URI.
///
/// Storage layout:
///   <docs>/editions/<editionId>/index.html
///   <docs>/editions/<editionId>/manifest.json
///   <docs>/editions/<editionId>/assets/...
class OfflineEditionService {
  static const _kEditionsDir = 'editions';

  final _dio = Dio(BaseOptions(
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(minutes: 3),
  ));

  // ── Public API ─────────────────────────────────────────────────────────────

  /// Returns true if the edition is already downloaded and intact.
  Future<bool> isDownloaded(String editionId) async {
    final dir = await _editionDir(editionId);
    return File('${dir.path}/index.html').exists();
  }

  /// Returns the file:// URI to the local index.html, or null if not present.
  Future<String?> getLocalViewerPath(String editionId) async {
    final dir = await _editionDir(editionId);
    final index = File('${dir.path}/index.html');
    if (await index.exists()) return index.uri.toString();
    return null;
  }

  /// Downloads and extracts the ZIP bundle for an edition.
  ///
  /// [zipUrl]     — full HTTPS URL to the edition ZIP
  /// [editionId]  — stable identifier used as the folder name
  /// [onProgress] — optional progress callback (0.0 → 1.0)
  ///
  /// Returns the file:// URI to the extracted index.html on success.
  /// Throws on download or extraction failure.
  Future<String> downloadEdition({
    required String zipUrl,
    required String editionId,
    void Function(double progress)? onProgress,
  }) async {
    final dir = await _editionDir(editionId);
    final zipFile = File('${dir.path}/.download.zip');

    try {
      // Download
      await _dio.download(
        zipUrl,
        zipFile.path,
        onReceiveProgress: (received, total) {
          if (total > 0 && onProgress != null) {
            onProgress(received / total);
          }
        },
      );

      // Extract
      await _extractZip(zipFile, dir);

      // Clean up the zip
      await zipFile.delete();

      final indexPath = '${dir.path}/index.html';
      if (!await File(indexPath).exists()) {
        throw Exception('Extracted ZIP is missing index.html');
      }

      return File(indexPath).uri.toString();
    } catch (e) {
      // Clean up on failure so a partial download doesn't block future attempts
      if (await dir.exists()) await dir.delete(recursive: true);
      rethrow;
    }
  }

  /// Deletes the locally stored edition to free storage.
  Future<void> deleteEdition(String editionId) async {
    final dir = await _editionDir(editionId);
    if (await dir.exists()) await dir.delete(recursive: true);
  }

  /// Returns the total size in bytes currently used by all downloaded editions.
  Future<int> totalStorageUsed() async {
    final root = await _rootDir();
    if (!await root.exists()) return 0;
    int total = 0;
    await for (final entity in root.list(recursive: true)) {
      if (entity is File) {
        total += await entity.length();
      }
    }
    return total;
  }

  /// Lists all edition IDs that are currently downloaded.
  Future<List<String>> downloadedEditionIds() async {
    final root = await _rootDir();
    if (!await root.exists()) return [];
    final ids = <String>[];
    await for (final entity in root.list()) {
      if (entity is Directory) {
        final index = File('${entity.path}/index.html');
        if (await index.exists()) ids.add(entity.uri.pathSegments.last);
      }
    }
    return ids;
  }

  // ── Internals ───────────────────────────────────────────────────────────────

  Future<Directory> _rootDir() async {
    final docs = await getApplicationDocumentsDirectory();
    return Directory('${docs.path}/$_kEditionsDir');
  }

  Future<Directory> _editionDir(String editionId) async {
    final root = await _rootDir();
    final dir = Directory('${root.path}/$editionId');
    await dir.create(recursive: true);
    return dir;
  }

  Future<void> _extractZip(File zipFile, Directory dest) async {
    // Use streaming extraction to avoid loading the entire ZIP into memory —
    // critical for low-RAM devices in Africa.
    final inputStream = InputFileStream(zipFile.path);
    final archive = ZipDecoder().decodeStream(inputStream);

    for (final file in archive.files) {
      if (!file.isFile) continue;

      // Sanitize path to prevent zip-slip attacks
      final safeName = _sanitizePath(file.name);
      if (safeName == null) continue;

      final outPath = '${dest.path}/$safeName';
      final outFile = File(outPath);
      await outFile.parent.create(recursive: true);

      final outputStream = OutputFileStream(outFile.path);
      file.writeContent(outputStream);
      await outputStream.close();
    }

    await inputStream.close();
  }

  /// Returns a sanitised relative path, or null if the entry looks like
  /// a zip-slip attack (path escaping outside the destination directory).
  String? _sanitizePath(String name) {
    // Normalise separators
    final normalized = name.replaceAll('\\', '/');
    // Reject absolute paths and path traversal sequences
    if (normalized.startsWith('/') || normalized.contains('../')) return null;
    return normalized;
  }
}
