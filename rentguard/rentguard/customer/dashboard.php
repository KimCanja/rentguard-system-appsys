<?php
$page_title = 'Dashboard';
require_once '../includes/header.php';
require_once '../config/database.php';

if (!isCustomer()) {
    redirect(BASE_URL . 'auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Get customer info
$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer = $stmt->fetch();

// Get recent rentals
$stmt = $pdo->prepare("
    SELECT r.*, v.model, v.plate_number 
    FROM rentals r 
    JOIN vehicles v ON r.vehicle_id = v.vehicle_id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_rentals = $stmt->fetchAll();

// Get stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM rentals WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_rentals = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as active FROM rentals WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$active_rentals = $stmt->fetch()['active'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending FROM rentals WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_rentals = $stmt->fetch()['pending'];
?>

<?php require_once '../includes/customer-navbar.php'; ?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1><i class="fas fa-chart-line"></i> Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <p class="text-muted">Manage your vehicle rentals and bookings</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon accent">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $total_rentals; ?></h3>
                    <p>Total Rentals</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $pending_rentals; ?></h3>
                    <p>Pending Approvals</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $active_rentals; ?></h3>
                    <p>Active Rentals</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $customer['damage_incidents_count'] ?? 0; ?></h3>
                    <p>Damage Reports</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="browse-vehicles.php" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Browse Vehicles
                    </a>
                    <a href="my-rentals.php" class="btn btn-secondary me-2">
                        <i class="fas fa-history"></i> View My Rentals
                    </a>
                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-user"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Rentals -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Rentals</h5>
                </div>
                <div class="card-body">
                    <?php if (count($recent_rentals) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Plate</th>
                                        <th>Pickup Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_rentals as $rental): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($rental['model']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($rental['plate_number']); ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($rental['pickup_date'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($rental['return_date'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $rental['status']; ?>">
                                                    <?php echo ucfirst($rental['status']); ?>
                                                </span>
                                            </td>
                                            <td>$<?php echo number_format($rental['total_price'], 2); ?></td>
                                            <td>
                                                <a href="rental-details.php?id=<?php echo $rental['rental_id']; ?>" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-inbox"></i> No rentals yet. <a href="browse-vehicles.php">Start booking now!</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
