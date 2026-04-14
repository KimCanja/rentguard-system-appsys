<?php
$page_title = 'Manage Vehicles';
require_once '../includes/header.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect(BASE_URL . 'auth/login.php');
}

$error = '';
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $vehicle_id = $_GET['delete'];
    try {
        // Get photo path to delete file
        $stmt = $pdo->prepare("SELECT photo_url FROM vehicles WHERE vehicle_id = ?");
        $stmt->execute([$vehicle_id]);
        $vehicle = $stmt->fetch();
        
        if ($vehicle && $vehicle['photo_url']) {
            $photo_path = $_SERVER['DOCUMENT_ROOT'] . '/rentguard/' . $vehicle['photo_url'];
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
        $stmt->execute([$vehicle_id]);
        $success = 'Vehicle deleted successfully!';
    } catch (PDOException $e) {
        $error = 'Failed to delete vehicle.';
    }
}

// Handle add/edit with photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id = $_POST['vehicle_id'] ?? null;
    $model = trim($_POST['model'] ?? '');
    $plate_number = trim($_POST['plate_number'] ?? '');
    $year = $_POST['year'] ?? '';
    $type = trim($_POST['type'] ?? '');
    $passenger_capacity = $_POST['passenger_capacity'] ?? 4;
    $status = $_POST['status'] ?? 'available';
    $current_mileage = $_POST['current_mileage'] ?? 0;
    $price_per_day = $_POST['price_per_day'] ?? 0;
    
    // Handle photo upload
    $photo_url = $_POST['existing_photo'] ?? '';
    if (isset($_FILES['vehicle_photo']) && $_FILES['vehicle_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/vehicles/';
        
        // Create directory if not exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['vehicle_photo']['name'], PATHINFO_EXTENSION);
        $filename = 'vehicle_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;
        $db_path = 'uploads/vehicles/' . $filename;
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (in_array($_FILES['vehicle_photo']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['vehicle_photo']['tmp_name'], $upload_path)) {
                // Delete old photo if exists
                if (!empty($photo_url) && file_exists('../' . $photo_url)) {
                    unlink('../' . $photo_url);
                }
                $photo_url = $db_path;
            } else {
                $error = 'Failed to upload photo.';
            }
        } else {
            $error = 'Invalid file type. Only JPG, PNG, and WEBP are allowed.';
        }
    }

    if (empty($model) || empty($plate_number) || empty($year) || empty($type)) {
        $error = 'All fields are required.';
    } else {
        try {
            if ($vehicle_id) {
                // Update existing vehicle
                if ($photo_url) {
                    $stmt = $pdo->prepare("
                        UPDATE vehicles 
                        SET model = ?, plate_number = ?, year = ?, type = ?, passenger_capacity = ?,
                            status = ?, current_mileage = ?, price_per_day = ?, photo_url = ?
                        WHERE vehicle_id = ?
                    ");
                    $stmt->execute([$model, $plate_number, $year, $type, $passenger_capacity, $status, $current_mileage, $price_per_day, $photo_url, $vehicle_id]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE vehicles 
                        SET model = ?, plate_number = ?, year = ?, type = ?, passenger_capacity = ?,
                            status = ?, current_mileage = ?, price_per_day = ?
                        WHERE vehicle_id = ?
                    ");
                    $stmt->execute([$model, $plate_number, $year, $type, $passenger_capacity, $status, $current_mileage, $price_per_day, $vehicle_id]);
                }
                $success = 'Vehicle updated successfully!';
            } else {
                // Insert new vehicle
                $stmt = $pdo->prepare("
                    INSERT INTO vehicles (model, plate_number, year, type, passenger_capacity, status, current_mileage, price_per_day, photo_url)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$model, $plate_number, $year, $type, $passenger_capacity, $status, $current_mileage, $price_per_day, $photo_url]);
                $success = 'Vehicle added successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Operation failed. Plate number may already exist.';
        }
    }
}

// Get vehicles
$stmt = $pdo->query("SELECT * FROM vehicles ORDER BY model ASC");
$vehicles = $stmt->fetchAll();

// Get vehicle to edit
$edit_vehicle = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE vehicle_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_vehicle = $stmt->fetch();
}
?>

<?php require_once '../includes/admin-sidebar.php'; ?>

