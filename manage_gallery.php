<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

$filename = 'gallery.json';

// Create upload folders if not exists
$photoUploadDir = __DIR__ . '/uploads/photos/';
$videoUploadDir = __DIR__ . '/uploads/videos/';

if (!is_dir($photoUploadDir)) mkdir($photoUploadDir, 0755, true);
if (!is_dir($videoUploadDir)) mkdir($videoUploadDir, 0755, true);

// Load existing gallery data
$galleryItems = [];
if (file_exists($filename)) {
    $galleryItems = json_decode(file_get_contents($filename), true) ?: [];
}

$galleryCount = count($galleryItems);

function save_gallery($items, $filename) {
    // Ensure directory exists and is writable
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $json = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $result = file_put_contents($filename, $json, LOCK_EX);
    
    // Set proper permissions for hosting
    if ($result !== false) {
        chmod($filename, 0644);
    }
    
    return $result !== false;
}

// Sanitize filename (replace unsafe chars)
function sanitize_filename($filename) {
    return preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $filename);
}

function normalize_video_categories($categories) {
  if (is_array($categories)) {
    return array_values(array_filter(array_map('trim', $categories), function ($category) {
      return $category !== '';
    }));
  }

  $categories = trim((string) $categories);
  return $categories === '' ? [] : [$categories];
}

function format_video_categories($categories) {
  $categories = normalize_video_categories($categories);

  if (empty($categories)) {
    return 'General';
  }

  return implode(', ', array_map(function ($category) {
    return ucwords(str_replace('_', ' ', $category));
  }, $categories));
}

