<?php
require_once 'db-functions.php';

// AJAX form handler
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
  $phone = trim($_POST["phone"]);
    $message = trim($_POST["message"]);
    
    $response = ['success' => false, 'message' => ''];
    
    $valid = true;
    $errors = [];
    
    if (!$name) {
        $errors['name'] = "Please enter your name.";
        $valid = false;
    }
    if (!$email) {
        $errors['email'] = "Please enter your email.";
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email address.";
        $valid = false;
    }
    if (!$phone) {
      $errors['phone'] = "Please enter your phone number.";
      $valid = false;
    }
    if (!$message) {
        $errors['message'] = "Please enter your message.";
        $valid = false;
    }
    
    if ($valid) {
        $contactData = [
            'name' => $name,
            'email' => $email,
          'phone' => $phone,
            'message' => $message
        ];
        
        if (addContactMessage($contactData)) {
          sendAdminDiscordNotification('contact', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message
          ]);
          sendFormThankYouEmail($name, $email, 'contact');
            $response['success'] = true;
            $response['message'] = '✅ Thank you! Your message has been sent successfully.';
        } else {
            $response['message'] = '❌ Something went wrong. Please try again.';
        }
    } else {
        $response['message'] = 'Please fill all required fields correctly.';
        $response['errors'] = $errors;
    }
    
    echo json_encode($response);
    exit;
}

// Fallback for non-JS users
$name=$email=$phone=$message="";
$nameErr=$emailErr=$phoneErr=$messageErr=$successMsg="";

if($_SERVER["REQUEST_METHOD"]=="POST" && !isset($_POST['ajax'])){
  $valid=true;
  $name=trim($_POST["name"]);
  $email=trim($_POST["email"]);
  $phone=trim($_POST["phone"]);
  $message=trim($_POST["message"]);

  if(!$name){$nameErr="Please enter your name."; $valid=false;}
  if(!$email){$emailErr="Please enter your email."; $valid=false;}
  elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){$emailErr="Enter a valid email address."; $valid=false;}
  if(!$phone){$phoneErr="Please enter your phone number."; $valid=false;}
  if(!$message){$messageErr="Please enter your message."; $valid=false;}

  if($valid){
    $contactData = [
      'name' => $name,
      'email' => $email,
      'phone' => $phone,
      'message' => $message
    ];
    if(addContactMessage($contactData)) {
      sendAdminDiscordNotification('contact', [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message
      ]);
      sendFormThankYouEmail($name, $email, 'contact');
      $successMsg = "✅ Thank you! Your message has been sent.";
      $name=$email=$phone=$message="";
    } else {
      $successMsg = "❌ Something went wrong. Try again.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact Us - EditX Studio</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style/contact.css" rel="stylesheet">
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
    <div class="navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="Home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="gallary.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="book.php">Book</a></li>
        <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="feedbacks.php">Feedbacks</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Contact -->
<section class="contact-box animate-item animate-zoom" data-aos="fade-up">
 <h2 class="text-center mb-4"><i class="fas fa-phone" style="color:#25d366"></i> Contact Us</h2>
 <div class="row mb-4 text-center">
  <div class="col-md-4 icon-box animate-item animate-right"><i class="bi bi-envelope-fill"></i><p class="mt-2">editxstudio@gmail.com</p></div>
  <div class="col-md-4 icon-box animate-item animate-up"><i class="bi bi-telephone-fill"></i><p class="mt-2">+91 98765 43210</p></div>
  <div class="col-md-4 icon-box animate-item animate-left"><i class="bi bi-geo-alt-fill"></i><p class="mt-2">Ahmedabad, Gujarat</p></div>
 </div>

 <!-- AJAX Response Message -->
<div id="ajaxResponse" class="success-msg text-center" style="display: none;"></div>

<?php if($successMsg): ?><div class="success-msg text-center"><?= $successMsg ?></div><?php endif; ?>

<form id="contactForm" method="POST">
  <input type="hidden" name="ajax" value="1">
  <div class="mb-3 animate-item animate-right">
   <label class="form-label">Your Name</label>
   <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
  <div class="error-msg"><?= htmlspecialchars($nameErr) ?></div>
  </div>
  <div class="mb-3 animate-item animate-left">
   <label class="form-label">Email</label>
   <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
    <div class="error-msg"><?= htmlspecialchars($emailErr) ?></div>
    </div>
    <div class="mb-3 animate-item animate-right">
    <label class="form-label">Phone Number</label>
    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>">
    <div class="error-msg"><?= htmlspecialchars($phoneErr) ?></div>
  </div>
  <div class="mb-3 animate-item animate-right">
   <label class="form-label">Message</label>
   <textarea name="message" class="form-control" rows="2"><?= htmlspecialchars($message) ?></textarea>
    <div class="error-msg"><?= htmlspecialchars($messageErr) ?></div>
  </div>
  <button type="submit" class="btn btn-dark w-100 animate-item animate-up" id="submitBtn">
   <i class="fas fa-spinner fa-spin" style="display: none;" id="loadingSpinner"></i>
   <span id="btnText">Send Message</span>
  </button>
 </form>
</section>

<!-- Footer -->
<footer><p>&copy; 2025 EditX Studio. All rights reserved.</p></footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// AJAX form submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitContactForm();
});

function submitContactForm() {
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const responseDiv = document.getElementById('ajaxResponse');
    
    // Clear previous errors
    document.querySelectorAll('.error-msg').forEach(elem => elem.textContent = '');
    
    // Show loading state
    btnText.style.display = 'none';
    loadingSpinner.style.display = 'inline-block';
    submitBtn.disabled = true;
    
    fetch('contact.php', {
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
        responseDiv.className = 'success-msg text-center';
        responseDiv.textContent = data.message;
        
        if (data.success) {
            // Reset form
            form.reset();
          document.querySelectorAll('.error-msg').forEach(elem => elem.textContent = '');
            // Hide message after 5 seconds
            setTimeout(() => {
                responseDiv.style.display = 'none';
            }, 5000);
        } else {
            // Show field errors if any
            if (data.errors) {
                if (data.errors.name) {
                    document.querySelector('input[name="name"]').nextElementSibling.textContent = data.errors.name;
                }
                if (data.errors.email) {
                    document.querySelector('input[name="email"]').nextElementSibling.textContent = data.errors.email;
                }
            if (data.errors.phone) {
              document.querySelector('input[name="phone"]').nextElementSibling.textContent = data.errors.phone;
            }
                if (data.errors.message) {
                    document.querySelector('textarea[name="message"]').nextElementSibling.textContent = data.errors.message;
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Hide loading state
        btnText.style.display = 'inline';
        loadingSpinner.style.display = 'none';
        submitBtn.disabled = false;
        
        responseDiv.style.display = 'block';
        responseDiv.className = 'error-msg text-center';
        responseDiv.textContent = 'An error occurred. Please try again.';
    });
}

// Auto-animate all page elements except navbar and footer
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

// Intersection Observer for scroll animations
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