<div class="main-content">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="fas fa-car"></i> Manage Vehicles</h1>
            <p class="text-muted">Add, edit, or delete vehicles from your fleet</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $edit_vehicle ? 'Edit Vehicle' : 'Add New Vehicle'; ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($edit_vehicle): ?>
                            <input type="hidden" name="vehicle_id" value="<?php echo $edit_vehicle['vehicle_id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="<?php echo htmlspecialchars($edit_vehicle['model'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Plate Number</label>
                            <input type="text" name="plate_number" class="form-control" value="<?php echo htmlspecialchars($edit_vehicle['plate_number'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="<?php echo htmlspecialchars($edit_vehicle['year'] ?? date('Y')); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <input type="text" name="type" class="form-control" placeholder="e.g., Sedan, SUV, Truck" value="<?php echo htmlspecialchars($edit_vehicle['type'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-users"></i> Number of Passengers
                            </label>
                            <select name="passenger_capacity" class="form-select" required>
                                <option value="2" <?php echo ($edit_vehicle['passenger_capacity'] ?? '') == 2 ? 'selected' : ''; ?>>2 passengers (Coupe)</option>
                                <option value="4" <?php echo ($edit_vehicle['passenger_capacity'] ?? 4) == 4 ? 'selected' : ''; ?>>4 passengers (Sedan/Compact)</option>
                                <option value="5" <?php echo ($edit_vehicle['passenger_capacity'] ?? '') == 5 ? 'selected' : ''; ?>>5 passengers (SUV/Sedan)</option>
                                <option value="6" <?php echo ($edit_vehicle['passenger_capacity'] ?? '') == 6 ? 'selected' : ''; ?>>6 passengers (MPV/SUV)</option>
                                <option value="7" <?php echo ($edit_vehicle['passenger_capacity'] ?? '') == 7 ? 'selected' : ''; ?>>7 passengers (SUV/Van)</option>
                                <option value="8" <?php echo ($edit_vehicle['passenger_capacity'] ?? '') == 8 ? 'selected' : ''; ?>>8 passengers (Van)</option>
                                <option value="10" <?php echo ($edit_vehicle['passenger_capacity'] ?? '') == 10 ? 'selected' : ''; ?>>10+ passengers (Large Van/Bus)</option>
                            </select>
                            <small class="text-muted">Select the maximum number of passengers this vehicle can accommodate</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vehicle Photo</label>
                            <?php if ($edit_vehicle && $edit_vehicle['photo_url']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo BASE_URL . $edit_vehicle['photo_url']; ?>" alt="Current Photo" style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 8px;">
                                    <input type="hidden" name="existing_photo" value="<?php echo $edit_vehicle['photo_url']; ?>">
                                    <small class="text-muted d-block mt-1">Current photo</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="vehicle_photo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                            <small class="text-muted">Upload vehicle image (JPG, PNG, WEBP). Max size: 5MB</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="available" <?php echo ($edit_vehicle['status'] ?? 'available') === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="rented" <?php echo ($edit_vehicle['status'] ?? '') === 'rented' ? 'selected' : ''; ?>>Rented</option>
                                <option value="maintenance" <?php echo ($edit_vehicle['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Mileage</label>
                            <input type="number" name="current_mileage" class="form-control" value="<?php echo htmlspecialchars($edit_vehicle['current_mileage'] ?? 0); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price per Day (₱)</label>
                            <input type="number" name="price_per_day" class="form-control" step="0.01" value="<?php echo htmlspecialchars($edit_vehicle['price_per_day'] ?? 0); ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> <?php echo $edit_vehicle ? 'Update Vehicle' : 'Add Vehicle'; ?>
                        </button>
                        <?php if ($edit_vehicle): ?>
                            <a href="vehicles.php" class="btn btn-secondary w-100 mt-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Fleet Inventory</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Model</th>
                                    <th>Plate</th>
                                    <th>Year</th>
                                    <th>Type</th>
                                    <th>Passengers</th>
                                    <th>Status</th>
                                    <th>Price/Day</th>
                                    <th>Mileage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <tr>
                                        <td>
                                            <?php if ($vehicle['photo_url']): ?>
                                                <img src="<?php echo BASE_URL . $vehicle['photo_url']; ?>" alt="<?php echo htmlspecialchars($vehicle['model']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: #E2E8F0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-car" style="color: #94A3B8;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($vehicle['plate_number']); ?></strong></td>
                                        <td><?php echo $vehicle['year']; ?></td>
                                        <td><?php echo htmlspecialchars($vehicle['type']); ?></td>
                                        <td>
                                            <i class="fas fa-users"></i> <?php echo $vehicle['passenger_capacity'] ?? 4; ?> seats
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch($vehicle['status']) {
                                                case 'available':
                                                    $badge_class = 'badge-active';
                                                    break;
                                                case 'rented':
                                                    $badge_class = 'badge-approved';
                                                    break;
                                                case 'maintenance':
                                                    $badge_class = 'badge-cancelled';
                                                    break;
                                                default:
                                                    $badge_class = 'badge-pending';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>" style="display: inline-block; min-width: 90px; text-align: center;">
                                                <strong><?php echo ucfirst($vehicle['status']); ?></strong>
                                            </span>
                                        </td>
                                        <td>₱<?php echo number_format($vehicle['price_per_day'], 2); ?></td>
                                        <td><?php echo number_format($vehicle['current_mileage']); ?> mi</td>
                                        <td>
                                            <a href="vehicles.php?edit=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-secondary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="vehicles.php?delete=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this vehicle?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-active {
    background: #3B82F6;
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
}
.badge-approved {
    background: #10B981;
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
}
.badge-cancelled {
    background: #EF4444;
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
}
.badge-pending {
    background: #F59E0B;
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
}
</style>

<?php require_once '../includes/footer.php'; ?>