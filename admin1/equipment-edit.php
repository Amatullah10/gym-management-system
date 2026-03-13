<?php
session_start();
require_once '../dbcon.php';

if (!isset($_SESSION['role']) || !isset($_SESSION['email'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$page = 'equipment-list';
$page_title = 'Edit Equipment - Gym Management';
$success = '';
$error   = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: equipment-list.php"); exit(); }

$equip = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM equipment WHERE id = '$id'"));
if (!$equip) { header("Location: equipment-list.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $equipment_name = mysqli_real_escape_string($conn, $_POST['equipment_name']);
    $quantity       = (int)$_POST['quantity'];
    $status         = $_POST['status'];

    if (empty($equipment_name)) {
        $error = "Equipment name is required!";
    } elseif ($quantity < 1) {
        $error = "Quantity must be at least 1!";
    } else {
        $update = mysqli_query($conn, "UPDATE equipment SET equipment_name='$equipment_name', quantity='$quantity', status='$status' WHERE id='$id'");
        if ($update) {
            header("Location: equipment-list.php?success=updated");
            exit();
        } else {
            $error = "Failed to update equipment! Error: " . mysqli_error($conn);
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
        <h1 class="page-title">Edit Equipment</h1>
        <p class="page-subtitle">Update equipment details</p>
      </div>
    </div>

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
              <label>Equipment Name</label>
              <input type="text" name="equipment_name" value="<?= htmlspecialchars($equip['equipment_name']) ?>" required>
            </div>
            <div>
              <label>Quantity</label>
              <input type="number" name="quantity" min="1" value="<?= $equip['quantity'] ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div>
              <label>Status</label>
              <select name="status" required>
                <option value="Working"      <?= $equip['status'] == 'Working'      ? 'selected' : '' ?>>Working</option>
                <option value="Maintenance"  <?= $equip['status'] == 'Maintenance'  ? 'selected' : '' ?>>Maintenance</option>
                <option value="Out of Order" <?= $equip['status'] == 'Out of Order' ? 'selected' : '' ?>>Out of Order</option>
              </select>
            </div>
          </div>
        </div>

        <div style="display:flex; gap:15px; margin-top:10px;">
          <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
          <a href="equipment-list.php" class="btn app-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Cancel</a>
        </div>

      </form>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>