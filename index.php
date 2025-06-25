<?php
require_once __DIR__ . '/includes/config.php'; // Load configuration and database connection

$current_page = 'home'; 
$contact_message = '';
$newsletter_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Contact Form Submission
    if (isset($_POST['form_action']) && $_POST['form_action'] === 'contact') {
        $name = sanitize_input($_POST['contact_name'] ?? '');
        $email = sanitize_input($_POST['contact_email'] ?? '');
        $subject = sanitize_input($_POST['contact_subject'] ?? '');
        $message_content = sanitize_input($_POST['contact_message'] ?? '');
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        if (!empty($name) && !empty($email) && !empty($subject) && !empty($message_content) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $sql = "INSERT INTO messages (name, email, subject, message, ip_address, user_agent) VALUES (:name, :email, :subject, :message, :ip_address, :user_agent)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':subject' => $subject,
                    ':message' => $message_content,
                    ':ip_address' => $ip_address,
                    ':user_agent' => $user_agent
                ]);
                $contact_message = '<div class="alert alert-success" role="alert">Thank you for your message! We will get back to you soon.</div>';
            } catch (PDOException $e) {
                error_log('Contact Form Error: ' . $e->getMessage());
                $contact_message = '<div class="alert alert-danger" role="alert">Sorry, there was an error sending your message. Please try again later.</div>';
            }
        } else {
            $contact_message = '<div class="alert alert-danger" role="alert">Please fill out all fields correctly.</div>';
        }
    }

    // Newsletter Subscription
    if (isset($_POST['form_action']) && $_POST['form_action'] === 'newsletter') {
        $email = sanitize_input($_POST['newsletter_email'] ?? '');

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $sql = "INSERT INTO subscribers (email) VALUES (:email)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':email' => $email]);
                $newsletter_message = '<div class="alert alert-success" role="alert">Thank you for subscribing to our newsletter!</div>';
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
                    $newsletter_message = '<div class="alert alert-warning" role="alert">This email is already subscribed. Thank you!</div>';
                } else {
                    error_log('Newsletter Subscription Error: ' . $e->getMessage());
                    $newsletter_message = '<div class="alert alert-danger" role="alert">Sorry, there was an error. Please try again.</div>';
                }
            }
        } else {
            $newsletter_message = '<div class="alert alert-danger" role="alert">Please enter a valid email address.</div>';
        }
    }
}

// --- DATA FETCHING FOR THE PAGE ---

// Fetch latest 3 sermons
$sermons_sql = "SELECT * FROM sermons ORDER BY date DESC, created_at DESC LIMIT 3";
$sermons_stmt = $conn->prepare($sermons_sql);
$sermons_stmt->execute();
$sermons = $sermons_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming 3 events
$events_sql = "SELECT * FROM events WHERE start_date > NOW() ORDER BY start_date ASC LIMIT 3";
$events_stmt = $conn->prepare($events_sql);
$events_stmt->execute();
$events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest 3 blog posts
$posts_sql = "SELECT p.*, u.full_name as author_name FROM blog_posts p JOIN users u ON p.author_id = u.id WHERE p.status = 'published' ORDER BY p.created_at DESC LIMIT 3";
$posts_stmt = $conn->prepare($posts_sql);
$posts_stmt->execute();
$posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch ALL approved testimonials (remove the LIMIT 3)
$testimonials_sql = "SELECT * FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC";
$testimonials_stmt = $conn->prepare($testimonials_sql);
$testimonials_stmt->execute();
$testimonials = $testimonials_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Welcome Home";
$page_description = "Join Christ performing Christian Centre - A vibrant Christian community in Nigeria. Experience worship, grow in faith, and connect with others.";
include 'includes/header.php';

// Fetch 6 gallery images for the snippet
$gallery_sql = "SELECT * FROM gallery ORDER BY RAND() LIMIT 6";
$gallery_stmt = $conn->prepare($gallery_sql);
$gallery_stmt->execute();
$gallery_items = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);

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

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(30, 58, 138, 0.7), rgba(30, 58, 138, 0.7)), 
                        url('https://images.unsplash.com/photo-1438032005730-c779502df39b?w=1920&h=1080&fit=crop') center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-primary-custom {
            background: var(--secondary-color);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary-custom:hover {
            background: #b2201e;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.3);
        }

        /* Section Styles */
        .section-padding {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.1rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card:hover .card-img-top {
            transform: scale(1.05);
        }

        /* Contact Form Styles */
        .contact-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 12px 15px;
            font-weight: 400;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
        }

        /* Testimonials */
        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin: 1rem;
        }

        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            object-fit: cover;
        }

        /* Testimonials Carousel */
        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 3px solid var(--secondary-color);
        }

        .carousel-controls {
            position: relative;
            z-index: 1;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: auto;
            opacity: 1;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: var(--primary-color);
            border-radius: 50%;
            padding: 15px;
            background-size: 60%;
        }

        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin: 0 10px;
            height: 100%;
        }

        /* Gallery */
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
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
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
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
            color:#fff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(251, 191, 36, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
    </style>
