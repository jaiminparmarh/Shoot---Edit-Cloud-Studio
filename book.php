<?php
require_once 'db-functions.php';

// AJAX form handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $service = $_POST["service"];
    $message = trim($_POST["message"]);
    $booking_date = $_POST["booking_date"];
    $booking_time = $_POST["booking_time"];

    $response = ['success' => false, 'message' => ''];

    if ($name && $email && $phone && $service && $booking_date && $booking_time && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $bookingData = [
            'full_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'message' => $message,
            'booking_date' => $booking_date,
            'booking_time' => $booking_time
        ];
        
        if (addBooking($bookingData)) {
          sendAdminDiscordNotification('booking', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'message' => $message,
            'booking_date' => $booking_date,
            'booking_time' => $booking_time
          ]);
          sendFormThankYouEmail($name, $email, 'booking', [
            'service' => $service,
            'booking_date' => $booking_date,
            'booking_time' => $booking_time
          ]);
            $response['success'] = true;
            $response['message'] = '✅ Booking submitted successfully! We will contact you soon.';
        } else {
            $response['message'] = 'Unable to save booking. Please try again.';
        }
    } else {
        $response['message'] = 'Please fill in all required fields with valid information.';
    }
    
    echo json_encode($response);
    exit;
}

$showModal = false;
$submitError = "";

