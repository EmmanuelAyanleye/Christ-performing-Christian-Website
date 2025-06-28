<?php
require_once '../includes/config.php';
require_once __DIR__ . '/partials/session_auth.php';

// This page is for super admins only
require_super_admin();

$message = '';

// Upload Photos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photos'])) {
    $category = sanitize_input($_POST['category']);
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $tags = sanitize_input($_POST['tags']);
    $date = $_POST['date'] ?? date('Y-m-d');
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (!empty($_FILES['photos']['name'][0])) {
        $upload_dir = '../assets/images/gallery/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        foreach ($_FILES['photos']['tmp_name'] as $index => $tmp_name) {
            $filename = time() . '_' . basename($_FILES['photos']['name'][$index]);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($tmp_name, $target_file)) {
                $relative_path = 'assets/images/gallery/' . $filename;

                $stmt = $pdo->prepare("INSERT INTO gallery (title, category, description, tags, image_url, event_date, is_featured) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $category, $description, $tags, $relative_path, $date, $featured]);
            }
        }
        $message = '<div class="alert alert-success">Photos uploaded successfully!</div>';
    }
}

// Delete Photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("SELECT image_url FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetchColumn();

    if ($image && file_exists('../' . $image)) {
        unlink('../' . $image);
    }

    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $message = '<div class="alert alert-danger">Photo deleted successfully!</div>';
}

// Edit Photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $title = sanitize_input($_POST['edit_title']);
    $category = sanitize_input($_POST['edit_category']);
    $description = sanitize_input($_POST['edit_description']);
    $date = $_POST['edit_date'];

    $stmt = $pdo->prepare("UPDATE gallery SET title = ?, category = ?, description = ?, event_date = ? WHERE id = ?");
    $stmt->execute([$title, $category, $description, $date, $id]);
    $message = '<div class="alert alert-success">Photo details updated successfully!</div>';
}

// Get filter values from query parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build the base query
$query = "SELECT * FROM gallery";
$where = [];
$params = [];

// Add search condition
if (!empty($search)) {
    $where[] = "(title LIKE ? OR description LIKE ? OR tags LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Add category filter
if ($category_filter !== 'all') {
    $where[] = "category = ?";
    $params[] = $category_filter;
}

// Add date filter
if (!empty($date_filter)) {
    $where[] = "DATE(event_date) = ?";
    $params[] = $date_filter;
}

// Combine WHERE conditions
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

// Add sorting
$query .= " ORDER BY event_date DESC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for filter dropdown
$categories = $pdo->query("SELECT DISTINCT category FROM gallery ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "Manage Gallery";
include 'partials/header.php';
include 'partials/sidebar.php';
?>

<div class="main-content">
    <div class="header">
        <h1>Gallery Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-upload"></i> Upload Photos
        </button>
    </div>

    <?= $message ?>

    <!-- Search and Filter Section -->
    <div class="card mt-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search by title, description, or tags..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="all" <?= $category_filter === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>" <?= $category_filter === $category ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($date_filter) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body table-responsive">
            <table class="table align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Tags</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($galleryItems) > 0): ?>
                        <?php foreach ($galleryItems as $i => $item): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><img src="../<?= htmlspecialchars($item['image_url']) ?>" style="height: 60px;" class="img-thumbnail"></td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= htmlspecialchars($item['category']) ?></td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($item['event_date']))) ?></td>
                                <td><?= htmlspecialchars($item['tags']) ?></td>
                                <td><?= $item['is_featured'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='openEditModal(<?= json_encode($item) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Delete this image?');" style="display:inline-block">
                                        <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No photos found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="upload_photos" value="1">
                <div class="mb-3">
                    <label class="form-label">Select Photos</label>
                    <input type="file" class="form-control" name="photos[]" multiple accept="image/*" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Sunday Service">Sunday Service</option>
                            <option value="Youth Ministry">Youth Church</option>
                            <option value="Community Events">Community Events</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Event Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tags (comma separated)</label>
                    <input type="text" class="form-control" name="tags">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="featured" id="featuredCheckbox">
                    <label class="form-check-label" for="featuredCheckbox">Feature on homepage</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editImageModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Photo Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="edit_title" id="edit_title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="edit_category" id="edit_category">
                        <option value="Sunday Service">Sunday Service</option>
                        <option value="Youth Ministry">Youth Ministry</option>
                        <option value="Community Events">Community Events</option>
                        <option value="Baptisms">Baptisms</option>
                        <option value="Holidays">Holidays</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="edit_description" id="edit_description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="edit_date" id="edit_date" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(item) {
    const modal = new bootstrap.Modal(document.getElementById('editImageModal'));
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_title').value = item.title;
    document.getElementById('edit_category').value = item.category;
    document.getElementById('edit_description').value = item.description;
    document.getElementById('edit_date').value = item.event_date;
    modal.show();
}
</script>

<?php include 'partials/footer.php'; ?>