<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'editing';
$username = 'root';
$password = '';

// Create database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_gallery':
                $title = trim($_POST['title']);
                $type = $_POST['type'];
                
                if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
                    $uploadDir = 'uploads/' . ($type == 'video' ? 'videos' : 'photos') . '/';
                    
                    // Create directory if not exists
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileName = time() . '_' . basename($_FILES['media']['name']);
                    $targetFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['media']['tmp_name'], $targetFile)) {
                        // Add to gallery.json
                        $galleryFile = 'gallery.json';
                        $gallery = [];
                        if (file_exists($galleryFile)) {
                            $gallery = json_decode(file_get_contents($galleryFile), true) ?: [];
                        }
                        
                        $newItem = [
                            'id' => time(),
                            'title' => $title,
                            'type' => $type,
                            'url' => $targetFile
                        ];
                        
                        $gallery[] = $newItem;
                        file_put_contents($galleryFile, json_encode($gallery, JSON_PRETTY_PRINT));
                        
                        $successMsg = "Gallery item added successfully!";
                    } else {
                        $errorMsg = "Failed to upload file.";
                    }
                } else {
                    $errorMsg = "Please select a file to upload.";
                }
                break;
                
            case 'delete_gallery':
                $id = $_POST['id'];
                $galleryFile = 'gallery.json';
                $gallery = [];
                
                if (file_exists($galleryFile)) {
                    $gallery = json_decode(file_get_contents($galleryFile), true) ?: [];
                }
                
                $gallery = array_filter($gallery, function($item) use ($id) {
                    return $item['id'] != $id;
                });
                
                file_put_contents($galleryFile, json_encode(array_values($gallery), JSON_PRETTY_PRINT));
                $successMsg = "Gallery item deleted successfully!";
                break;
        }
    }
}

// Load gallery items
$galleryFile = 'gallery.json';
$gallery = [];
if (file_exists($galleryFile)) {
    $gallery = json_decode(file_get_contents($galleryFile), true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - EditX Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .delete-btn:hover {
            background: rgba(220, 53, 69, 1);
        }
        .media-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-camera"></i> Manage Gallery</h2>
                <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>

            <?php if (isset($successMsg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $successMsg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMsg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $errorMsg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Add Gallery Item Form -->
            <div class="row mb-5">
                <div class="col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Add New Gallery Item</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="add_gallery">
                                
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="photo">Photo</option>
                                        <option value="video">Video</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="media" class="form-label">Upload File</label>
                                    <input type="file" class="form-control" id="media" name="media" accept="image/*,video/*" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add to Gallery
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery Items -->
            <div class="row">
                <?php if (empty($gallery)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No gallery items found. Add your first item above!
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($gallery as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="gallery-item">
                                <?php if ($item['type'] == 'video'): ?>
                                    <video class="media-preview" controls>
                                        <source src="<?php echo htmlspecialchars($item['url']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($item['url']); ?>" class="media-preview" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php endif; ?>
                                
                                <div class="p-3">
                                    <h6><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <small class="text-muted">Type: <?php echo ucfirst($item['type']); ?></small>
                                </div>
                                
                                <form method="POST" style="position: absolute; top: 10px; right: 10px;">
                                    <input type="hidden" name="action" value="delete_gallery">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
