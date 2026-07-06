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

        <div class="progress-wrap" id="progressWrap" style="display:none;">
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

    <div id="previewModal" class="modal">
        <div class="modal-content">
            <span id="closeModal" class="close-btn">&times;</span>
            <img id="modalImage" alt="Zoomed preview">
        </div>
    </div>

    <script src="app.js"></script>
    <!--
        const uploadInput = document.getElementById('imageUpload');
        const uploadBox = document.getElementById('dropZone');
        const qualityInput = document.getElementById('quality');
        const maxWidthInput = document.getElementById('maxWidth');
        const resultText = document.getElementById('result');
        const resultsContainer = document.getElementById('resultsContainer');
        const modal = document.getElementById('previewModal');
        const modalImage = document.getElementById('modalImage');
        const closeModal = document.getElementById('closeModal');

        let selectedImages = [];

        const readFileAsDataURL = (file) => new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });

        const loadImage = (dataUrl) => new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = dataUrl;
        });

        const sanitizeFileName = (name) => {
            const base = (name || 'optimized-image').toString().replace(/\.[^.]+$/, '');
            const safeBase = base.replace(/[^A-Za-z0-9._-]/g, '_') || 'optimized-image';
            return safeBase;
        };


        const canvasToBlob = (canvas, outputType, quality) => new Promise((resolve, reject) => {
            canvas.toBlob((blob) => {
                if (!blob) {
                    reject(new Error('Compression failed.'));
                    return;
                }
                resolve(blob);
            }, outputType, quality);
        });

        const openModal = (src) => {
            modalImage.src = src;
            modal.classList.add('show');
        };

        const closePreviewModal = () => {
            modal.classList.remove('show');
            modalImage.src = '';
        };

        const renderCards = () => {
            resultsContainer.innerHTML = '';

            selectedImages.forEach((item) => {
                const card = document.createElement('div');
                card.className = 'image-card';

                const previewStack = document.createElement('div');
                previewStack.className = 'preview-stack';

                const originalBox = document.createElement('div');
                originalBox.className = 'preview-box';
                originalBox.innerHTML = `<h4>Original</h4>`;
                const originalImage = document.createElement('img');
                originalImage.className = 'preview';
                originalImage.src = item.dataUrl;
                originalImage.alt = 'Original preview';
                originalImage.addEventListener('click', () => openModal(originalImage.src));
                originalBox.appendChild(originalImage);

                const optimizedBox = document.createElement('div');
                optimizedBox.className = 'preview-box';
                optimizedBox.innerHTML = '<h4>Optimized</h4>';
                const optimizedImage = document.createElement('img');
                optimizedImage.className = 'preview';
                optimizedImage.alt = 'Optimized preview';
                optimizedImage.addEventListener('click', () => {
                    if (optimizedImage.src) openModal(optimizedImage.src);
                });
                optimizedBox.appendChild(optimizedImage);

                previewStack.appendChild(originalBox);
                previewStack.appendChild(optimizedBox);

                const downloadButton = document.createElement('button');
                downloadButton.className = 'download-btn';
                downloadButton.textContent = 'Download';
                downloadButton.addEventListener('click', () => processImage(item, downloadButton, true));

                const label = document.createElement('div');
                const strong = document.createElement('strong');
                strong.textContent = item.fileName;
                label.appendChild(strong);

                card.appendChild(label);

                card.appendChild(previewStack);
                card.appendChild(downloadButton);

                resultsContainer.appendChild(card);

                item.originalImage = originalImage;
                item.optimizedImage = optimizedImage;
                item.downloadButton = downloadButton;
            });
        };

        const triggerDownload = (url, fileName) => {
            const downloadLink = document.createElement('a');
            downloadLink.href = url;
            downloadLink.download = fileName;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            downloadLink.remove();
        };

        const processImage = async (item, button = null, downloadAfterOptimize = false) => {
            if (button) {
                button.disabled = true;
                button.textContent = 'Processing...';
            }

            const maxWidth = parseInt(maxWidthInput.value, 10) || 1200;
            const quality = parseFloat(qualityInput.value) || 0.8;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const originalWidth = item.img.naturalWidth;
            const originalHeight = item.img.naturalHeight;
            const scale = Math.min(1, maxWidth / originalWidth);
            const width = Math.max(1, Math.round(originalWidth * scale));
            const height = Math.max(1, Math.round(originalHeight * scale));

            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(item.img, 0, 0, width, height);

            const outputType = item.fileName.toLowerCase().endsWith('.png') ? 'image/png' : 'image/jpeg';
            const blob = await canvasToBlob(canvas, outputType, quality);
            // Revoke previous object URL to avoid memory leaks
            if (item.optimizedObjectUrl) {
                URL.revokeObjectURL(item.optimizedObjectUrl);
            }

            const optimizedUrl = URL.createObjectURL(blob);
            item.optimizedObjectUrl = optimizedUrl;

            item.optimizedImage.src = optimizedUrl;
            item.optimizedImage.alt = 'Optimized preview';



            const downloadName = 'optimized-' + sanitizeFileName(item.fileName) + (
                outputType === 'image/png' ? '.png' : '.jpg');
            item.optimizedImage.downloadName = downloadName;

            if (downloadAfterOptimize) {
                triggerDownload(optimizedUrl, downloadName);
            }

            const formData = new FormData();
            formData.append('image', blob, downloadName);
            formData.append('fileName', downloadName);

            await fetch('index.php', {
                method: 'POST',
                body: formData
            });


            if (button) {
                button.disabled = false;
                button.textContent = 'Download';
            }

            resultText.textContent = `Optimized ${item.fileName}.`;
        };

        const handleFiles = async (files) => {
            const imageFiles = Array.from(files || []).filter((file) => file && file.type.startsWith('image/'));
            if (!imageFiles.length) {
                resultText.textContent = 'Please choose image files.';
                return;
            }

            selectedImages = [];

            for (const file of imageFiles) {
                const dataUrl = await readFileAsDataURL(file);
                const img = await loadImage(dataUrl);
                selectedImages.push({
                    file,
                    dataUrl,
                    img,
                    fileName: file.name
                });
            }

            renderCards();
            resultText.textContent =
                `${selectedImages.length} image(s) loaded. Click compress to generate lighter versions.`;
        };

        uploadInput.addEventListener('change', async (event) => {
            await handleFiles(event.target.files);
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            uploadBox.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                uploadBox.classList.add('drag-over');
            });
        });

        ['dragleave', 'dragend'].forEach((eventName) => {
            uploadBox.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
                uploadBox.classList.remove('drag-over');
            });
        });

        uploadBox.addEventListener('drop', async (event) => {
            event.preventDefault();
            event.stopPropagation();
            uploadBox.classList.remove('drag-over');
            await handleFiles(event.dataTransfer?.files || []);
        });

        document.getElementById('compressBtn').addEventListener('click', async () => {
            if (!selectedImages.length) {
                resultText.textContent = 'Please choose at least one image first.';
                return;
            }

            for (const item of selectedImages) {
                await processImage(item);
            }

            resultText.textContent =
                `Compressed ${selectedImages.length} image(s) and saved them on the server.`;
        });

        document.getElementById('downloadAllBtn').addEventListener('click', async () => {
            if (!selectedImages.length) {
                resultText.textContent = 'Please choose at least one image first.';
                return;
            }

            for (const item of selectedImages) {
                if (item.optimizedImage && item.optimizedImage.src && item.optimizedImage.src.startsWith(
                        'blob:')) {
                    const fileName = item.optimizedImage.downloadName || 'optimized-' + item.fileName.replace(
                        /\.[^.]+$/, '') + '.jpg';
                    triggerDownload(item.optimizedImage.src, fileName);
                }
            }

            resultText.textContent = 'Downloaded all optimized images.';
        });

        closeModal.addEventListener('click', closePreviewModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closePreviewModal();
            }
        });
    -->
</body>

</html>