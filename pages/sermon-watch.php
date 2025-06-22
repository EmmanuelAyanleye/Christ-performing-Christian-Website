<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get sermon ID from URL
$sermonId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch sermon data
$stmt = $pdo->prepare("SELECT * FROM sermons WHERE id = ?");
$stmt->execute([$sermonId]);
$sermon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sermon) {
    header('Location: /pages/sermons.php');
    exit();
}

// Increment view count
$pdo->prepare("UPDATE sermons SET views = views + 1 WHERE id = ?")->execute([$sermonId]);

$pageTitle = $sermon['title'] . " - Grace Fellowship Church";
$pageDescription = substr(strip_tags($sermon['description']), 0, 160) . "...";
include '../includes/header.php';
include '../includes/navigation.php';
?>

<section class="video-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="video-container" data-aos="fade-up">
                    <?php if ($sermon['youtube_url']): ?>
                        <iframe src="<?php echo embedYouTubeUrl($sermon['youtube_url']); ?>" title="<?php echo htmlspecialchars($sermon['title']); ?>" allowfullscreen></iframe>
                    <?php elseif ($sermon['video_url']): ?>
                        <video controls style="width:100%; height:100%;">
                            <source src="<?php echo htmlspecialchars($sermon['video_url']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php else: ?>
                        <div class="alert alert-info">Video content not available</div>
                    <?php endif; ?>
                </div>

                <div class="sermon-info" data-aos="fade-up" data-aos-delay="100">
                    <h1 class="font-display mb-3"><?php echo htmlspecialchars($sermon['title']); ?></h1>
                    <div class="sermon-meta">
                        <span><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($sermon['speaker']); ?></span>
                        <span><i class="fas fa-calendar me-2"></i><?php echo date('F j, Y', strtotime($sermon['date'])); ?></span>
                        <span><i class="fas fa-clock me-2"></i><?php echo htmlspecialchars($sermon['duration']); ?></span>
                        <span><i class="fas fa-eye me-2"></i><?php echo number_format($sermon['views']); ?> views</span>
                    </div>

                    <div class="sermon-actions">
                        <button class="action-btn" onclick="toggleLike(this, 'sermon', <?php echo $sermon['id']; ?>)">
                            <i class="fas fa-thumbs-up"></i>
                            <span>Like (<span id="like-count"><?php echo $sermon['likes']; ?></span>)</span>
                        </button>
                        <button class="action-btn" onclick="shareSermon()">
                            <i class="fas fa-share"></i>
                            <span>Share</span>
                        </button>
                        <?php if ($sermon['audio_url']): ?>
                        <button class="action-btn" onclick="downloadFile('<?php echo htmlspecialchars($sermon['audio_url']); ?>')">
                            <i class="fas fa-download"></i>
                            <span>Download Audio</span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="sermon-description">
                        <h5 class="mb-3">About this Sermon</h5>
                        <p><?php echo nl2br(htmlspecialchars($sermon['description'])); ?></p>

                        <?php if ($sermon['key_points']): ?>
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

                        <?php if ($sermon['bible_passage']): ?>
                        <h6 class="mt-4 mb-2">Scripture References:</h6>
                        <ul>
                            <li><?php echo htmlspecialchars($sermon['bible_passage']); ?></li>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="comment-section" data-aos="fade-up" data-aos-delay="200">
                    <h4 class="mb-4">Comments</h4>
                    <div class="comment-form">
                        <h6 class="mb-3">Join the Discussion</h6>
                        <form id="commentForm" method="POST" action="../actions/add-comment.php">
                            <input type="hidden" name="type" value="sermon">
                            <input type="hidden" name="content_id" value="<?php echo $sermon['id']; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="content" rows="3" placeholder="Share your thoughts on this sermon..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    </div>

                    <?php
                    $commentsStmt = $pdo->prepare("SELECT * FROM sermon_comments WHERE sermon_id = ? AND status = 'approved' ORDER BY created_at DESC");
                    $commentsStmt->execute([$sermon['id']]);
                    $comments = $commentsStmt->fetchAll();
                    if ($comments): ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-author"><?php echo htmlspecialchars($comment['name']); ?></div>
                            <div class="comment-date"><?php echo timeAgo($comment['created_at']); ?></div>
                            <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                            <div class="comment-actions">
                                <button class="comment-btn" onclick="likeComment(this, <?php echo $comment['id']; ?>)">
                                    <i class="fas fa-thumbs-up me-1"></i>Like (<span class="like-count"><?php echo $comment['likes']; ?></span>)
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No comments yet. Be the first to comment!</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <div data-aos="fade-left">
                        <h5 class="mb-4">Recommended Sermons</h5>
                        <?php
                        $recommendedStmt = $pdo->prepare("SELECT * FROM sermons WHERE id != ? ORDER BY views DESC LIMIT 5");
                        $recommendedStmt->execute([$sermon['id']]);
                        $recommended = $recommendedStmt->fetchAll();
                        foreach ($recommended as $rec): ?>
                        <div class="recommended-sermon">
                            <img src="<?php echo htmlspecialchars($rec['thumbnail_url'] ?: '../assets/images/default-sermon.jpg'); ?>" alt="<?php echo htmlspecialchars($rec['title']); ?>">
                            <div class="recommended-sermon-content">
                                <h6><a href="sermon-watch.php?id=<?php echo $rec['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($rec['title']); ?></a></h6>
                                <p class="text-muted"><?php echo htmlspecialchars($rec['speaker']); ?></p>
                                <p class="text-muted"><?php echo htmlspecialchars($rec['duration']); ?> • <?php echo number_format($rec['views']); ?> views</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
function toggleLike(button, type, id) {
    fetch('../actions/toggle-like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=${type}&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeCount = document.getElementById('like-count');
            likeCount.textContent = data.newCount;
            button.classList.toggle('liked');
        }
    });
}

function shareSermon() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($sermon['title']); ?> - Grace Fellowship Church',
            text: 'Watch this inspiring sermon from Grace Fellowship Church',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copied to clipboard!');
        });
    }
}

function downloadFile(url) {
    window.location.href = url;
}

function likeComment(button, commentId) {
    fetch('../actions/like-comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `comment_id=${commentId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeCount = button.querySelector('.like-count');
            likeCount.textContent = data.newCount;
            button.style.color = 'var(--primary-color)';
        }
    });
}
</script>
