<?php
require_once 'db-functions.php';

// AJAX form handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $description = trim($_POST["description"] ?? "");
    
    $response = ['success' => false, 'message' => ''];
    
    // Validate
    if ($name && $email && $phone && $description && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedbackData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'description' => $description
        ];
        
        if (addFeedback($feedbackData)) {
          sendAdminDiscordNotification('feedback', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'description' => $description
          ]);
          sendFormThankYouEmail($name, $email, 'feedback');
            $response['success'] = true;
            $response['message'] = '✅ Thank you for your feedback! It will be visible after admin approval.';
        } else {
            $response['message'] = 'Unable to submit feedback. Please try again.';
        }
    } else {
        $response['message'] = 'Please fill all fields with valid information.';
    }
    
    echo json_encode($response);
    exit;
}

// Fallback for non-JS users
$submitError = "";
$submitSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['ajax'])) {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $description = trim($_POST["description"] ?? "");
    
    // Validate
    if ($name && $email && $phone && $description && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedbackData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'description' => $description
        ];
        
        if (addFeedback($feedbackData)) {
          sendAdminDiscordNotification('feedback', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'description' => $description
          ]);
          sendFormThankYouEmail($name, $email, 'feedback');
            $submitSuccess = true;
            $_POST = []; // Clear form
        } else {
            $submitError = "Unable to submit feedback. Please try again.";
        }
    } else {
        $submitError = "Please fill all fields with valid information.";
    }
}

// Load approved feedbacks only
$approvedFeedbacks = array_reverse(getApprovedFeedbacks());
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Feedbacks - EditX Studio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style/feedbacks.css" rel="stylesheet">
</head>
<body>

<div class="floating-shapes">
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fa fa-clapperboard"></i> EditX Studio</a>
    <div class="navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="Home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="gallary.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="book.php">Book</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link active" href="feedbacks.php">Feedbacks</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="feedback-section">
  <div class="feedback-box animate-item animate-zoom">
    <h2 class="text-center mb-4"><i class="fas fa-comments" style="color:#ffe066"></i> Client Feedbacks</h2>
    <p class="text-center feedback-subtitle">Real words from our happy clients.</p>

    <div class="approved-feedbacks">
      <?php if (!empty($approvedFeedbacks)): ?>
        <div class="row g-3 mt-2">
          <?php foreach ($approvedFeedbacks as $feedback): ?>
            <div class="col-12 col-md-6 col-lg-3">
              <div class="feedback-card animate-item animate-right">
                <div class="feedback-text">
                  <p class="feedback-preview">"<?php echo htmlspecialchars($feedback['description']); ?>"</p>
                  <p class="feedback-full" style="display: none;">"<?php echo htmlspecialchars($feedback['description']); ?>"</p>
                </div>
                <a href="javascript:void(0);" class="read-more-link" onclick="toggleReadMore(this)">Read more</a>
                <strong class="feedback-author">- <?php echo htmlspecialchars($feedback['name']); ?></strong>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-feedbacks text-center py-5">
          <i class="fas fa-comments fa-3x mb-3" style="color: rgba(255,255,255,0.4);"></i>
          <p style="color: rgba(255,255,255,0.7);">No feedbacks yet. Be the first to share!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="your-feedback-section">
  <div class="feedback-form-box contact-box animate-item animate-zoom">
    <h3 class="text-center mb-4"><i class="fas fa-star" style="color:#ffc107;"></i> Your Feedback</h3>
    
    <!-- AJAX Response Message -->
    <div id="ajaxResponse" class="alert" style="display: none;"></div>
    
    <?php if ($submitSuccess): ?>
      <div class="alert alert-success text-center">
        ✅ Thank you for your feedback! It will be visible after admin approval.
      </div>
    <?php endif; ?>
    
    <?php if (!empty($submitError)): ?>
      <div class="alert alert-danger text-center"><?php echo htmlspecialchars($submitError); ?></div>
    <?php endif; ?>

    <form id="feedbackForm" method="POST">
      <input type="hidden" name="ajax" value="1">
      <div class="mb-3 animate-item animate-right">
        <label class="form-label">Your Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
      </div>
      
      <div class="mb-3 animate-item animate-left">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      </div>

      <div class="mb-3 animate-item animate-right">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
      </div>
      
      <div class="mb-3 animate-item animate-left">
        <label class="form-label">Your Feedback</label>
        <textarea name="description" class="form-control" rows="4" placeholder="Share your experience with us..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
      </div>
      
      <button type="submit" class="btn btn-dark w-100 animate-item animate-up" id="submitBtn">
        <i class="fas fa-spinner fa-spin" style="display: none;" id="loadingSpinner"></i>
        <span id="btnText">Submit Feedback</span>
      </button>
    </form>
  </div>
</section>

<footer><p>&copy; 2025 EditX Studio. All rights reserved.</p></footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// AJAX form submission
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitFeedbackForm();
});

function submitFeedbackForm() {
    const form = document.getElementById('feedbackForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const responseDiv = document.getElementById('ajaxResponse');
    
    // Show loading state
    btnText.style.display = 'none';
    loadingSpinner.style.display = 'inline-block';
    submitBtn.disabled = true;
    
    fetch('feedbacks.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading state
        btnText.style.display = 'inline';
        loadingSpinner.style.display = 'none';
        submitBtn.disabled = false;
        
        // Show response
        responseDiv.style.display = 'block';
        responseDiv.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
        responseDiv.textContent = data.message;
        
        if (data.success) {
            // Reset form
            form.reset();
            // Hide message after 5 seconds
            setTimeout(() => {
                responseDiv.style.display = 'none';
            }, 5000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Hide loading state
        btnText.style.display = 'inline';
        loadingSpinner.style.display = 'none';
        submitBtn.disabled = false;
        
        responseDiv.style.display = 'block';
        responseDiv.className = 'alert alert-danger';
        responseDiv.textContent = 'An error occurred. Please try again.';
    });
}

const autoAnimatedElements = document.querySelectorAll(
  'body *:not(nav):not(nav *):not(script):not(style):not(link):not(meta):not(title):not(br)'
);

autoAnimatedElements.forEach((element, index) => {
    if (element.classList.contains('floating-shapes') || element.classList.contains('shape')) return;
    if (element.classList.contains('navbar') || element.closest('nav')) return;
    if (element.tagName === 'FOOTER' || element.closest('footer')) return;
    if (!element.classList.contains('animate-item')) {
        element.classList.add('animate-item', 'animate-up');
        element.style.setProperty('--anim-delay', `${Math.min(index * 0.03, 1.2)}s`);
    }
});

const animateItems = document.querySelectorAll('.animate-item');
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) entry.target.classList.add('animate');
  });
}, { threshold: 0.2 });
animateItems.forEach(el => observer.observe(el));

function toggleReadMore(link) {
  const card = link.closest('.feedback-card');
  const preview = card.querySelector('.feedback-preview');
  const full = card.querySelector('.feedback-full');
  
  if (full.style.display === 'none') {
    preview.style.display = 'none';
    full.style.display = 'block';
    link.textContent = 'Read less';
  } else {
    preview.style.display = 'block';
    full.style.display = 'none';
    link.textContent = 'Read more';
  }
}
</script>
</body>
</html>