<?php
require_once __DIR__ . '/../includes/config.php';

// Get post ID from URL, default to 0
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id === 0) {
    header("Location: " . BASE_URL . "/pages/blog.php");
    exit();
}

// Fetch the specific blog post
$post_sql = "SELECT * FROM blog_posts WHERE id = :id AND status = 'published'";
$post_stmt = $conn->prepare($post_sql);
$post_stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
$post_stmt->execute();
$post = $post_stmt->fetch(PDO::FETCH_ASSOC);

// Set page title and description dynamically
$page_title = $post ? htmlspecialchars($post['title']) : "Article Not Found";
$page_description = $post ? htmlspecialchars($post['excerpt']) : "The requested article could not be found.";

// The header already includes the navigation
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

        /* Article Styles */
        .article-header {
            padding: 120px 0 60px;
            background: var(--bg-light);
        }

        .article-meta {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .article-content h2 {
            color: var(--primary-color);
            margin: 2rem 0 1rem;
        }

        .article-content h3 {
            color: var(--text-dark);
            margin: 1.5rem 0 1rem;
        }

        .article-content p {
            margin-bottom: 1.5rem;
        }

        .share-buttons {
            padding: 2rem 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .share-btn {
            background: var(--bg-light);
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            margin-right: 10px;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .share-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Comment Section */
        .comment-section {
            margin-top: 3rem;
        }

        .comment-form {
            background: var(--bg-light);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .comment {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .comment-author {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .comment-date {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .comment-actions {
            margin-top: 1rem;
        }

        .comment-btn {
            background: none;
            border: none;
            color: var(--text-light);
            margin-right: 1rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .comment-btn:hover {
            color: var(--primary-color);
        }

        /* Recommended Posts */
        .recommended-post {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .recommended-post:hover {
            transform: translateY(-5px);
        }

        .recommended-post img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .recommended-post-content {
            padding: 1.5rem;
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
    </style>

<section class="article-header">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="font-display mb-4" data-aos="fade-up"><?php echo $post ? htmlspecialchars($post['title']) : 'Article Not Found'; ?></h1>
                <div class="article-meta" data-aos="fade-up" data-aos-delay="100">
                    <span><i class="fas fa-user me-2"></i><?php echo $post ? get_author_name($post['author_id']) : 'N/A'; ?></span>
                    <span><i class="fas fa-calendar me-2"></i><?php echo $post ? date('F j, Y', strtotime($post['created_at'])) : 'N/A'; ?></span>
                    <span><i class="fas fa-folder me-2"></i><?php echo $post ? htmlspecialchars($post['category']) : 'N/A'; ?></span>
                    <!-- Reading time can be calculated if needed -->
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($post): ?>
                    <div class="article-content">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid rounded mb-4 shadow-sm">
                        <?php endif; ?>
                        <?php echo $post['content']; // Assuming content is trusted HTML from a WYSIWYG editor ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">The article you are looking for does not exist or has been removed.</div>
                    <div class="text-center mt-4">
                        <a href="<?php echo BASE_URL; ?>/pages/blog.php" class="btn btn-primary">Back to Blog</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include('../includes/footer.php'); ?>