<?php
session_start();
$error = "";

// Use local config only on localhost; keep hosting config for live domains.
$host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
$isLocalHost =
  in_array($host, ['localhost', '127.0.0.1', '::1'], true) ||
  str_starts_with($host, 'localhost:') ||
  str_starts_with($host, '127.0.0.1:') ||
  str_starts_with($host, '[::1]:');

if ($isLocalHost && file_exists(__DIR__ . '/config.local.php')) {
  require_once __DIR__ . '/config.local.php';
} elseif (file_exists(__DIR__ . '/config.php')) {
  require_once __DIR__ . '/config.php';
}

$dbHost = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: 'localhost');
$dbUser = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: 'root');
$dbPass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') ?: '');
$dbName = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ?: 'editing');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Validate inputs server-side
    if (empty($username) || empty($password)) {
        $error = "❌ Both username and password are required.";
    } else {
        // Master admin fallback (works without database)
        // Check if credentials match the master admin hash in config.php
        $masterUsername = defined('ADMIN_USERNAME') ? ADMIN_USERNAME : 'admin';
        $masterHashConfigured = defined('ADMIN_PASSWORD_HASH') && trim((string)ADMIN_PASSWORD_HASH) !== '';
        $isMasterUserAttempt = $masterHashConfigured && ($username === $masterUsername);
        
        if ($isMasterUserAttempt) {
          if (password_verify($password, ADMIN_PASSWORD_HASH)) {
            // Master admin login successful
            session_regenerate_id(true);
                
            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_id"] = 1;
            $_SESSION["admin_username"] = $username;
            $_SESSION["login_time"] = time();
                
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt_time']);
                
            header("Location: dashboard.php");
            exit;
          } else {
            $error = "❌ Incorrect password!";
          }
        }
        
        if (!empty($error)) {
          // For master user attempts, do not fall back to database auth.
        } else {
        
        // Try database authentication
        try {
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
            if ($stmt === false) {
                throw new Exception("Database query preparation failed.");
            }
            
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($admin_id, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    $_SESSION["admin_logged_in"] = true;
                    $_SESSION["admin_id"] = $admin_id;
                    $_SESSION["admin_username"] = $username;
                    $_SESSION["login_time"] = time();
                    
                    // Clear any previous login attempts
                    unset($_SESSION['login_attempts']);
                    unset($_SESSION['last_attempt_time']);
                    
                    $stmt->close();
                    $conn->close();
                    
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "❌ Incorrect password!";
                }
            } else {
                $error = "❌ Admin not found!";
            }

            $stmt->close();
            $conn->close();
            
        } catch (Exception $e) {
          error_log("Login error: " . $e->getMessage());
          $error = "❌ Login system error. Please try again later.";
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EditX Studio | Admin Login</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- AOS Animation -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Custom Login CSS -->
  <link href="style/login.css?v=<?php echo filemtime('style/login.css'); ?>" rel="stylesheet">
</head>
<body>
  <!-- Floating Background Shapes -->
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <div class="main-container">
    <div class="login-card" data-aos="fade-up">
      <div class="login-icon-wrapper">
        <i class="fas fa-lock login-icon"></i>
      </div>
      
      <h2 class="login-title">Admin Login</h2>
      <p class="login-subtitle">Access your EditX Studio dashboard</p>
      
      <?php if (!empty($error)) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
      
      <form method="POST" onsubmit="return validateForm();">
        <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
        <div id="username-error" class="error-msg"></div>
        
        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
        <div id="password-error" class="error-msg"></div>
        
        <button type="submit" class="login-btn">
          <i class="fas fa-sign-in-alt"></i> Login to Dashboard
        </button>
      </form>
      
      <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Portal
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

    function validateForm() {
      let valid = true;

      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();

      document.getElementById("username-error").innerText = "";
      document.getElementById("password-error").innerText = "";

      if (username === "") {
        document.getElementById("username-error").innerText = "Username is required.";
        valid = false;
      }

      if (password === "") {
        document.getElementById("password-error").innerText = "Password is required.";
        valid = false;
      }

      return valid;
    }

    document.getElementById("username").addEventListener("input", function () {
      document.getElementById("username-error").innerText = "";
    });

    document.getElementById("password").addEventListener("input", function () {
      document.getElementById("password-error").innerText = "";
    });

    // Add interactive hover effects
    document.querySelector('.login-card').addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-5px) scale(1.02)';
    });
    
    document.querySelector('.login-card').addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
    });
  </script>
</body>
</html>