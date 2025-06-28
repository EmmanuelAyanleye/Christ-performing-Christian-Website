<?php
// Get user role from session, default to 'guest' if not set
$user_role = $_SESSION['user_role'] ?? 'guest';
$is_super_admin = ($user_role === 'super_admin');

// Get the current script name to set the 'active' class
$current_script = basename($_SERVER['PHP_SELF']);
?>
<style>
  @media (max-width: 991.98px) {
    .sidebar {
        position: fixed;
        top: 0;
        right: 0;
        left: auto;
        height: 100vh;
        width: 250px;
        z-index: 2101;
        background: linear-gradient(135deg, #3488dd, #043261);
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(.4,0,.2,1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        overflow-y: auto;
        overflow-x: hidden;
    }
    .sidebar.show {
        transform: translateX(0);
    }
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.4);
        z-index: 2100;
        transition: opacity 0.3s;
        opacity: 0;
    }
    .sidebar.show ~ .sidebar-overlay,
    .sidebar-overlay.active {
        display: block;
        opacity: 1;
    }
    body.sidebar-open {
        overflow: hidden;
    }
    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    .menu-toggle {
        display: block;
        position: fixed;
        top: 18px;
        right: 18px;
        left: auto;
        z-index: 2200;
        background: #043261;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: background 0.2s;
    }
    .menu-toggle:focus {
        outline: 2px solid #3488dd;
    }
}
@media (min-width: 992px) {
    .sidebar-overlay,
    .menu-toggle {
        display: none !important;
    }
}

@media (max-width: 991.98px) {
    html, body {
        max-width: 100vw;
        overflow-x: hidden !important;
    }
    body.sidebar-open {
        position: fixed;
        width: 100vw;
        overflow-y: hidden;
        overflow-x: hidden !important;
    }
    .sidebar {
        position: fixed;
        top: 0;
        right: 0;
        left: auto;
        height: 100vh;
        width: 250px;
        max-width: 90vw;
        z-index: 2101;
        background: linear-gradient(135deg, #3488dd, #043261);
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(.4,0,.2,1);
        box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        overflow-y: auto;
        overflow-x: hidden;
    }
    .sidebar.show {
        transform: translateX(0);
    }
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.4);
        z-index: 2100;
        transition: opacity 0.3s;
        opacity: 0;
    }
    .sidebar.show ~ .sidebar-overlay,
    .sidebar-overlay.active {
        display: block;
        opacity: 1;
    }
}
</style>

<button class="menu-toggle" id="menuToggle" aria-label="Open sidebar">
    <i class="fas fa-bars"></i>
</button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const icon = menuToggle.querySelector('i');

    function openSidebar() {
        sidebar.classList.add('show');
        overlay.classList.add('active');
        document.body.classList.add('sidebar-open');
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    }
    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }

    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        if (sidebar.classList.contains('show')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener('click', function() {
        closeSidebar();
    });

    // Optional: Close sidebar when a menu item is clicked (for better UX)
    sidebar.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 991.98) closeSidebar();
        });
    });
});
</script>