<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
spl_autoload_register(function ($class) {
    $prefix = 'DoubleTickB24\\';
    $baseDir = dirname(__DIR__) . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use DoubleTickB24\Bitrix24\BitrixClient;
use DoubleTickB24\Core\Logger;
use DoubleTickB24\DoubleTick\DoubleTickClient;

$config = require dirname(__DIR__) . '/config/config.php';

// Remove restrictive framing/CORS to allow inline rendering inside Bitrix24 iframe
header_remove('X-Frame-Options');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: Range, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$mediaDir = dirname(__DIR__) . '/storage/media';
if (!is_dir($mediaDir)) {
    @mkdir($mediaDir, 0777, true);
}

// 1. Resolve target file path
$filePath = null;
$originalFilename = 'attachment';
$mimeType = null;

// Case A: Direct local file request (e.g. ?file=voice_123.ogg)
if (!empty($_GET['file'])) {
    $safeName = basename((string)$_GET['file']);
    $candidate = $mediaDir . '/' . $safeName;
    if (file_exists($candidate) && is_file($candidate)) {
        $filePath = $candidate;
        $originalFilename = $safeName;
    }
}

// Case B: Remote DoubleTick / CDN URL (e.g. ?url=https://cdn.doubletick.io/...)
if (!$filePath && !empty($_GET['url'])) {
    $remoteUrl = trim((string)$_GET['url']);
    
    // Prevent SSRF: only allow HTTP/HTTPS URLs
    if (filter_var($remoteUrl, FILTER_VALIDATE_URL) && in_array(parse_url($remoteUrl, PHP_URL_SCHEME), ['http', 'https'])) {
        $urlExt = strtolower(pathinfo(parse_url($remoteUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        if (!$urlExt) {
            $urlExt = 'dat';
        }
        $cacheKey = md5($remoteUrl) . '.' . $urlExt;
        $cacheFile = $mediaDir . '/' . $cacheKey;

        // Extract original filename if passed or derive from URL
        if (!empty($_GET['name'])) {
            $originalFilename = basename((string)$_GET['name']);
        } else {
            $parsedName = basename(parse_url($remoteUrl, PHP_URL_PATH) ?? '');
            $originalFilename = $parsedName ?: ('attachment.' . $urlExt);
        }

        // If not cached or 0-byte, download using DoubleTick credentials
        if (!file_exists($cacheFile) || filesize($cacheFile) === 0) {
            $b24 = null;
            if (!empty($_GET['member_id'])) {
                $b24 = BitrixClient::getByMemberId((string)$_GET['member_id']);
            } elseif (!empty($_GET['domain'])) {
                $b24 = BitrixClient::getByDomain((string)$_GET['domain']);
            }
            if (!$b24) {
                $b24 = BitrixClient::getFirstActive();
            }

            $apiKey = $b24 ? $b24->getDoubleTickApiKey() : $config['doubletick']['api_key'];
            $waba = $b24 ? $b24->getDoubleTickWaba() : $config['doubletick']['default_waba'];

            if ($apiKey) {
                try {
                    $dt = new DoubleTickClient($apiKey, $waba, $config['doubletick']['api_url']);
                    $res = $dt->downloadMedia($remoteUrl);
                    if ($res['success'] && !empty($res['content'])) {
                        file_put_contents($cacheFile, $res['content']);
                        $filePath = $cacheFile;
                        $mimeType = $res['mime_type'] ?: null;
                    } else {
                        Logger::warning("DoubleTick download failed for: {$remoteUrl}", ['code' => $res['status_code']]);
                    }
                } catch (\Throwable $e) {
                    Logger::error("Proxy media fetch error: " . $e->getMessage(), ['url' => $remoteUrl]);
                }
            }
        } else {
            $filePath = $cacheFile;
        }
    }
}

if (!$filePath || !file_exists($filePath)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Media file not found or inaccessible']);
    exit;
}

// 2. Determine MIME type
if (!$mimeType) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $filePath) : null;
    if ($finfo) finfo_close($finfo);
}

if (!$mimeType || $mimeType === 'application/octet-stream') {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeMap = [
        'ogg' => 'audio/ogg',
        'oga' => 'audio/ogg',
        'opus' => 'audio/ogg',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'webm' => 'audio/webm',
        'm4a' => 'audio/mp4',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'mp4' => 'video/mp4',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt' => 'text/plain; charset=utf-8',
    ];
    $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';
}

$fileSize = filesize($filePath);
$isDownload = !empty($_GET['download']);
$disposition = $isDownload ? 'attachment' : 'inline';

// 3. Handle HTTP Range Requests (HTTP 206) for Audio/Video seeking
$start = 0;
$end = $fileSize - 1;
$isRangeRequest = false;

if (isset($_SERVER['HTTP_RANGE'])) {
    $rangeHeader = $_SERVER['HTTP_RANGE'];
    if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $rangeHeader, $matches)) {
        $start = (int)$matches[1];
        if (!empty($matches[2])) {
            $end = (int)$matches[2];
        }
        if ($start <= $end && $start < $fileSize) {
            $isRangeRequest = true;
            if ($end >= $fileSize) {
                $end = $fileSize - 1;
            }
        }
    }
}

// 4. Stream response
header('Content-Type: ' . $mimeType);
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=604800'); // 7 days cache
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($originalFilename) . '"');

if ($isRangeRequest) {
    $length = ($end - $start) + 1;
    http_response_code(206);
    header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
    header("Content-Length: {$length}");

    $fp = fopen($filePath, 'rb');
    if ($fp) {
        fseek($fp, $start);
        $bufferSize = 8192;
        $bytesRemaining = $length;
        while (!feof($fp) && $bytesRemaining > 0 && (connection_status() === 0)) {
            $bytesToRead = min($bufferSize, $bytesRemaining);
            echo fread($fp, $bytesToRead);
            flush();
            $bytesRemaining -= $bytesToRead;
        }
        fclose($fp);
    }
} else {
    http_response_code(200);
    header('Content-Length: ' . $fileSize);
    readfile($filePath);
}
exit;
