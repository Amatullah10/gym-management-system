<?php
session_start();
require_once '../dbcon.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if user has permission
if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'trainer' && $_SESSION['role'] != 'receptionist') {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to mark attendance']);
    exit();
}

// Check if required data is provided
if (!isset($_POST['attendance_date']) || !isset($_POST['attendance_data'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

$attendance_date = $_POST['attendance_date'];
$attendance_data = json_decode($_POST['attendance_data'], true);
$marked_by = $_SESSION['email'];

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendance_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit();
}

// Validate attendance data
if (empty($attendance_data) || !is_array($attendance_data)) {
    echo json_encode(['success' => false, 'message' => 'No attendance data provided']);
    exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    $success_count = 0;
    $error_count = 0;
    
    foreach ($attendance_data as $member_id => $status) {
        // Validate status
        if ($status !== 'Present' && $status !== 'Absent') {
            $error_count++;
            continue;
        }
        
        // Check if attendance already exists for this member and date
        $check_sql = "SELECT id FROM attendance WHERE member_id = ? AND attendance_date = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "is", $member_id, $attendance_date);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing attendance
            if ($status === 'Present') {
                $update_sql = "UPDATE attendance 
                              SET status = ?, check_in_time = IFNULL(check_in_time, CURRENT_TIME), 
                                  marked_by = ?, updated_at = CURRENT_TIMESTAMP 
                              WHERE member_id = ? AND attendance_date = ?";
            } else {
                $update_sql = "UPDATE attendance 
                              SET status = ?, check_in_time = NULL, check_out_time = NULL,
                                  marked_by = ?, updated_at = CURRENT_TIMESTAMP 
                              WHERE member_id = ? AND attendance_date = ?";
            }
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssis", $status, $marked_by, $member_id, $attendance_date);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success_count++;
            } else {
                $error_count++;
            }
            mysqli_stmt_close($update_stmt);
        } else {
            // Insert new attendance
            if ($status === 'Present') {
                $insert_sql = "INSERT INTO attendance (member_id, attendance_date, status, check_in_time, marked_by) 
                              VALUES (?, ?, ?, CURRENT_TIME, ?)";
            } else {
                $insert_sql = "INSERT INTO attendance (member_id, attendance_date, status, marked_by) 
                              VALUES (?, ?, ?, ?)";
            }
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "isss", $member_id, $attendance_date, $status, $marked_by);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $success_count++;
            } else {
                $error_count++;
            }
            mysqli_stmt_close($insert_stmt);
        }
        
        mysqli_stmt_close($check_stmt);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    if ($error_count > 0) {
        echo json_encode([
            'success' => true, 
            'message' => "Attendance saved successfully! ($success_count saved, $error_count errors)",
            'success_count' => $success_count,
            'error_count' => $error_count
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'message' => "Attendance saved successfully for $success_count member(s)!",
            'success_count' => $success_count,
            'error_count' => 0
        ]);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>