<!-- Hero Section -->
<section id="home" class="hero">
    <div class="hero-content" data-aos="fade-up">
        <h1 class="font-display">Welcome to Christ performing Christian Centre</h1>
        <p>Building Faith, Strengthening Community, Serving with Love</p>
        <a href="#about" class="btn btn-primary-custom">Discover Our Story</a>
    </div>
</section>

<!-- About Snippet -->
<section id="about" class="section-padding bg-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2 class="font-display">About Our Church</h2>
            <p>A community where faith grows, hearts are transformed, and lives are touched by God's love</p>
        </div>
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&h=400&fit=crop" 
                     alt="Church Community" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h3 class="font-display mb-3">Our Mission</h3>
                <p class="mb-4">Christ performing Christian Centre is a vibrant Christian community in the heart of Nigeria, dedicated to spreading God's love, building strong relationships, and serving our community with compassion and purpose.</p>
                <p class="mb-4">We believe in creating an environment where everyone can experience God's grace, grow in their faith, and find their calling to serve others.</p>
                <a href="<?php echo BASE_URL; ?>/about.php" class="btn btn-primary-custom">Learn More About Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Sermon Preview -->
<section id="sermons" class="section-padding">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2 class="font-display">Latest Sermons</h2>
            <p>Watch our recent messages and be inspired by God's word</p>
        </div>
        <div class="row">
            <?php foreach($sermons as $sermon): ?>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card">
                    <div class="ratio ratio-16x9">
                        <iframe src="<?php echo embedYouTubeUrl($sermon['youtube_url']); ?>" 
                                title="<?php echo htmlspecialchars($sermon['title']); ?>" allowfullscreen></iframe>
                    </div>
                    <div class="card-body">
                        <a href="<?php echo BASE_URL; ?>/sermon-watch.php?id=<?php echo $sermon['id']; ?>" class="text-decoration-none text-dark">
                            <h5 class="card-title"><?php echo htmlspecialchars($sermon['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(get_excerpt($sermon['description'], 80)); ?></p>
                            <small class="text-muted"><?php echo format_date($sermon['date']); ?></small>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center" data-aos="fade-up">
            <a href="<?php echo BASE_URL; ?>/sermons.php" class="btn btn-primary-custom">View All Sermons</a>
        </div>
    </div>
</section>

<!-- Upcoming Events -->
<section id="events" class="section-padding bg-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2 class="font-display">Upcoming Events</h2>
            <p>Join us for these special gatherings and community activities</p>
        </div>
        <div class="row">
            <?php foreach($events as $event): ?>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card">
                    <img src="<?php echo htmlspecialchars($event['featured_image']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="card-text">
                            <i class="fas fa-calendar me-2 text-primary"></i><?php echo format_date($event['start_date']); ?><br>
                            <i class="fas fa-clock me-2 text-primary"></i><?php echo date('g:i A', strtotime($event['start_date'])); ?><br>
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i><?php echo htmlspecialchars($event['location']); ?>
                        </p>
                        <p class="card-text"><?php echo htmlspecialchars(get_excerpt($event['description'], 100)); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Gallery Snippet -->
<section id="gallery" class="section-padding">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2 class="font-display">Church Gallery</h2>
            <p>Moments of worship, fellowship, and community life</p>
        </div>
        <div class="row">
            <?php foreach ($gallery_items as $index => $item): ?>
            <div class="col-md-4 col-sm-6 mb-3" data-aos="zoom-in" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                <a href="<?php echo BASE_URL; ?>/gallery.php" class="gallery-item">
                    <img src="<?php echo htmlspecialchars($item['thumbnail_url'] ?: $item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center" data-aos="fade-up">
            <a href="<?php echo BASE_URL; ?>/gallery.php" class="btn btn-primary-custom">View Full Gallery</a>
        </div>
    </div>
</section>

<!-- Blog Snippet -->
<section id="blog" class="section-padding bg-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2 class="font-display">Latest From Our Blog</h2>
            <p>Insights, devotions, and updates from our church community</p>
        </div>
        <div class="row">
            <?php foreach($posts as $post): ?>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <a href="<?php echo BASE_URL; ?>/blog-article.php?slug=<?php echo $post['slug']; ?>"><img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><a href="<?php echo BASE_URL; ?>/blog-article.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                        <p class="card-text"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-muted"><?php echo format_date($post['created_at']); ?></small>
                            <a href="<?php echo BASE_URL; ?>/blog-article.php?slug=<?php echo $post['slug']; ?>" class="btn btn-outline-primary btn-sm">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center" data-aos="fade-up">
            <a href="<?php echo BASE_URL; ?>/blog.php" class="btn btn-primary-custom">Read All Posts</a>
        </div>
    </div>
</section>

<!-- Testimonials Carousel -->
<section class="section-padding">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2 class="font-display">Testimonials</h2>
            <p>Hear from our church family about their journey of faith</p>
        </div>
        
        <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach(array_chunk($testimonials, 3) as $index => $testimonialGroup): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="row">
                        <?php foreach($testimonialGroup as $testimonial): ?>
                        <div class="col-md-4 mb-4">
                            <div class="testimonial-card h-100">
                                <?php
                                // Set default avatar path
                                $defaultAvatar = BASE_URL . '/images/church_logo.png';
                                
                                // Check if avatar exists in database and is not empty
                                if (!empty($testimonial['avatar_url'])) {
                                    // Handle the different path formats
                                    if (strpos($testimonial['avatar_url'], '../') === 0) {
                                        // For testimonial paths like "../uploads/avatars/..."
                                        $avatarPath = str_replace('../', '', $testimonial['avatar_url']);
                                        $avatarSrc = BASE_URL . '/' . ltrim($avatarPath, '/');
                                    } else {
                                        // For other paths (absolute or relative)
                                        $avatarSrc = $testimonial['avatar_url'];
                                        
                                        // If it's not a full URL, prepend BASE_URL
                                        if (!filter_var($avatarSrc, FILTER_VALIDATE_URL)) {
                                            $avatarSrc = BASE_URL . '/' . ltrim($avatarSrc, '/');
                                        }
                                    }
                                } else {
                                    $avatarSrc = $defaultAvatar;
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($avatarSrc); ?>" 
                                     alt="<?php echo htmlspecialchars($testimonial['name']); ?>" 
                                     class="testimonial-avatar"
                                     onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($defaultAvatar); ?>'">
                                <h5><?php echo htmlspecialchars($testimonial['name']); ?></h5>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($testimonial['role']); ?></p>
                                <p class="fst-italic">"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                                <div class="text-warning">
                                    <?php for($i = 0; $i < (int)$testimonial['rating']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($testimonials) > 3): ?>
            <div class="carousel-controls text-center mt-4">
                <button class="btn btn-outline-primary me-2" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
                <button class="btn btn-outline-primary" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5" data-aos="fade-up">
                <h2 class="font-display">Get In Touch</h2>
                <p class="lead">We'd love to hear from you. Reach out to us anytime.</p>
            </div>
        </div>
        <div class="row">
            <!-- Contact Form -->
            <div class="col-md-6 mb-4" data-aos="fade-right">
                <div class="contact-form bg-white p-4 rounded-3 shadow">
                    <h4 class="mb-4">Send us a Message</h4>
                    <?php if (!empty($contact_message)) echo $contact_message; ?>
                    <form action="<?php echo BASE_URL; ?>/index.php#contact" method="POST">
                        <input type="hidden" name="form_action" value="contact">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" name="contact_name" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="email" name="contact_email" class="form-control" placeholder="Your Email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="contact_subject" class="form-control" placeholder="Subject" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="contact_message" class="form-control" rows="5" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-md-6 mb-4" data-aos="fade-left">
                <div class="contact-info p-4 rounded-3 h-100">
                    <h4 class="mb-4">Contact Information</h4>
                    <div class="contact-item mb-4 d-flex align-items-start">
                        <i class="fas fa-map-marker-alt text-primary fa-fw me-3 mt-1"></i>
                        <div><strong>Address</strong><br>23 Ayofayemi Street, off Princess Abiola Street, Nobex bus stop, Idimu Ikotun Road, Lagos, Nigeria</div>
                    </div>
                    <div class="contact-item mb-4 d-flex align-items-start">
                        <i class="fas fa-phone text-primary fa-fw me-3 mt-1"></i>
                        <div><strong>Phone</strong><br>+234 916 661 6862</div>
                    </div>
                    <div class="contact-item mb-4 d-flex align-items-start">
                        <i class="fas fa-envelope text-primary fa-fw me-3 mt-1"></i>
                        <div><strong>Email</strong><br>christperformingcentre@gmail.com</div>
                    </div>
                    <div class="contact-item mb-4 d-flex align-items-start">
                        <i class="fab fa-whatsapp text-primary fa-fw me-3 mt-1"></i>
                        <div><strong>WhatsApp</strong><br>+234 916 664 8407</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section id="newsletter" class="section-padding bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h3 class="font-display mb-3">Stay Connected</h3>
                <p class="mb-lg-0">Subscribe to our newsletter to receive updates about upcoming events, sermons, and church activities. Join our community and never miss an important announcement.</p>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <?php if (!empty($newsletter_message)) echo $newsletter_message; ?>
                <form action="<?php echo BASE_URL; ?>/index.php#newsletter" method="POST">
                    <input type="hidden" name="form_action" value="newsletter">
                    <div class="input-group">
                        <input type="email" name="newsletter_email" class="form-control" placeholder="Enter your email address" required>
                        <button class="btn btn-warning" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>