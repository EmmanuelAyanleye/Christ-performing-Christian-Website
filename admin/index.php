<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/partials/session_auth.php';

// --- Fetch data for dashboard stats ---
try {
    // Total Blog Posts
    $total_posts_stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
    $total_posts = $total_posts_stmt->fetchColumn();

    // Total Sermons
    $total_sermons_stmt = $pdo->query("SELECT COUNT(*) FROM sermons");
    $total_sermons = $total_sermons_stmt->fetchColumn();

    // Total Gallery Items
    $total_gallery_stmt = $pdo->query("SELECT COUNT(*) FROM gallery");
    $total_gallery_items = $total_gallery_stmt->fetchColumn();

    // Total Subscribers
    $total_subscribers_stmt = $pdo->query("SELECT COUNT(*) FROM subscribers WHERE is_active = 1");
    $total_subscribers = $total_subscribers_stmt->fetchColumn();

    // Recent Activities (Example: last 5 blog posts and sermons)
    $recent_activity_stmt = $pdo->query("
        (SELECT 'blog' as type, title, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'sermon' as type, title, created_at FROM sermons ORDER BY created_at DESC LIMIT 2)
        ORDER BY created_at DESC
    ");
    $recent_activities = $recent_activity_stmt->fetchAll();

} catch (PDOException $e) {
    // Handle potential database errors gracefully
    error_log("Dashboard Error: " . $e->getMessage());
    $total_posts = $total_sermons = $total_gallery_items = $total_subscribers = 'N/A';
    $recent_activities = [];
}


$pageTitle = "Admin Dashboard";
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/sidebar.php';
?>

<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <h1>Dashboard</h1>
        <div class="header-actions">
            <span class="admin-info">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_full_name']); ?></strong></span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
                    <div class="stat-card bg-primary">
                        <div class="stat-icon"><i class="fas fa-blog"></i></div>
                        <div class="stat-info">
                            <h3><?php echo htmlspecialchars($total_posts); ?></h3>
                            <p>Blog Posts</p>
                        </div>
                    </div>
                </div>
        <div class="col-md-3">
            <div class="stat-card bg-success">
                <div class="stat-icon"><i class="fas fa-microphone"></i></div>
                <div class="stat-info">
                    <h3><?php echo htmlspecialchars($total_sermons); ?></h3>
                    <p>Sermons</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-warning">
                <div class="stat-icon"><i class="fas fa-images"></i></div>
                <div class="stat-info">
                    <h3><?php echo htmlspecialchars($total_gallery_items); ?></h3>
                    <p>Gallery Items</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-info">
                <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                <div class="stat-info">
                    <h3><?php echo htmlspecialchars($total_subscribers); ?></h3>
                    <p>Subscribers</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h5>Recent Activity</h5></div>
                <div class="card-body">
                    <div class="activity-list">
                        <?php if (empty($recent_activities)): ?>
                            <p>No recent activity to display.</p>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $activity['type'] === 'blog' ? 'bg-primary' : 'bg-success'; ?>">
                                        <i class="fas <?php echo $activity['type'] === 'blog' ? 'fa-blog' : 'fa-microphone'; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p><strong>New <?php echo htmlspecialchars($activity['type']); ?></strong> "<?php echo htmlspecialchars($activity['title']); ?>" was added.</p>
                                        <small class="text-muted"><?php echo format_date($activity['created_at']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & System Info -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h5>Quick Actions</h5></div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="blog-posts.php" class="btn btn-outline-primary"><i class="fas fa-plus me-2"></i>Add Blog Post</a>
                        <a href="sermons.php" class="btn btn-outline-success"><i class="fas fa-plus me-2"></i>Add Sermon</a>
                        <a href="events.php" class="btn btn-outline-warning"><i class="fas fa-plus me-2"></i>Add Event</a>
                        <a href="gallery.php" class="btn btn-outline-info"><i class="fas fa-plus me-2"></i>Upload Photos</a>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header"><h5>System Info</h5></div>
                <div class="card-body">
                    <p><strong>Last Login:</strong> <span id="lastLogin">Just now</span></p>
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- External JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // (Optional) JS-based fallback check, though PHP session should already handle access
    <?php if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true): ?>
        window.location.href = 'login.php';
    <?php endif; ?>

    // Set admin name (from PHP session to JavaScript, not localStorage)
    document.addEventListener("DOMContentLoaded", function () {
        const adminName = <?php echo json_encode($_SESSION['admin_email'] ?? 'Admin'); ?>;
        const lastLogin = new Date().toLocaleString();

        const nameElement = document.getElementById('adminName');
        if (nameElement) nameElement.textContent = adminName;

        const lastLoginElement = document.getElementById('lastLogin');
        if (lastLoginElement) lastLoginElement.textContent = lastLogin;
    });

    // Logout function to trigger PHP logout
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>