// Handle adding new gallery item
if (isset($_POST['add'])) {
    $newId = 1;
    if (!empty($galleryItems)) {
        $ids = array_column($galleryItems, 'id');
        $newId = max($ids) + 1;
    }

    $type = $_POST['mediaType'] ?? 'photo';
    $title = trim($_POST['title'] ?? '');
    $uploadPath = '';
    $errorMessage = '';
    $videoCategories = [];

    // Validate title
    if (empty($title)) {
        $errorMessage = "Title is required!";
    }

    // Handle file upload
    if ($type === 'photo') {
        if (!isset($_FILES['photoFile']) || $_FILES['photoFile']['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = "Please select a photo file to upload!";
        } else {
            $file = $_FILES['photoFile'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                $errorMessage = "Invalid file type! Only JPG, PNG, GIF, and WebP images are allowed.";
            }
            // Validate file size (10MB max)
            elseif ($file['size'] > 10 * 1024 * 1024) {
                $errorMessage = "File too large! Maximum size is 10MB.";
            } else {
                $tmpName = $file['tmp_name'];
                $fileName = sanitize_filename($file['name']);
                $targetPath = $photoUploadDir . $fileName;
                
                if (file_exists($targetPath)) {
                    $fileName = time() . '_' . $fileName;
                    $targetPath = $photoUploadDir . $fileName;
                }
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $uploadPath = 'uploads/photos/' . $fileName;
                } else {
                    $errorMessage = "Failed to upload file! Check folder permissions.";
                }
            }
        }
    } elseif ($type === 'video') {
      $videoCategories = normalize_video_categories($_POST['videoCategory'] ?? []);
      if (empty($videoCategories)) {
        $errorMessage = "Please select at least one video type!";
      }

        if (!isset($_FILES['videoFile']) || $_FILES['videoFile']['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = "Please select a video file to upload!";
        } else {
            $file = $_FILES['videoFile'];
            
            // Validate file type
            $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
            if (!in_array($file['type'], $allowedTypes)) {
                $errorMessage = "Invalid file type! Only MP4, WebM, and OGG videos are allowed.";
            }
            // Validate file size (50MB max)
            elseif ($file['size'] > 50 * 1024 * 1024) {
                $errorMessage = "File too large! Maximum size is 50MB.";
            } else {
                $tmpName = $file['tmp_name'];
                $fileName = sanitize_filename($file['name']);
                $targetPath = $videoUploadDir . $fileName;
                
                if (file_exists($targetPath)) {
                    $fileName = time() . '_' . $fileName;
                    $targetPath = $videoUploadDir . $fileName;
                }
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $uploadPath = 'uploads/videos/' . $fileName;
                } else {
                    $errorMessage = "Failed to upload file! Check folder permissions.";
                }
            }
        }
    }

    // Save new entry if upload was successful
    if ($uploadPath && empty($errorMessage)) {
        $galleryItems[] = [
            'id' => $newId,
            'title' => $title,
            'type' => $type,
            'url' => $uploadPath,
        'videoCategory' => $type === 'video' ? $videoCategories : [],
        ];
        
        if (save_gallery($galleryItems, $filename)) {
            $_SESSION['gallery_message'] = "✅ Gallery item added successfully!";
            $_SESSION['gallery_message_type'] = 'success';
        } else {
            $_SESSION['gallery_message'] = "❌ Failed to save gallery data!";
            $_SESSION['gallery_message_type'] = 'danger';
        }
    } else {
        $_SESSION['gallery_message'] = $errorMessage ?: "❌ Upload failed!";
        $_SESSION['gallery_message_type'] = 'danger';
    }

    header("Location: manage_gallery.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $idToDelete = (int)$_GET['delete'];
    $deleted = false;
    
    foreach ($galleryItems as $idx => $item) {
        if ($item['id'] === $idToDelete) {
            // Delete file on server if exists
            $filePath = __DIR__ . '/' . $item['url'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            unset($galleryItems[$idx]);
            $deleted = true;
            break;
        }
    }
    
    if ($deleted) {
        $galleryItems = array_values($galleryItems);
        if (save_gallery($galleryItems, $filename)) {
            $_SESSION['gallery_message'] = "✅ Gallery item deleted successfully!";
            $_SESSION['gallery_message_type'] = 'success';
        } else {
            $_SESSION['gallery_message'] = "❌ Failed to update gallery data!";
            $_SESSION['gallery_message_type'] = 'danger';
        }
    } else {
        $_SESSION['gallery_message'] = "❌ Item not found!";
        $_SESSION['gallery_message_type'] = 'danger';
    }
    
    header("Location: manage_gallery.php");
    exit;
}

// Handle edit request
$editingItem = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($galleryItems as $item) {
        if ($item['id'] === $editId) {
            $editingItem = $item;
            break;
        }
    }
}

// Handle update
if (isset($_POST['update'])) {
    $editId = (int)($_POST['edit_id'] ?? 0);
    $type = $_POST['mediaType'] ?? 'photo';
    $title = trim($_POST['title'] ?? ''); 
    $errorMessage = '';
    $videoCategories = [];
    
    // Validate title
    if (empty($title)) {
        $errorMessage = "Title is required!";
    }
    
    // Find item to update
    $itemToUpdate = null;
    foreach ($galleryItems as $idx => $item) {
        if ($item['id'] === $editId) {
            $itemToUpdate = $item;
            break;
        }
    }
    
    if ($itemToUpdate) {
        $uploadPath = $itemToUpdate['url']; // Keep existing URL by default
      $videoCategories = normalize_video_categories($itemToUpdate['videoCategory'] ?? []);

        // Handle file upload if new file is provided
        if ($type === 'photo' && isset($_FILES['photoFile']) && $_FILES['photoFile']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photoFile'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                $errorMessage = "Invalid file type! Only JPG, PNG, GIF, and WebP images are allowed.";
            }
            // Validate file size (10MB max)
            elseif ($file['size'] > 10 * 1024 * 1024) {
                $errorMessage = "File too large! Maximum size is 10MB.";
            } else {
                $tmpName = $file['tmp_name'];
                $fileName = sanitize_filename($file['name']);
                $targetPath = $photoUploadDir . $fileName;
                
                if (file_exists($targetPath)) {
                    $fileName = time() . '_' . $fileName;
                    $targetPath = $photoUploadDir . $fileName;
                }
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Delete old file
                    $oldFilePath = __DIR__ . '/' . $itemToUpdate['url'];
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                    $uploadPath = 'uploads/photos/' . $fileName;
                } else {
                    $errorMessage = "Failed to upload file! Check folder permissions.";
                }
            }
        } elseif ($type === 'video') {
          $videoCategories = normalize_video_categories($_POST['videoCategory'] ?? []);
          if (empty($videoCategories)) {
            $errorMessage = "Please select at least one video type!";
          }

          if (empty($errorMessage) && isset($_FILES['videoFile']) && $_FILES['videoFile']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['videoFile'];
            
            // Validate file type
            $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
            if (!in_array($file['type'], $allowedTypes)) {
                $errorMessage = "Invalid file type! Only MP4, WebM, and OGG videos are allowed.";
            }
            // Validate file size (50MB max)
            elseif ($file['size'] > 50 * 1024 * 1024) {
                $errorMessage = "File too large! Maximum size is 50MB.";
            } else {
                $tmpName = $file['tmp_name'];
                $fileName = sanitize_filename($file['name']);
                $targetPath = $videoUploadDir . $fileName;
                
                if (file_exists($targetPath)) {
                    $fileName = time() . '_' . $fileName;
                    $targetPath = $videoUploadDir . $fileName;
                }
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Delete old file
                    $oldFilePath = __DIR__ . '/' . $itemToUpdate['url'];
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                    $uploadPath = 'uploads/videos/' . $fileName;
                } else {
                    $errorMessage = "Failed to upload file! Check folder permissions.";
                }
            }
        }
            }

        // Update the item if no errors
        if (empty($errorMessage)) {
            foreach ($galleryItems as $idx => $item) {
                if ($item['id'] === $editId) {
                    $galleryItems[$idx] = [
                        'id' => $editId,
                        'title' => $title,
                        'type' => $type,
                        'url' => $uploadPath,
                    'videoCategory' => $type === 'video' ? $videoCategories : [],
                    ];
                    break;
                }
            }
            
            if (save_gallery($galleryItems, $filename)) {
                $_SESSION['gallery_message'] = "✅ Gallery item updated successfully!";
                $_SESSION['gallery_message_type'] = 'success';
            } else {
                $_SESSION['gallery_message'] = "❌ Failed to update gallery data!";
                $_SESSION['gallery_message_type'] = 'danger';
            }
        } else {
            $_SESSION['gallery_message'] = $errorMessage;
            $_SESSION['gallery_message_type'] = 'danger';
        }
    } else {
        $_SESSION['gallery_message'] = "❌ Item not found!";
        $_SESSION['gallery_message_type'] = 'danger';
    }
    
    header("Location: manage_gallery.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Manage Gallery - EditX Studio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
  /* Portal-style animated background - darker colors like Home.php */
  html {
    scroll-behavior: smooth;
  }
  
  body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(-45deg, #ee7752, #e73c7e, #2a6f88ff, #23d5ab);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    min-height: 100vh;
    overflow-x: hidden;
  }

  @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* Floating shapes with different colors */
  .floating-shapes {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
  }

  .floating-shapes .shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(5px);
    animation: float 20s infinite ease-in-out;
  }

  .floating-shapes .shape:nth-child(1) {
    width: 80px;
    height: 80px;
    top: 20%;
    left: 10%;
    animation-delay: 0s;
    background: rgba(238, 119, 82, 0.2); /* Orange from Home.php */
  }

  .floating-shapes .shape:nth-child(2) {
    width: 120px;
    height: 120px;
    top: 60%;
    right: 10%;
    animation-delay: 5s;
    background: rgba(231, 60, 126, 0.2); /* Pink from Home.php */
  }

  .floating-shapes .shape:nth-child(3) {
    width: 60px;
    height: 60px;
    bottom: 20%;
    left: 50%;
    animation-delay: 10s;
    background: rgba(42, 111, 136, 0.2); /* Teal from Home.php */
  }

  .floating-shapes .shape:nth-child(4) {
    width: 100px;
    height: 100px;
    top: 40%;
    right: 30%;
    animation-delay: 15s;
    background: rgba(35, 213, 171, 0.2); /* Green from Home.php */
  }

  @keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    33% { transform: translateY(-30px) rotate(120deg); }
    66% { transform: translateY(30px) rotate(240deg); }
  }

  .navbar-brand {
    font-weight: bold;
  }

  .manage-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    background-size: 400% 400%;
    animation: formGradientShift 15s ease infinite;
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1), inset 0 0 30px rgba(138, 43, 226, 0.1);
    border: 1px solid rgba(138, 43, 226, 0.3);
    max-width: 1200px;
    margin: 40px auto;
    position: relative;
    overflow: hidden;
    color: white;
  }

  .manage-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 25%, #6366f1 50%, #8b5cf6 75%, #6366f1 100%);
    animation: borderGlow 3s ease-in-out infinite;
  }

  @keyframes borderGlow {
    0%, 100% { opacity: 0.8; }
    50% { opacity: 1; }
  }

  @keyframes formGradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  .manage-section h1 {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 30px;
    color: #ffffff;
    text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
  }

  .manage-section h2 {
    font-size: 1.8rem;
    font-weight: 600;
    text-align: center;
    margin: 40px 0 20px 0;
    color: #ffffff;
    text-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
  }

  .form-control{
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white !important;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    animation: floatForm 6s ease-in-out infinite;
  }

  @keyframes floatForm {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
  }

  .form-control:focus{
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.3);
    outline: none;
    animation: none;
  }

  .form-label{
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 14px;
    animation: floatLabel 4s ease-in-out infinite;
  }

  @keyframes floatLabel {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-3px); }
  }

  .btn-primary {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
  }

  .btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  }

  .btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
  }

  /* Table styles */
  .table-container {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    color: white;
  }

  thead tr th {
    background: rgba(99, 102, 241, 0.3);
    color: #fff;
    font-weight: 700;
    padding: 15px;
    text-align: left;
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  thead tr th:first-child {
    border-radius: 10px 0 0 10px;
  }

  thead tr th:last-child {
    border-radius: 0 10px 10px 0;
  }

  tbody tr {
    background: rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
  }

  tbody tr:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
  }

  tbody tr td {
    padding: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    vertical-align: middle;
  }

  tbody tr:last-child td {
    border-bottom: none;
  }

  .thumbnail {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid rgba(255, 255, 255, 0.2);
  }

  .thumbnail-video {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid rgba(255, 255, 255, 0.2);
  }

  .btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border: none;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    margin-right: 5px;
  }

  .btn-delete:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
    color: white;
    text-decoration: none;
  }

  .btn-edit {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border: none;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }

  .btn-edit:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
    color: white;
    text-decoration: none;
  }

  .no-items {
    text-align: center;
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.2rem;
    padding: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .form-text {
    color: rgba(255, 255, 255, 0.7);
    font-size: 12px;
    margin-top: -5px;
    margin-bottom: 15px;
  }

  /* Alert Styles */
  .alert {
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    margin-bottom: 20px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
  }

  .alert-success {
    background: rgba(40, 167, 69, 0.3);
    border-color: rgba(40, 167, 69, 0.5);
  }

  .alert-danger {
    background: rgba(220, 53, 69, 0.3);
    border-color: rgba(220, 53, 69, 0.5);
  }

  .alert .btn-close {
    filter: brightness(1.5);
  }

  /* Video Category Styles */
  .video-category-options {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 15px;
    margin-top: 10px;
  }

  .form-check {
    margin-bottom: 10px;
  }

  .form-check-input {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    margin-right: 8px;
  }

  .form-check-input:checked {
    background-color: #6366f1;
    border-color: #6366f1;
  }

  .form-check-label {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
    font-size: 13px;
  }

  /* Filter Tabs */
  .filter-tabs {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .filter-btn {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.8);
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .filter-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    color: white;
  }

  .filter-btn.active {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
  }

  .filter-btn i {
    font-size: 12px;
  }

  /* Hidden class for filtering */
  .hidden {
    display: none !important;
  }

  /* Responsive Design */
  @media (max-width: 1200px) {
    .manage-section {
      max-width: 1000px;
      margin: 30px auto;
      padding: 25px;
    }
    .manage-section h1 {
      font-size: 2.2rem;
    }
    .thumbnail, .thumbnail-video {
      width: 70px;
      height: 50px;
    }
  }

  @media (max-width: 991px) {
    .manage-section {
      max-width: 900px;
      margin: 25px auto;
      padding: 20px;
    }
    .manage-section h1 {
      font-size: 2rem;
    }
    .manage-section h2 {
      font-size: 1.6rem;
    }
    .btn-primary, .btn-secondary {
      padding: 10px 25px;
      font-size: 15px;
    }
    .thumbnail, .thumbnail-video {
      width: 65px;
      height: 48px;
    }
  }

  @media (max-width: 768px) {
    .manage-section {
      margin: 20px;
      padding: 20px;
    }
    .manage-section h1 {
      font-size: 1.8rem;
      margin-bottom: 25px;
    }
    .manage-section h2 {
      font-size: 1.4rem;
      margin: 30px 0 15px 0;
    }
    .form-control {
      padding: 10px 12px;
      font-size: 14px;
    }
    .form-label {
      font-size: 13px;
    }
    .btn-primary, .btn-secondary {
      padding: 10px 20px;
      font-size: 14px;
      margin: 3px;
    }
    .thumbnail, .thumbnail-video {
      width: 60px;
      height: 45px;
    }
    .btn-edit, .btn-delete {
      padding: 6px 12px;
      font-size: 12px;
    }
  }

  @media (max-width: 576px) {
    .manage-section {
      margin: 15px;
      padding: 15px;
    }
    .manage-section h1 {
      font-size: 1.6rem;
      margin-bottom: 20px;
    }
    .manage-section h2 {
      font-size: 1.3rem;
      margin: 25px 0 15px 0;
    }
    .form-control {
      padding: 8px 10px;
      font-size: 13px;
    }
    .form-label {
      font-size: 12px;
      margin-bottom: 6px;
    }
    .btn-primary, .btn-secondary {
      padding: 8px 15px;
      font-size: 13px;
      margin: 2px;
      display: block;
      width: 100%;
      margin-bottom: 8px;
    }
    .table-container {
      padding: 10px;
    }
    table, thead, tbody, th, td, tr {
      display: block;
    }
    thead tr {
      display: none;
    }
    tbody tr {
      margin-bottom: 20px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 10px;
      padding: 15px;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    tbody tr:hover {
      background: rgba(255, 255, 255, 0.08);
      transform: none;
    }
    tbody tr td {
      padding: 8px 0;
      border: none;
      position: relative;
      padding-left: 100px;
      min-height: 40px;
    }
    tbody tr td::before {
      position: absolute;
      left: 10px;
      top: 8px;
      font-weight: 600;
      color: #8b5cf6;
      white-space: nowrap;
      font-size: 11px;
    }
    tbody tr td:nth-of-type(1)::before { content: "#"; }
    tbody tr td:nth-of-type(2)::before { content: "Title"; }
    tbody tr td:nth-of-type(3)::before { content: "Type"; }
    tbody tr td:nth-of-type(4)::before { content: "Preview"; }
    tbody tr td:nth-of-type(5)::before { content: "Actions"; }
    .thumbnail, .thumbnail-video {
      position: absolute;
      right: 10px;
      top: 8px;
      transform: none;
      width: 50px;
      height: 35px;
    }
    tbody tr td:nth-of-type(5) {
      padding-right: 10px;
      padding-left: 100px;
      min-height: 50px;
    }
    .btn-edit, .btn-delete {
      padding: 5px 10px;
      font-size: 11px;
      margin: 2px;
    }
    .badge {
      font-size: 9px;
      padding: 3px 8px;
    }
  }

  @media (max-width: 400px) {
    .manage-section {
      margin: 10px;
      padding: 12px;
    }
    .manage-section h1 {
      font-size: 1.4rem;
      margin-bottom: 15px;
    }
    .manage-section h2 {
      font-size: 1.2rem;
      margin: 20px 0 12px 0;
    }
    .form-control {
      padding: 6px 8px;
      font-size: 12px;
    }
    .form-label {
      font-size: 11px;
      margin-bottom: 4px;
    }
    .btn-primary, .btn-secondary {
      padding: 6px 12px;
      font-size: 12px;
    }
    .form-text {
      font-size: 10px;
    }
    tbody tr {
      padding: 10px;
      margin-bottom: 15px;
    }
    tbody tr td {
      padding-left: 85px;
      padding-top: 6px;
      padding-bottom: 6px;
    }
    tbody tr td::before {
      left: 8px;
      font-size: 10px;
      top: 6px;
    }
    .thumbnail, .thumbnail-video {
      width: 45px;
      height: 32px;
      right: 8px;
      top: 6px;
    }
    tbody tr td:nth-of-type(5) {
      padding-left: 85px;
      min-height: 40px;
    }
    .btn-edit, .btn-delete {
      padding: 4px 8px;
      font-size: 10px;
    }
    .badge {
      font-size: 8px;
      padding: 2px 6px;
    }
  }
</style>
<script>
  function toggleInputFields() {
    var type = document.getElementById('mediaType').value;
    document.getElementById('photoInput').style.display = (type === 'photo') ? 'block' : 'none';
    document.getElementById('photoFile').required = (type === 'photo');
    document.getElementById('videoInput').style.display = (type === 'video') ? 'block' : 'none';
    document.getElementById('videoFile').required = (type === 'video');
  }

  function showItems(filter) {
    // Remove active class from all buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
    
    // Filter table rows
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
      if (filter === 'all') {
        row.classList.remove('hidden');
      } else {
        const itemType = row.getAttribute('data-type');
        if (itemType === filter) {
          row.classList.remove('hidden');
        } else {
          row.classList.add('hidden');
        }
      }
    });
  }

  window.onload = function() {
    toggleInputFields();
  }
