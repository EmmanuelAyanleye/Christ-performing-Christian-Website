<?php
require_once __DIR__ . '/../includes/config.php';
$current_page = 'sermons'; 
$search_term = $_GET['search'] ?? '';
$filter_series = $_GET['series'] ?? '';
$sort_by = $_GET['sort'] ?? 'newest'; // Changed default to 'newest' for clarity

$where_clauses = [];
$params_for_execute = []; // Use a single array for all positional parameters

if (!empty($search_term)) {
    $where_clauses[] = "(title LIKE ? OR description LIKE ? OR speaker LIKE ? OR series LIKE ?)";
    $params_for_execute[] = '%' . $search_term . '%';
    $params_for_execute[] = '%' . $search_term . '%';
    $params_for_execute[] = '%' . $search_term . '%';
    $params_for_execute[] = '%' . $search_term . '%';
}

if (!empty($filter_series)) {
    $where_clauses[] = "series = ?";
    $params_for_execute[] = $filter_series;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

$order_by_sql = 'ORDER BY created_at DESC'; // Default sort by newest
if ($sort_by === 'popular') {
    $order_by_sql = 'ORDER BY views DESC';
} elseif ($sort_by === 'title_asc') {
    $order_by_sql = 'ORDER BY title ASC';
} elseif ($sort_by === 'oldest') {
    $order_by_sql = 'ORDER BY created_at ASC';
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12; // Changed from 6 to 12
$offset = ($page - 1) * $per_page;

$total_sermons_sql = "SELECT COUNT(*) FROM sermons" . $where_sql;
$total_sermons_stmt = $conn->prepare($total_sermons_sql); // Prepare statement for total count
$total_sermons_stmt->execute($params_for_execute); // Execute with collected parameters
$total_sermons = $total_sermons_stmt->fetchColumn();
$total_pages = ceil($total_sermons / $per_page);

$sermons_sql = "SELECT * FROM sermons " . $where_sql . " " . $order_by_sql . " LIMIT ?, ?"; // Use positional placeholders for LIMIT
$sermons_stmt = $conn->prepare($sermons_sql);

// Append LIMIT parameters to the same array
$params_for_execute[] = $offset;
$params_for_execute[] = $per_page;
$sermons_stmt->execute($params_for_execute); // Execute with all positional parameters
$sermons = $sermons_stmt->fetchAll(PDO::FETCH_ASSOC); 

$all_series_sql = "SELECT series, SUM(views) as total_views FROM sermons WHERE series IS NOT NULL AND series != '' GROUP BY series ORDER BY total_views DESC LIMIT 3";
$all_series = $conn->query($all_series_sql)->fetchAll(PDO::FETCH_ASSOC); // Fetch top 3 series by views

$page_title = "Sermons";
$page_description = "Watch and listen to inspiring sermons from Christ performing Christian Centre. Search our sermon library and grow in your faith.";
include '../includes/header.php';
?>
<style>
        :root {
            --primary-color: #0b2067;
            --secondary-color: #f2db37;
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
                        url('<?php echo BASE_URL; ?>/images/sermon.jpg') center/cover;
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

        .search-bar {
            max-width: 600px;
            margin: 0 auto;
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

        /* Sermon Cards */
        .sermon-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
        }

        .sermon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .sermon-video {
            position: relative;
            width: 100%;
            /* height: 250px; */
        }

        .sermon-video iframe {
            width: 100%;
            height: 100%;
            border-radius: 10px !important;
        }

        .sermon-card-body {
            padding: 1.5rem;
        }

        .sermon-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .sermon-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            color: var(--primary-color);
        }

        .action-btn.liked {
            color: var(--accent-color);
        }

        .action-btn.disliked {
            color: #dc2626;
        }

        /* Filter Buttons */
        .filter-buttons {
            margin-bottom: 2rem;
        }

        .filter-btn {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 25px;
            margin: 0 5px 10px 0;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--primary-color);
            color: white;
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
            color:rgb(255, 255, 255);
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
<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div data-aos="fade-up">
            <h1 class="font-display">Sermons</h1>
            <p>Be inspired by God's Word through our sermon library</p>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section">
    <div class="container">
        <form action="sermons.php" method="GET" class="search-bar" data-aos="fade-up">
            <div class="input-group">
                <input type="text" class="form-control" name="search" id="sermonSearch" placeholder="Search sermons by title, topic, or speaker..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Sermons Section -->
<section class="section-padding">
    <div class="container">
        <div class="filter-buttons text-center" data-aos="fade-up">
            <a href="sermons.php" class="btn filter-btn <?php echo (empty($_GET['sort']) || $_GET['sort'] === 'newest') && empty($_GET['series']) && empty($_GET['search']) ? 'active' : ''; ?>">All Sermons</a>
            <!-- Removed "Recent" filter button as it's redundant with default sort by date DESC -->
            <a href="sermons.php?sort=popular" class="btn filter-btn <?php echo $sort_by === 'popular' ? 'active' : ''; ?>">Popular</a>
            <?php foreach ($all_series as $series_item): ?>
                <a href="sermons.php?series=<?php echo urlencode($series_item['series']); ?>" class="btn filter-btn <?php echo $filter_series === $series_item['series'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($series_item['series']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div id="sermonsContainer">
            <?php if (empty($sermons)): ?>
                <div class="text-center" data-aos="fade-up">
                    <h3>No Sermons Found</h3>
                    <p>Your search for "<?php echo htmlspecialchars($search_term); ?>" did not return any results.</p>
                    <a href="sermons.php" class="btn btn-primary">View All Sermons</a>
                </div>
            <?php else: ?>
                <ul class="list-group" data-aos="fade-up">
                <?php foreach ($sermons as $sermon): ?>
                    <?php
                        $youtube_id = get_youtube_id($sermon['youtube_url']);
                        $thumbnail = $sermon['thumbnail_url'] 
    ?: ($youtube_id ? "https://img.youtube.com/vi/$youtube_id/mqdefault.jpg" : 'assets/img/default-thumbnail.jpg');
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start flex-wrap mb-2 p-3">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($thumbnail); ?>"
                                 alt="Thumbnail" class="me-3 rounded" width="100" height="60" style="object-fit: cover;">
                            <div>
                                <a href="sermon-watch.php?id=<?php echo $sermon['id']; ?>"
                                   class="fw-bold h6 mb-1 d-block text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($sermon['title']); ?>
                                </a>
                                <div class="text-muted small">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($sermon['speaker']); ?> &middot;
                                    <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($sermon['date'])); ?> &middot;
                                    <i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($sermon['duration']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <a href="sermon-watch.php?id=<?php echo $sermon['id']; ?>" class="btn btn-sm btn-outline-primary">
                                Watch
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Sermon pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_term); ?>&sort=<?php echo urlencode($sort_by); ?>&series=<?php echo urlencode($filter_series); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>&sort=<?php echo urlencode($sort_by); ?>&series=<?php echo urlencode($filter_series); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_term); ?>&sort=<?php echo urlencode($sort_by); ?>&series=<?php echo urlencode($filter_series); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
