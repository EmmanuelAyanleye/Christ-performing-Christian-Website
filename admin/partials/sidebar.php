<nav class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-church"></i> Grace Fellowship</h4>
        <p>Admin Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admins.php' ? 'active' : ''; ?>">
            <a href="admins.php"><i class="fas fa-users-cog"></i> Manage Admins</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'blog-posts.php' ? 'active' : ''; ?>">
            <a href="blog-posts.php"><i class="fas fa-blog"></i> Blog Posts</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'sermons.php' ? 'active' : ''; ?>">
            <a href="sermons.php"><i class="fas fa-microphone"></i> Sermons</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">
            <a href="gallery.php"><i class="fas fa-images"></i> Gallery</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>">
            <a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : ''; ?>">
            <a href="testimonials.php"><i class="fas fa-quote-left"></i> Testimonials</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'newsletter.php' ? 'active' : ''; ?>">
            <a href="newsletter.php"><i class="fas fa-envelope"></i> Newsletter</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
            <a href="messages.php"><i class="fas fa-comments"></i> Messages</a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="<?php echo BASE_URL; ?>/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

