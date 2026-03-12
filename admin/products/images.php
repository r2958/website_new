<?php
require_once('../../application.php');
require_once('../auth.php');

$productId = isset($_GET['ProductID']) ? intval($_GET['ProductID']) : 0;

$qid = $Admin->queryProductDetails($productId);
if($DB->numRows($qid) == 0) header('Location: index.php');
$row = $DB->fetchObject($qid);

require_once($CFG->serverroot . '/common/functions/class.ImageManager.php');
$IM = new ImageManager();
$IM->ImagePath = '/images/products/';

$Page->PageTitle = 'Product Images - ' . $row->ProductName;
$Admin->showAdminHeader();
$Admin->showProductHeader();
?>
<style>
.image-manager-container {
    max-width: 1200px;
    margin: 0 auto;
}
.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}
.image-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    background: #fff;
    position: relative;
}
.image-card.primary {
    border-color: #C5A059;
    box-shadow: 0 0 0 2px #C5A059;
}
.image-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 4px;
}
.image-card .image-info {
    margin-top: 10px;
    font-size: 12px;
    color: #666;
}
.image-card .image-actions {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}
.image-card .image-actions button {
    flex: 1;
    padding: 8px;
    font-size: 12px;
    border: 1px solid #ddd;
    background: #fff;
    cursor: pointer;
    border-radius: 4px;
}
.image-card .image-actions button:hover {
    background: #f5f5f5;
}
.image-card .image-actions button.btn-danger {
    color: #c33;
    border-color: #c33;
}
.image-card .image-actions button.btn-primary {
    color: #C5A059;
    border-color: #C5A059;
}
.image-card .primary-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #C5A059;
    color: white;
    padding: 4px 12px;
    font-size: 11px;
    border-radius: 4px;
    font-weight: bold;
}
.add-image-section {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}
.add-image-section h3 {
    margin-bottom: 15px;
    color: #333;
}
.upload-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}
.upload-form .form-group {
    flex: 1;
}
.upload-form label {
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
    color: #666;
}
.upload-form input[type="text"],
.upload-form input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.upload-form button {
    padding: 10px 30px;
    background: #1a1a1a;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}
.upload-form button:hover {
    background: #333;
}
.no-images {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}
.sort-hint {
    font-size: 12px;
    color: #999;
    margin-bottom: 10px;
}
</style>

<div class="image-manager-container">
    <h2>Manage Images for: <?php echo htmlspecialchars($row->ProductName); ?></h2>
    <p style="color: #666; margin-bottom: 20px;">Product ID: <?php echo $productId; ?></p>
    
    <!-- 添加新图片 -->
    <div class="add-image-section">
        <h3>Add New Image</h3>
        <form class="upload-form" id="uploadForm" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <div class="form-group">
                <label>Image Type</label>
                <select name="image_type" id="imageType">
                    <option value="full">Full Size</option>
                    <option value="thumbnail">Thumbnail</option>
                </select>
            </div>
            <div class="form-group" style="flex: 2;">
                <label>Select Image File</label>
                <input type="file" name="image_file" id="imageFile" accept="image/*" required>
            </div>
            <button type="submit">Upload</button>
        </form>
    </div>
    
    <!-- 图片列表 -->
    <h3>Product Images</h3>
    <p class="sort-hint">💡 Drag and drop to reorder images. The first image will be used as the primary image.</p>
    
    <div id="imageList" class="image-grid">
        <!-- Images will be loaded here via JavaScript -->
        <div class="no-images">Loading images...</div>
    </div>
</div>

<script>
const productId = <?php echo $productId; ?>;

// Load images on page load
document.addEventListener('DOMContentLoaded', loadImages);

// Handle form submission
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const imageFile = document.getElementById('imageFile').files[0];
    if (!imageFile) {
        alert('Please select an image file');
        return;
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('image_type', document.getElementById('imageType').value);
    formData.append('image_file', imageFile);
    
    try {
        const uploadResponse = await fetch('/api/upload_product_image.php', {
            method: 'POST',
            body: formData
        });
        const uploadResult = await uploadResponse.json();
        
        if (uploadResult.status === 'success') {
            document.getElementById('uploadForm').reset();
            loadImages();
        } else {
            alert('Upload failed: ' + uploadResult.message);
        }
    } catch (err) {
        alert('Upload error: ' + err.message);
    }
});

// Load images from API
async function loadImages() {
    try {
        const response = await fetch(`/api/product_images.php?action=list&product_id=${productId}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            renderImages(result.images);
        }
    } catch (err) {
        console.error('Failed to load images:', err);
        document.getElementById('imageList').innerHTML = '<div class="no-images">Failed to load images</div>';
    }
}

// Render images
function renderImages(images) {
    const container = document.getElementById('imageList');
    
    if (images.length === 0) {
        container.innerHTML = '<div class="no-images">No images found. Add your first image above.</div>';
        return;
    }
    
    container.innerHTML = images.map(img => `
        <div class="image-card ${img.is_primary ? 'primary' : ''}" data-id="${img.id}">
            ${img.is_primary ? '<span class="primary-badge">PRIMARY</span>' : ''}
            <img src="${img.image_url}" onerror="this.style.display='none'; this.parentElement.innerHTML='<div style=\'width:100%; height:200px; display:flex; align-items:center; justify-content:center; background:#f5f5f5; color:#999;\'>Image Not Found</div>'" alt="${img.image_name}">
            <div class="image-info">
                <strong>${img.image_name}</strong><br>
                Type: ${img.image_type} | Order: ${img.sort_order}
            </div>
            <div class="image-actions">
                ${!img.is_primary ? `<button class="btn-primary" onclick="setPrimary(${img.id})">Set Primary</button>` : ''}
                <button class="btn-danger" onclick="deleteImage(${img.id})">Delete</button>
            </div>
        </div>
    `).join('');
}

// Add image to database
async function addImageToDatabase(filename, imageType) {
    try {
        const response = await fetch(`/api/product_images.php?action=add&product_id=${productId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                image_name: filename,
                image_type: imageType,
                sort_order: 999
            })
        });
        
        const result = await response.json();
        if (result.status === 'success') {
            document.getElementById('uploadForm').reset();
            loadImages();
        } else {
            alert('Failed to add image: ' + result.message);
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

// Set primary image
async function setPrimary(imageId) {
    try {
        const response = await fetch(`/api/product_images.php?action=set_primary&product_id=${productId}&image_id=${imageId}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            loadImages();
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

// Delete image
async function deleteImage(imageId) {
    if (!confirm('Are you sure you want to delete this image?')) return;
    
    try {
        const response = await fetch(`/api/product_images.php?action=delete&image_id=${imageId}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            loadImages();
        } else {
            alert('Failed to delete: ' + result.message);
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
}
</script>

<?php $Admin->showAdminFooter(); ?>
