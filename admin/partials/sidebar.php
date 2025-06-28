<?php
// Get user role from session, default to 'guest' if not set
$user_role = $_SESSION['user_role'] ?? 'guest';
$is_super_admin = ($user_role === 'super_admin');

// Get the current script name to set the 'active' class
$current_script = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <img src="<?php echo BASE_URL; ?>/images/church_logo.png" alt="Church Logo" width="40" height="40">
        <h4>Christ performing Christian Centre</h4>
        <p>Admin Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li class="<?= $current_script === 'index.php' ? 'active' : '' ?>"><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        
        <?php if ($is_super_admin): ?>
            <li class="<?= $current_script === 'admins.php' ? 'active' : '' ?>"><a href="admins.php"><i class="fas fa-users-cog"></i> Manage Admins</a></li>
        <?php endif; ?>

        <li class="<?= $current_script === 'blog-posts.php' ? 'active' : '' ?>"><a href="blog-posts.php"><i class="fas fa-blog"></i> Blog Posts</a></li>
        <li class="<?= $current_script === 'blog-comments.php' ? 'active' : '' ?>"><a href="blog-comments.php"><i class="fas fa-comments"></i> Blog Comments</a></li>

        <?php if ($is_super_admin): ?>
            <li class="<?= $current_script === 'sermons.php' ? 'active' : '' ?>"><a href="sermons.php"><i class="fas fa-microphone"></i> Sermons</a></li>
            <li class="<?= $current_script === 'gallery.php' ? 'active' : '' ?>"><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
            <li class="<?= $current_script === 'events.php' ? 'active' : '' ?>"><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
            <li class="<?= $current_script === 'testimonials.php' ? 'active' : '' ?>"><a href="testimonials.php"><i class="fas fa-quote-left"></i> Testimonials</a></li>
            <li class="<?= $current_script === 'newsletter.php' ? 'active' : '' ?>"><a href="newsletter.php"><i class="fas fa-envelope"></i> Newsletter</a></li>
            <li class="<?= $current_script === 'messages.php' ? 'active' : '' ?>"><a href="messages.php"><i class="fas fa-inbox"></i> Messages</a></li>
        <?php endif; ?>
    </ul>
    
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>