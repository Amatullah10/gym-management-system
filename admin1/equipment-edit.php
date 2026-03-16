<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page    = 'equipment-list';
$success = '';
$error   = '';

// Get equipment id from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: equipment-list.php"); exit(); }

// Fetch existing equipment data
$res = mysqli_query($conn, "SELECT * FROM equipment WHERE id = $id");
if (!$res || mysqli_num_rows($res) === 0) { header("Location: equipment-list.php"); exit(); }
$equipment = mysqli_fetch_assoc($res);

// Handle form submission
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
        $update = mysqli_query($conn, "UPDATE equipment SET 
            equipment_name  = '$equipment_name',
            quantity        = '$quantity',
            working_units   = '$working_units',
            status          = '$status',
            purchase_date   = '$purchase_date',
            purchase_amount = '$purchase_amount',
            description     = '$description'
            WHERE id = $id");
        if ($update) {
            header("Location: equipment-list.php?success=Equipment updated successfully!");
            exit();
        } else {
            $error = "Failed to update equipment! Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Equipment - Gym Management</title>

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
        <h1 class="page-title">Edit Equipment</h1>
        <p class="page-subtitle">Update equipment details</p>
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
          <p class="section-subtitle">Update the equipment information below</p>

          <div class="form-row">
            <div>
              <label>Equipment Name *</label>
              <input type="text" name="equipment_name" value="<?= htmlspecialchars($equipment['equipment_name']) ?>" placeholder="e.g., Treadmill, Dumbbells, Bench Press" required>
            </div>
            <div>
              <label>Quantity *</label>
              <input type="number" name="quantity" min="1" value="<?= $equipment['quantity'] ?>" required>
            </div>
            <div>
              <label>Working Units *</label>
              <input type="number" name="working_units" min="0" value="<?= $equipment['working_units'] ?? 0 ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Status *</label>
              <select name="status" required>
                <option value="Working"      <?= $equipment['status'] == 'Working'      ? 'selected' : '' ?>>Working</option>
                <option value="Maintenance"  <?= $equipment['status'] == 'Maintenance'  ? 'selected' : '' ?>>Under Maintenance</option>
                <option value="Out of Order" <?= $equipment['status'] == 'Out of Order' ? 'selected' : '' ?>>Out of Order</option>
              </select>
            </div>
            <div>
              <label>Purchase Date</label>
              <input type="date" name="purchase_date" value="<?= htmlspecialchars($equipment['purchase_date'] ?? '') ?>">
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Purchase Amount (₹)</label>
              <input type="number" name="purchase_amount" min="0" step="0.01" placeholder="e.g. 25000" value="<?= htmlspecialchars($equipment['purchase_amount'] ?? '') ?>">
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Description / Notes</label>
              <textarea name="description" placeholder="Any additional notes about the equipment..."><?= htmlspecialchars($equipment['description'] ?? '') ?></textarea>
            </div>
          </div>

        </div>

        <div class="flex gap-2 mt-10">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Update Equipment</button>
          <a href="equipment-list.php" class="btn app-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
        </div>

      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>