<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page    = 'equipment-list';
$success = '';
$error   = '';

// Auto-add missing columns to equipment table if they don't exist
$cols_to_add = [
    "working_units"   => "ALTER TABLE equipment ADD COLUMN working_units int(11) NOT NULL DEFAULT 0",
    "purchase_date"   => "ALTER TABLE equipment ADD COLUMN purchase_date date DEFAULT NULL",
    "purchase_amount" => "ALTER TABLE equipment ADD COLUMN purchase_amount decimal(10,2) DEFAULT NULL",
    "description"     => "ALTER TABLE equipment ADD COLUMN description text DEFAULT NULL"
];
foreach ($cols_to_add as $col => $sql) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM equipment LIKE '$col'");
    if ($check && mysqli_num_rows($check) === 0) {
        mysqli_query($conn, $sql);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_name  = mysqli_real_escape_string($conn, $_POST['equipment_name']);
    $quantity        = (int)$_POST['quantity'];
    $working_units   = (int)$_POST['working_units'];
    $status          = $_POST['status'];
    $purchase_date   = mysqli_real_escape_string($conn, $_POST['purchase_date']);
    $purchase_amount = mysqli_real_escape_string($conn, $_POST['purchase_amount']);
    $description     = mysqli_real_escape_string($conn, $_POST['description']);

    if (empty($equipment_name)) {
        $error = "Equipment name is required!";
    } elseif ($quantity < 1) {
        $error = "Quantity must be at least 1!";
    } elseif ($working_units > $quantity) {
        $error = "Working units cannot be more than total quantity!";
    } else {
        // Try full insert first; fall back to minimal columns if DB schema is older
        $insert = mysqli_query($conn, "INSERT INTO equipment (equipment_name, quantity, working_units, status, purchase_date, purchase_amount, description) 
            VALUES ('$equipment_name', '$quantity', '$working_units', '$status', '$purchase_date', '$purchase_amount', '$description')");
        if (!$insert) {
            // Fallback: try with only base columns that exist in older schema
            $insert = mysqli_query($conn, "INSERT INTO equipment (equipment_name, quantity, status) 
                VALUES ('$equipment_name', '$quantity', '$status')");
        }
        if ($insert) {
            header("Location: equipment-list.php?msg=added");
            exit();
        } else {
            $error = "Failed to add equipment. Please check that the equipment table has the required columns (working_units, purchase_date, purchase_amount, description). DB Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Equipment - Gym Management</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/common.css">
</head>
<body>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>

<div class="main-wrapper">
  <div class="main-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Add Equipment</h1>
        <p class="page-subtitle">Register new gym equipment</p>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="app-alert app-alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="app-alert app-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form method="POST">

        <div class="section">
          <h3>Equipment Details</h3>
          <p class="section-subtitle">Enter the equipment information below</p>

          <div class="form-row">
            <div>
              <label>Equipment Name *</label>
              <input type="text" name="equipment_name" placeholder="e.g., Treadmill, Dumbbells, Bench Press" required>
            </div>
            <div>
              <label>Quantity *</label>
              <input type="number" name="quantity" min="1" value="1" required>
            </div>
            <div>
              <label>Working Units *</label>
              <input type="number" name="working_units" min="0" value="1" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Status *</label>
              <select name="status" required>
                <option value="Working">Working</option>
                <option value="Maintenance">Under Maintenance</option>
                <option value="Out of Order">Out of Order</option>
              </select>
            </div>
            <div>
              <label>Purchase Date</label>
              <input type="date" name="purchase_date">
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Purchase Amount (₹)</label>
              <input type="number" name="purchase_amount" min="0" step="0.01" placeholder="e.g. 25000">
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Description / Notes</label>
              <textarea name="description" placeholder="Any additional notes about the equipment..."></textarea>
            </div>
          </div>

        </div>

        <div class="flex gap-2 mt-10">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-plus"></i> Add Equipment</button>
          <a href="equipment-list.php" class="btn app-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
        </div>

      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>