<?php
require_once __DIR__ . '/../includes/config.php'; // Load configuration and database connection
require_once __DIR__ . '/../includes/config.php'; // Load configuration and database connection

// Initialize newsletter message variable
$newsletter_message = '';

// Handle newsletter subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = trim($_POST['newsletter_email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $newsletter_message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                // Check if subscriber is active
                $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ? AND is_active = 1");
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() > 0) {
                    $newsletter_message = '<div class="alert alert-info">You are already subscribed. Thank you!</div>';
                } else {
                    // Reactivate existing subscriber
                    $stmt = $pdo->prepare("UPDATE subscribers SET is_active = 1, subscribed_at = NOW(), unsubscribed_at = NULL WHERE email = ?");
                    $stmt->execute([$email]);
                    $newsletter_message = '<div class="alert alert-success">Welcome back! Your subscription has been reactivated.</div>';
                }
            } else {
                // Insert new subscriber
                $stmt = $pdo->prepare("INSERT INTO subscribers (email, subscribed_at) VALUES (?, NOW())");
                $stmt->execute([$email]);
                
                $newsletter_message = '<div class="alert alert-success">Thank you for subscribing to our newsletter!</div>';
                
                // Optional: Send welcome email
                // send_welcome_email($email);
            }
        } catch (PDOException $e) {
            error_log("Subscription Error: " . $e->getMessage());
            $newsletter_message = '<div class="alert alert-danger">Sorry, there was an error processing your subscription. Please try again later.</div>';
        }
    }
}

// Get current page from URL, default to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total posts count
$total_posts_sql = "SELECT COUNT(*) FROM blog_posts WHERE status = 'published'";
$total_posts_stmt = $conn->query($total_posts_sql);
$total_posts = $total_posts_stmt->fetchColumn();

// Calculate total pages
$total_pages = ceil($total_posts / $per_page);