if ($_SERVER["REQUEST_METHOD"]=="POST" && !isset($_POST['ajax'])) {
  $name = trim($_POST["name"]);
  $email = trim($_POST["email"]);
  $phone = trim($_POST["phone"]);
  $service = $_POST["service"];
  $message = trim($_POST["message"]);
  $booking_date = $_POST["booking_date"];
  $booking_time = $_POST["booking_time"];

  if ($name && $email && $phone && $service && $booking_date && $booking_time && filter_var($email,FILTER_VALIDATE_EMAIL)) {
    $bookingData = [
      'full_name' => $name,
      'email' => $email,
      'phone' => $phone,
      'service' => $service,
      'message' => $message,
      'booking_date' => $booking_date,
      'booking_time' => $booking_time
    ];
    if (addBooking($bookingData)) {
      sendAdminDiscordNotification('booking', [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'service' => $service,
        'message' => $message,
        'booking_date' => $booking_date,
        'booking_time' => $booking_time
      ]);
      sendFormThankYouEmail($name, $email, 'booking', [
        'service' => $service,
        'booking_date' => $booking_date,
        'booking_time' => $booking_time
      ]);
      $showModal = true;
      $_POST = [];
    } else {
      $submitError = "Unable to save booking. Please check file permissions or try again.";
    }
  } else {
    $submitError = "Please fill in all required fields with valid information.";
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Book Now - EditX Studio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style/book.css" rel="stylesheet">
</head>
<body>

<!-- Floating Background Shapes -->
<div class="floating-shapes">
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fa fa-clapperboard"></i> EditX Studio</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="Home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="gallary.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link active" href="book.php">Book</a></li>
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

<!-- Booking Form -->
<section class="form-section animate-item animate-zoom">
<h2 class="text-center mb-4 animate-item animate-zoom"><i class="fas fa-envelope"></i> Book Your Editing Now</h2>
<!-- AJAX Response Message -->
<div id="ajaxResponse" class="alert" style="display: none;"></div>

<?php if (!empty($submitError)): ?>
  <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($submitError); ?></div>
<?php endif; ?>

<form id="bookingForm" method="POST" onsubmit="return validateForm()">
  <input type="hidden" name="ajax" value="1">
  <div class="row">
    <!-- Left Column -->
    <div class="col-md-6">
      <div class="mb-3 animate-item animate-right">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" id="name" class="form-control">
        <div id="nameError" class="error"></div>
      </div>
      <div class="mb-3 animate-item animate-left">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" id="email" class="form-control">
        <div id="emailError" class="error"></div>
      </div>
      <div class="mb-3 animate-item animate-right">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" id="phone" class="form-control">
        <div id="phoneError" class="error"></div>
      </div>
      <div class="mb-3 animate-item animate-left">
        <label class="form-label">Select Service</label>
        <select name="service" id="service" class="form-select form-control">
          <option value="">Choose...</option>
          <option value="photo"> Photo Editing</option>
          <option value="video"> Video Editing</option>
          <option value="reel"> Reels / Shorts</option>
          <option value="wedding"> Wedding Highlights</option>
        </select>
        <div id="serviceError" class="error"></div>
      </div>
    </div>
    
    <!-- Right Column -->
    <div class="col-md-6">
      <div class="mb-3 animate-item animate-right">
        <label class="form-label">Select Date</label>
        <input type="date" name="booking_date" id="booking_date" class="form-control" required>
        <div id="dateError" class="error"></div>
      </div>
      <div class="mb-3 animate-item animate-left">
        <label class="form-label">Select Time</label>
        <input type="time" name="booking_time" id="booking_time" class="form-control" required>
        <div id="timeError" class="error"></div>
      </div>
      <div class="mb-3 animate-item animate-right">
        <label class="form-label">Message / Instructions</label>
        <textarea name="message" id="message" class="form-control" rows="4"></textarea>
      </div>
      <button type="submit" class="btn btn-dark w-100 animate-item animate-up" id="submitBtn">
        <i class="fas fa-spinner fa-spin" style="display: none;" id="loadingSpinner"></i>
        <span id="btnText">Submit Booking</span>
      </button>
    </div>
  </div>
</form>
</section>

<!-- Success Modal -->
<?php if($showModal): ?>
<div class="modal fade" id="successModal">
 <div class="modal-dialog modal-dialog-centered">
  <div class="modal-content text-center">
   <div class="modal-header bg-success text-white"><h5 class="modal-title w-100"> Booking Confirmed!</h5></div>
   <div class="modal-body"><p>Thank you! Your booking has been successfully submitted.</p></div>
   <div class="modal-footer justify-content-center"><button class="btn btn-success" data-bs-dismiss="modal">Close</button></div>
  </div>
 </div>
</div>
<?php endif; ?>

<!-- Footer -->
<footer class="mt-5"><div class="container"><p class="mb-0">&copy; 2025 EditX Studio. All rights reserved.</p></div></footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if($showModal): ?>window.onload=()=>new bootstrap.Modal('#successModal').show();<?php endif; ?>

// Get all form elements
const nameEl = document.getElementById('name');
const emailEl = document.getElementById('email');
const phoneEl = document.getElementById('phone');
const serviceEl = document.getElementById('service');
const dateEl = document.getElementById('booking_date');
const timeEl = document.getElementById('booking_time');

// Get all error elements
const nameError = document.getElementById('nameError');
const emailError = document.getElementById('emailError');
const phoneError = document.getElementById('phoneError');
const serviceError = document.getElementById('serviceError');
const dateError = document.getElementById('dateError');
const timeError = document.getElementById('timeError');

// Add event listeners
["name","email","phone","service"].forEach(id=>{
 let el=document.getElementById(id);
 if(el) {
   el.oninput = validateForm;
   el.onchange = validateForm;
 }
});

function validateForm() {
  let ok = true;
  let name = nameEl.value.trim(), email = emailEl.value.trim(), phone = phoneEl.value.trim(), service = serviceEl.value;
  let booking_date = dateEl.value;
  let booking_time = timeEl.value;

  nameError.textContent = name ? "" : "Name is required."; ok &= !!name;
  emailError.textContent = email ? (/^\S+@\S+\.\S+$/.test(email) ? "" : "Invalid email.") : "Email is required."; ok &= !!email && /^\S+@\S+\.\S+$/.test(email);
  phoneError.textContent = phone ? "" : "Phone is required."; ok &= !!phone;
  serviceError.textContent = service ? "" : "Please select a service."; ok &= !!service;
  dateError.textContent = booking_date ? "" : "Please select a date."; ok &= !!booking_date;
  timeError.textContent = booking_time ? "" : "Please select a time."; ok &= !!booking_time;

  if (ok) {
    submitFormAjax();
  }
  
  return false; // Prevent normal form submission
}

function submitFormAjax() {
    const form = document.getElementById('bookingForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const responseDiv = document.getElementById('ajaxResponse');
    
    // Show loading state
    btnText.style.display = 'none';
    loadingSpinner.style.display = 'inline-block';
    submitBtn.disabled = true;
    
    fetch('book.php', {
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
    
    return false;
}


// Animate on scroll
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
</script>
</body>
</html>
<style>
  /* CSS styles */
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

  .form-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    background-size: 400% 400%;
    animation: formGradientShift 15s ease infinite;
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1), inset 0 0 30px rgba(138, 43, 226, 0.1);
    border: 1px solid rgba(138, 43, 226, 0.3);
    max-width: 900px;
    margin: 40px auto;
    position: relative;
    overflow: hidden;
    color: white;
  }

  .form-section::before {
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

  .error {
    color: red;
    font-size: 14px;
    margin-top: 3px;
  }

  footer {
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
    color: #000;
    padding: 8px 0;
    text-align: center;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 2;
    font-size: 14px;
  }

@media (max-width: 991px) {
  .form-section {
    max-width: 700px;
    padding: 20px;
    margin: 30px 15px;
  }
  .form-section h2 {
    font-size: 1.8rem;
  }
}

@media (max-width: 768px) {
  .form-section {
    max-width: 600px;
    padding: 20px 15px;
    margin: 25px 10px;
  }
  .form-section h2 {
    font-size: 1.6rem;
  }
  .form-control, .form-select {
    padding: 10px 12px;
    font-size: 14px;
  }
  .form-label {
    font-size: 13px;
  }
}

@media (max-width: 576px) {
  .form-section {
    width: 80% !important;
    max-width: 80% !important;
    padding: 8px 6px !important;
    margin: 15px auto !important;
  }
  .form-section h2 {
    font-size: 1rem !important;
    margin-bottom: 12px !important;
  }
  .form-control, .form-select {
    padding: 5px 8px !important;
    font-size: 11px !important;
    min-height: 30px !important;
  }
  .form-label {
    font-size: 10px !important;
    margin-bottom: 3px !important;
  }
  .btn-dark {
    padding: 5px 10px !important;
    font-size: 11px !important;
    min-height: 30px !important;
  }
  .row {
    margin: 0;
  }
  .col-md-6 {
    padding: 0 3px !important;
  }
  .mb-3 {
    margin-bottom: 6px !important;
  }
  textarea {
    min-height: 40px !important;
  }
}

@media (max-width: 400px) {
  .form-section {
    width: 80% !important;
    max-width: 80% !important;
    padding: 6px 4px !important;
    margin: 12px auto !important;
  }
  .form-section h2 {
    font-size: 0.9rem !important;
    margin-bottom: 10px !important;
  }
  .form-control, .form-select {
    padding: 4px 6px !important;
    font-size: 10px !important;
    min-height: 25px !important;
  }
  .form-label {
    font-size: 9px !important;
    margin-bottom: 2px !important;
  }
  .btn-dark {
    padding: 4px 8px !important;
    font-size: 10px !important;
    min-height: 25px !important;
  }
  .col-md-6 {
    padding: 0 2px !important;
  }
  .mb-3 {
    margin-bottom: 4px !important;
  }
  textarea {
    min-height: 30px !important;
  }
}
</style>