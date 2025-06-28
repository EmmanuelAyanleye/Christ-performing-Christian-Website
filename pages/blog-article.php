<?php
require_once __DIR__ . '/../includes/config.php';
$current_page = 'blog'; 

// Get post slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: " . BASE_URL . "/blog.php");
    exit();
}

// Fetch the specific blog post by slug, joining with users table to get author name
$post_sql = "SELECT p.*, u.full_name as author_name 
             FROM blog_posts p 
             JOIN users u ON p.author_id = u.id 
             WHERE p.slug = :slug AND p.status = 'published'";
$post_stmt = $conn->prepare($post_sql);
$post_stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
$post_stmt->execute();
$post = $post_stmt->fetch(PDO::FETCH_ASSOC);

// If post is not found, redirect to blog index
if (!$post) {
    header("Location: " . BASE_URL . "/404.php");
    exit();
}

// Increment view count
$update_view_sql = "UPDATE blog_posts SET view_count = view_count + 1 WHERE id = :id";
$update_stmt = $conn->prepare($update_view_sql);
$update_stmt->execute([':id' => $post['id']]);

// Fetch comments for this post (parent comments only)
$comments_sql = "SELECT c.*, u.full_name as author_name, u.avatar as author_avatar, u.role as author_role, c.name as commenter_name, c.likes
                 FROM blog_comments c
                 LEFT JOIN users u ON c.user_id = u.id 
                 WHERE c.post_id = :post_id AND c.parent_id IS NULL AND c.status IN ('approved', 'replied') 
                 ORDER BY c.created_at DESC";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bindParam(':post_id', $post['id'], PDO::PARAM_INT);
$comments_stmt->execute();
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch 3 random recommended posts (excluding current post)
$recommended_sql = "SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image, u.full_name as author_name, p.created_at 
                    FROM blog_posts p 
                    JOIN users u ON p.author_id = u.id 
                    WHERE p.id != :post_id AND p.status = 'published' 
                    ORDER BY RAND() LIMIT 3";
