<?php
session_start();
require_once 'db-functions.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

$action = $_GET['action'] ?? '';
$feedback_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$message = '';
$messageType = '';

// Handle approve action
if ($action === 'approve' && $feedback_id) {
    if (approveFeedback($feedback_id)) {
        $message = "✅ Feedback approved successfully!";
        $messageType = 'success';
    } else {
        $message = "❌ Error approving feedback.";
        $messageType = 'danger';
    }
}

// Handle reject/delete action
if ($action === 'reject' && $feedback_id) {
    if (rejectFeedback($feedback_id)) {
        $message = "✅ Feedback rejected and removed.";
        $messageType = 'success';
    } else {
        $message = "❌ Error rejecting feedback.";
        $messageType = 'danger';
    }
}

// Handle delete approved feedback
if ($action === 'deleteapproved' && $feedback_id) {
    if (rejectFeedback($feedback_id)) {
        $message = "✅ Approved feedback deleted.";
        $messageType = 'success';
    } else {
        $message = "❌ Error deleting feedback.";
        $messageType = 'danger';
    }
}

// Handle edit form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
    $edit_id = (int)$_POST['edit_id'];
    $new_description = trim($_POST['description'] ?? '');
    
    if ($new_description) {
        if (updateFeedback($edit_id, ['description' => $new_description])) {
            $message = "✅ Feedback updated successfully!";
            $messageType = 'success';
        } else {
            $message = "❌ Error updating feedback.";
            $messageType = 'danger';
        }
    } else {
        $message = "❌ Feedback description cannot be empty.";
        $messageType = 'danger';
    }
}

// Load feedback data
$pendingFeedbacks = getPendingFeedbacks();
$approvedFeedbacks = getApprovedFeedbacks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Feedbacks - EditX Studio</title>
  
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="style/manage_feedbacks.css" rel="stylesheet">
  
  <style>
    /* Add custom styles here */
  </style>
</head>
<body>


  <div class="manage-container">
    <div class="manage-header">
      <h1><i class="fas fa-comments"></i> Manage Feedbacks</h1>
      <a href="dashboard.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>

    <!-- Message Alert -->
    <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- PENDING FEEDBACKS SECTION -->
    <div class="manage-content">
      <div class="section-box">
        <h2><i class="fas fa-hourglass-half" style="color:#ffc107;"></i> Pending Feedbacks</h2>
        <p class="section-subtitle">Review and approve or reject client feedbacks</p>
        
        <div class="feedback-list">
          <?php if (!empty($pendingFeedbacks)): ?>
            <?php foreach ($pendingFeedbacks as $feedback): ?>
              <div class="feedback-item animate-item animate-up">
                <div class="feedback-header">
                  <div class="feedback-info">
                    <strong><?php echo htmlspecialchars($feedback['name']); ?></strong>
                    <small><?php echo htmlspecialchars($feedback['email']); ?> | <?php echo htmlspecialchars($feedback['phone']); ?></small>
                  </div>
                  <span class="badge bg-warning">Pending</span>
                </div>
                <div class="feedback-desc">
                  <p><?php echo htmlspecialchars($feedback['description']); ?></p>
                </div>
                <div class="feedback-actions">
                  <a href="?action=approve&id=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-check"></i> Approve
                  </a>
                  <a href="?action=reject&id=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="fas fa-times"></i> Reject
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-inbox fa-2x mb-2"></i>
              <p>No pending feedbacks. All feedbacks have been reviewed!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- APPROVED FEEDBACKS SECTION -->
      <div class="section-box">
        <h2><i class="fas fa-check-circle" style="color:#28a745;"></i> Approved Feedbacks</h2>
        <p class="section-subtitle">View, edit, or delete approved feedbacks</p>
        
        <div class="feedback-list">
          <?php if (!empty($approvedFeedbacks)): ?>
            <?php foreach ($approvedFeedbacks as $feedback): ?>
              <div class="feedback-item animate-item animate-up">
                <div class="feedback-header">
                  <div class="feedback-info">
                    <strong><?php echo htmlspecialchars($feedback['name']); ?></strong>
                    <small><?php echo htmlspecialchars($feedback['email']); ?> | <?php echo htmlspecialchars($feedback['phone']); ?></small>
                  </div>
                  <span class="badge bg-success">Approved</span>
                </div>
                <div class="feedback-desc">
                  <p><?php echo htmlspecialchars($feedback['description']); ?></p>
                </div>
                <div class="feedback-actions">
                  <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $feedback['id']; ?>">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <a href="?action=deleteapproved&id=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                    <i class="fas fa-trash"></i> Delete
                  </a>
                </div>
              </div>

              <!-- Edit Modal -->
              <div class="modal fade" id="editModal<?php echo $feedback['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Feedback - <?php echo htmlspecialchars($feedback['name']); ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                      <div class="modal-body">
                        <input type="hidden" name="edit_id" value="<?php echo $feedback['id']; ?>">
                        <div class="mb-3">
                          <label class="form-label">Feedback Description</label>
                          <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($feedback['description']); ?></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-star fa-2x mb-2"></i>
              <p>No approved feedbacks yet. Approve pending feedbacks to display them on the website.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    document.querySelectorAll('.modal').forEach((modalElement) => {
      document.body.appendChild(modalElement);
    });

    const alertElements = document.querySelectorAll('.alert');
    if (alertElements.length) {
      setTimeout(() => {
        alertElements.forEach((alertElement) => {
          const bootstrapAlert = bootstrap.Alert.getOrCreateInstance(alertElement);
          bootstrapAlert.close();
        });
      }, 2000);
    }

  </script>
</body>
</html>
