<?php
$uploadDir = __DIR__ . '/uploads';
$maxFileSizeBytes = 10 * 1024 * 1024;
$uploadMessage = '';

$cacheTtlSeconds = 10 * 60; // 10 minutes
$sessionCacheKey = 'imgopt_last_active';

// Session-based cleanup:
// - deletes uploads after 10 minutes of inactivity
// - deletes uploads again on every page refresh (new request)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$now = time();
$last = isset($_SESSION[$sessionCacheKey]) ? (int)$_SESSION[$sessionCacheKey] : 0;

$shouldPurge = false;
if ($last === 0) {
    $_SESSION[$sessionCacheKey] = $now;
} else {
    if (($now - $last) > $cacheTtlSeconds) {
        $shouldPurge = true;
    }
    // Also purge on every refresh/new request from the same session.
    $shouldPurge = true;
    $_SESSION[$sessionCacheKey] = $now;
}

if ($shouldPurge && is_dir($uploadDir)) {
    foreach (glob($uploadDir . '/*') as $path) {
        if (is_file($path)) {
            @unlink($path);
        }
    }
}


if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        $uploadMessage = 'The uploads directory could not be created on the server.';
    }
}

if (is_dir($uploadDir) && !is_writable($uploadDir)) {
    $uploadMessage = 'The uploads directory is not writable on this server.';
}

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST' && isset($_FILES['image']) && isset($_POST['fileName'])) {
    $savedFiles = [];


    // Only handle single file upload (current UI sends one image per request)
    $tmpPath = $_FILES['image']['tmp_name'] ?? '';
    $origError = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $fileSize = (int)($_FILES['image']['size'] ?? 0);

    if ($origError !== UPLOAD_ERR_OK) {
        $uploadMessage = 'Upload error: ' . $origError;
    } elseif ($fileSize <= 0 || $fileSize > $maxFileSizeBytes) {
        $uploadMessage = 'One of the images is larger than the allowed size.';
    } elseif (!is_uploaded_file($tmpPath)) {
        $uploadMessage = 'Invalid upload received.';
    } else {
        $fileName = $_POST['fileName'] ?? 'optimized-image';
        $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) ?: 'optimized-image';
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath) ?: '';

        // Only allow PNG/JPEG/WebP based on actual bytes.
        $ext = match ($mime) {
            'image/png' => '.png',
            'image/jpeg' => '.jpg',
            'image/webp' => '.webp',
            default => ''
        };

        if ($ext === '') {
            $uploadMessage = 'Unsupported image type.';
        } else {
            $counter = 1;
            $safeName = $baseName . $ext;

            while (file_exists($uploadDir . '/' . $safeName)) {
                $safeName = $baseName . '-' . $counter++ . $ext;
            }

            $savedPath = $uploadDir . '/' . $safeName;
            $tmpSave = $savedPath . '.tmp.' . bin2hex(random_bytes(6));

            if (move_uploaded_file($tmpPath, $tmpSave)) {
                if (@rename($tmpSave, $savedPath)) {
                    $savedFiles[] = $safeName;
                } else {
                    @unlink($tmpSave);
                    $uploadMessage = 'One of the optimized images could not be saved on the server.';
                }
            } else {
                $uploadMessage = 'One of the optimized images could not be saved on the server.';
            }
        }
    }

    if (!empty($savedFiles) && $uploadMessage === '') {
        $uploadMessage = count($savedFiles) . ' optimized image(s) saved to uploads/' . implode(', ', $savedFiles);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Optimizer</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="card">
        <div class="hero">
            <div>
                <h2>Image Optimizer for Web</h2>
                <p>Upload one or more photos, reduce their size, and keep them sharp for faster websites and smoother
                    mobile loading.</p>
            </div>
            <div class="chip">Deployment-ready</div>
        </div>

        <label class="upload-box" id="dropZone" for="imageUpload">
            <strong>Drag and drop images here or click to browse</strong>
            <input class="file-input" type="file" id="imageUpload" accept="image/*" multiple>
        </label>

        <div class="controls">
            <div class="row">
                <label>
                    Quality
                    <input type="range" id="quality" min="0.5" max="1" step="0.05" value="0.8">
                </label>
                <label>
                    Max Width
                    <input type="number" id="maxWidth" value="1200" min="200" max="2500">
                </label>
                <label>
                    Output Format
                    <select id="outputFormat">
                        <option value="jpeg">JPEG</option>
                        <option value="webp">WebP</option>
                    </select>
                </label>
            </div>

            <div class="row">
                <button id="compressBtn">Run Optimize</button>
                <button id="downloadAllBtn">Download All</button>
            </div>
        </div>

        <div class="progress-wrap" id="progressWrap">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-text" id="progressText">0/0 images processed</div>
        </div>

        <p class="tip">Tip: lower the max width and quality slightly for the best balance between speed and clarity.</p>

        <?php if ($uploadMessage !== ''): ?>
            <p class="message"><?php echo htmlspecialchars($uploadMessage); ?></p>
        <?php endif; ?>

        <div id="resultsContainer"></div>
        <p id="result" class="result-text"></p>
    </div>

    <div id="previewModal" class="modal" aria-hidden="true">
        <div class="modal-content">
            <span id="closeModal" class="close-btn" role="button" tabindex="0" aria-label="Close preview">&times;</span>
            <img id="modalImage" alt="Zoomed preview">
        </div>
    </div>

    <div class="tutorial-card">
        <h3>Here is a tutorial on how you can create your own.</h3>
        <p>Download the guide below to see the steps for building this image optimizer webpage.</p>
        <a class="tutorial-link" href="image_optimizer_webpage_tutorial.pdf" download>
            <span class="tutorial-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <path d="M9 15h6"></path>
                    <path d="M9 11h6"></path>
                    <path d="M9 19h4"></path>
                </svg>
            </span>
            <span>Download tutorial PDF</span>
        </a>
    </div>

    <script src="app.js"></script>
</body>

</html>