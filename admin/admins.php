<?php
require_once '../includes/config.php';
require_once __DIR__ . '/partials/session_auth.php';

// This page is for super admins only
require_super_admin();

$message = '';

// Add Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_admin'])) {
    $name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $role = sanitize_input($_POST['role']);
    $status = strtolower(sanitize_input($_POST['status']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $message = '<div class="alert alert-danger">Email already exists!</div>';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, role, status, password, username) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $role, $status, $password, strtolower(strtok($name, " ")) . rand(100,999)]);
        $message = '<div class="alert alert-success">Admin added successfully!</div>';
    }
}

// Update Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $id = (int)$_POST['admin_id'];
    $name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $role = sanitize_input($_POST['role']);
    $status = strtolower(sanitize_input($_POST['status']));
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Prevent demoting the last super admin
    $currentUserStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $currentUserStmt->execute([$id]);
    $currentUserRole = $currentUserStmt->fetchColumn();

    if ($currentUserRole === 'super_admin' && $role !== 'super_admin') {
        $superAdminCountStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'");
        if ($superAdminCountStmt->fetchColumn() <= 1) {
            $message = '<div class="alert alert-danger">Cannot demote the last Super Admin!</div>';
        }
    }

    if (empty($message)) {
        $existingCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $existingCheck->execute([$email, $id]);
        if ($existingCheck->rowCount() > 0) {
            $message = '<div class="alert alert-danger">Another admin already uses this email!</div>';
        } else {
            if ($password) {
                $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, status=?, password=? WHERE id=?");
                $stmt->execute([$name, $email, $role, $status, $password, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, status=? WHERE id=?");
                $stmt->execute([$name, $email, $role, $status, $id]);
            }
            $message = '<div class="alert alert-success">Admin updated successfully!</div>';
        }
    }
}

// Delete Admin
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id === $_SESSION['user_id']) {
        $message = '<div class="alert alert-danger">You cannot delete yourself!</div>';
    } else {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        $message = '<div class="alert alert-danger">Admin deleted successfully!</div>';
    }
}

// Search and filter functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Base query
$query = "SELECT * FROM users WHERE role != 'editor'";
$params = [];

// Add search condition
if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

// Add status filter
if ($status_filter !== 'all') {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main-content">
    <div class="header">
        <h1>Manage Admins</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminModal" onclick="resetForm()">
            <i class="fas fa-plus"></i> Add New Admin
        </button>
    </div>

    <?= $message ?>

    <!-- Search and Filter Section -->
    <div class="card mt-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No admins found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?= htmlspecialchars($admin['full_name']) ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td><span class="badge bg-<?= $admin['role'] === 'super_admin' ? 'danger' : 'primary' ?>"><?= ucfirst($admin['role']) ?></span></td>
                                <td><span class="badge bg-<?= $admin['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($admin['status']) ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick='editAdmin(<?= json_encode($admin) ?>)'><i class="fas fa-edit"></i></button>
                                    <?php if ($admin['id'] !== $_SESSION['user_id']): // Prevent self-deletion ?>
                                        <a href="?delete=<?= $admin['id'] ?>" onclick="return confirm('Delete this admin?')" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-danger" disabled><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="adminModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminModalLabel">Add/Edit Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="admin_id" id="admin_id">
                <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" class="form-control" name="full_name" id="full_name" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select" id="role" required>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select" id="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" id="password">
                    <small class="form-text text-muted">Leave blank if not changing password.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" name="save_admin" id="saveBtn">Save Admin</button>
                <button type="submit" class="btn btn-primary" name="update_admin" id="updateBtn" style="display:none;">Update Admin</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editAdmin(admin) {
        document.getElementById('admin_id').value = admin.id;
        document.getElementById('full_name').value = admin.full_name;
        document.getElementById('email').value = admin.email;
        document.getElementById('role').value = admin.role;
        document.getElementById('status').value = admin.status;
        document.getElementById('password').value = '';

        document.getElementById('saveBtn').style.display = 'none';
        document.getElementById('updateBtn').style.display = 'inline-block';
        new bootstrap.Modal(document.getElementById('adminModal')).show();
    }

    function resetForm() {
        document.getElementById('admin_id').value = '';
        document.getElementById('full_name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('role').value = 'admin';
        document.getElementById('status').value = 'active';
        document.getElementById('password').value = '';

        document.getElementById('saveBtn').style.display = 'inline-block';
        document.getElementById('updateBtn').style.display = 'none';
    }
</script>

<?php include 'partials/footer.php'; ?>