</script>
</head>
<body>

<!-- Floating Background Shapes -->
<div class="floating-shapes">
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
</div>

<!-- Manage Gallery Section -->
<section class="manage-section">
  <h1><i class="fas fa-images" style="color:#ffffff;"></i> Manage Gallery</h1>
  
  <!-- Message Alert -->
  <?php if (isset($_SESSION['gallery_message'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['gallery_message_type']); ?> alert-dismissible fade show" role="alert">
      <?php 
        echo htmlspecialchars($_SESSION['gallery_message']);
        unset($_SESSION['gallery_message']);
        unset($_SESSION['gallery_message_type']);
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <p class="text-center mb-4" style="color: rgba(255,255,255,0.8);">
    <?php echo $editingItem ? 'Edit existing item' : 'Upload and manage your photos and videos'; ?>
  </p>

  <form method="POST" action="manage_gallery.php" enctype="multipart/form-data" novalidate>
    <?php if ($editingItem): ?>
      <input type="hidden" name="update" value="1">
      <input type="hidden" name="edit_id" value="<?php echo $editingItem['id']; ?>">
    <?php endif; ?>
    
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input id="title" name="title" type="text" class="form-control"
             value="<?php echo $editingItem ? htmlspecialchars($editingItem['title']) : ''; ?>" required />
    </div>

    <div class="mb-3">
      <label for="mediaType" class="form-label">Media Type</label>
      <select id="mediaType" name="mediaType" class="form-control" onchange="toggleInputFields()" required>
        <option value="photo" <?php echo ($editingItem && $editingItem['type'] === 'photo') ? 'selected' : ''; ?>>Photo</option>
        <option value="video" <?php echo ($editingItem && $editingItem['type'] === 'video') ? 'selected' : ''; ?>>Video</option>
      </select>
    </div>

    <div id="photoInput" class="mb-3" style="<?php echo ($editingItem && $editingItem['type'] === 'video') ? 'display:none;' : ''; ?>">
      <label for="photoFile" class="form-label">
        <?php echo $editingItem ? 'Upload New Photo (optional)' : 'Upload Photo'; ?>
      </label>
      <input id="photoFile" name="photoFile" type="file" class="form-control" accept="image/*" 
             <?php echo !$editingItem ? 'required' : ''; ?> />
      <?php if ($editingItem && $editingItem['type'] === 'photo'): ?>
        <small class="form-text">Current: <?php echo htmlspecialchars($editingItem['url']); ?></small>
      <?php endif; ?>
    </div>

    <?php $editingVideoCategories = normalize_video_categories($editingItem['videoCategory'] ?? []); ?>
    <div id="videoInput" class="mb-3" style="<?php echo ($editingItem && $editingItem['type'] === 'photo') ? 'display:none;' : 'display:none;'; ?>">
      <label for="videoFile" class="form-label">
        <?php echo $editingItem ? 'Upload New Video (optional)' : 'Upload Video'; ?>
      </label>
      <input id="videoFile" name="videoFile" type="file" class="form-control" accept="video/*" 
             <?php echo !$editingItem ? 'required' : ''; ?> />
      
      <!-- Video Category Selection -->
      <div class="mt-3">
        <label class="form-label">Video Types</label>
        <div class="video-category-options">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="videoCategory[]" id="catBusiness" value="business" 
                   <?php echo in_array('business', $editingVideoCategories, true) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="catBusiness">Business / Professional</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="videoCategory[]" id="catCinematic" value="cinematic"
                   <?php echo in_array('cinematic', $editingVideoCategories, true) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="catCinematic">Cinematic Edits</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="videoCategory[]" id="catInsta" value="insta_reels"
                   <?php echo in_array('insta_reels', $editingVideoCategories, true) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="catInsta">Insta Reels</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="videoCategory[]" id="catMusic" value="music_based"
                   <?php echo in_array('music_based', $editingVideoCategories, true) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="catMusic">Music-Based Edits</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="videoCategory[]" id="catEvent" value="event_edits"
                   <?php echo in_array('event_edits', $editingVideoCategories, true) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="catEvent">Event Edits</label>
          </div>
        </div>
      </div>
      
      <small class="form-text">
        Upload mp4/webm files up to server limits.
        <?php if ($editingItem && $editingItem['type'] === 'video'): ?>
          <br>Current: <?php echo htmlspecialchars($editingItem['url']); ?>
          <br>Types: <?php echo htmlspecialchars(format_video_categories($editingItem['videoCategory'] ?? [])); ?>
        <?php endif; ?>
      </small>
    </div>

    <div class="text-center mb-4">
      <?php if ($editingItem): ?>
        <button type="submit" class="btn-primary me-2">
          <i class="fas fa-save"></i> Update Item
        </button>
        <button type="button" onclick="window.location.href='manage_gallery.php'" class="btn-secondary me-2">
          <i class="fas fa-times"></i> Cancel
        </button>
      <?php else: ?>
        <button type="submit" name="add" class="btn-primary me-2">
          <i class="fas fa-plus"></i> Add Item
        </button>
      <?php endif; ?>
      <button type="button" onclick="window.location.href='gallary.php'" class="btn-secondary me-2">
        <i class="fas fa-eye"></i> Preview
      </button>
      <button type="button" onclick="window.location.href='dashboard.php'" class="btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </button>
    </div>
  </form>

  <h2><i class="fas fa-list" style="color:#ffffff;"></i> Existing Items (Total: <?php echo $galleryCount; ?>)</h2>

  <!-- Filter Tabs -->
  <div class="filter-tabs mb-4">
    <button class="filter-btn active" onclick="showItems('all')" data-filter="all">
      <i class="fas fa-th"></i> All Items
    </button>
    <button class="filter-btn" onclick="showItems('photo')" data-filter="photo">
      <i class="fas fa-image"></i> Photos
    </button>
    <button class="filter-btn" onclick="showItems('video')" data-filter="video">
      <i class="fas fa-video"></i> Videos
    </button>
  </div>

  <div class="table-container">

    <?php if (!empty($galleryItems)): ?>
      <table>
        <thead>
          <tr>
            <th>No.</th>
            <th>Title</th>
            <th>Type</th>
            <th>Video Types</th>
            <th>Preview</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($galleryItems as $index => $item): ?>
            <tr data-type="<?php echo htmlspecialchars($item['type']); ?>">
              <td><?php echo $index + 1; ?></td>
              <td><?php echo htmlspecialchars($item['title']); ?></td>
              <td>
                <span class="badge" style="background: <?php echo $item['type'] === 'photo' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #f59e0b, #d97706)'; ?>; color: white; padding: 5px 10px; border-radius: 15px;">
                  <?php echo htmlspecialchars(ucfirst($item['type'])); ?>
                </span>
              </td>
              <td>
                <?php if ($item['type'] === 'video'): ?>
                  <span class="badge" style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; padding: 5px 10px; border-radius: 15px;">
                    <?php echo htmlspecialchars(format_video_categories($item['videoCategory'] ?? [])); ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($item['type'] === 'photo'): ?>
                  <img 
                    src="<?php echo htmlspecialchars($item['url']); ?>" 
                    alt="<?php echo htmlspecialchars($item['title']); ?>" 
                    class="thumbnail" 
                    loading="lazy"
                  />
                <?php elseif ($item['type'] === 'video'): ?>
                  <video class="thumbnail-video" controls preload="metadata">
                    <source src="<?php echo htmlspecialchars($item['url']); ?>" type="video/mp4" />
                    Your browser does not support the video tag.
                  </video>
                <?php endif; ?>
              </td>
              <td>
                <a href="manage_gallery.php?edit=<?php echo $item['id']; ?>" class="btn-edit">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <a href="manage_gallery.php?delete=<?php echo $item['id']; ?>" class="btn-delete" onclick="return confirm('Delete this item?');">
                  <i class="fas fa-trash"></i> Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="no-items">
        <i class="fas fa-images fa-3x mb-3"></i>
        <p>No gallery items found.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>