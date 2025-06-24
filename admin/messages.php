<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/partials/session_auth.php';

$pageTitle = "Contact Messages";
$message_feedback = '';

// Handle POST Actions (Bulk, Delete, Reply)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bulk Actions
    if (isset($_POST['bulk_action']) && !empty($_POST['message_ids']) && !empty($_POST['bulk_action'])) {
        $action = $_POST['bulk_action'];
        $ids = $_POST['message_ids'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $message_feedback = '<div class="alert alert-success">Selected messages have been deleted.</div>';
        } elseif (in_array($action, ['read', 'replied', 'unread'])) {
            $stmt = $pdo->prepare("UPDATE messages SET status = ? WHERE id IN ($placeholders)");
            $stmt->execute([$action, ...$ids]);
            $message_feedback = '<div class="alert alert-success">Selected messages have been marked as ' . htmlspecialchars($action) . '.</div>';
        }
    }
    // Single Delete
    elseif (isset($_POST['delete_message'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        $message_feedback = '<div class="alert alert-success">Message deleted successfully.</div>';
    }
    // Send Reply
    elseif (isset($_POST['send_reply'])) {
        $id = (int)$_POST['id'];
        $to_email = filter_var($_POST['to_email'], FILTER_VALIDATE_EMAIL);
        $subject = sanitize_input($_POST['subject']);
        $reply_body = $_POST['reply_body']; // Allow HTML for better formatting

        if (!$to_email) {
            $message_feedback = '<div class="alert alert-danger">Invalid recipient email address.</div>';
        } else {
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = SMTP_PORT;

                //Recipients
                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($to_email);
                $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

                //Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = nl2br(htmlspecialchars($reply_body));
                $mail->AltBody = strip_tags($reply_body);

                $mail->send();

                // Update message status in DB
                $stmt = $pdo->prepare("UPDATE messages SET status = 'replied' WHERE id = ?");
                $stmt->execute([$id]);

                $message_feedback = '<div class="alert alert-success">Reply sent successfully and message marked as replied.</div>';
            } catch (Exception $e) {
                $message_feedback = "<div class='alert alert-danger'>Message could not be sent. Mailer Error: " . htmlspecialchars($mail->ErrorInfo) . "</div>";
            }
        }
    }
}

// Handle GET actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'mark_all_read') {
        $pdo->query("UPDATE messages SET status = 'read' WHERE status = 'unread'");
        $message_feedback = '<div class="alert alert-success">All unread messages marked as read.</div>';
    }
    // Mark as read when viewing (called via JS fetch)
    if ($action === 'mark_read' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = ? AND status = 'unread'");
        $stmt->execute([$id]);
        // Return a success response for JS
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}

// Fetching messages with filtering and sorting
$search = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'newest';

$sql = "SELECT * FROM messages";
$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
}

