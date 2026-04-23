<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>EditX Studio | Portal</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- AOS Animation -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link href="style/portal.css?v=<?php echo filemtime('style/portal.css'); ?>" rel="stylesheet">
  <style>
    /* Debug style - remove this when CSS loads properly */
    .debug-indicator {
      background: red !important;
      color: white !important;
      padding: 5px;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 9999;
    }
  </style>
</head>
<body>
  <!-- Floating Background Shapes -->
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <div class="main-container">
    <!-- Top Right Login Button -->
    <div class="top-login-btn" data-aos="fade-left">
      <a href="login.php" class="login-btn">
        <div class="sign">
          <i class="fas fa-lock"></i>
        </div>
        <div class="text">Admin_Login</div>
      </a>
    </div>

    <!-- Brand Header -->
    <div class="brand-header" data-aos="fade-down">
      <div class="brand-logo">
        <i class="fa fa-clapperboard"></i>
      </div>
      <h1 class="brand-title">EditX Studio</h1>
      <p class="brand-subtitle">Photo & Video Editing Services</p>
    </div>

    <!-- Client Portal Only -->
    <div class="client-portal-section">
      <div class="portal-card client" data-aos="fade-up">
        <div class="portal-icon-wrapper">
          <i class="fas fa-user portal-icon"></i>
        </div>
        
        <h2 class="portal-title">Welcome to EditX Studio</h2>
        <p class="portal-description">
          Transform your creative vision into stunning reality with our professional photo and video editing services
        </p>
        
        <div class="features-grid">
          <div class="feature-item">
            <i class="fas fa-camera"></i>
            <div>
              <h4>Photo Editing</h4>
              <p>Professional retouching, color grading, and enhancement</p>
            </div>
          </div>
          <div class="feature-item">
            <i class="fas fa-video"></i>
            <div>
              <h4>Video Editing</h4>
              <p>Cinematic cuts, transitions, and post-production</p>
            </div>
          </div>
          <div class="feature-item">
            <i class="fas fa-mobile-alt"></i>
            <div>
              <h4>Reels & Shorts</h4>
              <p>Instagram and YouTube content</p>
            </div>
          </div>
          <div class="feature-item">
            <i class="fas fa-heart"></i>
            <div>
              <h4>Wedding Highlights</h4>
              <p>Teasers, trailers, and full wedding edits</p>
            </div>
          </div>
        </div>
        
        <a href="Home.php" class="portal-btn" aria-label="Go to Home page and explore services">
          <span class="portal-btn-main">
            <i class="fas fa-rocket"></i>
            Explore Our Services
          </span>
          <span class="portal-btn-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>
        <p class="portal-btn-hint">Tap to continue to the Home page</p>
        
        <div class="trust-badges">
          <div class="badge-item">
            <i class="fas fa-star"></i>
            <span>100+ Projects</span>
          </div>
          <div class="badge-item">
            <i class="fas fa-smile"></i>
            <span>98% Satisfaction</span>
          </div>
          <div class="badge-item">
            <i class="fas fa-shield-alt"></i>
            <span>Secure Payment</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Information -->
    <div class="footer-info" data-aos="fade-up" data-aos-delay="300">
      <p> 2024 EditX Studio | Creative Excellence Delivered</p>
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

    // Add interactive hover effects
    document.querySelectorAll('.portal-card').forEach(card => {
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
      });
    });

    // Add ripple effect to buttons
    document.querySelectorAll('.portal-btn').forEach(btn => {
      // Ripple effect removed to prevent size issues
    });

    // Dynamic stats animation
    function animateStats() {
      const stats = document.querySelectorAll('.stat-number');
      stats.forEach(stat => {
        const target = stat.innerText;
        const isPercentage = target.includes('%');
        const isTime = target.includes('h');
        const isPlus = target.includes('+');
        
        let current = 0;
        const increment = isPercentage ? 1 : isTime ? 1 : 5;
        const max = parseInt(target.replace(/\D/g, ''));
        
        const timer = setInterval(() => {
          current += increment;
          if (current >= max) {
            current = max;
            clearInterval(timer);
          }
          
          if (isPercentage) {
            stat.innerText = current + '%';
          } else if (isTime) {
            stat.innerText = current + 'h';
          } else if (isPlus) {
            stat.innerText = current + '+';
          } else {
            stat.innerText = current;
          }
        }, 50);
      });
    }

    // Trigger stats animation when page loads
    window.addEventListener('load', animateStats);
  </script>
</body>
</html>