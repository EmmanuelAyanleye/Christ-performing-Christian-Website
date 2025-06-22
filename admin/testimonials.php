<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/partials/session_auth.php';

$pageTitle = "Manage Testimonials";

$message = '';

// Handle Actions (Delete, Approve, Toggle Featured)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<div class="alert alert-success">Testimonial deleted successfully.</div>';
        } elseif ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<div class="alert alert-success">Testimonial approved and published.</div>';
        } elseif ($action === 'toggle_featured') {
            $stmt = $pdo->prepare("UPDATE testimonials SET is_featured = !is_featured WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<div class="alert alert-success">Featured status toggled.</div>';
        }
    }
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_testimonial'])) {
    $id = (int)$_POST['testimonial_id'];
    $name = sanitize_input($_POST['name']);
    $role = sanitize_input($_POST['role']);
    $content = sanitize_input($_POST['content']);
    $status = in_array($_POST['status'], ['approved', 'pending']) ? $_POST['status'] : 'pending';
    $rating = (int)$_POST['rating'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $avatar_url = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('avatar_', true) . '.' . $ext;
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
            $avatar_url = $uploadPath;
        }
    }

    if ($id > 0) {
        $sql = "UPDATE testimonials SET name = ?, role = ?, content = ?, status = ?, rating = ?, is_featured = ?";
        $params = [$name, $role, $content, $status, $rating, $is_featured];
        if ($avatar_url) {
            $sql .= ", avatar_url = ?";
            $params[] = $avatar_url;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = '<div class="alert alert-success">Testimonial updated successfully.</div>';
    } else {
        $stmt = $pdo->prepare("INSERT INTO testimonials (name, role, content, status, rating, is_featured, avatar_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $role, $content, $status, $rating, $is_featured, $avatar_url]);
        $message = '<div class="alert alert-success">Testimonial added successfully.</div>';
    }
}

// Fetching testimonials with filtering and pagination
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT * FROM testimonials";
$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(name LIKE ? OR content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter_status) {
    $conditions[] = "status = ?";
    $params[] = $filter_status;
}

if ($conditions) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$testimonials = $stmt->fetchAll();

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/sidebar.php';
?>

<div class="main-content">
    <div class="header">
        <h1>Testimonials</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testimonialModal" onclick="prepareAddModal()">
            <i class="fas fa-plus"></i> Add New Testimonial
        </button>
    </div>

    <?php if ($message) echo $message; ?>

    <div class="search-filter-section">
        <form action="testimonials.php" method="GET">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or content..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="approved" <?php echo ($filter_status === 'approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="pending" <?php echo ($filter_status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Person</th>
                            <th>Testimonial</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($testimonials as $testimonial): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($testimonial['avatar_url'] ?: '../assets/images/default-avatar.png'); ?>" class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <strong><?php echo htmlspecialchars($testimonial['name']); ?></strong>
                                        <div class="text-muted small"><?php echo htmlspecialchars($testimonial['role']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><div style="max-width:300px" title="<?php echo htmlspecialchars($testimonial['content']); ?>"><?php echo htmlspecialchars($testimonial['content']); ?></div></td>
                            <td><span class="text-warning"><?php echo str_repeat('★', $testimonial['rating']) . str_repeat('☆', 5 - $testimonial['rating']); ?></span></td>
                            <td><span class="badge bg-<?php echo $testimonial['status'] == 'approved' ? 'success' : 'warning'; ?>"><?php echo ucfirst($testimonial['status']); ?></span></td>
                            <td><span class="badge bg-<?php echo $testimonial['is_featured'] ? 'info' : 'light text-dark'; ?>"><?php echo $testimonial['is_featured'] ? 'Yes' : 'No'; ?></span></td>
                            <td><?php echo format_date($testimonial['created_at'], 'M j, Y'); ?></td>
                            <td>
                                <?php if ($testimonial['status'] == 'pending'): ?>
                                <a href="?action=approve&id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                                <a href="?action=toggle_featured&id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-star"></i></a>
                                <button class="btn btn-sm btn-outline-primary" onclick='prepareEditModal(<?php echo json_encode($testimonial); ?>)'><i class="fas fa-edit"></i></button>
                                <a href="?action=delete&id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="testimonials.php" method="POST" enctype="multipart/form-data" id="testimonialForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="testimonial_id" id="testimonial_id" value="0">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" name="role" id="role">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Testimonial</label>
                        <textarea class="form-control" name="content" id="content" rows="5" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Rating (1-5)</label>
                            <input type="number" class="form-control" name="rating" id="rating" min="1" max="5" value="5" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_featured" id="is_featured">
                                <label class="form-check-label" for="is_featured">Feature</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Avatar (Image)</label>
                        <input type="file" name="avatar" id="avatar" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="save_testimonial" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
const testimonialModal = new bootstrap.Modal(document.getElementById('testimonialModal'));
function prepareAddModal() {
    document.getElementById('testimonialForm').reset();
    document.getElementById('testimonial_id').value = '0';
    document.getElementById('modalTitle').innerText = 'Add New Testimonial';
    testimonialModal.show();
}
function prepareEditModal(data) {
    document.getElementById('testimonialForm').reset();
    document.getElementById('testimonial_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('role').value = data.role;
    document.getElementById('content').value = data.content;
    document.getElementById('status').value = data.status;
    document.getElementById('rating').value = data.rating;
    document.getElementById('is_featured').checked = !!parseInt(data.is_featured);
    document.getElementById('modalTitle').innerText = 'Edit Testimonial';
    testimonialModal.show();
}
</script>
