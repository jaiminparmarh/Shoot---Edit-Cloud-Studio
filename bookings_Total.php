<?php
session_start();
require_once 'db-functions.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

// Get bookings from JSON file
$bookings = getBookings();
$bookingCount = count($bookings);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Client Bookings - EditX Studio</title>
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

  tbody tr td:nth-child(1) {
    font-weight: 600;
    color: #8bb9ff;
    width: 50px;
  }

  tbody tr td:nth-child(5) {
    text-transform: capitalize;
    font-weight: 600;
    color: #fdbce7;
    width: 130px;
  }

  tbody tr td:nth-child(6) {
    max-width: 280px;
    white-space: pre-wrap;
    word-break: break-word;
    color: rgba(255, 255, 255, 0.9);
    font-style: italic;
  }

  tbody tr td:nth-child(7) {
    width: 140px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    font-weight: 500;
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

  .print-btn {
    padding: 6px 12px !important;
    font-size: 12px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    opacity: 0.8;
    transition: all 0.3s ease;
  }

  .print-btn:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    opacity: 1;
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2) !important;
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
    .btn-secondary {
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
    .btn-secondary {
      padding: 10px 20px;
      font-size: 14px;
      margin: 3px;
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
    .btn-secondary {
      padding: 8px 15px;
      font-size: 13px;
      margin: 2px;
      display: block;
      width: 100%;
      margin-bottom: 8px;
    }
    .print-btn {
      padding: 4px 8px !important;
      font-size: 10px !important;
      margin: 0 !important;
      width: auto !important;
      display: inline-block !important;
    }
    .table-container {
      padding: 10px;
      overflow-x: visible;
    }
    table {
      width: 100%;
      min-width: auto;
    }
    thead tr th {
      padding: 6px 4px;
      font-size: 9px;
      font-weight: 600;
    }
    tbody tr td {
      padding: 6px 4px;
      font-size: 9px;
      vertical-align: top;
    }
    tbody tr td:nth-child(1) {
      width: 30px;
      font-size: 8px;
    }
    tbody tr td:nth-child(2) {
      width: 60px;
      font-size: 8px;
    }
    tbody tr td:nth-child(3) {
      width: 70px;
      font-size: 8px;
      word-break: break-all;
    }
    tbody tr td:nth-child(4) {
      width: 70px;
      font-size: 8px;
      word-break: break-all;
    }
    tbody tr td:nth-child(5) {
      width: 50px;
      font-size: 8px;
    }
    tbody tr td:nth-child(6) {
      width: 80px;
      font-size: 8px;
      max-width: 80px;
      word-break: break-word;
      white-space: normal;
    }
    tbody tr td:nth-child(7) {
      width: 60px;
      font-size: 7px;
      line-height: 1.2;
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
    .btn-secondary {
      padding: 6px 12px;
      font-size: 12px;
      display: block;
      width: 100%;
      margin-bottom: 8px;
    }
    .print-btn {
      padding: 3px 6px !important;
      font-size: 9px !important;
      margin: 0 !important;
      width: auto !important;
      display: inline-block !important;
    }
    .table-container {
      padding: 8px;
      overflow-x: visible;
    }
    table {
      width: 100%;
      min-width: auto;
    }
    thead tr th {
      padding: 4px 2px;
      font-size: 8px;
      font-weight: 600;
    }
    tbody tr td {
      padding: 4px 2px;
      font-size: 8px;
      vertical-align: top;
    }
    tbody tr td:nth-child(1) {
      width: 25px;
      font-size: 7px;
    }
    tbody tr td:nth-child(2) {
      width: 50px;
      font-size: 7px;
    }
    tbody tr td:nth-child(3) {
      width: 60px;
      font-size: 7px;
      word-break: break-all;
    }
    tbody tr td:nth-child(4) {
      width: 60px;
      font-size: 7px;
      word-break: break-all;
    }
    tbody tr td:nth-child(5) {
      width: 45px;
      font-size: 7px;
    }
    tbody tr td:nth-child(6) {
      width: 70px;
      font-size: 7px;
      max-width: 70px;
      word-break: break-word;
      white-space: normal;
    }
    tbody tr td:nth-child(7) {
      width: 50px;
      font-size: 6px;
      line-height: 1.1;
    }
  }

  /* Print Styles */
  @media print {
    body {
      background: white !important;
      color: black !important;
      font-family: Arial, sans-serif !important;
    }
    
    .floating-shapes {
      display: none !important;
    }
    
    .manage-section {
      background: white !important;
      box-shadow: none !important;
      border: none !important;
      margin: 0 !important;
      padding: 20px !important;
      max-width: 100% !important;
    }
    
    .manage-section::before {
      display: none !important;
    }
    
    .manage-section h1,
    .manage-section h2 {
      color: black !important;
      text-shadow: none !important;
      margin-bottom: 20px !important;
    }
    
    .table-container {
      background: white !important;
      backdrop-filter: none !important;
      border: 1px solid black !important;
      border-radius: 0 !important;
      padding: 0 !important;
    }
    
    table {
      width: 100% !important;
      color: black !important;
      border-collapse: collapse !important;
    }
    
    thead tr th {
      background: #f0f0f0 !important;
      color: black !important;
      border: 1px solid black !important;
      padding: 10px !important;
      font-size: 12px !important;
      font-weight: bold !important;
    }
    
    tbody tr td {
      color: black !important;
      border: 1px solid black !important;
      padding: 8px !important;
      font-size: 11px !important;
      vertical-align: top !important;
    }
    
    tbody tr:nth-child(even) {
      background: #f9f9f9 !important;
    }
    
    tbody tr td:nth-child(1) {
      color: black !important;
      font-weight: bold !important;
    }
    
    tbody tr td:nth-child(5) {
      color: black !important;
      font-weight: bold !important;
    }
    
    tbody tr td:nth-child(6) {
      color: black !important;
      font-style: italic !important;
    }
    
    .btn-secondary {
      display: none !important;
    }
    
    .no-items {
      color: black !important;
      background: white !important;
      border: 1px solid black !important;
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

<!-- Manage Bookings Section -->
<section class="manage-section">
  <h1><i class="fas fa-calendar-check" style="color:#ffffff;"></i> Client Bookings</h1>
  <p class="text-center mb-4" style="color: rgba(255,255,255,0.8);">
    View and manage all client booking requests
  </p>

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <h2 style="margin: 0; color: #ffffff; text-shadow: 0 0 15px rgba(255, 255, 255, 0.2); flex: 1; min-width: 200px;">
      <i class="fas fa-list" style="color:#ffffff;"></i> All Bookings (Total: <?php echo $bookingCount; ?>)
    </h2>
    <button type="button" onclick="window.print()" class="btn-secondary print-btn">
      <i class="fas fa-print"></i> Print
    </button>
  </div>

  <div class="table-container">

    <?php if (!empty($bookings)): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Service</th>
            <th>Message</th>
            <th>Booked At</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['id']); ?></td>
              <td><?php echo htmlspecialchars($row['full_name']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['phone']); ?></td>
              <td><?php echo htmlspecialchars(ucfirst($row['service'])); ?></td>
              <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
              <td><?php echo date("d-M-Y H:i", strtotime($row['created_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="no-items">
        <i class="fas fa-calendar-times fa-3x mb-3"></i>
        <p>No bookings found.</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="text-center mt-4">
    <button type="button" onclick="window.location.href='dashboard.php'" class="btn-secondary">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </button>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>