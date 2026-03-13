<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'equipment-add';
$page_title = 'Add Equipment - Gym Management';
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_name = mysqli_real_escape_string($conn, $_POST['equipment_name']);
    $quantity       = (int)$_POST['quantity'];
    $status         = $_POST['status'];

    if (empty($equipment_name)) {
        $error = "Equipment name is required!";
    } elseif ($quantity < 1) {
        $error = "Quantity must be at least 1!";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO equipment (equipment_name, quantity, status) VALUES ('$equipment_name', '$quantity', '$status')");
        if ($insert) {
            $success = "Equipment <strong>$equipment_name</strong> added successfully!";
        } else {
            $error = "Failed to add equipment! Error: " . mysqli_error($conn);
        }
    }
}
?>
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
              <label>Equipment Name</label>
              <input type="text" name="equipment_name" placeholder="e.g. Treadmill, Dumbbells, Bench Press" required>
            </div>
            <div>
              <label>Quantity</label>
              <input type="number" name="quantity" min="1" value="1" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Status</label>
              <select name="status" required>
                <option value="Working">Working</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Out of Order">Out of Order</option>
              </select>
            </div>
          </div>
        </div>

        <div style="display:flex; gap:15px; margin-top:10px;">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-plus"></i> Add Equipment</button>
          <a href="equipment-list.php" class="btn app-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Cancel</a>
        </div>

      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>