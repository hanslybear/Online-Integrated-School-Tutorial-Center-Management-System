<?php
session_start();
include "db_connect.php";

// Can be accessed by both admin and students
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['student_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

// Get student ID
if (isset($_SESSION['admin_id']) && isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
} elseif (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
} else {
    die("Invalid access");
}

// Get student data
$stmt = $conn->prepare("SELECT student_name, email, qr_code FROM users WHERE id = ? AND status = 'approved'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student not found");
}

$student = $result->fetch_assoc();
$qr_code = $student['qr_code'];

// If no QR code exists, generate one
if (empty($qr_code)) {
    $qr_code = hash('sha256', "ACTS-" . str_pad($student_id, 6, "0", STR_PAD_LEFT) . "-" . strtoupper(substr($student['student_name'], 0, 3)) . "-" . time());
    
    $update_stmt = $conn->prepare("UPDATE users SET qr_code = ? WHERE id = ?");
    $update_stmt->bind_param("si", $qr_code, $student_id);
    $update_stmt->execute();
}

// QR Code API URL (using Google Charts API)
$qr_image_url = "https://quickchart.io/qr?text=" . urlencode($qr_code) . "&size=300";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>QR Code - <?php echo htmlspecialchars($student['student_name']); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
 <link rel="icon" type="image/png" href="actslogo.png">
<style>
body {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.qr-card {
  background: white;
  border-radius: 24px;
  padding: 40px;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
}

.qr-header {
  margin-bottom: 30px;
}

.qr-header h2 {
  color: #1a202c;
  font-weight: 700;
  margin-bottom: 8px;
}

.qr-header p {
  color: #6B7280;
  font-size: 1rem;
}

.qr-container {
  background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  border-radius: 20px;
  padding: 30px;
  margin-bottom: 30px;
  position: relative;
}

.qr-container::before {
  content: '';
  position: absolute;
  inset: -4px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 20px;
  z-index: -1;
}

.qr-image {
  max-width: 100%;
  height: auto;
  border-radius: 12px;
  background: white;
  padding: 15px;
}

.student-info {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.student-info h5 {
  color: #667eea;
  font-weight: 700;
  margin-bottom: 15px;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #e2e8f0;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  color: #6B7280;
  font-weight: 600;
  font-size: 0.9rem;
}

.info-value {
  color: #1a202c;
  font-weight: 600;
}

.btn-download {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  padding: 14px 32px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1rem;
  width: 100%;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.btn-download:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
  color: white;
}

.btn-back {
  background: #f3f4f6;
  color: #1a202c;
  border: none;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  margin-top: 15px;
  width: 100%;
  transition: all 0.3s ease;
}

.btn-back:hover {
  background: #e5e7eb;
  transform: translateY(-2px);
}

.instructions {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
  border: 2px dashed #8b5cf6;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.instructions h6 {
  color: #8b5cf6;
  font-weight: 700;
  margin-bottom: 10px;
}

.instructions p {
  color: #6B7280;
  font-size: 0.9rem;
  margin: 0;
}

@media print {
  body {
    background: white;
  }
  
  .btn-download, .btn-back {
    display: none;
  }
  
  .qr-card {
    box-shadow: none;
  }
}
</style>
</head>
<body>

<div class="qr-card">
  <div class="qr-header">
    <h2><i class="fas fa-qrcode"></i> Attendance QR Code</h2>
    <p>ACTS Learning Center</p>
  </div>

  <div class="instructions">
    <h6><i class="fas fa-info-circle"></i> How to Use</h6>
    <p>Show this QR code to the admin's scanner to mark your attendance automatically. You can download or print this code for easy access.</p>
  </div>

  <div class="qr-container">
    <img src="<?php echo $qr_image_url; ?>" alt="QR Code" class="qr-image" id="qr-image">
  </div>

  <div class="student-info">
    <h5>Student Information</h5>
    <div class="info-row">
      <span class="info-label">Name:</span>
      <span class="info-value"><?php echo htmlspecialchars($student['student_name']); ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Email:</span>
      <span class="info-value"><?php echo htmlspecialchars($student['email']); ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Student ID:</span>
      <span class="info-value"><?php echo str_pad($student_id, 6, "0", STR_PAD_LEFT); ?></span>
    </div>
  </div>

  <button class="btn btn-download" onclick="downloadQR()">
    <i class="fas fa-download"></i>
    Download QR Code
  </button>

  <button class="btn btn-back" onclick="window.history.back()">
    <i class="fas fa-arrow-left"></i> Go Back
  </button>
</div>

<script>
function downloadQR() {
  const qrImage = document.getElementById('qr-image');
  const link = document.createElement('a');
  link.href = qrImage.src;
  link.download = 'QR_Code_<?php echo preg_replace('/[^A-Za-z0-9]/', '_', $student['student_name']); ?>.png';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Alternative: Print QR code
function printQR() {
  window.print();
}
</script>

</body>
</html>