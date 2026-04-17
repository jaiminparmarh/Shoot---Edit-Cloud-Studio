<?php
$galleryItems = [];
if (file_exists('gallery.json')) {
    $galleryItems = json_decode(file_get_contents('gallery.json'), true) ?: [];
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

function render_video_launcher($video) {
    $videoUrl = htmlspecialchars($video['url'], ENT_QUOTES);
    $videoTitle = htmlspecialchars($video['title'] ?? 'Video', ENT_QUOTES);

    echo '<button type="button" class="video-launcher" data-video-src="' . $videoUrl . '" data-video-title="' . $videoTitle . '" aria-label="Play ' . $videoTitle . '">';
    echo '  <div class="video-thumb">';
  echo '    <video class="video-thumb-video" muted playsinline preload="none"></video>';
    echo '    <div class="video-thumb-overlay">';
    echo '      <span class="video-play-icon"><i class="fas fa-play"></i></span>';
    echo '    </div>';
    echo '  </div>';
    echo '</button>';
}

// Separate photos and videos
$photos = array_filter($galleryItems, fn($item) => $item['type'] === 'photo');
$videos = array_filter($galleryItems, fn($item) => $item['type'] === 'video');

// Group videos by category
$videoCategories = [
    'business' => [],
    'cinematic' => [],
    'insta_reels' => [],
    'music_based' => [],
    'event_edits' => [],
    'general' => []
];

foreach ($videos as $video) {
  $categories = normalize_video_categories($video['videoCategory'] ?? []);

  if (empty($categories)) {
    $videoCategories['general'][] = $video;
    continue;
  }

  foreach ($categories as $category) {
    if (isset($videoCategories[$category])) {
      $videoCategories[$category][] = $video;
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Gallery - EditX Studio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style/gallary.css" rel="stylesheet">
</head>
<body>

<!-- Floating Background Shapes -->
<div class="floating-shapes">
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fa fa-clapperboard"></i> EditX Studio</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="Home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="gallary.php">Gallery</a></li>
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

<div class="gallery-container">
  <h1 class="gallery-title" data-aos="fade-down"><i class="fas fa-paint-brush" style="color:#ffffff;"></i>Some Demo Editing Showcase</h1>
  <p class="gallery-subtitle" data-aos="fade-up">Explore our recent photo and video edits with stunning visual effects</p>
  
  <!-- Filter Buttons -->
  <div class="filter-buttons" data-aos="fade-up">
    <button class="filter-btn active" id="videosBtn" onclick="showVideos()">
      <i class="fas fa-video"></i> Videos
    </button>
    <button class="filter-btn" id="photosBtn" onclick="showPhotos()">
      <i class="fas fa-camera"></i> Photos
    </button>
  </div>

  <!-- Photos Section -->
  <div id="photosSection" class="gallery-section hidden">
    <h2 class="gallery-section-title" data-aos="fade-up"><i class="fas fa-camera" style="color:#ffffff;"></i> Photo Gallery</h2>
    <?php if (!empty($photos)): ?>
    <div class="row g-4 mb-5" data-aos="zoom-in">
      <?php foreach ($photos as $photo): ?>
      <div class="col-lg-2-4 col-md-4 col-sm-6">
        <div class="gallery-card">
          <div class="gallery-card-content">
            <img src="<?php echo htmlspecialchars($photo['url']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>" class="gallery-img" />
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="no-items" data-aos="fade-up">
        <i class="fas fa-camera fa-3x mb-3"></i>
        <p>No photos found.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Videos Section -->
  <div id="videosSection" class="gallery-section">
    <h2 class="gallery-section-title" data-aos="fade-up"><i class="fas fa-video" style="color:#ffffff;"></i> Video Gallery</h2>
    
    <!-- Video Category Filter Buttons -->
    <div class="video-category-filters" data-aos="fade-up">
      <button class="video-filter-btn active" onclick="showVideoCategory('all')">
        <i class="fas fa-video"></i> All Videos
      </button>
      <button class="video-filter-btn" onclick="showVideoCategory('business')">
        <i class="fas fa-briefcase"></i> Business / Professional
      </button>
      <button class="video-filter-btn" onclick="showVideoCategory('cinematic')">
        <i class="fas fa-film"></i> Cinematic Edits
      </button>
      <button class="video-filter-btn" onclick="showVideoCategory('insta_reels')">
        <i class="fas fa-instagram"></i> Insta Reels
      </button>
      <button class="video-filter-btn" onclick="showVideoCategory('music_based')">
        <i class="fas fa-music"></i> Music-Based Edits
      </button>
      <button class="video-filter-btn" onclick="showVideoCategory('event_edits')">
        <i class="fas fa-calendar"></i> Event Edits
      </button>
    </div>
    
    <!-- All Videos -->
    <div id="videoCategory_all" class="video-category-section">
      <?php if (!empty($videos)): ?>
      <div class="row g-4" data-aos="zoom-in">
        <?php foreach ($videos as $video): ?>
        <div class="col-lg-2-4 col-md-4 col-sm-6">
          <div class="gallery-card">
            <div class="gallery-card-content">
              <?php render_video_launcher($video); ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="no-items" data-aos="fade-up">
          <i class="fas fa-video fa-3x mb-3"></i>
          <p>No videos found.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Business / Professional Videos -->
    <div id="videoCategory_business" class="video-category-section hidden">
      <?php if (!empty($videoCategories['business'])): ?>
      <div class="row g-4" data-aos="zoom-in">
        <?php foreach ($videoCategories['business'] as $video): ?>
        <div class="col-lg-2-4 col-md-4 col-sm-6">
          <div class="gallery-card">
            <div class="gallery-card-content">
              <?php render_video_launcher($video); ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="no-items" data-aos="fade-up">
          <i class="fas fa-briefcase fa-3x mb-3"></i>
          <p>No business videos found.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Cinematic Edits -->
    <div id="videoCategory_cinematic" class="video-category-section hidden">
      <?php if (!empty($videoCategories['cinematic'])): ?>
      <div class="row g-4" data-aos="zoom-in">
        <?php foreach ($videoCategories['cinematic'] as $video): ?>
        <div class="col-lg-2-4 col-md-4 col-sm-6">
          <div class="gallery-card">
            <div class="gallery-card-content">
              <?php render_video_launcher($video); ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="no-items" data-aos="fade-up">
          <i class="fas fa-film fa-3x mb-3"></i>
          <p>No cinematic videos found.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Insta Reels -->
    <div id="videoCategory_insta_reels" class="video-category-section hidden">
      <?php if (!empty($videoCategories['insta_reels'])): ?>
      <div class="row g-4" data-aos="zoom-in">
        <?php foreach ($videoCategories['insta_reels'] as $video): ?>
        <div class="col-lg-2-4 col-md-4 col-sm-6">
          <div class="gallery-card">
            <div class="gallery-card-content">
              <?php render_video_launcher($video); ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="no-items" data-aos="fade-up">
          <i class="fas fa-instagram fa-3x mb-3"></i>
          <p>No Insta reels found.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Music-Based Edits -->
    <div id="videoCategory_music_based" class="video-category-section hidden">
      <?php if (!empty($videoCategories['music_based'])): ?>
      <div class="row g-4" data-aos="zoom-in">
        <?php foreach ($videoCategories['music_based'] as $video): ?>
        <div class="col-lg-2-4 col-md-4 col-sm-6">
          <div class="gallery-card">
            <div class="gallery-card-content">
              <?php render_video_launcher($video); ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="no-items" data-aos="fade-up">
          <i class="fas fa-music fa-3x mb-3"></i>
          <p>No music-based videos found.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Event Edits -->
    <div id="videoCategory_event_edits" class="video-category-section hidden">
      <?php if (!empty($videoCategories['event_edits'])): ?>
      <div class="row g-4" data-aos="zoom-in">
        <?php foreach ($videoCategories['event_edits'] as $video): ?>
        <div class="col-lg-2-4 col-md-4 col-sm-6">
          <div class="gallery-card">
            <div class="gallery-card-content">
              <?php render_video_launcher($video); ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="no-items" data-aos="fade-up">
          <i class="fas fa-calendar fa-3x mb-3"></i>
          <p>No event videos found.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
  <span class="image-modal-close">&times;</span>
  <img class="image-modal-content" id="modalImage">
</div>

<footer class="mt-5"><div class="container"><p class="mb-0">&copy; 2025 EditX Studio. All rights reserved.</p></div></footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const animatedElements = document.querySelectorAll('body *:not(nav):not(nav *):not(script):not(style):not(link):not(meta):not(title):not(.floating-shapes):not(.floating-shapes *)');

  animatedElements.forEach((el, index) => {
    el.classList.add('reveal-item');
    el.style.transitionDelay = `${Math.min(index * 12, 240)}ms`;
  });

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('reveal-in', 'aos-animate');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

  animatedElements.forEach(el => observer.observe(el));

  // Image Modal functionality
  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('modalImage');
  const modalClose = document.getElementsByClassName('image-modal-close')[0];

  // Add click event to all gallery images
  document.querySelectorAll('.gallery-img').forEach(img => {
    img.addEventListener('click', function() {
      modal.classList.add('show');
      modalImg.src = this.src;
      document.body.style.overflow = 'hidden'; // Prevent scrolling
    });
  });

  // Add click event to all video launchers for fullscreen autoplay
  const videoLaunchers = document.querySelectorAll('.video-launcher');

  function loadThumbVideo(launcher) {
    const thumbVideo = launcher.querySelector('.video-thumb-video');
    if (!thumbVideo || thumbVideo.dataset.loaded === '1') {
      return;
    }

    const videoSource = launcher.getAttribute('data-video-src');
    if (!videoSource) {
      return;
    }

    thumbVideo.src = videoSource;
    thumbVideo.dataset.loaded = '1';
    thumbVideo.load();

    thumbVideo.addEventListener('loadeddata', () => {
      try {
        thumbVideo.currentTime = 0.1;
      } catch (e) {
        // Some browsers may restrict seeking before enough data is loaded.
      }
    }, { once: true });

    thumbVideo.addEventListener('seeked', () => {
      thumbVideo.pause();
    }, { once: true });
  }

  if ('IntersectionObserver' in window) {
    const thumbObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          loadThumbVideo(entry.target);
          thumbObserver.unobserve(entry.target);
        }
      });
    }, { rootMargin: '220px 0px' });

    videoLaunchers.forEach(launcher => thumbObserver.observe(launcher));
  } else {
    videoLaunchers.forEach(loadThumbVideo);
  }

  videoLaunchers.forEach(launcher => {
    launcher.addEventListener('mouseenter', function() {
      loadThumbVideo(this);
    });

    launcher.addEventListener('focus', function() {
      loadThumbVideo(this);
    });

    launcher.addEventListener('click', function() {
      const videoSource = this.getAttribute('data-video-src');

      if (!videoSource) {
        return;
      }

      // Create fullscreen video modal
      const videoModal = document.createElement('div');
      videoModal.className = 'video-modal';
      videoModal.innerHTML = `
        <div class="video-modal-content">
          <span class="video-modal-close">&times;</span>
          <video class="fullscreen-video" controls autoplay preload="auto" playsinline>
            <source src="${videoSource}" type="video/mp4">
            Your browser does not support the video tag.
          </video>
        </div>
      `;
      
      document.body.appendChild(videoModal);
      document.body.style.overflow = 'hidden';
      
      // Play the video
      const fullscreenVideo = videoModal.querySelector('.fullscreen-video');
      fullscreenVideo.play();
      
      // Close modal functionality
      const closeBtn = videoModal.querySelector('.video-modal-close');
      closeBtn.onclick = function() {
        document.body.removeChild(videoModal);
        document.body.style.overflow = 'auto';
        fullscreenVideo.pause();
      };
      
      // Close when clicking outside
      videoModal.onclick = function(event) {
        if (event.target === videoModal) {
          document.body.removeChild(videoModal);
          document.body.style.overflow = 'auto';
          fullscreenVideo.pause();
        }
      };
      
      // Close with Escape key
      document.addEventListener('keydown', function escHandler(event) {
        if (event.key === 'Escape') {
          document.body.removeChild(videoModal);
          document.body.style.overflow = 'auto';
          fullscreenVideo.pause();
          document.removeEventListener('keydown', escHandler);
        }
      });
    });
  });

  // Close modal when clicking the X
  modalClose.onclick = function() {
    modal.classList.remove('show');
    document.body.style.overflow = 'auto'; // Enable scrolling
  }

  // Close modal when clicking outside the image
  modal.onclick = function(event) {
    if (event.target == modal) {
      modal.classList.remove('show');
      document.body.style.overflow = 'auto'; // Enable scrolling
    }
  }

  // Close modal with Escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && modal.classList.contains('show')) {
      modal.classList.remove('show');
      document.body.style.overflow = 'auto'; // Enable scrolling
    }
  });

  // Filter functionality
  function showPhotos() {
    const photosSection = document.getElementById('photosSection');
    const videosSection = document.getElementById('videosSection');
    const photosBtn = document.getElementById('photosBtn');
    const videosBtn = document.getElementById('videosBtn');
    
    // Show photos, hide videos
    photosSection.classList.remove('hidden');
    videosSection.classList.add('hidden');
    
    // Update button states
    photosBtn.classList.add('active');
    videosBtn.classList.remove('active');
  }

  function showVideos() {
    const photosSection = document.getElementById('photosSection');
    const videosSection = document.getElementById('videosSection');
    const photosBtn = document.getElementById('photosBtn');
    const videosBtn = document.getElementById('videosBtn');
    
    // Show videos, hide photos
    videosSection.classList.remove('hidden');
    photosSection.classList.add('hidden');
    
    // Update button states
    videosBtn.classList.add('active');
    photosBtn.classList.remove('active');
  }

  // Video category filtering
  function showVideoCategory(category) {
    // Hide all video category sections
    const allSections = document.querySelectorAll('.video-category-section');
    allSections.forEach(section => section.classList.add('hidden'));
    
    // Show selected category section
    const selectedSection = document.getElementById('videoCategory_' + category);
    if (selectedSection) {
      selectedSection.classList.remove('hidden');
    }
    
    // Update button states
    const allButtons = document.querySelectorAll('.video-filter-btn');
    allButtons.forEach(btn => btn.classList.remove('active'));
    
    const activeButton = Array.from(allButtons).find(btn => 
      btn.getAttribute('onclick').includes(category)
    );
    if (activeButton) {
      activeButton.classList.add('active');
    }
  }
</script>

</body>
</html>