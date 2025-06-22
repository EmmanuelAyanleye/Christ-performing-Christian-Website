<?php
require_once __DIR__ . '/../includes/config.php'; // Load configuration and database connection

// --- FILTERS, SEARCH, AND PAGINATION ---
$search_term = $_GET['search'] ?? '';
$filter_category = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$per_page = 12; // Number of images per page
$offset = ($page - 1) * $per_page;

// --- BUILD QUERY ---
$where_clauses = [];
$params = [];

if (!empty($search_term)) {
    // Search in title, description, and tags
    $where_clauses[] = "(title LIKE :search OR description LIKE :search OR tags LIKE :search)";
    $params[':search'] = '%' . $search_term . '%';
}

if (!empty($filter_category)) {
    $where_clauses[] = "category = :category";
    $params[':category'] = $filter_category;
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

// --- TOTAL COUNT ---
$total_sql = "SELECT COUNT(*) FROM gallery" . $where_sql;
$total_stmt = $conn->prepare($total_sql);
$total_stmt->execute($params);
$total_items = $total_stmt->fetchColumn();
$total_pages = ceil($total_items / $per_page);

// --- FETCH GALLERY ITEMS FOR CURRENT PAGE ---
$gallery_sql = "SELECT * FROM gallery " . $where_sql . " ORDER BY event_date DESC, created_at DESC LIMIT :offset, :per_page";
$gallery_stmt = $conn->prepare($gallery_sql);

// Bind WHERE params
foreach ($params as $key => &$val) {
    $gallery_stmt->bindParam($key, $val);
}
// Bind LIMIT params
$gallery_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$gallery_stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);

$gallery_stmt->execute();
$gallery_items = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- FETCH ALL CATEGORIES FOR FILTERING ---
$all_categories_sql = "SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL AND category != '' ORDER BY category ASC";
$all_categories = $conn->query($all_categories_sql)->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Photo Gallery";
$page_description = "Browse through our church gallery. See moments of worship, fellowship, and community life at Grace Fellowship Church.";
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
                        url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=1920&h=1080&fit=crop') center/cover;
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

        /* Gallery Styles */
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 2rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: scale(1.03);
        }

        .gallery-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 1.5rem;
            color: white;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-overlay h5 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .gallery-overlay p {
            font-size: 0.9rem;
            margin: 0;
            opacity: 0.9;
        }

        .gallery-meta {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 0.5rem;
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

        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 0;
        }

        .modal-body img {
            width: 100%;
            height: auto;
            max-height: 70vh;
            object-fit: contain;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
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

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div data-aos="fade-up">
            <h1 class="font-display">Photo Gallery</h1>
            <p>Moments of worship, fellowship, and community life</p>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section">
    <div class="container">
        <form action="gallery.php" method="GET" class="search-bar" data-aos="fade-up">
            <div class="input-group">
                <input type="text" class="form-control" name="search" id="gallerySearch" placeholder="Search photos by title, event, or tag..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Gallery Section -->
<section class="section-padding">
    <div class="container">
        <!-- Filter Buttons -->
        <div class="filter-buttons text-center" data-aos="fade-up">
            <a href="gallery.php" class="btn filter-btn <?php echo empty($filter_category) ? 'active' : ''; ?>">All Photos</a>
            <?php foreach ($all_categories as $cat): ?>
                <a href="gallery.php?category=<?php echo urlencode($cat['category']); ?>" class="btn filter-btn <?php echo ($filter_category === $cat['category']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars(ucfirst($cat['category'])); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Gallery Grid -->
        <div class="row" id="galleryContainer">
            <?php if (empty($gallery_items)): ?>
                <div class="col-12 text-center" data-aos="fade-up">
                    <h3>No Photos Found</h3>
                    <p>Your search or filter did not return any results. Please try again.</p>
                    <a href="gallery.php" class="btn btn-primary mt-3">View Full Gallery</a>
                </div>
            <?php else: ?>
                <?php foreach ($gallery_items as $item): ?>
                    <div class="col-lg-4 col-md-6 mb-4 gallery-photo" data-aos="zoom-in">
                        <div class="gallery-item" onclick="openModal(
                            '<?php echo htmlspecialchars($item['image_url']); ?>', 
                            '<?php echo htmlspecialchars(addslashes($item['title'])); ?>', 
                            '<?php echo htmlspecialchars(addslashes($item['description'])); ?>', 
                            '<?php echo date('F j, Y', strtotime($item['event_date'])); ?>'
                        )">
                            <img src="<?php echo htmlspecialchars($item['thumbnail_url'] ?: $item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="gallery-overlay">
                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</p>
                                <?php if ($item['event_date']): ?>
                                <div class="gallery-meta">
                                    <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($item['event_date'])); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Gallery pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php
                $query_params = http_build_query(['search' => $search_term, 'category' => $filter_category]);
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
</section>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid">
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <p id="modalDescription" class="mb-0"></p>
                    <small id="modalDate" class="text-muted"></small>
                </div>
                <a href="#" id="downloadBtn" class="btn btn-primary" download>
                    <i class="fas fa-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
    // Modal functionality
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const modalDate = document.getElementById('modalDate');
    const downloadBtn = document.getElementById('downloadBtn');
    const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));

    function openModal(imageSrc, title, description, date) {
        modalImage.src = imageSrc;
        modalTitle.textContent = title;
        modalDescription.textContent = description;
        modalDate.textContent = date;

        const baseFilenameForDownload = title.replace(/\s+/g, '_').toLowerCase();
        
        downloadBtn.href = imageSrc;
        downloadBtn.download = baseFilenameForDownload + ".jpg";

        downloadBtn.onclick = function(event) {
            event.preventDefault();
            triggerDownload(imageSrc, baseFilenameForDownload);
        };
        imageModal.show();
    }

    async function triggerDownload(url, baseFilename) {
        try {
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Downloading...';
            downloadBtn.disabled = true;

            // Use cors-anywhere proxy for cross-origin images if needed, or ensure images are served with CORS headers
            const response = await fetch(url, { mode: 'cors' });

            if (!response.ok) throw new Error(`Network response was not ok: ${response.statusText}`);

            const blob = await response.blob();
            const objectUrl = URL.createObjectURL(blob);
            
            let extension = '.jpg';
            if (blob.type && blob.type.startsWith('image/')) {
                extension = '.' + blob.type.split('/')[1].split('+')[0];
            }
            const filenameWithExt = baseFilename + extension;

            const tempLink = document.createElement('a');
            tempLink.href = objectUrl;
            tempLink.download = filenameWithExt;
            document.body.appendChild(tempLink);
            tempLink.click();
            document.body.removeChild(tempLink);
            URL.revokeObjectURL(objectUrl);

        } catch (error) {
            console.error('Download failed:', error);
            alert('Failed to download image. You can try right-clicking the image and selecting "Save image as...".');
        } finally {
            downloadBtn.innerHTML = '<i class="fas fa-download me-2"></i>Download';
            downloadBtn.disabled = false;
        }
    }
</script>