// Get posts for current page
$posts_sql = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT :offset, :per_page";
$posts_stmt = $conn->prepare($posts_sql);
$posts_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$posts_stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
$posts_stmt->execute();
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for sidebar
$categories_sql = "SELECT category, COUNT(*) as count FROM blog_posts WHERE status = 'published' GROUP BY category";
$categories_stmt = $conn->query($categories_sql);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent posts for sidebar
$recent_posts_sql = "SELECT id, title, featured_image, created_at FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3";
$recent_posts_stmt = $conn->query($recent_posts_sql);
$recent_posts = $recent_posts_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Our Blog";
$page_description = "Read inspiring articles, devotions, and updates from Grace Fellowship Church. Grow in your faith through our blog content.";
include '../includes/header.php';
?>
<style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #fbbf24;
            --accent-color: #059669;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Navigation Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background-color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }

        /* Page Header */
        .page-header {
            height: 70vh;
            background: linear-gradient(rgba(30, 58, 138, 0.8), rgba(30, 58, 138, 0.8)), 
                        url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=1920&h=1080&fit=crop') center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .page-header h1 {
            margin-top: 76px;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Search Bar */
        .search-section {
            padding: 2rem 0;
            background: var(--bg-light);
        }

        .search-bar .form-control {
            border-radius: 50px;
            padding: 12px 20px;
            border: 2px solid #e5e7eb;
            font-size: 1rem;
        }

        .search-bar .btn {
            border-radius: 50px;
            padding: 12px 25px;
            background: var(--primary-color);
            border: none;
        }

        /* Section Styles */
        .section-padding {
            padding: 60px 0;
        }

        /* Blog Card Styles - Updated for horizontal layout */
        .blog-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
            display: flex;
            height: 200px;
        }

        .blog-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .blog-card img {
            width: 250px;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .blog-card:hover img {
            transform: scale(1.05);
        }

        .blog-card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
        }

        .blog-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .blog-card h5 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .blog-card p {
            font-size: 0.9rem;
            margin-bottom: 1rem;
            flex: 1;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .blog-tags {
            margin-top: auto;
        }

        .blog-tag {
            display: inline-block;
            background: var(--bg-light);
            color: var(--primary-color);
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.7rem;
            margin: 0 0.25rem 0.25rem 0;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .blog-tag:hover {
            background: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .blog-card {
                flex-direction: column;
                height: auto;
            }
            
            .blog-card img {
                width: 100%;
                height: 150px;
            }
        }

        /* Sidebar Styles */
        .sidebar {
            position: sticky;
            top: 100px;
        }

        .sidebar-widget {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar-widget h5 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .category-list {
            list-style: none;
            padding: 0;
        }

        .category-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .category-list li:last-child {
            border-bottom: none;
        }

        .category-list a {
            color: var(--text-dark);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .category-list a:hover {
            color: var(--primary-color);
        }

        .recent-post {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .recent-post:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .recent-post img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .recent-post-content h6 {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .recent-post-content small {
            color: var(--text-light);
        }

        /* Footer */
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer h5 {
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--secondary-color);
        }

        .social-icons a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: var(--secondary-color);
            color: var(--primary-color);
            text-align: center;
            line-height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(251, 191, 36, 0.3);
        }

        /* Pagination */
        .pagination {
            justify-content: center;
            margin-top: 3rem;
        }

        .page-link {
            color: var(--primary-color);
            border: 1px solid #e5e7eb;
            padding: 0.75rem 1rem;
            margin: 0 0.25rem;
            border-radius: 8px;
        }

        .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
    
<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div data-aos="fade-up">
            <h1 class="font-display">Our Blog</h1>
            <p>Insights, devotions, and updates from our church community</p>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Blog Posts -->
            <div class="col-lg-8">
                <div id="blogContainer">
                    <?php foreach($posts as $post): ?>
                    <div class="blog-post" data-aos="fade-up">
                        <article class="blog-card">
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <div class="blog-card-body">
                                <div>
                                    <div class="blog-meta">
                                        <span><i class="fas fa-user me-1"></i><?php echo get_author_name($post['author_id']); ?></span>
                                        <span><i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                        <span><i class="fas fa-folder me-1"></i><?php echo htmlspecialchars($post['category']); ?></span>
                                    </div>
                                    <h5><a href="blog-article.php?id=<?php echo $post['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars(substr($post['excerpt'], 0, 150)); ?>...</p>
                                </div>
                                <div class="blog-tags">
                                    <a href="#" class="blog-tag"><?php echo htmlspecialchars($post['category']); ?></a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <nav aria-label="Blog pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar">
                    <!-- Categories Widget -->
                    <div class="sidebar-widget" data-aos="fade-left">
                        <h5><i class="fas fa-folder me-2"></i>Categories</h5>
                        <ul class="category-list">
                            <?php foreach($categories as $category): ?>
                            <li><a href="#" onclick="filterByCategory('<?php echo htmlspecialchars($category['category']); ?>')">
                                <?php echo htmlspecialchars($category['category']); ?> <span class="float-end">(<?php echo $category['count']; ?>)</span>
                            </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Recent Posts Widget -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="100">
                        <h5><i class="fas fa-clock me-2"></i>Recent Posts</h5>
                        <?php foreach($recent_posts as $recent_post): ?>
                        <div class="recent-post">
                            <img src="<?php echo htmlspecialchars($recent_post['featured_image']); ?>" alt="Recent Post">
                            <div class="recent-post-content">
                                <h6><a href="blog-article.php?id=<?php echo $recent_post['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($recent_post['title']); ?></a></h6>
                                <small><?php echo date('M j, Y', strtotime($recent_post['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Newsletter Widget -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="300" id="newsletter">
                        <h5><i class="fas fa-envelope me-2"></i>Newsletter</h5>
                        <p class="mb-3">Subscribe to receive our latest blog posts and church updates.</p>
                        <?php if (!empty($newsletter_message)) echo $newsletter_message; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <input type="email" name="newsletter_email" class="form-control" placeholder="Enter your email address" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>