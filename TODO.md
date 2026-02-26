# QR Code Attendance System - Complete Setup Guide

## 📋 Overview
This is a complete QR Code-based attendance system for ACTS Learning Center that uses webcam scanning for automatic student check-in.

## 🗄️ Database Setup

### Step 1: Add QR Code Column to Users Table
Run this SQL command in your database:

```sql
ALTER TABLE users ADD COLUMN qr_code VARCHAR(255) UNIQUE AFTER email;
```

### Step 2: Verify Attendance Table Structure
Make sure your attendance table has these columns:

```sql
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    time_in TIME NULL,
    time_out TIME NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    UNIQUE KEY unique_attendance (student_id, date)
);
```

## 📁 File Structure

Place these files in your project root:

```
your-project/
├── adminattendance.php              (Main attendance page with QR scanner)
├── process_qr_attendance.php        (Handles QR code scanning)
├── generate_student_qr.php          (Generates QR codes for students)
├── student_qr_display.php           (Shows/downloads individual QR codes)
├── save_attendance_batch.php        (Manual batch attendance)
└── delete_attendance.php            (Delete attendance records)
```

## 🚀 Installation Steps

### 1. Upload Files
- Upload all PHP files to your server
- Make sure `db_connect.php` is in the same directory

### 2. Generate QR Codes for Existing Students
Visit this URL in your browser (admin only):
```
http://yoursite.com/generate_student_qr.php
```

This will automatically create unique QR codes for all approved students.

### 3. Access the System
Admin attendance page:
```
http://yoursite.com/adminattendance.php
```

## 🎯 How It Works

### For Admins:

1. **QR Scanner Mode** (Recommended)
   - Click "QR Scanner" button
   - Allow webcam access when prompted
   - Point camera at student's QR code
   - Student is automatically marked present
   - Time is recorded automatically

2. **Manual Mode**
   - Click "Mark Manually" button
   - Select status for each student
   - Click "Save Attendance"

### For Students:

Students can access their QR code at:
```
http://yoursite.com/student_qr_display.php
```

Or from their student dashboard (you'll need to add a link).

## 🔧 Technical Details

### QR Code Generation
- Each student gets a unique SHA-256 hash QR code
- Format: `ACTS-{STUDENT_ID}-{NAME_INITIALS}-{TIMESTAMP}`
- Stored in `users.qr_code` column

### Webcam Scanner
- Uses **jsQR library** for QR code detection
- Works with any webcam/built-in camera
- Auto-stops after successful scan
- Prevents duplicate scans

### Security Features
- Session validation for admin access
- Prevents duplicate attendance for same day
- SQL injection protection (prepared statements)
- QR codes are unique and non-guessable

## 📱 Browser Compatibility

**Fully Supported:**
- Chrome 53+
- Firefox 36+
- Safari 11+
- Edge 12+

**Requirements:**
- HTTPS connection (for webcam access)
- Webcam permissions granted

## 🎨 Features

✅ Real-time QR code scanning via webcam
✅ Automatic attendance marking
✅ Manual attendance option
✅ Duplicate prevention
✅ Time tracking (time in/out)
✅ Status options (Present, Absent, Late, Excused)
✅ Student QR code download
✅ Attendance statistics dashboard
✅ Filter and search functionality
✅ Export capability (ready for implementation)

## 🔐 Adding QR Code to Student Dashboard

Add this code to your student dashboard:

```php
<a href="student_qr_display.php" class="btn btn-primary">
    <i class="fas fa-qrcode"></i> View My QR Code
</a>
```

## 📊 Admin Dashboard Features

### Statistics Cards
- Total Present
- Total Absent
- Total Late
- Total Excused

### Actions
- **QR Scanner**: Scan student QR codes
- **Mark Manually**: Batch mark attendance
- **Export**: Download attendance reports
- **Edit**: Modify attendance records
- **Delete**: Remove attendance records

## 🛠️ Troubleshooting

### Problem: Webcam not working
**Solution:**
- Ensure you're using HTTPS
- Grant camera permissions in browser
- Check if another app is using the camera

### Problem: QR codes not generating
**Solution:**
- Run `generate_student_qr.php` as admin
- Check database permissions
- Verify `qr_code` column exists

### Problem: Duplicate attendance error
**Solution:**
- This is normal - prevents double check-in
- Delete old record if needed to re-mark

### Problem: "Student not found" error
**Solution:**
- Ensure student has QR code generated
- Check student status is 'approved'
- Verify QR code data matches database

## 🎓 Usage Workflow

### Daily Attendance Process:

1. **Admin opens** `adminattendance.php`
2. **Clicks** "QR Scanner" button
3. **Students show** their QR code to webcam
4. **System automatically**:
   - Scans QR code
   - Identifies student
   - Marks as present
   - Records time
   - Shows success message
5. **Page auto-refreshes** to show updated attendance

## 📸 Printing QR Codes

Students can:
1. Visit `student_qr_display.php`
2. Click "Download QR Code"
3. Save image to phone/print it
4. Use printed/digital copy for attendance

## 🔄 Future Enhancements (Optional)

- Email QR codes to students
- SMS notifications when marked present
- Attendance reports (PDF export)
- Mobile app for students
- QR code expiration/rotation
- Geolocation verification
- Photo capture on check-in

## 📝 Important Notes

1. **HTTPS Required**: Webcam access only works on HTTPS
2. **Camera Permissions**: Users must allow camera access
3. **One Scan Per Day**: System prevents duplicate check-ins
4. **Unique QR Codes**: Each student has a unique, secure QR code
5. **Backup Option**: Manual marking available if scanner fails

## 👨‍💻 Support

For issues or questions:
1. Check this documentation first
2. Verify database structure
3. Check browser console for errors
4. Ensure all files are uploaded correctly

---

## Quick Start Checklist

- [ ] Database: Add `qr_code` column to users table
- [ ] Files: Upload all PHP files
- [ ] Generate: Run `generate_student_qr.php`
- [ ] Test: Open `adminattendance.php` and try QR scanner
- [ ] Student: Add QR code link to student dashboard
- [ ] Production: Ensure site is using HTTPS

**System Ready!** 🎉
