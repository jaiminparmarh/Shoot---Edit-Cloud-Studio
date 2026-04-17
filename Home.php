<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Editing Studio - Home</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="style/Home.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fa fa-clapperboard"></i> EditX Studio</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="Home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="gallary.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="book.php">Book</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="feedbacks.php">Feedbacks</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item ms-3"><a class="nav-link" href="index.php"><i class="fas fa-user"></i> Portal</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="main-container">
  <div class="left-hero">
    <div class="hero-content animate-item animate-up">
      <h1>Your Story, Our Creativity</h1>
      <p class="lead mt-3">Professional Photo & Video Editing Services</p>
      <a href="book.php" class="btn btn-pink mt-4"><i class="fas fa-envelope" style="color:#6366f1;"></i> Book Now</a>
    </div>
  </div>

  <div class="right-content">
    <div class="modern-section animate-item animate-up">
      <h2 class="section-title"><i class="fas fa-fire" style="color:#6366f1;"></i>Trending Offers</h2>
      <?php
        $offers = [];
        if (file_exists('offers.json')) {
            $json = file_get_contents('offers.json');
            $offers = json_decode($json, true) ?: [];
        }
      ?>
      <div class="row">
        <?php if (!empty($offers)): ?>
          <?php foreach ($offers as $offer): ?>
            <div class="col-md-4 mb-4">
              <div class="offer-card animate-item animate-up">
                <h5><?php echo htmlspecialchars($offer['title']); ?></h5>
                <p><?php echo htmlspecialchars($offer['description']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center">No trending offers right now.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="modern-section animate-item animate-up">
      <h2 class="section-title"><i class="fas fa-puzzle-piece" style="color:#6366f1;"></i> Our Services</h2>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="modern-card animate-item animate-up">
            <div class="service-icon">
              <i class="fa fa-camera"></i>
            </div>
            <h5>Photo Editing</h5>
            <p>
  Professional retouching, skin correction, and color grading.  
  Background enhancement and lighting adjustments.<br>
  Clean, natural, and high-end results.<br>
</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="modern-card animate-item animate-up">
            <div class="service-icon">
              <i class="fa fa-video"></i>
            </div>
            <h5>Video Editing</h5>
            <p>
  Cinematic cuts, smooth transitions, and creative storytelling.  
  Advanced color grading and sound balancing.<br>
  Engaging edits tailored to your vision.<br>
</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="modern-card animate-item animate-up">
            <div class="service-icon">
              <i class="fa fa-mobile"></i>
            </div>
            <h5>Reels & Shorts</h5>
            <p>
  High-impact vertical edits for Instagram & YouTube.  
  Trend-based transitions and dynamic captions.<br>
  Fast 24-hour delivery available.<br>
</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="modern-card animate-item animate-up">
            <div class="service-icon">
              <i class="fas fa-gem"></i>
            </div>
            <h5>Wedding Highlights</h5>
            <p>
  Emotional teasers, cinematic trailers, and highlight reels.  
  Perfect music sync and storytelling flow.<br>
  Capturing your special moments beautifully.<br>
</p>
          </div>
        </div>
      </div>
    </div>

    <div class="modern-section animate-item animate-up">
      <h2 class="section-title"><i class="fas fa-star" style="color:#6366f1;"></i> Why Choose Our Editing Studio?</h2>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="modern-card animate-item animate-up">
            <div class="service-icon">
              <i class="fa fa-bolt"></i>
            </div>
            <h5>Fast Delivery</h5>
            <p>24–48 hour turnaround (depending on order requirements). <br>
Quick revisions and on-time delivery guaranteed.<br>
Your deadlines matter — we respect them.</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="modern-card animate-item animate-up">
            <div class="service-icon">
              <i class="fa fa-star"></i>
            </div>
            <h5>Top Quality</h5>
            <p>
  Edited using professional tools and industry standards.  
  Smooth transitions, clean cuts, and perfect color grading.<br>
  Every project crafted with attention to detail.<br>
</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="modern-card animate-item animate-up">
            <div class="service-icon">
              <i class="fa fa-money-bill"></i>
            </div>
            <h5>Affordable</h5>
            <p>
  Edited using professional tools and industry standards.  
  Smooth transitions, clean cuts, and perfect color grading.<br>
  Every project crafted with attention to detail.<br>
</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="modern-card animate-item animate-up">
            <div class="service-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <h5>Friendly Support</h5>
            <p>
  Clear communication from start to finish.  
  Regular updates during the editing process.<br>
  Your feedback is always welcome.<br>
</p>
          </div>
        </div>
      </div>
    </div>

    <div class="cta-section animate-item animate-up">
      <h2><i class="fas fa-phone" style="color:#6366f1;"></i> Get in Touch</h2>
      

<button class="btn-17">
  <span class="text-container">
    <a class="text" href="book.php">Book Now</a>
  </span>
</button>
<button class="btn-17">
  <span class="text-container">
    <a class="text" href="contact.php">Contact Us</a>
  </span>
</button>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Animate on scroll
  const animateItems = document.querySelectorAll('.animate-item');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('animate');
    });
  }, { threshold: 0.2 });
  animateItems.forEach(el => observer.observe(el));

  const rightContent = document.querySelector('.right-content');
  const scrollTopBtn = document.getElementById('scrollTop');

  function updateScrollTopBtn() {
    if (window.innerWidth < 992) {
      scrollTopBtn.style.display = window.scrollY > 300 ? 'block' : 'none';
    } else {
      scrollTopBtn.style.display = rightContent.scrollTop > 300 ? 'block' : 'none';
    }
  }

  rightContent.addEventListener('scroll', updateScrollTopBtn);
  window.addEventListener('scroll', updateScrollTopBtn);
  window.addEventListener('resize', updateScrollTopBtn);
  window.addEventListener('DOMContentLoaded', updateScrollTopBtn);

  scrollTopBtn.onclick = function () {
    if (window.innerWidth < 992) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      rightContent.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };
</script>
</body>
</html>