<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php'; // For PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/partials/session_auth.php';

$pageTitle = "Manage Blog Comments";
$current_page = 'blog-comments'; // For sidebar highlighting
$message_feedback = '';

// Handle POST Actions (Delete, Reply)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Single Delete
    if (isset($_POST['delete_comment'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE id = ?");
        $stmt->execute([$id]);
        $message_feedback = '<div class="alert alert-success">Comment deleted successfully.</div>';
    }
    // Post Reply to Blog
    elseif (isset($_POST['post_reply'])) {
        $parent_id = (int)$_POST['parent_id'];
        $post_id = (int)$_POST['post_id'];
        $reply_content = sanitize_input($_POST['reply_content']);
        $admin_id = $_SESSION['user_id'];
        $admin_name = $_SESSION['user_full_name'];
        $admin_email = $_SESSION['user_email'];

        if ($parent_id > 0 && $post_id > 0 && !empty($reply_content)) {
            try {
                // Insert the admin's reply
                $sql = "INSERT INTO blog_comments (post_id, parent_id, user_id, name, email, content, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, 'approved', NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$post_id, $parent_id, $admin_id, $admin_name, $admin_email, $reply_content]);

                // The original comment's status remains unchanged (e.g., 'approved').
                // Its 'replied' status is now implied by the existence of this reply.
                // We will update the 'Replied' count query to reflect this.

                $message_feedback = '<div class="alert alert-success">Your reply has been posted successfully.</div>';
            } catch (Exception $e) {
                error_log("Admin reply error: " . $e->getMessage());
                $message_feedback = "<div class='alert alert-danger'>Error posting reply. Please try again.</div>";
            }
        } else {
            $message_feedback = '<div class="alert alert-danger">Could not post reply. Missing information.</div>';
        }
    }
    // Change Status
    elseif (isset($_POST['change_status'])) {
        $id = (int)$_POST['id'];
        $new_status = sanitize_input($_POST['new_status']);
        if (in_array($new_status, ['approved', 'pending', 'rejected'])) {
            $stmt = $pdo->prepare("UPDATE blog_comments SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            $message_feedback = '<div class="alert alert-success">Comment status updated to ' . htmlspecialchars($new_status) . '.</div>';
        } else {
            $message_feedback = '<div class="alert alert-danger">Invalid status provided.</div>';
        }
    }
}

// Fetching comments with filtering and sorting
$search = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterCategory = $_GET['category'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'newest';

$sql = "SELECT bc.*, bp.title as post_title, bp.category as post_category, bp.slug as post_slug, u.full_name as user_full_name
        FROM blog_comments bc
        JOIN blog_posts bp ON bc.post_id = bp.id
        LEFT JOIN users u ON bc.user_id = u.id";

$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(bc.name LIKE ? OR bc.email LIKE ? OR bc.content LIKE ? OR bp.title LIKE ?)";
    array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
}

if ($filterStatus) {
    $conditions[] = "bc.status = ?";
    $params[] = $filterStatus;
}

if ($filterCategory) {
    $conditions[] = "bp.category = ?";
    $params[] = $filterCategory;
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

switch ($sortBy) {
    case 'oldest':
        $sql .= " ORDER BY bc.created_at ASC";
        break;
    case 'pending_first':
        $sql .= " ORDER BY FIELD(bc.status, 'pending', 'approved', 'replied', 'rejected'), bc.created_at DESC";
        break;
    default: // newest
        $sql .= " ORDER BY bc.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comments_data = $stmt->fetchAll();

// Get unique categories for filter dropdown
$categories_stmt = $pdo->query("SELECT DISTINCT category FROM blog_posts WHERE status = 'published' AND category IS NOT NULL AND category != '' ORDER BY category ASC");
$blog_categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

// Stats for comments
$totalComments = $pdo->query("SELECT COUNT(*) FROM blog_comments")->fetchColumn();
$pendingComments = $pdo->query("SELECT COUNT(*) FROM blog_comments WHERE status = 'pending'")->fetchColumn();
$approvedComments = $pdo->query("SELECT COUNT(*) FROM blog_comments WHERE status = 'approved'")->fetchColumn();
// Count parent comments that have at least one approved reply
$repliedComments = $pdo->query("SELECT COUNT(DISTINCT bc.id)
                                FROM blog_comments bc
                                JOIN blog_comments reply ON bc.id = reply.parent_id
                                WHERE reply.status = 'approved'")->fetchColumn();


include __DIR__ . '/partials/header.php';
?>
<style>
    .comment-content-truncate {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .comment-row.pending {
        background-color: #fff3cd; /* Light yellow for pending */
    }
    .comment-row.pending:hover {
        background-color: #ffeeba;
    }
    .comment-row.replied {
        background-color: #d4edda; /* Light green for replied */
    }
    .comment-row.replied:hover {
        background-color: #c3e6cb;
    }
    .comment-row.rejected {
        background-color: #f8d7da; /* Light red for rejected */
    }
    .comment-row.rejected:hover {
        background-color: #f5c6cb;
    }
</style>
<body>
<div class="admin-wrapper">
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h1>Blog Comments</h1>
    </div>

    <?php if ($message_feedback) echo $message_feedback; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary"><div class="stat-icon"><i class="fas fa-comments"></i></div><div class="stat-info"><h3><?php echo $totalComments; ?></h3><p>Total Comments</p></div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning"><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div><div class="stat-info"><h3><?php echo $pendingComments; ?></h3><p>Pending Approval</p></div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-info"><h3><?php echo $approvedComments; ?></h3><p>Approved</p></div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info"><div class="stat-icon"><i class="fas fa-reply"></i></div><div class="stat-info"><h3><?php echo $repliedComments; ?></h3><p>Replied</p></div></div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter-section">
        <form action="blog-comments.php" method="GET">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search comments..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php if ($filterStatus === 'pending') echo 'selected'; ?>>Pending</option>
                        <option value="approved" <?php if ($filterStatus === 'approved') echo 'selected'; ?>>Approved</option>
                        <option value="replied" <?php if ($filterStatus === 'replied') echo 'selected'; ?>>Replied</option>
                        <option value="rejected" <?php if ($filterStatus === 'rejected') echo 'selected'; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($blog_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($filterCategory === $cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="sort_by" onchange="this.form.submit()">
                        <option value="newest" <?php if ($sortBy === 'newest') echo 'selected'; ?>>Newest First</option>
                        <option value="oldest" <?php if ($sortBy === 'oldest') echo 'selected'; ?>>Oldest First</option>
                        <option value="pending_first" <?php if ($sortBy === 'pending_first') echo 'selected'; ?>>Pending First</option>
                    </select>
                </div>
                <div class="col-md-12 mt-2"><button type="submit" class="btn btn-primary w-100">Apply Filters</button></div>
            </div>
        </form>
    </div>

    <!-- Comments Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Commenter</th>
                            <th>Email</th>
                            <th>Post Title</th>
                            <th>Comment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($comments_data)): ?>
                            <tr><td colspan="7" class="text-center">No comments found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($comments_data as $comment): ?>
                                <tr class="comment-row <?php echo htmlspecialchars($comment['status']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($comment['name']); ?></strong>
                                        <?php if ($comment['user_full_name']): ?>
                                            <br><small class="text-muted">(User: <?php echo htmlspecialchars($comment['user_full_name']); ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($comment['email']); ?></td>
                                    <td><a href="<?php echo BASE_URL; ?>/pages/blog-article.php?slug=<?php echo htmlspecialchars($comment['post_slug']); ?>" target="_blank"><?php echo htmlspecialchars($comment['post_title']); ?></a></td>
                                    <td><div class="comment-content-truncate" title="<?php echo htmlspecialchars($comment['content']); ?>"><?php echo htmlspecialchars($comment['content']); ?></div></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            if ($comment['status'] === 'approved') echo 'success';
                                            elseif ($comment['status'] === 'pending') echo 'warning';
                                            elseif ($comment['status'] === 'replied') echo 'info';
                                            elseif ($comment['status'] === 'rejected') echo 'danger';
                                            else echo 'secondary';
                                        ?>"><?php echo ucfirst($comment['status']); ?></span>
                                    </td>
                                    <td><?php echo format_date($comment['created_at'], 'M j, Y g:i A'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" onclick='viewComment(<?php echo json_encode($comment); ?>)' title="View/Reply"><i class="fas fa-eye"></i></button>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $comment['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" title="Change Status">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $comment['id']; ?>">
                                                <li><a class="dropdown-item" href="#" onclick="changeCommentStatus(<?php echo $comment['id']; ?>, 'approved')">Approve</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="changeCommentStatus(<?php echo $comment['id']; ?>, 'pending')">Mark Pending</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="changeCommentStatus(<?php echo $comment['id']; ?>, 'rejected')">Reject</a></li>
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="deleteComment(<?php echo $comment['id']; ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- View Comment Modal -->
<div class="modal fade" id="viewCommentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="comment-details">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>From:</strong> <span id="commentFrom"></span></div>
                        <div class="col-md-6"><strong>Date:</strong> <span id="commentDate"></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Email:</strong> <span id="commentEmail"></span></div>
                        <div class="col-md-6"><strong>Post:</strong> <span id="commentPostTitle"></span></div>
                    </div>
                    <div class="mb-3"><strong>Content:</strong><div class="border rounded p-3 mt-2 bg-light" id="commentContent" style="white-space: pre-wrap;"></div></div>
                    <div class="mb-3"><strong>Status:</strong> <span id="commentStatusBadge" class="badge"></span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="replyFromViewButton"><i class="fas fa-reply"></i> Reply</button>
            </div>
        </div>
    </div>
</div>

<!-- Reply Comment Modal -->
<div class="modal fade" id="replyCommentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="replyForm" method="POST" action="blog-comments.php" autocomplete="off">
                <input type="hidden" name="parent_id" id="replyParentId">
                <input type="hidden" name="post_id" id="replyPostId">
                <input type="hidden" name="post_reply" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Post a Reply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p><strong>Replying to:</strong> "<em id="originalCommentContent" class="text-muted"></em>"</p>
                        <p><strong>On Post:</strong> <strong id="originalPostTitle"></strong></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Reply:</label>
                        <textarea class="form-control" name="reply_content" rows="5" required placeholder="Compose your reply here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Post Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden form for single delete -->
<form method="POST" action="blog-comments.php" id="deleteForm" style="display:none;">
    <input type="hidden" name="delete_comment" value="1">
    <input type="hidden" name="id" id="deleteCommentId">
</form>

<!-- Hidden form for status change -->
<form method="POST" action="blog-comments.php" id="statusChangeForm" style="display:none;">
    <input type="hidden" name="change_status" value="1">
    <input type="hidden" name="id" id="statusChangeCommentId">
    <input type="hidden" name="new_status" id="newStatusValue">
</form>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
    const viewCommentModal = new bootstrap.Modal(document.getElementById('viewCommentModal'));
    const replyCommentModal = new bootstrap.Modal(document.getElementById('replyCommentModal'));

    function viewComment(comment) {
        document.getElementById('commentFrom').textContent = comment.name + (comment.user_full_name ? ` (User: ${comment.user_full_name})` : '');
        document.getElementById('commentEmail').textContent = comment.email;
        document.getElementById('commentPostTitle').textContent = comment.post_title;
        document.getElementById('commentDate').textContent = new Date(comment.created_at).toLocaleString();
        document.getElementById('commentContent').textContent = comment.content;

        const statusBadge = document.getElementById('commentStatusBadge');
        statusBadge.textContent = comment.status.charAt(0).toUpperCase() + comment.status.slice(1);
        statusBadge.className = 'badge'; // Reset classes
        if (comment.status === 'approved') statusBadge.classList.add('bg-success');
        else if (comment.status === 'pending') statusBadge.classList.add('bg-warning');
        else if (comment.status === 'replied') statusBadge.classList.add('bg-info');
        else if (comment.status === 'rejected') statusBadge.classList.add('bg-danger');
        else statusBadge.classList.add('bg-secondary');

        document.getElementById('replyFromViewButton').onclick = function() {
            viewCommentModal.hide();
            prepareReplyModal(comment);
        };

        viewCommentModal.show();
    }

    function prepareReplyModal(comment) {
        document.getElementById('replyParentId').value = comment.id;
        document.getElementById('replyPostId').value = comment.post_id;

        // Show a snippet of the original comment in the modal
        let originalContent = comment.content;
        if (originalContent.length > 100) {
            originalContent = originalContent.substring(0, 100) + '...';
        }
        document.getElementById('originalCommentContent').textContent = originalContent;
        document.getElementById('originalPostTitle').textContent = comment.post_title;
        
        // Clear previous reply text
        document.querySelector('#replyForm textarea[name="reply_content"]').value = '';

        replyCommentModal.show();
    }

    function deleteComment(id) {
        if (confirm('Are you sure you want to permanently delete this comment? This action cannot be undone.')) {
            document.getElementById('deleteCommentId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    function changeCommentStatus(id, newStatus) {
        if (confirm(`Are you sure you want to change this comment's status to "${newStatus}"?`)) {
            document.getElementById('statusChangeCommentId').value = id;
            document.getElementById('newStatusValue').value = newStatus;
            document.getElementById('statusChangeForm').submit();
        }
    }
</script>