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

        // Only allow PNG/JPEG based on actual bytes.
        $ext = match ($mime) {
            'image/png' => '.png',
            'image/jpeg' => '.jpg',
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
    <style>
        :root {
            color-scheme: light;
            --bg: #f3f7ff;
            --panel: #ffffff;
            --text: #172033;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #dce5f5;
            --shadow: 0 16px 40px rgba(15, 23, 42, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg), #e9f2ff 60%, #f8fbff);
            color: var(--text);
            padding: 24px;
        }

        .card {
            background: var(--panel);
            padding: 28px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            max-width: 980px;
            margin: auto;
            border: 1px solid rgba(255, 255, 255, 0.7);
        }

        .hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .hero h2 {
            margin: 0 0 8px;
            font-size: 1.8rem;
        }

        .hero p {
            margin: 0;
            color: var(--muted);
            max-width: 620px;
            line-height: 1.5;
        }

        .chip {
            background: #eff6ff;
            color: var(--primary-dark);
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .upload-box {
            display: block;
            border: 2px dashed var(--border);
            border-radius: 14px;
            padding: 18px;
            text-align: center;
            color: var(--primary-dark);
            background: #f8fbff;
            cursor: pointer;
            margin-bottom: 16px;
        }

        .upload-box:hover {
            border-color: var(--primary);
            background: #f0f7ff;
        }

        .upload-box.drag-over {
            border-color: var(--primary);
            background: #eaf4ff;
            transform: scale(1.01);
        }

        .file-input {
            display: block;
            margin: 8px auto 0;
            font-size: 0.95rem;
        }

        .controls {
            background: #f8fbff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        input[type="range"],
        input[type="number"] {
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        button {
            margin-top: 8px;
            padding: 10px 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        button:hover {
            transform: translateY(-1px);
        }

        #compressBtn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }

        #downloadAllBtn,
        .download-btn {
            background: #eef4ff;
            color: var(--primary-dark);
            border: 1px solid #cfe0ff;
        }

        .image-card {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            justify-content: space-between;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            margin-top: 14px;
            background: #fcfdff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .preview-stack {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            flex: 1;
        }

        .preview-box {
            min-width: 180px;
            flex: 1;
        }

        .preview-box h4 {
            margin: 0 0 6px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .preview {
            width: 100%;
            max-width: 220px;
            height: 160px;
            object-fit: cover;
            border: 1px solid var(--border);
            border-radius: 10px;
            margin-top: 8px;
            cursor: zoom-in;
            background: #f4f7fb;
        }

        .download-btn {
            white-space: nowrap;
            align-self: center;
        }

        .message {
            color: #0f766e;
            font-weight: 600;
            margin: 12px 0 0;
            padding: 10px 12px;
            background: #ecfdf5;
            border-radius: 10px;
            border: 1px solid #a7f3d0;
        }

        .tip {
            margin: 12px 0 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .result-text {
            margin-top: 10px;
            color: var(--muted);
        }

        .modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.72);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            background: #fff;
            border-radius: 16px;
            padding: 10px;
            box-shadow: var(--shadow);
        }

        .modal-content img {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            border-radius: 10px;
        }

        .close-btn {
            position: absolute;
            top: 8px;
            right: 12px;
            cursor: pointer;
            font-size: 24px;
            color: #334155;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
        }

        @media (max-width: 700px) {
            .card {
                padding: 18px;
            }

            .image-card {
                flex-direction: column;
            }

            .download-btn {
                align-self: flex-start;
            }
        }
    </style>
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
            </div>

            <div class="row">
                <button id="compressBtn">Run Optimize</button>
                <button id="downloadAllBtn">Download All</button>
            </div>
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

    <script>
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
    </script>
</body>

</html>