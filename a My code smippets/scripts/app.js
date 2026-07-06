const uploadInput = document.getElementById('imageUpload');
const uploadBox = document.getElementById('dropZone');
const qualityInput = document.getElementById('quality');
const maxWidthInput = document.getElementById('maxWidth');
const outputFormatInput = document.getElementById('outputFormat');
const resultText = document.getElementById('result');
const resultsContainer = document.getElementById('resultsContainer');
const progressWrap = document.getElementById('progressWrap');
const progressFill = document.getElementById('progressFill');
const progressText = document.getElementById('progressText');
const modal = document.getElementById('previewModal');
const modalImage = document.getElementById('modalImage');
const closeModal = document.getElementById('closeModal');

let selectedImages = [];

const formatBytes = (bytes) => {
    if (!bytes || Number.isNaN(bytes)) return '0 KB';
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex += 1;
    }
    return `${size.toFixed(size >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
};

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

const openModal = (src) => {
    modalImage.src = src;
    modal.classList.add('show');
};

const closePreviewModal = () => {
    modal.classList.remove('show');
    modalImage.src = '';
};

const triggerDownload = (url, fileName) => {
    const downloadLink = document.createElement('a');
    downloadLink.href = url;
    downloadLink.download = fileName;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    downloadLink.remove();
};

const updateProgress = (done, total) => {
    const percent = total ? Math.round((done / total) * 100) : 0;
    progressFill.style.width = `${percent}%`;
    progressText.textContent = `${done}/${total} images processed`;
    progressWrap.style.display = total ? 'block' : 'none';
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
        originalBox.innerHTML = '<h4>Original</h4>';
        const originalImage = document.createElement('img');
        originalImage.className = 'preview';
        originalImage.src = item.dataUrl;
        originalImage.alt = 'Original preview';
        originalImage.addEventListener('click', () => openModal(originalImage.src));
        originalBox.appendChild(originalImage);
        const originalSize = document.createElement('p');
        originalSize.className = 'size-info';
        originalSize.textContent = `Size: ${formatBytes(item.originalSize)}`;
        originalBox.appendChild(originalSize);

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
        const optimizedSize = document.createElement('p');
        optimizedSize.className = 'size-info';
        optimizedSize.textContent = 'Size: pending';
        optimizedBox.appendChild(optimizedSize);

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
        item.optimizedSizeText = optimizedSize;
    });
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

const processImage = async (item, button = null, downloadAfterOptimize = false) => {
    if (button) {
        button.disabled = true;
        button.textContent = 'Processing...';
    }

    const maxWidth = parseInt(maxWidthInput.value, 10) || 1200;
    const quality = parseFloat(qualityInput.value) || 0.8;
    const outputFormat = outputFormatInput.value || 'jpeg';
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

    const outputType = outputFormat === 'webp' ? 'image/webp' : (item.fileName.toLowerCase().endsWith('.png') ? 'image/png' : 'image/jpeg');
    const blob = await canvasToBlob(canvas, outputType, quality);

    if (item.optimizedObjectUrl) {
        URL.revokeObjectURL(item.optimizedObjectUrl);
    }

    const optimizedUrl = URL.createObjectURL(blob);
    item.optimizedObjectUrl = optimizedUrl;
    item.optimizedImage.src = optimizedUrl;
    item.optimizedImage.alt = 'Optimized preview';
    item.optimizedSizeText.textContent = `Size: ${formatBytes(blob.size)}`;

    const downloadName = 'optimized-' + sanitizeFileName(item.fileName) + (outputFormat === 'webp' ? '.webp' : (outputType === 'image/png' ? '.png' : '.jpg'));
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
            fileName: file.name,
            originalSize: file.size
        });
    }

    renderCards();
    resultText.textContent = `${selectedImages.length} image(s) loaded. Click Run Optimize to create lighter versions.`;
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

    progressWrap.style.display = 'block';
    updateProgress(0, selectedImages.length);

    for (let index = 0; index < selectedImages.length; index += 1) {
        await processImage(selectedImages[index]);
        updateProgress(index + 1, selectedImages.length);
    }

    resultText.textContent = `Compressed ${selectedImages.length} image(s) and saved them on the server.`;
});

document.getElementById('downloadAllBtn').addEventListener('click', async () => {
    if (!selectedImages.length) {
        resultText.textContent = 'Please choose at least one image first.';
        return;
    }

    for (const item of selectedImages) {
        if (item.optimizedImage && item.optimizedImage.src && item.optimizedImage.src.startsWith('blob:')) {
            const fileName = item.optimizedImage.downloadName || 'optimized-' + sanitizeFileName(item.fileName) + '.jpg';
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
