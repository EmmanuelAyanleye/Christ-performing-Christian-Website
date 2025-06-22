<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/partials/session_auth.php';

$pageTitle = "Manage Events";
$message = '';

// Handle Actions (Delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $message = '<div class="alert alert-success">Event deleted successfully.</div>';
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    $id = (int)$_POST['event_id'];
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $location = sanitize_input($_POST['location']);
    $start_date = sanitize_input($_POST['start_date']);

    $featured_image = $_POST['existing_image'];
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $upload_dir = '../assets/images/events/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = 'assets/images/events/' . $file_name;
        }
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, start_date = ?, location = ?, featured_image = ? WHERE id = ?");
        $stmt->execute([$title, $description, $start_date, $location, $featured_image, $id]);
        $message = '<div class="alert alert-success">Event updated successfully.</div>';
    } else {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, start_date, location, featured_image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $start_date, $location, $featured_image]);
        $message = '<div class="alert alert-success">Event added successfully.</div>';
    }
}

$events = $pdo->query("SELECT * FROM events ORDER BY start_date DESC")->fetchAll();

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/sidebar.php';
?>

<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <h1>Events Management</h1>
        <button class="btn btn-primary" onclick="prepareAddModal()">
            <i class="fas fa-plus"></i> Add New Event
        </button>
    </div>

    <?php if ($message) echo $message; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($event['featured_image'] ?: 'assets/images/default-thumbnail.png'); ?>" 
                                         class="rounded me-2" width="60" height="40" alt="Event Image">
                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <?php echo format_date($event['start_date'], 'M j, Y'); ?><br>
                                <small class="text-muted"><?php echo format_date($event['start_date'], 'g:i A'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                            <td>
                                <?php
                                $now = new DateTime();
                                $start = new DateTime($event['start_date']);
                                echo $now > $start ? '<span class="badge bg-secondary">Completed</span>' : '<span class="badge bg-primary">Upcoming</span>';
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-action" title="Edit" onclick='prepareEditModal(<?php echo json_encode($event); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?action=delete&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-danger btn-action" title="Delete" onclick="return confirm('Are you sure you want to delete this event?');">
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

<?php include __DIR__ . '/partials/footer.php'; ?>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content">
            <form action="events.php" method="POST" id="eventForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="event_id" id="event_id" value="0">
                    <input type="hidden" name="existing_image" id="existing_image">

                    <div class="mb-3">
                        <label class="form-label">Event Title *</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date & Time *</label>
                            <input type="datetime-local" class="form-control" name="start_date" id="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location *</label>
                            <input type="text" class="form-control" name="location" id="location" required>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" id="description" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Event Image</label>
                        <input type="file" class="form-control" name="featured_image" id="featured_image" accept="image/*">
                        <small class="text-muted">Max file size: 5MB (JPEG, PNG, GIF)</small>
                        <div class="mt-2 text-center">
                            <img src="" id="current_image_preview" class="img-thumbnail" style="max-width: 200px; display: none;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_event" class="btn btn-primary">Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const eventModalEl = document.getElementById('eventModal');
const eventModal = new bootstrap.Modal(eventModalEl);
const form = document.getElementById('eventForm');

function prepareAddModal() {
    form.reset();
    document.getElementById('event_id').value = '0';
    document.getElementById('modalTitle').textContent = 'Add New Event';
    document.getElementById('current_image_preview').style.display = 'none';
    eventModal.show();
}

function prepareEditModal(event) {
    form.reset();
    document.getElementById('modalTitle').textContent = 'Edit Event';
    document.getElementById('event_id').value = event.id;
    document.getElementById('title').value = event.title;
    document.getElementById('description').value = event.description;
    document.getElementById('location').value = event.location;
    document.getElementById('start_date').value = event.start_date.replace(' ', 'T');
    document.getElementById('existing_image').value = event.featured_image;

    const preview = document.getElementById('current_image_preview');
    if (event.featured_image) {
        preview.src = '<?php echo BASE_URL; ?>/' + event.featured_image;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    eventModal.show();
}
</script>
