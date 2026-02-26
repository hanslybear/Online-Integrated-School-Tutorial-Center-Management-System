<!-- Student Profile Page -->
<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: homepage.php?error=Access denied");
    exit();
}

$student_id = $_SESSION['student_id'];
$sql = "SELECT * FROM users WHERE id = ? AND status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: homepage.php?error=Access denied");
    exit();
}

$student = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (isset($_POST['parents_name']) && isset($_POST['parents_contact']) && !isset($_POST['student_name'])) {
        $guardian_name = mysqli_real_escape_string($conn, $_POST['parents_name']);
        $guardian_contact = mysqli_real_escape_string($conn, $_POST['parents_contact']);
        $update_sql = "UPDATE users SET parents_name = ?, parents_contact = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $guardian_name, $guardian_contact, $student_id);
        if ($update_stmt->execute()) {
            $success_message = "Guardian information updated successfully!";
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
        } else {
            $error_message = "Error updating guardian information.";
        }
        $update_stmt->close();
    } else if (isset($_POST['student_name'], $_POST['email'], $_POST['contact'], $_POST['address'])) {
        $student_name = mysqli_real_escape_string($conn, $_POST['student_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['contact']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $email_check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_check_stmt = $conn->prepare($email_check_sql);
        $email_check_stmt->bind_param("si", $email, $student_id);
        $email_check_stmt->execute();
        $email_check_result = $email_check_stmt->get_result();
        if ($email_check_result->num_rows > 0) {
            $error_message = "This email address is already in use.";
        } else {
            $update_sql = "UPDATE users SET student_name = ?, email = ?, contact = ?, address = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssi", $student_name, $email, $phone, $address, $student_id);
            if ($update_stmt->execute()) {
                $success_message = "Personal information updated successfully!";
                $stmt->execute();
                $result = $stmt->get_result();
                $student = $result->fetch_assoc();
            } else {
                $error_message = "Error updating profile.";
            }
            $update_stmt->close();
        }
        $email_check_stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (password_verify($current_password, $student['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 8) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $pwd_sql = "UPDATE users SET password = ? WHERE id = ?";
                $pwd_stmt = $conn->prepare($pwd_sql);
                $pwd_stmt->bind_param("si", $hashed_password, $student_id);
                if ($pwd_stmt->execute()) {
                    $password_success = "Password changed successfully!";
                } else {
                    $password_error = "Error changing password.";
                }
                $pwd_stmt->close();
            } else {
                $password_error = "Password must be at least 8 characters long.";
            }
        } else {
            $password_error = "New passwords do not match.";
        }
    } else {
        $password_error = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile | ACTS Learning Center</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="css/studentstyle.css" rel="stylesheet">
<link rel="icon" type="image/png" href="actslogo.png">
<style>
body{background:#f8fafc}
.header-section{margin-bottom:32px}
.header-section h2{font-size:1.75rem;font-weight:700;color:#1a202c;margin-bottom:8px}
.header-section p{color:#6B7280;margin:0;font-size:1rem}
.qr-quick-access{background:linear-gradient(135deg,#8b5cf6 0%,#6366f1 100%);border-radius:16px;padding:24px;margin-bottom:32px;color:white;box-shadow:0 8px 32px rgba(139,92,246,0.3);position:relative;overflow:hidden}
.qr-quick-access::before{content:'';position:absolute;top:-50%;right:-20%;width:200px;height:200px;background:rgba(255,255,255,0.1);border-radius:50%}
.qr-quick-access-content{position:relative;z-index:1;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px}
.qr-quick-access-text h4{color:white;font-weight:700;margin-bottom:8px;font-size:1.3rem}
.qr-quick-access-text p{color:rgba(255,255,255,0.9);margin:0;font-size:1rem}
.btn-view-qr{background:white;color:#8b5cf6;border:none;padding:12px 28px;border-radius:12px;font-weight:700;transition:all 0.3s ease;display:inline-flex;align-items:center;gap:10px;box-shadow:0 4px 16px rgba(0,0,0,0.15);text-decoration:none}
.btn-view-qr:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.25);background:#f8f9ff;color:#7c3aed}
.profile-tabs{display:flex;gap:8px;margin-bottom:32px;background:white;padding:6px;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,0.06);border:1px solid #f1f5f9}
.profile-tab{flex:1;padding:12px 20px;border:none;background:transparent;border-radius:10px;font-weight:600;color:#64748b;cursor:pointer;transition:all 0.3s ease;display:flex;align-items:center;justify-content:center;gap:8px;font-size:0.9rem}
.profile-tab:hover{background:#f8fafc;color:#667eea}
.profile-tab.active{background:linear-gradient(135deg,#667eea,#764ba2);color:white;box-shadow:0 4px 12px rgba(102,126,234,0.3)}
.profile-tab-content{display:none}
.profile-tab-content.active{display:block;animation:fadeIn 0.4s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:translateY(0)}}
.profile-section{background:white;border-radius:16px;padding:24px;margin-bottom:24px;box-shadow:0 4px 16px rgba(0,0,0,0.08);border:1px solid #f1f5f9}
.profile-section-header{display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:2px solid #f1f5f9}
.profile-section-icon{width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,rgba(102,126,234,0.15),rgba(118,75,162,0.15));display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#667eea}
.profile-section-title{font-size:1.3rem;font-weight:700;color:#1a202c;margin:0}
.form-field{margin-bottom:20px}
.form-label{display:block;font-weight:600;color:#1a202c;margin-bottom:8px;font-size:0.9rem}
.form-label i{color:#667eea;margin-right:6px}
.form-control-custom{width:100%;padding:12px 16px;border:2px solid #E5E7EB;border-radius:10px;font-size:0.95rem;transition:all 0.3s ease;background:white;font-weight:500;color:#1a202c}
.form-control-custom:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 4px rgba(102,126,234,0.1)}
.form-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
.btn-update{background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;padding:12px 32px;border-radius:10px;font-weight:700;font-size:0.95rem;display:inline-flex;align-items:center;gap:8px;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 12px rgba(102,126,234,0.3)}
.btn-update:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(102,126,234,0.4)}
.btn-cancel{background:#f1f5f9;color:#64748b;border:none;padding:12px 32px;border-radius:10px;font-weight:700;font-size:0.95rem;display:inline-flex;align-items:center;gap:8px;cursor:pointer;transition:all 0.3s ease;margin-left:12px}
.btn-cancel:hover{background:#e2e8f0}
.alert-custom{padding:16px 20px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-weight:600;font-size:0.95rem}
.alert-success{background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(5,150,105,0.15));color:#065f46;border:2px solid #10b981}
.alert-error{background:linear-gradient(135deg,rgba(239,68,68,0.15),rgba(220,38,38,0.15));color:#991b1b;border:2px solid #ef4444}
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
.info-card{background:#F9FAFB;padding:20px;border-radius:12px;border:2px solid #E5E7EB;transition:all 0.3s ease}
.info-card:hover{transform:translateY(-4px);border-color:#667eea;box-shadow:0 8px 16px rgba(102,126,234,0.15)}
.info-card-header{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.info-card-icon{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-size:1.1rem}
.info-card-label{font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase}
.info-card-value{font-size:1.1rem;font-weight:700;color:#1a202c;line-height:1.4}
.stats-card{background:white;border-radius:16px;padding:24px;box-shadow:0 4px 16px rgba(0,0,0,0.08);border:1px solid #f1f5f9;position:relative;overflow:hidden}
.stats-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#667eea,#764ba2)}
.stats-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.stats-card-title{font-weight:700;color:#1a202c;font-size:1rem}
.stats-card-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.stats-card-icon.primary{background:linear-gradient(135deg,rgba(102,126,234,0.15),rgba(118,75,162,0.15));color:#667eea}
.stats-card-icon.purple{background:linear-gradient(135deg,rgba(139,92,246,0.15),rgba(124,58,237,0.15));color:#8b5cf6}
.stats-card-value{font-size:1.8rem;font-weight:700;color:#1a202c;margin-bottom:8px}
.stats-card-label{font-size:0.85rem;color:#64748b}
.edit-mode-toggle{display:flex;align-items:center;gap:12px;padding:14px 20px;background:linear-gradient(135deg,rgba(59,130,246,0.1),rgba(37,99,235,0.1));border-radius:12px;margin-bottom:24px;border:2px solid rgba(59,130,246,0.2)}
.edit-mode-toggle i{color:#3b82f6;font-size:1.2rem}
.edit-mode-toggle span{font-weight:600;color:#1a202c;font-size:0.9rem}
.tutorial-badge{display:inline-block;padding:8px 16px;background:linear-gradient(135deg,rgba(102,126,234,0.1),rgba(118,75,162,0.1));border:2px solid rgba(102,126,234,0.25);border-radius:20px;font-weight:600;color:#1a202c;font-size:0.85rem;margin:4px}
.password-strength{height:6px;border-radius:3px;background:#E5E7EB;margin-top:10px;overflow:hidden}
.password-strength-bar{height:100%;transition:all 0.3s ease;border-radius:3px}
.password-strength-weak{width:33%;background:linear-gradient(90deg,#ef4444,#dc2626)}
.password-strength-medium{width:66%;background:linear-gradient(90deg,#f59e0b,#d97706)}
.password-strength-strong{width:100%;background:linear-gradient(90deg,#10b981,#059669)}
.password-requirements{margin-top:16px;padding:16px;background:#f8fafc;border-radius:10px;border:1px solid #E5E7EB}
.password-requirements h6{font-size:0.8rem;font-weight:700;color:#64748b;margin-bottom:12px;text-transform:uppercase}
.password-requirements ul{list-style:none;padding:0;margin:0}
.password-requirements li{font-size:0.85rem;color:#64748b;padding:6px 0;display:flex;align-items:center;gap:8px}
.section-divider{height:2px;background:linear-gradient(90deg,transparent,#E5E7EB,transparent);margin:24px 0}
.form-actions{display:flex;gap:12px;align-items:center;padding-top:20px;border-top:2px solid #f1f5f9;margin-top:20px}
@media (max-width:768px){
.profile-tabs{flex-direction:column}
.form-row{grid-template-columns:1fr}
.form-actions{flex-direction:column}
.btn-update,.btn-cancel{width:100%;justify-content:center;margin-left:0}
}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sidebar-header">
    <div class="student-profile">
      <div class="student-avatar"><?php echo strtoupper(substr($student['student_name'],0,1));?></div>
      <div class="student-info">
        <h4><?php echo htmlspecialchars($student['student_name']);?></h4>
        <p><?php echo htmlspecialchars($student['email']);?></p>
      </div>
    </div>
  </div>
  <div class="sidebar-menu">
    <a href="studentdashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
    <a href="student_myclass.php"><i class="fas fa-book-open"></i><span>My Classes</span></a>
    <a href="student_tutorprofile.php"><i class="fas fa-chalkboard-teacher"></i><span>List of Tutor</span></a>
    <a href="student_attendance.php"><i class="fas fa-clipboard-check"></i><span>Attendance</span></a>
    <a href="student_billing.php"><i class="fas fa-file-invoice-dollar"></i><span>Billing</span></a>
    <a href="student_profile.php" class="active"><i class="fas fa-user-circle"></i><span>My Profile</span></a>
    <a href="student_feedback.php"><i class="fas fa-comment-dots"></i><span>Feedback & Reports</span></a>
    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
  </div>
</div>
<div class="main">
<div class="header-section">
<h2>My Profile ⚙️</h2>
<p>Manage your personal information and account settings</p>
</div>
<div class="qr-quick-access">
<div class="qr-quick-access-content">
<div class="qr-quick-access-text">
<h4><i class="fas fa-qrcode"></i> Attendance QR Code</h4>
<p>Use your QR code for quick attendance check-in</p>
</div>
<a href="student_qr_display.php" class="btn-view-qr"><i class="fas fa-eye"></i> View My QR Code</a>
</div>
</div>
<div class="profile-tabs">
<button class="profile-tab active" onclick="switchTab('overview')"><i class="fas fa-id-card"></i> Overview</button>
<button class="profile-tab" onclick="switchTab('personal')"><i class="fas fa-user-edit"></i> Personal Info</button>
<button class="profile-tab" onclick="switchTab('academic')"><i class="fas fa-graduation-cap"></i> Academic</button>
<button class="profile-tab" onclick="switchTab('security')"><i class="fas fa-shield-alt"></i> Security</button>
</div>
<div id="overview" class="profile-tab-content active">
<div class="row g-4 mb-4">
<div class="col-lg-6">
<div class="stats-card">
<div class="stats-card-header">
<h6 class="stats-card-title">Enrolled Tutorials</h6>
<div class="stats-card-icon primary"><i class="fas fa-book"></i></div>
</div>
<div class="stats-card-value"><?php $c=0;if(!empty($student['subject_tutorials']))$c+=count(explode(',',$student['subject_tutorials']));if(!empty($student['computer_tutorials']))$c+=count(explode(',',$student['computer_tutorials']));echo $c;?></div>
<div class="stats-card-label">Active Subjects</div>
</div>
</div>
<div class="col-lg-6">
<div class="stats-card">
<div class="stats-card-header">
<h6 class="stats-card-title">Sessions/Week</h6>
<div class="stats-card-icon purple"><i class="fas fa-calendar-week"></i></div>
</div>
<div class="stats-card-value"><?php echo isset($student['sessions_per_week'])?$student['sessions_per_week']:'0';?></div>
<div class="stats-card-label">Tutorial Sessions</div>
</div>
</div>
</div>
<div class="profile-section">
<div class="profile-section-header">
<div class="profile-section-icon"><i class="fas fa-info-circle"></i></div>
<h3 class="profile-section-title">Quick Information</h3>
</div>
<div class="info-grid">
<div class="info-card">
<div class="info-card-header">
<div class="info-card-icon"><i class="fas fa-book-reader"></i></div>
<span class="info-card-label">Subject Tutorials</span>
</div>
<div class="info-card-value"><?php echo !empty($student['subject_tutorials'])?htmlspecialchars($student['subject_tutorials']):'None enrolled';?></div>
</div>
<div class="info-card">
<div class="info-card-header">
<div class="info-card-icon"><i class="fas fa-laptop-code"></i></div>
<span class="info-card-label">Computer Tutorials</span>
</div>
<div class="info-card-value"><?php echo !empty($student['computer_tutorials'])?htmlspecialchars($student['computer_tutorials']):'None enrolled';?></div>
</div>
<div class="info-card">
<div class="info-card-header">
<div class="info-card-icon"><i class="fas fa-clock"></i></div>
<span class="info-card-label">Schedule Preference</span>
</div>
<div class="info-card-value"><?php if(!empty($student['schedule_preference']))echo $student['schedule_preference']==='self'?'Self-Scheduled':'Tutor-Suggested';else echo 'Not set';?></div>
</div>
<div class="info-card">
<div class="info-card-header">
<div class="info-card-icon"><i class="fas fa-map-marker-alt"></i></div>
<span class="info-card-label">Address</span>
</div>
<div class="info-card-value"><?php echo !empty($student['address'])?htmlspecialchars($student['address']):'Not provided';?></div>
</div>
</div>
</div>
</div>
<div id="personal" class="profile-tab-content">
<?php if(isset($success_message)):?><div class="alert-custom alert-success"><i class="fas fa-check-circle"></i><?php echo $success_message;?></div><?php endif;?>
<?php if(isset($error_message)):?><div class="alert-custom alert-error"><i class="fas fa-exclamation-circle"></i><?php echo $error_message;?></div><?php endif;?>
<div class="profile-section">
<div class="profile-section-header">
<div class="profile-section-icon"><i class="fas fa-user"></i></div>
<h3 class="profile-section-title">Personal Information</h3>
</div>
<form method="POST">
<div class="form-row">
<div class="form-field">
<label class="form-label"><i class="fas fa-user"></i> Full Name</label>
<input type="text" name="student_name" class="form-control-custom" value="<?php echo htmlspecialchars($student['student_name']);?>" required>
</div>
<div class="form-field">
<label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
<input type="email" name="email" class="form-control-custom" value="<?php echo htmlspecialchars($student['email']);?>" required>
</div>
</div>
<div class="form-row">
<div class="form-field">
<label class="form-label"><i class="fas fa-phone"></i> Phone Number</label>
<input type="tel" name="contact" class="form-control-custom" value="<?php echo htmlspecialchars(@$student['contact']);?>">
</div>
<div class="form-field">
<label class="form-label"><i class="fas fa-calendar"></i> Date of Birth</label>
<input type="date" name="date_of_birth" class="form-control-custom" value="<?php echo htmlspecialchars(@$student['birthday']);?>">
</div>
</div>
<div class="form-field">
<label class="form-label"><i class="fas fa-map-marker-alt"></i> Complete Address</label>
<textarea name="address" class="form-control-custom" rows="3"><?php echo htmlspecialchars(!empty($student['address'])?$student['address']:'');?></textarea>
</div>
<div class="form-actions">
<button type="submit" name="update_profile" class="btn-update"><i class="fas fa-save"></i> Save Changes</button>
<button type="reset" class="btn-cancel"><i class="fas fa-undo"></i> Reset</button>
</div>
</form>
</div>
<div class="section-divider"></div>
<div class="profile-section">
<div class="profile-section-header">
<div class="profile-section-icon"><i class="fas fa-users"></i></div>
<h3 class="profile-section-title">Guardian & Emergency Contact</h3>
</div>
<form method="POST">
<div class="form-row">
<div class="form-field">
<label class="form-label"><i class="fas fa-user-shield"></i> Guardian Name</label>
<input type="text" name="parents_name" class="form-control-custom" value="<?php echo htmlspecialchars(!empty($student['parents_name']) ? $student['parents_name'] : '');?>">
</div>
<div class="form-field">
<label class="form-label"><i class="fas fa-phone-alt"></i> Guardian Contact</label>
<input type="tel" name="parents_contact" class="form-control-custom" value="<?php echo htmlspecialchars(@$student['parents_contact']);?>">
</div>
</div>
<div class="form-actions">
<button type="submit" name="update_profile" class="btn-update"><i class="fas fa-save"></i> Update Guardian Info</button>
<button type="reset" class="btn-cancel"><i class="fas fa-undo"></i> Reset</button>
</div>
</form>
</div>
</div>
<div id="academic" class="profile-tab-content">
<div class="profile-section">
<div class="profile-section-header">
<div class="profile-section-icon"><i class="fas fa-book-open"></i></div>
<h3 class="profile-section-title">Tutorial Enrollment</h3>
</div>
<div class="edit-mode-toggle">
<i class="fas fa-info-circle"></i>
<span>To modify your tutorial enrollment, please contact your administrator</span>
</div>
<div class="form-field">
<label class="form-label"><i class="fas fa-book"></i> Subject Tutorials</label>
<div style="padding:16px;background:#f8fafc;border-radius:12px;border:2px dashed #cbd5e1">
<?php if(!empty($student['subject_tutorials'])):foreach(explode(',',$student['subject_tutorials'])as $t):?>
<span class="tutorial-badge"><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars(trim($t));?></span>
<?php endforeach;else:?>
<p style="color:#64748b;margin:0"><i class="fas fa-info-circle" style="color:#667eea"></i> Not enrolled in any subject tutorials</p>
<?php endif;?>
</div>
</div>
<div class="form-field">
<label class="form-label"><i class="fas fa-laptop-code"></i> Computer Tutorials</label>
<div style="padding:16px;background:#f8fafc;border-radius:12px;border:2px dashed #cbd5e1">
<?php if(!empty($student['computer_tutorials'])):foreach(explode(',',$student['computer_tutorials'])as $t):?>
<span class="tutorial-badge"><i class="fas fa-laptop-code"></i> <?php echo htmlspecialchars(trim($t));?></span>
<?php endforeach;else:?>
<p style="color:#64748b;margin:0"><i class="fas fa-info-circle" style="color:#667eea"></i> Not enrolled in any computer tutorials</p>
<?php endif;?>
</div>
</div>
</div>
</div>
<div id="security" class="profile-tab-content">
<?php if(isset($password_success)):?><div class="alert-custom alert-success"><i class="fas fa-check-circle"></i><?php echo $password_success;?></div><?php endif;?>
<?php if(isset($password_error)):?><div class="alert-custom alert-error"><i class="fas fa-exclamation-circle"></i><?php echo $password_error;?></div><?php endif;?>
<div class="profile-section">
<div class="profile-section-header">
<div class="profile-section-icon"><i class="fas fa-lock"></i></div>
<h3 class="profile-section-title">Change Password</h3>
</div>
<form method="POST" id="passwordForm">
<div class="form-field">
<label class="form-label"><i class="fas fa-key"></i> Current Password</label>
<input type="password" name="current_password" class="form-control-custom" required>
</div>
<div class="section-divider"></div>
<div class="form-field">
<label class="form-label"><i class="fas fa-lock"></i> New Password</label>
<input type="password" name="new_password" id="newPassword" class="form-control-custom" required>
<div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div></div>
</div>
<div class="form-field">
<label class="form-label"><i class="fas fa-check-double"></i> Confirm New Password</label>
<input type="password" name="confirm_password" class="form-control-custom" required>
</div>
<div class="password-requirements">
<h6>Password Requirements:</h6>
<ul>
<li><i class="fas fa-circle"></i> At least 8 characters long</li>
<li><i class="fas fa-circle"></i> Contains uppercase and lowercase letters</li>
<li><i class="fas fa-circle"></i> Includes at least one number</li>
<li><i class="fas fa-circle"></i> Contains at least one special character</li>
</ul>
</div>
<div class="form-actions">
<button type="submit" name="change_password" class="btn-update"><i class="fas fa-shield-alt"></i> Update Password</button>
<button type="reset" class="btn-cancel"><i class="fas fa-times"></i> Cancel</button>
</div>
</form>
</div>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function switchTab(t){document.querySelectorAll('.profile-tab').forEach(e=>e.classList.remove('active'));document.querySelectorAll('.profile-tab-content').forEach(e=>e.classList.remove('active'));event.target.closest('.profile-tab').classList.add('active');document.getElementById(t).classList.add('active')}
document.getElementById('newPassword')?.addEventListener('input',function(e){const p=e.target.value;const b=document.getElementById('strengthBar');let s=0;if(p.length>=8)s++;if(p.match(/[a-z]/)&&p.match(/[A-Z]/))s++;if(p.match(/[0-9]/))s++;if(p.match(/[^a-zA-Z0-9]/))s++;b.className='password-strength-bar';if(s<=1)b.classList.add('password-strength-weak');else if(s<=3)b.classList.add('password-strength-medium');else b.classList.add('password-strength-strong')});
document.getElementById('passwordForm')?.addEventListener('submit',function(e){if(document.querySelector('input[name="new_password"]').value!==document.querySelector('input[name="confirm_password"]').value){e.preventDefault();alert('New passwords do not match!')}});
</script>
</body>
</html>