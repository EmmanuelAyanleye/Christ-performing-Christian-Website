<?php
require_once __DIR__ . '/../includes/config.php'; // Load configuration and database connection
$current_page = 'blog';

// --- NEWSLETTER FORM HANDLING ---
$newsletter_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = sanitize_input($_POST['newsletter_email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $newsletter_message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, is_active FROM subscribers WHERE email = ?");
            $stmt->execute([$email]);
            $subscriber = $stmt->fetch();
            if ($subscriber && $subscriber['is_active']) {
                $newsletter_message = '<div class="alert alert-info">You are already subscribed. Thank you!</div>';
            } elseif ($subscriber && !$subscriber['is_active']) {
                $stmt = $conn->prepare("UPDATE subscribers SET is_active = 1, unsubscribed_at = NULL WHERE email = ?");
                $stmt->execute([$email]);
                $newsletter_message = '<div class="alert alert-success">Welcome back! Your subscription has been reactivated.</div>';
            } else {
                $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
                $stmt->execute([$email]);
                $newsletter_message = '<div class="alert alert-success">Thank you for subscribing to our newsletter!</div>';
            }
        } catch (PDOException $e) {
            $newsletter_message = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        }
    }
}

// --- FILTERS, SEARCH, AND PAGINATION ---
$search_term = $_GET['search'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_tag = $_GET['tag'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$per_page = 7; // Number of posts per page
$offset = ($page - 1) * $per_page;

// --- BUILD QUERY ---
$base_sql = "FROM blog_posts p JOIN users u ON p.author_id = u.id WHERE p.status = 'published'";
$where_clauses = []; // Stores parts of the WHERE clause
$params_for_execute = []; // Stores parameters for PDOStatement::execute() in order

if (!empty($search_term)) {
    $where_clauses[] = "(p.title LIKE ? OR p.content LIKE ? OR u.full_name LIKE ?)"; // Positional placeholders
    $params_for_execute[] = '%' . $search_term . '%';
    $params_for_execute[] = '%' . $search_term . '%';
    $params_for_execute[] = '%' . $search_term . '%';
}
if (!empty($filter_category)) {
    $where_clauses[] = "p.category = ?"; // Positional placeholder
    $params_for_execute[] = $filter_category;
}
if (!empty($filter_tag)) {
    $where_clauses[] = "FIND_IN_SET(?, p.tags)"; // Positional placeholder
    $params_for_execute[] = $filter_tag;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' AND ' . implode(' AND ', $where_clauses);
}

// --- TOTAL COUNT ---
$total_posts_sql = "SELECT COUNT(*) " . $base_sql . $where_sql;
$total_posts_stmt = $conn->prepare($total_posts_sql);
$total_posts_stmt->execute($params_for_execute); // Execute with the collected parameters
$total_posts = $total_posts_stmt->fetchColumn();
$total_pages = ceil($total_posts / $per_page);

// --- FETCH POSTS FOR CURRENT PAGE ---
$posts_sql = "SELECT p.*, u.full_name as author_name " . $base_sql . $where_sql . " ORDER BY p.created_at DESC LIMIT ?, ?"; // Positional placeholders for LIMIT
$posts_stmt = $conn->prepare($posts_sql);

// Combine WHERE parameters with LIMIT parameters into a single array
$all_params_for_posts_query = $params_for_execute;
$all_params_for_posts_query[] = $offset;
$all_params_for_posts_query[] = $per_page;

$posts_stmt->execute($all_params_for_posts_query); // Execute with all positional parameters
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- SIDEBAR DATA ---
$categories_sql = "SELECT category, COUNT(*) as count FROM blog_posts WHERE status = 'published' AND category IS NOT NULL AND category != '' GROUP BY category ORDER BY category ASC";
$categories = $conn->query($categories_sql)->fetchAll(PDO::FETCH_ASSOC);

$recent_posts_sql = "SELECT title, slug, featured_image, created_at FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3";
$recent_posts = $conn->query($recent_posts_sql)->fetchAll(PDO::FETCH_ASSOC);

$tags_sql = "SELECT tags FROM blog_posts WHERE status = 'published' AND tags IS NOT NULL AND tags != ''";
$all_tags_raw = $conn->query($tags_sql)->fetchAll(PDO::FETCH_COLUMN);
$tags_array = [];
foreach ($all_tags_raw as $tag_string) {
    $tags_array = array_merge($tags_array, array_map('trim', explode(',', $tag_string)));
}
$tags = array_count_values(array_filter($tags_array)); // Count occurrences of each tag
arsort($tags); // Sort by value (count) in descending order
$tags = array_slice($tags, 0, 12, true); // Get the top 12 most used tags, preserving keys

$page_title = "Our Blog";
$page_description = "Read inspiring articles, devotions, and updates from Christ performing Christian Centre. Grow in your faith through our blog content.";
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

.hero,
.page-header {
    min-height: 70vh;
    max-height: 80vh;
    width: 100vw;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #fff;
    position: relative;
    padding-top: 70px; 
    padding-left: 0;
    padding-right: 0;
    box-sizing: border-box;
    overflow: hidden;
}

.hero .container,
.page-header .container {
    width: 100%;
    max-width: 1200px;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0 auto;
}

.hero-content,
.page-header > .container > div {
    width: 100%;
    padding: 0 10px;
    box-sizing: border-box;
}

.hero h1,
.page-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    line-height: 1.2;
}

.hero p,
.page-header p {
    font-size: 1.15rem;
    margin-bottom: 1.5rem;
    opacity: 0.92;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 991.98px) {
    .hero,
    .page-header {
        min-height: 40vh;
        padding-top: 70px;
    }
    .hero h1,
    .page-header h1 {
        font-size: 2rem;
    }
    .hero p,
    .page-header p {
        font-size: 1rem;
    }
}

@media (max-width: 575.98px) {
    .hero,
    .page-header {
        min-height: 50vh;
        padding-top: 55px;
    }
    .hero h1,
    .page-header h1 {
        font-size: 1.2rem;
    }
    .hero p,
    .page-header p {
        font-size: 0.95rem;
    }
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
            width: 100%;
            min-width: 0;
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0;
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
            /* Stack image and text vertically on smaller screens */
            .blog-card, .recent-post {
                flex-direction: column;
                height: auto;
            }
            
            .blog-card img {
                width: 100%;
                height: 150px;
            }

            .recent-post img{
                width: 100%;
                height: 150px;

            }
            .recent-post {
                flex-direction: column; /* Stack image and text in recent posts */
                align-items: center;
                text-align: center;
            }
            .recent-post img { margin-bottom: 0.5rem; }
            .page-header h1 {font-size: 2.5rem;} /* Further reduce header size on mobile */
        }
        
        @media (max-width: 576px) { /* Adjust for very small screens */
            .page-header h1 {font-size: 2.5rem;}
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

        /* Prevent horizontal overflow */
        html, body {
            overflow-x: hidden;
            width: 100vw;
        }

        /* Make all images responsive */
        img, .img-fluid {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Container and row fixes */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding-left: 15px;
            padding-right: 15px;
            box-sizing: border-box;
        }
        .row {
            margin-left: -15px;
            margin-right: -15px;
            flex-wrap: wrap;
        }

        /* Remove negative margins on small screens */
        @media (max-width: 575.98px) {
            .row {
                margin-left: 0;
                margin-right: 0;
            }
        }

        /* Responsive section padding */
        .section-padding {
            padding-left: 0;
            padding-right: 0;
        }

        /* Blog card image fix */
        .blog-card img {
            width: 100%;
            min-width: 0;
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0;
        }

        /* Responsive adjustments for blog cards and sidebar */
        @media (max-width: 991.98px) {
            .sidebar {
                position: static;
                top: auto;
                margin-top: 2rem;
            }
        }

        @media (max-width: 768px) {
            .section-padding {
                padding: 20px 0;
            }
            .blog-card,
            .recent-post {
                flex-direction: column !important;
                height: auto !important;
            }
            .blog-card img,
            .recent-post img {
                width: 100% !important;
                height: 150px !important;
            }
            .blog-card-body {
                padding: 1rem !important;
            }
            .sidebar-widget {
                padding: 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .row {
                margin-left: 0;
                margin-right: 0;
            }
            .blog-card img,
            .recent-post img {
                height: 120px !important;
            }
            .blog-card-body {
                padding: 0.7rem !important;
            }
            .sidebar-widget {
                padding: 0.7rem;
            }
            .page-header h1 {
                font-size: 1.5rem !important;
            }
        }
    </style>

<!-- Page Header -->
 <section class="page-header" style="background: linear-gradient(rgba(30, 58, 138, 0.8), rgba(30, 58, 138, 0.8)),
                        url('<?php echo BASE_URL; ?>/images/blog.jpg') center/cover;">
  <div class="container">
    <div>
      <h1>Our Blog</h1>
      <p>Insights, devotions, and updates from our church community</p>
    </div>
  </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Blog Posts -->
            <div class="col-lg-8">
                <div id="blogContainer">
                    <?php if (empty($posts)): ?>
                        <div class="text-center" data-aos="fade-up">
                            <h3>No Posts Found</h3>
                            <p>Your search or filter did not return any results. Please try again.</p>
                            <a href="blog.php" class="btn btn-primary mt-3">View All Posts</a>
                        </div>
                    <?php else: ?>
                        <?php foreach($posts as $post): ?>
                        <div class="blog-post" data-aos="fade-up">
                            <article class="blog-card">
                                <a href="blog-article.php?slug=<?php echo $post['slug']; ?>">
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($post['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>"
                                         onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/assets/images/default-image.jpg';">
                                </a>
                                <div class="blog-card-body">
                                    <div>
                                        <div class="blog-meta">
                                            <span><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($post['author_name']); ?></span>
                                            <span><i class="fas fa-calendar me-1"></i><?php echo format_date($post['created_at']); ?></span>
                                            <span><i class="fas fa-folder me-1"></i><?php echo htmlspecialchars($post['category']); ?></span>
                                        </div>
                                        <h5><a href="blog-article.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                                        <p class="text-muted"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                    </div>
                                    <div class="blog-tags">
                                        <?php if(!empty($post['tags'])): ?>
                                            <?php foreach(explode(',', $post['tags']) as $tag): ?>
                                                <a href="blog.php?tag=<?php echo urlencode(trim($tag)); ?>" class="blog-tag"><?php echo htmlspecialchars(trim($tag)); ?></a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Blog pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php
                            $query_params = http_build_query(['search' => $search_term, 'category' => $filter_category, 'tag' => $filter_tag]);
                            ?>
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $query_params; ?>" aria-label="Previous">&laquo;</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo $query_params; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $query_params; ?>" aria-label="Next">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar">
                    <!-- Search Widget -->
                    <div class="sidebar-widget" data-aos="fade-left">
                        <form action="blog.php" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search_term); ?>">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>

                    <!-- Categories Widget -->
                    <div class="sidebar-widget" data-aos="fade-left">
                        <h5><i class="fas fa-folder me-2"></i>Categories</h5>
                        <ul class="category-list">
                            <li><a href="blog.php">All Categories <span class="float-end">(<?php echo $total_posts; ?>)</span></a></li>
                            <?php foreach($categories as $category): ?>
                                <li><a href="blog.php?category=<?php echo urlencode($category['category']); ?>">
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
                            <a href="blog-article.php?slug=<?php echo $recent_post['slug']; ?>">
                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($recent_post['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($recent_post['title']); ?>"
                                     onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/assets/images/default-image.jpg';">
                            </a>
                            <div class="recent-post-content">
                                <h6><a href="blog-article.php?slug=<?php echo $recent_post['slug']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($recent_post['title']); ?></a></h6>
                                <small><?php echo format_date($recent_post['created_at']); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tags Widget -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="200">
                        <h5><i class="fas fa-tags me-2"></i>Popular Tags</h5>
                        <div class="tags-cloud">
                            <?php foreach($tags as $tag => $count): ?>
                                <a href="blog.php?tag=<?php echo urlencode($tag); ?>" class="blog-tag"><?php echo htmlspecialchars($tag); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Newsletter Widget -->
                    <div class="sidebar-widget" data-aos="fade-left" data-aos-delay="300" id="newsletter">
                        <h5><i class="fas fa-envelope me-2"></i>Newsletter</h5>
                        <p class="mb-3">Subscribe to receive our latest blog posts and church updates.</p>
                        <?php if (!empty($newsletter_message)) echo $newsletter_message; ?>
                        <form action="blog.php#newsletter" method="POST">
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