if ($filterStatus) {
    $conditions[] = "status = ?";
    $params[] = $filterStatus;
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

switch ($sortBy) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC";
        break;
    case 'unread':
        $sql .= " ORDER BY FIELD(status, 'unread', 'read', 'replied'), created_at DESC";
        break;
    default: // newest
        $sql .= " ORDER BY created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages_data = $stmt->fetchAll();

// Stats
$totalMessages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$unreadMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'")->fetchColumn();
$repliedMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'replied'")->fetchColumn();
$todayMessages = $pdo->query("SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()")->fetchColumn();

include __DIR__ . '/partials/header.php';
?>
<style>
    .unread-message {
        background-color: #f8f9fa;
        font-weight: 500;
    }
    .unread-message:hover {
        background-color: #e9ecef;
    }
    .message-details {
        font-size: 0.95rem;
    }
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
<body>
<div class="admin-wrapper">
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main-content">
    <div class="header">
        <h1>Contact Messages</h1>
        <div class="header-actions">
            <a href="messages.php?action=mark_all_read" class="btn btn-success">
                <i class="fas fa-check-double"></i> Mark All as Read
            </a>
        </div>
    </div>

    <?php if ($message_feedback) echo $message_feedback; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary"><div class="stat-icon"><i class="fas fa-envelope"></i></div><div class="stat-info"><h3><?php echo $totalMessages; ?></h3><p>Total Messages</p></div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning"><div class="stat-icon"><i class="fas fa-envelope-open"></i></div><div class="stat-info"><h3><?php echo $unreadMessages; ?></h3><p>Unread Messages</p></div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success"><div class="stat-icon"><i class="fas fa-reply"></i></div><div class="stat-info"><h3><?php echo $repliedMessages; ?></h3><p>Replied</p></div></div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info"><div class="stat-icon"><i class="fas fa-calendar-day"></i></div><div class="stat-info"><h3><?php echo $todayMessages; ?></h3><p>Today</p></div></div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter-section">
        <form action="messages.php" method="GET">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="unread" <?php if ($filterStatus === 'unread') echo 'selected'; ?>>Unread</option>
                        <option value="read" <?php if ($filterStatus === 'read') echo 'selected'; ?>>Read</option>
                        <option value="replied" <?php if ($filterStatus === 'replied') echo 'selected'; ?>>Replied</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort_by" onchange="this.form.submit()">
                        <option value="newest" <?php if ($sortBy === 'newest') echo 'selected'; ?>>Newest First</option>
                        <option value="oldest" <?php if ($sortBy === 'oldest') echo 'selected'; ?>>Oldest First</option>
                        <option value="unread" <?php if ($sortBy === 'unread') echo 'selected'; ?>>Unread First</option>
                    </select>
                </div>
                <div class="col-md-12 mt-2"><button type="submit" class="btn btn-primary w-100">Apply Filters</button></div>
            </div>
        </form>
    </div>

    <!-- Messages Table -->
    <div class="card">
        <div class="card-body">
            <form action="messages.php" method="POST" id="bulkActionForm">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%"><input type="checkbox" id="selectAll"></th>
                                <th width="20%">From</th>
                                <th width="25%">Subject</th>
                                <th width="20%">Message</th>
                                <th width="10%">Date</th>
                                <th width="10%">Status</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages_data)): ?>
                                <tr><td colspan="7" class="text-center">No messages found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($messages_data as $msg): ?>
                                    <tr class="<?php echo $msg['status'] === 'unread' ? 'unread-message' : ''; ?>" id="message-row-<?php echo $msg['id']; ?>">
                                        <td><input type="checkbox" class="message-checkbox" name="message_ids[]" value="<?php echo $msg['id']; ?>"></td>
                                        <td>
                                            <div><strong><?php echo htmlspecialchars($msg['name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($msg['email']); ?></small></div>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($msg['subject']); ?></strong></td>
                                        <td><div class="text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($msg['message']); ?></div></td>
                                        <td><div><?php echo format_date($msg['created_at'], 'M j, Y'); ?></div><small class="text-muted"><?php echo format_date($msg['created_at'], 'g:i A'); ?></small></td>
                                        <td class="status-cell"><span class="badge bg-<?php echo $msg['status'] === 'unread' ? 'warning' : ($msg['status'] === 'replied' ? 'success' : 'primary'); ?>"><?php echo ucfirst($msg['status']); ?></span></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-action" onclick='viewMessage(<?php echo json_encode($msg); ?>, this)'><i class="fas fa-eye"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-success btn-action" onclick='replyMessage(<?php echo json_encode($msg); ?>)'><i class="fas fa-reply"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="deleteMessage(<?php echo $msg['id']; ?>)"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="bulkActions" style="display: none;">
                        <select name="bulk_action" class="form-select form-select-sm d-inline-block" style="width: auto;">
                            <option value="">Bulk Actions...</option>
                            <option value="read">Mark as Read</option>
                            <option value="unread">Mark as Unread</option>
                            <option value="replied">Mark as Replied</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary ms-2">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">View Message</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="message-details">
        <div class="row mb-3"><div class="col-md-6"><strong>From:</strong> <span id="messageFrom"></span></div><div class="col-md-6"><strong>Date:</strong> <span id="messageDate"></span></div></div>
        <div class="row mb-3"><div class="col-md-12"><strong>Email:</strong> <span id="messageEmail"></span></div></div>
        <div class="mb-3"><strong>Subject:</strong> <span id="messageSubject"></span></div>
        <div class="mb-3"><strong>Message:</strong><div class="border rounded p-3 mt-2 bg-light" id="messageContent" style="white-space: pre-wrap;"></div></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="button" class="btn btn-success" id="viewReplyButton"><i class="fas fa-reply"></i> Reply</button></div>
</div></div></div>

<!-- Reply Message Modal -->
<div class="modal fade" id="replyMessageModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form id="replyForm" method="POST" action="messages.php">
        <input type="hidden" name="id" id="replyId">
        <input type="hidden" name="send_reply" value="1">
        <div class="modal-header"><h5 class="modal-title">Reply to Message</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">To:</label>
                <input type="email" class="form-control" id="replyTo" name="to_email" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Subject:</label>
                <input type="text" class="form-control" id="replySubject" name="subject" required>
            </div>
            <div class="mb-3"><label class="form-label">Your Reply:</label><textarea class="form-control" id="replyMessage" name="reply_body" rows="8" required placeholder="Compose your reply here..."></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button></div>
    </form>
</div></div></div>

<!-- Hidden form for single delete -->
<form method="POST" action="messages.php" id="deleteForm" style="display:none;"><input type="hidden" name="delete_message" value="1"><input type="hidden" name="id" id="deleteId"></form>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
    const viewMessageModal = new bootstrap.Modal(document.getElementById('viewMessageModal'));
    const replyMessageModal = new bootstrap.Modal(document.getElementById('replyMessageModal'));

    function viewMessage(msg, button) {
        const row = button.closest('tr');
        if (row.classList.contains('unread-message')) {
            fetch(`messages.php?action=mark_read&id=${msg.id}`)
                .then(() => {
                    row.classList.remove('unread-message');
                    const statusCell = row.querySelector('.status-cell');
                    if(statusCell) statusCell.innerHTML = '<span class="badge bg-primary">Read</span>';
                });
        }

        document.getElementById('messageFrom').textContent = msg.name;
        document.getElementById('messageEmail').textContent = msg.email;
        document.getElementById('messageDate').textContent = new Date(msg.created_at).toLocaleString();
        document.getElementById('messageSubject').textContent = msg.subject;
        document.getElementById('messageContent').textContent = msg.message;

        document.getElementById('viewReplyButton').onclick = function() {
            viewMessageModal.hide();
            replyMessage(msg);
        };

        viewMessageModal.show();
    }

    function replyMessage(msg) {
        document.getElementById('replyId').value = msg.id;
        document.getElementById('replyTo').value = msg.email;
        document.getElementById('replySubject').value = `Re: ${msg.subject}`;
        document.getElementById('replyMessage').value = `\n\n--- Original Message ---\nFrom: ${msg.name} <${msg.email}>\nDate: ${new Date(msg.created_at).toLocaleString()}\nSubject: ${msg.subject}\n\n${msg.message}`;
        replyMessageModal.show();
    }

    function deleteMessage(id) {
        if (confirm('Are you sure you want to permanently delete this message?')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    // Bulk actions
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.message-checkbox');
    const bulkActionsDiv = document.getElementById('bulkActions');

    selectAllCheckbox.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        toggleBulkActions();
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });

    function toggleBulkActions() {
        const anyChecked = Array.from(checkboxes).some(c => c.checked);
        bulkActionsDiv.style.display = anyChecked ? 'block' : 'none';
    }
</script>
</body>
</html>