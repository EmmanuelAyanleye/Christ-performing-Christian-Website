<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>/index.php">
            <img src="<?php echo BASE_URL; ?>/images/church_logo.png" alt="Church Logo" width="35" height="35">
            <span class="ms-2 church-name">Christ performing Christian Centre</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($current_page) && $current_page === 'home') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($current_page) && $current_page === 'about') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($current_page) && $current_page === 'sermons') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/sermons.php">Sermons</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#events">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($current_page) && $current_page === 'gallery') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/gallery.php">Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (isset($current_page) && $current_page === 'blog') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/blog.php">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php#contact">Contact</a>
                </li>

            </ul>
        </div>
    </div>
    <style>
      /* Responsive adjustments for Navbar */
      @media (max-width: 768px) {
          .navbar-brand .church-name {
              display: none; /* Hide church name on mobile */
          }
      }
    </style>
</nav>
