<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

// Load offers from JSON
$filename = 'offers.json';
$offers = [];
if (file_exists($filename)) {
    $offers = json_decode(file_get_contents($filename), true) ?: [];
}

$offerCount = count($offers);

// Helper to save offers into JSON file
function save_offers($offers, $filename) {
    file_put_contents($filename, json_encode($offers, JSON_PRETTY_PRINT));
}

// Add new offer
if (isset($_POST['add'])) {
    $newId = 1;
    if (!empty($offers)) {
        $ids = array_column($offers, 'id');
        $newId = max($ids) + 1;
    }
    $newOffer = [
        'id' => $newId,
        'title' => $_POST['title'],
        'description' => $_POST['description']
    ];
    $offers[] = $newOffer;
    save_offers($offers, $filename);
    header("Location: offer.php");
    exit;
}

// Delete offer
if (isset($_GET['delete'])) {
    $idToDelete = (int)$_GET['delete'];
    $offers = array_filter($offers, fn($o) => $o['id'] !== $idToDelete);
    $offers = array_values($offers);
    save_offers($offers, $filename);
    header("Location: offer.php");
    exit;
}

// Edit offer
$editingOffer = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($offers as $offer) {
        if ($offer['id'] === $editId) {
            $editingOffer = $offer;
            break;
        }
    }
}

// Update offer
if (isset($_POST['update'])) {
    $idToUpdate = (int)$_POST['id'];
    foreach ($offers as &$offer) {
        if ($offer['id'] === $idToUpdate) {
            $offer['title'] = $_POST['title'];
            $offer['description'] = $_POST['description'];
            break;
        }
    }
    save_offers($offers, $filename);
    header("Location: offer.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Manage Offers - EditX Studio</title>
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
    backdrop-filter: blur(10px);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
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
    tbody tr td:nth-of-type(3)::before { content: "Description"; }
    tbody tr td:nth-of-type(4)::before { content: "Actions"; }
    .btn-edit, .btn-delete {
      padding: 5px 10px;
      font-size: 11px;
      margin: 2px;
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
    .btn-edit, .btn-delete {
      padding: 4px 8px;
      font-size: 10px;
    }
  }
</style>
</head>
<body>

<!-- Floating Background Shapes -->
<div class="floating-shapes">
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
</div>

<!-- Manage Offers Section -->
<section class="manage-section">
  <h1><i class="fas fa-tag" style="color:#ffffff;"></i> Manage Offers</h1>
  <p class="text-center mb-4" style="color: rgba(255,255,255,0.8);">
    <?php echo $editingOffer ? 'Edit existing offer' : 'Create and manage your special offers'; ?>
  </p>

  <form method="POST" action="offer.php" novalidate>
    <?php if ($editingOffer): ?>
      <input type="hidden" name="id" value="<?php echo $editingOffer['id']; ?>">
      <input type="hidden" name="update" value="1">
    <?php endif; ?>
    
    <div class="mb-3">
      <label for="title" class="form-label">Offer Title</label>
      <input id="title" name="title" type="text" class="form-control" 
             value="<?php echo $editingOffer ? htmlspecialchars($editingOffer['title']) : ''; ?>" required />
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Offer Description</label>
      <textarea id="description" name="description" class="form-control" rows="4" required><?php echo $editingOffer ? htmlspecialchars($editingOffer['description']) : ''; ?></textarea>
    </div>

    <div class="text-center mb-4">
      <?php if ($editingOffer): ?>
        <button type="submit" class="btn-primary me-2">
          <i class="fas fa-save"></i> Update Offer
        </button>
        <button type="button" onclick="window.location.href='offer.php'" class="btn-secondary me-2">
          <i class="fas fa-times"></i> Cancel
        </button>
      <?php else: ?>
        <button type="submit" name="add" class="btn-primary me-2">
          <i class="fas fa-plus"></i> Add Offer
        </button>
      <?php endif; ?>
      <button type="button" onclick="window.location.href='Home.php'" class="btn-secondary me-2">
        <i class="fas fa-eye"></i> Preview
      </button>
      <button type="button" onclick="window.location.href='dashboard.php'" class="btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </button>
    </div>
  </form>

  <h2><i class="fas fa-list" style="color:#ffffff;"></i> Existing Offers (Total: <?php echo $offerCount; ?>)</h2>

  <div class="table-container">

    <?php if (!empty($offers)): ?>
      <table>
        <thead>
          <tr>
            <th>No.</th>
            <th>Title</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $counter = 1; foreach ($offers as $offer): ?>
            <tr>
              <td><?php echo $counter; ?></td>
              <td><?php echo htmlspecialchars($offer['title']); ?></td>
              <td><?php echo htmlspecialchars($offer['description']); ?></td>
              <td>
                <a href="offer.php?edit=<?php echo $offer['id']; ?>" class="btn-edit">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <a href="offer.php?delete=<?php echo $offer['id']; ?>" class="btn-delete" onclick="return confirm('Delete this offer?');">
                  <i class="fas fa-trash"></i> Delete
                </a>
              </td>
            </tr>
            <?php $counter++; endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="no-items">
        <i class="fas fa-tag fa-3x mb-3"></i>
        <p>No offers found.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>