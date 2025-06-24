<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
$current_page = 'sermons'; 

// Get sermon ID from URL
$sermonId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch sermon data
$stmt = $pdo->prepare("SELECT * FROM sermons WHERE id = ?");
$stmt->execute([$sermonId]);
$sermon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sermon) {
    header('Location: sermons.php');
    exit();
}

// Increment view count
$pdo->prepare("UPDATE sermons SET views = views + 1 WHERE id = ?")->execute([$sermonId]);

// Determine user's current interaction status for this sermon from session
// This tracks 'liked' or 'disliked' for the current session, even for non-logged-in users.
if (session_status() == PHP_SESSION_NONE) { session_start(); } // Ensure session is started
$userInteractionStatus = $_SESSION['sermon_interactions'][$sermonId] ?? null;


$pageTitle = $sermon['title'] . " - Grace Fellowship Church";
$pageDescription = substr(strip_tags($sermon['description']), 0, 160) . "...";
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

        /* Video Section */
        .video-section {
            padding: 100px 0 60px;
            background: var(--bg-light);
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .sermon-info {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .sermon-meta {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .sermon-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            flex-wrap: wrap;
        }

        .action-btn {
            background: none;
            border: 2px solid var(--text-light);
            color: var(--text-light);
            padding: 8px 16px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .action-btn.liked {
            border-color: var(--accent-color);
            color: var(--accent-color);
            background: rgba(5, 150, 105, 0.1);
        }

        .action-btn.disliked {
            border-color: #dc2626;
            color: #dc2626;
            background: rgba(220, 38, 38, 0.1);
        }

        /* Description */
        .sermon-description {
            font-size: 1.1rem;
            line-height: 1.8;
            margin: 2rem 0;
        }

        /* Comment Section */
        .comment-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .comment-form {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .comment {
            padding: 1.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .comment:last-child {
            border-bottom: none;
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
            font-size: 0.9rem;
        }

        .comment-btn:hover {
            color: var(--primary-color);
        }

        /* Recommended Sermons */
        .recommended-sermon {
            display: flex;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .recommended-sermon:hover {
            transform: translateY(-3px);
        }

        .recommended-sermon img {
            width: 150px;
            height: 100px;
            object-fit: cover;
        }

        .recommended-sermon-content {
            padding: 1rem;
            flex: 1;
        }

        .recommended-sermon h6 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .recommended-sermon .text-muted {
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
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

        @media (max-width: 768px) {
            .recommended-sermon {
                flex-direction: column;
            }
            
            .recommended-sermon img {
                width: 100%;
                height: 150px;
            }
        }
    </style>

<section class="video-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Video Player -->
                <div class="video-container" data-aos="fade-up">
                    <?php if (!empty($sermon['youtube_url'])): ?>
                        <iframe src="<?php echo embedYouTubeUrl($sermon['youtube_url']); ?>" title="<?php echo htmlspecialchars($sermon['title']); ?>" allowfullscreen></iframe>
                    <?php elseif (!empty($sermon['video_url'])): ?>
                        <video controls>
                            <source src="<?php echo htmlspecialchars($sermon['video_url']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php else: ?>
                        <div class="alert alert-info">Video content not available</div>
                    <?php endif; ?>
                </div>

                <!-- Sermon Info -->
                <div class="sermon-info" data-aos="fade-up" data-aos-delay="100">
                    <h1 class="font-display mb-3"><?php echo htmlspecialchars($sermon['title']); ?></h1>
                    <div class="sermon-meta">
                        <span><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($sermon['speaker']); ?></span>
                        <span><i class="fas fa-calendar me-2"></i><?php echo date('F j, Y', strtotime($sermon['date'])); ?></span>
                        <span><i class="fas fa-clock me-2"></i><?php echo htmlspecialchars($sermon['duration']); ?></span>
                        <span><i class="fas fa-eye me-2"></i><?php echo number_format($sermon['views']); ?> views</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="sermon-actions">
                        <button class="action-btn <?php echo ($userInteractionStatus === 'liked') ? 'liked' : ''; ?>" onclick="toggleSermonInteraction(this, <?php echo $sermon['id']; ?>, 'like')">
                            <i class="fas fa-thumbs-up"></i>
                            <span>Like (<span id="like-count"><?php echo $sermon['likes']; ?></span>)</span>
                        </button>
                        <button class="action-btn <?php echo ($userInteractionStatus === 'disliked') ? 'disliked' : ''; ?>" onclick="toggleSermonInteraction(this, <?php echo $sermon['id']; ?>, 'dislike')">
                            <i class="fas fa-thumbs-down"></i>
                            <span>Dislike (<span id="dislike-count"><?php echo $sermon['dislikes'] ?? 0; ?></span>)</span>
                        </button>
                        <button class="action-btn" onclick="shareSermon()">
                            <i class="fas fa-share"></i>
                            <span>Share</span>
                        </button>
                        <button class="action-btn" onclick="copyLink()">
                            <i class="fas fa-link"></i>
                            <span>Copy Link</span>
                        </button>
                    </div>

                    <!-- Description -->
                    <div class="sermon-description">
                        <h5 class="mb-3">About this Sermon</h5>
                        <p><?php echo nl2br(htmlspecialchars($sermon['description'])); ?></p>

                        <?php if (!empty($sermon['key_points'])): ?>
                        <h6 class="mt-4 mb-2">Key Points:</h6>
                        <ul>
                            <?php 
                            $points = explode("\n", $sermon['key_points']);
                            foreach ($points as $point) {
                                if (trim($point)) {
                                    echo '<li>'.htmlspecialchars(trim($point, "- \t\n\r\0\x0B")).'</li>';
                                }
                            }
                            ?>
                        </ul>
                        <?php endif; ?>

                        <?php if (!empty($sermon['bible_passage'])): ?>
                        <h6 class="mt-4 mb-2">Scripture References:</h6>
                        <ul>
                            <li><?php echo htmlspecialchars($sermon['bible_passage']); ?></li>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <!-- Recommended Sermons -->
                    <div data-aos="fade-left">
                        <h5 class="mb-4">Recommended Sermons</h5>
                        <?php
                        // Fetch 5 recommended sermons (excluding current one)
                        $recommendedStmt = $pdo->prepare("SELECT * FROM sermons WHERE id != ? ORDER BY views DESC LIMIT 5");
                        $recommendedStmt->execute([$sermon['id']]);
                        $recommended = $recommendedStmt->fetchAll();
                        
                        if (!empty($recommended)): 
                            foreach ($recommended as $rec): ?>
                            <div class="recommended-sermon">
                                <img src="<?php echo htmlspecialchars($rec['thumbnail_url'] ?: '../assets/images/default-sermon.jpg'); ?>" alt="<?php echo htmlspecialchars($rec['title']); ?>">
                                <div class="recommended-sermon-content">
                                    <h6><a href="sermon-watch.php?id=<?php echo $rec['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($rec['title']); ?></a></h6>
                                    <p class="text-muted"><?php echo htmlspecialchars($rec['speaker']); ?></p>
                                    <p class="text-muted"><?php echo htmlspecialchars($rec['duration']); ?> • <?php echo number_format($rec['views']); ?> views</p>
                                </div>
                            </div>
                            <?php endforeach; 
                        else: ?>
                            <p class="text-muted">No recommended sermons available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

    // Like/Dislike functionality
    function toggleSermonInteraction(button, sermonId, action) {
        const likeButton = document.querySelector('.action-btn:nth-child(1)');
        const dislikeButton = document.querySelector('.action-btn:nth-child(2)');
        const likeCountElement = document.getElementById('like-count');
        const dislikeCountElement = document.getElementById('dislike-count');

        fetch('<?php echo BASE_URL; ?>/actions/toggle-sermon-like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${sermonId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                likeCountElement.textContent = data.newLikeCount;
                dislikeCountElement.textContent = data.newDislikeCount;

                // Update button styles based on the new user interaction status
                likeButton.classList.remove('liked');
                dislikeButton.classList.remove('disliked');

                if (data.userInteraction === 'liked') {
                    likeButton.classList.add('liked');
                } else if (data.userInteraction === 'disliked') {
                    dislikeButton.classList.add('disliked');
                }
            } else {
                alert(data.message || 'Failed to update interaction.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        });
    }


    // Share functionality
    function shareSermon() {
        if (navigator.share) {
            navigator.share({
                title: '<?php echo addslashes($sermon['title']); ?> - Grace Fellowship Church',
                text: 'Watch this inspiring sermon from Grace Fellowship Church',
                url: window.location.href
            });
        } else {
            copyLink();
        }
    }

    // Copy link functionality
    function copyLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copied to clipboard!');
        });
    }
</script>