<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/partials/session_auth.php';

$pageTitle = "Manage Sermons";
$message = '';

// Handle Actions (Delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM sermons WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<div class="alert alert-success">Sermon deleted successfully.</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Failed to delete sermon. Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sermon'])) {
    $id = (int)$_POST['sermon_id'];
    $title = sanitize_input($_POST['title']);
    $speaker = sanitize_input($_POST['speaker']);
    $series = sanitize_input($_POST['series']);
    $date = sanitize_input($_POST['date']);
    $duration = sanitize_input($_POST['duration']);
    $bible_passage = sanitize_input($_POST['bible_passage']);
    $youtube_url = sanitize_input($_POST['youtube_url']);
    $description = sanitize_input($_POST['description']);
    $tags = sanitize_input($_POST['tags']);

    // Auto-generate thumbnail from YouTube URL
    $thumbnail_url = get_youtube_thumbnail_url($youtube_url);

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE sermons SET title = ?, speaker = ?, series = ?, date = ?, duration = ?, bible_passage = ?, youtube_url = ?, description = ?, thumbnail_url = ?, tags = ? WHERE id = ?");
            $stmt->execute([$title, $speaker, $series, $date, $duration, $bible_passage, $youtube_url, $description, $thumbnail_url, $tags, $id]);
            $message = '<div class="alert alert-success">Sermon updated successfully.</div>';
        } else {
            $stmt = $pdo->prepare("INSERT INTO sermons (title, speaker, series, date, duration, bible_passage, youtube_url, description, thumbnail_url, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $speaker, $series, $date, $duration, $bible_passage, $youtube_url, $description, $thumbnail_url, $tags]);
            $message = '<div class="alert alert-success">Sermon added successfully.</div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Failed to save sermon. Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Fetching sermons
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM sermons";
if ($search) {
    $sql .= " WHERE title LIKE ? OR speaker LIKE ? OR series LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $sql .= " ORDER BY date DESC";
    $stmt = $pdo->query($sql);
}
$sermons = $stmt->fetchAll();

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/sidebar.php';
?>

<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <h1>Sermons</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sermonModal" onclick="prepareAddModal()">
            <i class="fas fa-plus"></i> Add New Sermon
        </button>
    </div>

    <?php if ($message) echo $message; ?>

    <!-- Search and Filter -->
    <div class="search-filter-section">
        <form action="sermons.php" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by title, speaker, or series..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>

    <!-- Sermons Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Series</th>
                            <th>Speaker</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sermons as $sermon): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($sermon['thumbnail_url'] ?: '../assets/images/default-thumbnail.png'); ?>" 
                                         class="rounded me-2" width="60" height="40" alt="Sermon Thumbnail">
                                    <div>
                                        <strong><?php echo htmlspecialchars($sermon['title']); ?></strong>
                                        <div class="text-muted small"><?php echo htmlspecialchars($sermon['bible_passage']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($sermon['series']); ?></span></td>
                            <td><?php echo htmlspecialchars($sermon['speaker']); ?></td>
                            <td><?php echo format_date($sermon['date']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-action" title="Edit" onclick='prepareEditModal(<?php echo json_encode($sermon); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>

                                <a href="<?php echo BASE_URL . '/pages/sermons.php'; ?>" target="_blank" class="btn btn-sm btn-outline-info btn-action" title="View on Site">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?action=delete&id=<?php echo $sermon['id']; ?>" class="btn btn-sm btn-outline-danger btn-action" title="Delete" onclick="return confirm('Are you sure you want to delete this sermon?');">
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

<!-- Add/Edit Sermon Modal -->
<div class="modal fade" id="sermonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="sermons.php" method="POST" id="sermonForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Sermon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="sermon_id" id="sermon_id" value="0">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" id="date" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Series</label>
                            <input type="text" class="form-control" name="series" id="series">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Speaker</label>
                            <input type="text" class="form-control" name="speaker" id="speaker" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bible Passage</label>
                            <input type="text" class="form-control" name="bible_passage" id="bible_passage" placeholder="e.g., John 3:16-18">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (mm:ss)</label>
                            <input type="text" class="form-control" name="duration" id="duration" placeholder="e.g., 45:30">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">YouTube URL</label>
                        <input type="url" class="form-control" name="youtube_url" id="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required>
                        <div class="form-text">The sermon thumbnail will be generated automatically from this link.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tags (comma separated)</label>
                        <input type="text" class="form-control" name="tags" id="tags" placeholder="faith, grace, hope">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_sermon" class="btn btn-primary">Save Sermon</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Bootstrap JS (Include before closing body tag) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<!-- Footer (before </body>) -->
<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
    const sermonModal = new bootstrap.Modal(document.getElementById('sermonModal'));
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('sermonForm');

    function prepareAddModal() {
        form.reset();
        document.getElementById('sermon_id').value = '0';
        modalTitle.textContent = 'Add New Sermon';
        document.getElementById('date').valueAsDate = new Date();
    }

    function prepareEditModal(sermon) {
        form.reset();
        modalTitle.textContent = 'Edit Sermon';

        document.getElementById('sermon_id').value = sermon.id;
        document.getElementById('title').value = sermon.title;
        document.getElementById('speaker').value = sermon.speaker;
        document.getElementById('series').value = sermon.series;
        document.getElementById('date').value = sermon.date;
        document.getElementById('duration').value = sermon.duration;
        document.getElementById('bible_passage').value = sermon.bible_passage;
        document.getElementById('youtube_url').value = sermon.youtube_url;
        document.getElementById('description').value = sermon.description;
        document.getElementById('tags').value = sermon.tags;

        sermonModal.show();
    }
</script>