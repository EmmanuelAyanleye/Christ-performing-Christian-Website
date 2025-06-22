<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/partials/session_auth.php';

$pageTitle = "Manage Newsletter Subscribers";
$message = '';

// Handle Actions (Add, Edit, Delete, Unsubscribe)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subscriber'])) {
        $email = sanitize_input($_POST['email']);
        $name = sanitize_input($_POST['name']);
        $is_active = $_POST['is_active'] === '1' ? 1 : 0;

        try {
            $stmt = $pdo->prepare("INSERT INTO subscribers (email, name, is_active) VALUES (?, ?, ?)");
            $stmt->execute([$email, $name, $is_active]);
            $message = '<div class="alert alert-success">Subscriber added successfully.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error adding subscriber: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } elseif (isset($_POST['edit_subscriber'])) {
        $id = (int)$_POST['id'];
        $email = sanitize_input($_POST['email']);
        $name = sanitize_input($_POST['name']);
        $is_active = $_POST['is_active'] === '1' ? 1 : 0;

        try {
            $stmt = $pdo->prepare("UPDATE subscribers SET email = ?, name = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$email, $name, $is_active, $id]);
            $message = '<div class="alert alert-success">Subscriber updated successfully.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error updating subscriber: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } elseif (isset($_POST['delete_subscriber'])) {
        $id = (int)$_POST['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM subscribers WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<div class="alert alert-success">Subscriber deleted successfully.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error deleting subscriber: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } elseif (isset($_POST['unsubscribe'])) {
        $id = (int)$_POST['id'];
        try {
            $stmt = $pdo->prepare("UPDATE subscribers SET is_active = 0, unsubscribed_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<div class="alert alert-success">Subscriber unsubscribed successfully.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error unsubscribing: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Fetching subscribers with search and filters
$search = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterMonth = $_GET['month'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'newest';

$sql = "SELECT * FROM subscribers";
$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(email LIKE ? OR name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filterStatus) {
    $conditions[] = "is_active = ?";
    $params[] = $filterStatus === 'Active' ? 1 : 0;
}

if ($filterMonth) {
    $conditions[] = "MONTH(subscribed_at) = ? AND YEAR(subscribed_at) = ?";
    [$year, $month] = explode('-', $filterMonth);
    $params[] = $month;
    $params[] = $year;
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

switch ($sortBy) {
    case 'oldest':
        $sql .= " ORDER BY subscribed_at ASC";
        break;
    case 'email':
        $sql .= " ORDER BY email ASC";
        break;
    default:
        $sql .= " ORDER BY subscribed_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subscribers = $stmt->fetchAll();

// Stats
$totalSubscribers = $pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
$activeSubscribers = $pdo->query("SELECT COUNT(*) FROM subscribers WHERE is_active = 1")->fetchColumn();
$unsubscribedCount = $pdo->query("SELECT COUNT(*) FROM subscribers WHERE is_active = 0")->fetchColumn();
$newThisMonth = $pdo->query("SELECT COUNT(*) FROM subscribers WHERE MONTH(subscribed_at) = MONTH(CURRENT_DATE()) AND YEAR(subscribed_at) = YEAR(CURRENT_DATE())")->fetchColumn();

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/sidebar.php';
?>


<div class="main-content">
    <div class="header">
        <h1>Newsletter Subscribers</h1>
        <div class="header-actions">
            <!-- Export to Excel (Not implemented in this example) -->
            <button class="btn btn-success me-2" onclick="exportToExcel()">
                <i class="fas fa-file-excel"></i> Export to Excel
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubscriberModal" onclick="prepareAddModal()">
                <i class="fas fa-plus"></i> Add Subscriber
            </button>
        </div>
    </div>

    <?php if ($message) echo $message; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalSubscribers; ?></h3>
                    <p>Total Subscribers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success">
                <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                <div class="stat-info">
                    <h3><?php echo $newThisMonth; ?></h3>
                    <p>New This Month</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info">
                <div class="stat-icon"><i class="fas fa-envelope-open"></i></div>
                <div class="stat-info">
                    <h3><?php echo $activeSubscribers; ?></h3>
                    <p>Active Subscribers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning">
                <div class="stat-icon"><i class="fas fa-user-times"></i></div>
                <div class="stat-info">
                    <h3><?php echo $unsubscribedCount; ?></h3>
                    <p>Unsubscribed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter-section">
        <form action="newsletter.php" method="GET">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search subscribers..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="Active" <?php echo $filterStatus === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Unsubscribed" <?php echo $filterStatus === 'Unsubscribed' ? 'selected' : ''; ?>>Unsubscribed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="month" class="form-control" name="month" value="<?php echo htmlspecialchars($filterMonth); ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="sort_by">
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="email" <?php echo $sortBy === 'email' ? 'selected' : ''; ?>>Email A-Z</option>
                    </select>
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Subscribers Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Subscription Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                <td><?php echo htmlspecialchars($subscriber['name']); ?></td>
                                <td><?php echo format_date($subscriber['subscribed_at'], 'M j, Y'); ?></td>
                                <td><span class="badge bg-<?php echo $subscriber['is_active'] ? 'success' : 'warning'; ?>"><?php echo $subscriber['is_active'] ? 'Active' : 'Unsubscribed'; ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" onclick="prepareEditModal(<?php echo htmlspecialchars(json_encode($subscriber)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($subscriber['is_active']): ?>
                                        <form method="post" action="newsletter.php" style="display:inline;">
                                            <input type="hidden" name="unsubscribe" value="1">
                                            <input type="hidden" name="id" value="<?php echo $subscriber['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning btn-action">
                                                <i class="fas fa-user-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="newsletter.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this subscriber?');">
                                        <input type="hidden" name="delete_subscriber" value="1">
                                        <input type="hidden" name="id" value="<?php echo $subscriber['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Subscriber Modal -->
<div class="modal fade" id="subscriberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subscriberModalLabel">Add/Edit Subscriber</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="newsletter.php" id="subscriberForm">
                    <input type="hidden" name="id" id="subscriberId" value="0">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Unsubscribed</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="add_subscriber" id="saveButton">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
    const subscriberModal = new bootstrap.Modal(document.getElementById('subscriberModal'));

    function prepareAddModal() {
        document.getElementById('subscriberForm').reset();
        document.getElementById('subscriberId').value = '0';
        document.getElementById('subscriberModalLabel').innerText = 'Add New Subscriber';
        document.getElementById('saveButton').name = 'add_subscriber';
        subscriberModal.show();
    }

    function prepareEditModal(subscriber) {
        document.getElementById('subscriberForm').reset();
        document.getElementById('subscriberId').value = subscriber.id;
        document.getElementById('email').value = subscriber.email;
        document.getElementById('name').value = subscriber.name;
        document.getElementById('is_active').value = subscriber.is_active;
        document.getElementById('subscriberModalLabel').innerText = 'Edit Subscriber';
        document.getElementById('saveButton').name = 'edit_subscriber';
        subscriberModal.show();
    }

    function exportToExcel() {
        window.location.href = 'export_subscribers.php';
    }
</script>