$recommended_stmt = $conn->prepare($recommended_sql);
$recommended_stmt->bindParam(':post_id', $post['id'], PDO::PARAM_INT);
$recommended_stmt->execute();
$recommended_posts = $recommended_stmt->fetchAll(PDO::FETCH_ASSOC);

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

    @media (max-width: 768px) {
        .article-meta {
            flex-wrap: wrap;
            gap: 1rem;
        }

        .article-header {
            padding: 90px 0px 0px 0px;
            background: var(--bg-light);
        }
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

    .article-content {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #333;
    }

    .article-content h2 {
        color: var(--primary-color);
        margin: 2rem 0 1rem;
        font-family: 'Playfair Display', serif;
        font-weight: 600;
    }

    .article-content h3 {
        color: var(--text-dark);
        margin: 1.8rem 0 1rem;
        font-family: 'Playfair Display', serif;
        font-weight: 500;
    }

    .article-content h4, .article-content h5, .article-content h6 {
        color: var(--text-dark);
        margin: 1.5rem 0 1rem;
        font-weight: 600;
    }

    .article-content p {
        margin-bottom: 1.5rem;
    }

    .article-content a {
        color: var(--primary-color);
        text-decoration: underline;
    }

    .article-content ul, .article-content ol {
        padding-left: 2rem;
        margin-bottom: 1.5rem;
    }

    .article-content blockquote {
        border-left: 4px solid var(--secondary-color);
        padding-left: 1.5rem;
        margin: 2rem 0;
        font-style: italic;
        color: var(--text-dark);
    }

    .article-content strong {
        font-weight: 600;
    }
    .article-content em {
        font-style: italic;
    }
    .article-content code {
        background-color: #f1f1f1;
        padding: .2em .4em;
        margin: 0;
        font-size: 85%;
        border-radius: 3px;
        font-family: SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    }
    .article-content pre {
        background: #f1f1f1;
        padding: 1rem;
        border-radius: 5px;
        overflow-x: auto;
    }
    .article-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
    }
    .article-content th, .article-content td {
        border: 1px solid #ddd;
        padding: 0.8rem;
        text-align: left;
    }
    .article-content th {
        background-color: #f2f2f2;
    }

    .share-buttons {
        padding: 2rem 0;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    .share-btn {
        background: var(--bg-light);
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        margin-right: 10px;
        color: var(--text-dark);
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
        font-size: 1rem !important;
        cursor: pointer;
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
                    <span><i class="fas fa-user me-2"></i><?php echo $post ? htmlspecialchars($post['author_name']) : 'N/A'; ?></span>
                    <span><i class="fas fa-calendar me-2"></i><?php echo $post ? date('F j, Y', strtotime($post['created_at'])) : 'N/A'; ?></span>
                    <span><i class="fas fa-folder me-2"></i><?php echo $post ? htmlspecialchars($post['category']) : 'N/A'; ?></span>
                    <span><i class="fas fa-eye me-2"></i><?php echo $post ? number_format($post['view_count'] + 1) : '0'; ?> views</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($post): ?>
                    <div class="article-content mb-4" data-aos="fade-up">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid rounded mb-4">
                        <?php endif; ?>
                        
                        <?php echo $post['content']; ?>
                    </div>

                    <!-- Share Buttons -->
                    <div class="share-buttons" data-aos="fade-up">
                        <h5 class="mb-3">Share this article: </h5>
                        <div>
                            <button class="share-btn" onclick="shareArticle('facebook')">
                            <i class="fab fa-facebook-f me-2"></i>Facebook
                            </button>
                            <button class="share-btn" onclick="shareArticle('twitter')">
                                <i class="fab fa-twitter me-2"></i>Twitter
                            </button>
                            <button class="share-btn" onclick="shareArticle('whatsapp')">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </button>
                            <button class="share-btn" onclick="shareArticle('copy')">
                                <i class="fas fa-link me-2"></i>Copy Link
                            </button>
                        </div>
                    </div>

                    <!-- Comment Section -->
                    <div class="comment-section" data-aos="fade-up">
                        <h4 class="mb-4">Comments (<?php echo count($comments); ?>)</h4>
                        
                        <!-- Comment Form -->
                        <div class="comment-form">
                            <h5 class="mb-3">Leave a Comment</h5>
                            <form id="commentForm" method="POST" action="<?php echo BASE_URL; ?>/includes/process_blog_comment.php">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" name="content" rows="4" placeholder="Your Comment" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        </div>

                        <!-- Comments List -->
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                                    <?php if (!empty($comment['author_avatar'])): ?>
                                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($comment['author_avatar']); ?>" alt="<?php echo htmlspecialchars($comment['author_name']); ?>" class="rounded-circle me-2" width="40">
                                    <?php endif; ?>
                                    <div class="comment-author">
                                        <?php echo htmlspecialchars($comment['author_name'] ?: $comment['commenter_name']); ?>
                                        <?php if (isset($comment['author_role']) && in_array($comment['author_role'], ['admin', 'super_admin'])): ?>
                                            <span class="badge bg-primary ms-2">Admin</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-date"><?php echo date('F j, Y \a\t g:i a', strtotime($comment['created_at'])); ?></div>
                                    <p><?php echo html_entity_decode($comment['content']); ?></p>
                                    <div class="comment-actions">
                                        <button class="comment-btn like-comment" data-comment-id="<?php echo $comment['id']; ?>">
                                            <i class="fas fa-thumbs-up me-1"></i>Like (<span class="like-count"><?php echo htmlspecialchars($comment['likes']); ?></span>)
                                        </button>
                                        <button class="comment-btn reply-comment" data-comment-id="<?php echo $comment['id']; ?>">
                                            <i class="fas fa-reply me-1"></i>Reply
                                        </button>
                                    </div>
                                    
                                    <!-- Reply Form (Hidden by default) -->
                                    <div class="reply-form mt-3" id="reply-form-<?php echo $comment['id']; ?>" style="display: none;">
                                        <form class="replyForm" method="POST" action="<?php echo BASE_URL; ?>/includes/process_blog_comment.php">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <textarea class="form-control" name="content" rows="2" placeholder="Your Reply" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Post Reply</button>
                                            <button type="button" class="btn btn-secondary btn-sm cancel-reply">Cancel</button>
                                        </form>
                                    </div>
                                    
                                    <!-- Replies (if any) -->
                                    <?php 
                                    // Fetch replies for this comment, also getting the anonymous name and user role
                                    $replies_sql = "SELECT r.*, u.full_name as author_name, u.avatar as author_avatar, u.role as author_role, r.name as commenter_name
                                                   FROM blog_comments r 
                                                   LEFT JOIN users u ON r.user_id = u.id 
                                                   WHERE r.parent_id = :comment_id AND r.status = 'approved' 
                                                   ORDER BY r.created_at ASC";
                                    $replies_stmt = $conn->prepare($replies_sql);
                                    $replies_stmt->bindParam(':comment_id', $comment['id'], PDO::PARAM_INT);
                                    $replies_stmt->execute();
                                    $replies = $replies_stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (!empty($replies)): ?>
                                        <div class="replies mt-3 ps-3 border-start border-2 border-light">
                                            <?php foreach ($replies as $reply): ?>
                                                <div class="reply mt-2">
                                                    <?php if (!empty($reply['author_avatar'])): ?>
                                                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($reply['author_avatar']); ?>" alt="<?php echo htmlspecialchars($reply['author_name']); ?>" class="rounded-circle me-2" width="30">
                                                    <?php endif; ?>
                                                    <div class="comment-author">
                                                        <?php echo htmlspecialchars($reply['author_name'] ?: $reply['commenter_name']); ?>
                                                        <?php if (isset($reply['author_role']) && in_array($reply['author_role'], ['admin', 'super_admin'])): ?>
                                                            <span class="badge bg-primary ms-2">Admin</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="comment-date"><?php echo date('F j, Y \a\t g:i a', strtotime($reply['created_at'])); ?></div>
                                                    <p><?php echo htmlspecialchars($reply['content']); ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No comments yet. Be the first to comment!</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">The article you are looking for does not exist or has been removed.</div>
                    <div class="text-center mt-4">
                        <a href="<?php echo BASE_URL; ?>/blog.php" class="btn btn-primary">Back to Blog</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Recommended Articles -->
<?php if (!empty($recommended_posts)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-5" data-aos="fade-up">You Might Also Like</h3>
        <div class="row">
            <?php foreach ($recommended_posts as $index => $recommended): ?>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="recommended-post h-100">
                        <?php if (!empty($recommended['featured_image'])): ?>
                            <a href="<?php echo BASE_URL; ?>/blog-article.php?slug=<?php echo htmlspecialchars($recommended['slug']); ?>"><img src="<?php echo BASE_URL . '/' . htmlspecialchars($recommended['featured_image']); ?>" alt="<?php echo htmlspecialchars($recommended['title']); ?>"></a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/blog-article.php?slug=<?php echo htmlspecialchars($recommended['slug']); ?>"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=200&fit=crop" alt="<?php echo htmlspecialchars($recommended['title']); ?>"></a>
                        <?php endif; ?>
                        <div class="recommended-post-content">
                            <h5><a href="<?php echo BASE_URL; ?>/blog-article.php?slug=<?php echo htmlspecialchars($recommended['slug']); ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($recommended['title']); ?></a></h5>
                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($recommended['author_name']); ?> • <?php echo date('M j, Y', strtotime($recommended['created_at'])); ?></p>
                            <p class="text-muted"><?php echo htmlspecialchars(substr($recommended['excerpt'], 0, 100)); ?>...</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize AOS
    AOS.init({
        duration: 1000,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });

    // Share functionality
    function shareArticle(platform) {
        const url = window.location.href;
        const title = "<?php echo addslashes($post['title']); ?> - Grace Fellowship Church";
        
        switch(platform) {
            case 'facebook':
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
                break;
            case 'twitter':
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`, '_blank');
                break;
            case 'whatsapp':
                window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`, '_blank');
                break;
            case 'copy':
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Link copied to clipboard!');
                    }).catch(err => {
                        console.error('Failed to copy: ', err);
                        fallbackCopy(url);
                    });
                } else {
                    fallbackCopy(url);
                }
                break;
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        } catch (err) {
            alert('Failed to copy link. Please copy it manually.');
        }
        document.body.removeChild(textarea);
    }

    // Like comment functionality
    document.querySelectorAll('.like-comment').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const likeCountElement = this.querySelector('.like-count');
            
            // Send AJAX request to like the comment
            fetch('<?php echo BASE_URL; ?>/includes/like_blog_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `comment_id=${commentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    likeCountElement.textContent = data.like_count;
                    this.style.color = 'var(--primary-color)';
                } else {
                    alert(data.message || 'Failed to like comment');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while liking the comment');
            });
        });
    });

    // Reply comment functionality
    document.querySelectorAll('.reply-comment').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            
            // Hide all other reply forms first
            document.querySelectorAll('.reply-form').forEach(form => {
                if (form.id !== `reply-form-${commentId}`) {
                    form.style.display = 'none';
                }
            });
            
            // Toggle the current reply form
            if (replyForm.style.display === 'none') {
                replyForm.style.display = 'block';
            } else {
                replyForm.style.display = 'none';
            }
        });
    });

    // Cancel reply functionality
    document.querySelectorAll('.cancel-reply').forEach(button => {
        button.addEventListener('click', function() {
            const replyForm = this.closest('.reply-form');
            replyForm.style.display = 'none';
        });
    });

    // Comment form submission
    document.getElementById('commentForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thank you for your comment! It will be reviewed and published shortly.');
                this.reset();
                // Optionally refresh comments section
                window.location.reload();
            } else {
                alert(data.message || 'Failed to submit comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting your comment');
        });
    });

    // Reply form submission
    document.querySelectorAll('.replyForm').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you for your reply! It will be reviewed and published shortly.');
                    this.reset();
                    this.closest('.reply-form').style.display = 'none';
                    // Optionally refresh comments section
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to submit reply');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your reply');
            });
        });
    });
</script>