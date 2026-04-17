<?php
session_start();
require_once 'db-functions.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

// Load gallery item count from JSON file
$galleryFilename = 'gallery.json';
$galleryItems = [];
if (file_exists($galleryFilename)) {
    $galleryItems = json_decode(file_get_contents($galleryFilename), true) ?: [];
}
$galleryCount = count($galleryItems);

// Load offer count from JSON file
$offerFilename = 'offers.json';
$offers = [];
if (file_exists($offerFilename)) {
    $offers = json_decode(file_get_contents($offerFilename), true) ?: [];
}
$offerCount = count($offers);

// Get bookings count from JSON file
$bookingCount = getBookingCount();

// Get Contact Us count from JSON file
$contectUsCount = getContactMessageCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EditX Studio | Admin Dashboard</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- AOS Animation -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Custom Dashboard CSS -->
  <link href="style/dashboard.css?v=<?php echo filemtime('style/dashboard.css'); ?>" rel="stylesheet">
</head>
<body>
  <!-- Floating Background Shapes -->
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <div class="main-container">
    <div class="dashboard-header" data-aos="fade-down">
      <h1 class="dashboard-title">Admin Dashboard</h1>
      <a href="logout.php" class="logout-btn">
        <div class="sign">
          <svg viewBox="0 0 512 512">
            <path
              d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"
            ></path>
          </svg>
        </div>
        <div class="text">Logout</div>
      </a>
    </div>

    <div class="stats-grid">
      <div class="stats-card gallery" data-aos="fade-up" data-aos-delay="100">
        <div class="stats-number"><?php echo $galleryCount; ?></div>
        <div class="stats-label">Total Gallery Items</div>
      </div>

      <div class="stats-card offers" data-aos="fade-up" data-aos-delay="200">
        <div class="stats-number"><?php echo $offerCount; ?></div>
        <div class="stats-label">Offers available</div>
      </div>

      <div class="stats-card bookings" data-aos="fade-up" data-aos-delay="300">
        <div class="stats-number"><?php echo $bookingCount; ?></div>
        <div class="stats-label">Total Bookings</div>
      </div>

      <div class="stats-card contact" data-aos="fade-up" data-aos-delay="400">
        <div class="stats-number"><?php echo $contectUsCount; ?></div>
        <div class="stats-label">Total Contact Us</div>
      </div>
    </div>

    <div class="dashboard-links" data-aos="fade-up" data-aos-delay="500">
      <a href="manage_gallery.php" class="btn">
        <i class="fas fa-camera"></i> Manage Gallery
      </a>
      <a href="offer.php" class="btn">
        <i class="far fa-lightbulb"></i> Manage Offers
      </a>
      <a href="bookings_Total.php" class="btn">
        <i class="fa fa-receipt"></i> Bookings List
      </a>
      <a href="ContectUs_total.php" class="btn">
        <i class="fa fa-phone"></i> Contact Us List
      </a>
      <a href="manage_feedbacks.php" class="btn">
        <i class="fas fa-comments"></i> Manage Feedbacks
      </a>
      
      <a href="Home.php" class="btn">
        <i class="fas fa-home"></i> Homepage
      </a>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  
  <script>
    // Initialize AOS
    AOS.init({
      duration: 1000,
      once: true,
      offset: 100
    });
  </script>
</body>
</html>