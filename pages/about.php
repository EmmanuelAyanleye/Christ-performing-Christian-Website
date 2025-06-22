<?php
require_once __DIR__ . '/../includes/config.php'; // Ensure this path is correct
$page_title = "About Us - Grace Fellowship Church";
$page_description = "Learn about Grace Fellowship Church - Our history, mission, vision, and leadership team.";
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
            height: 60vh;
            background: linear-gradient(rgba(30, 58, 138, 0.8), rgba(30, 58, 138, 0.8)), 
                        url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1920&h=1080&fit=crop') center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
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
        .info-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .info-card h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Leadership Cards */
        .leader-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .leader-card:hover {
            transform: translateY(-5px);
        }

        .leader-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .leader-card-body {
            padding: 1.5rem;
            text-align: center;
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
            <h1 class="font-display">About Grace Fellowship Church</h1>
            <p>Building Faith, Strengthening Community, Serving with Love</p>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="font-display mb-4">Our Story</h2>
                <p class="mb-4">Grace Fellowship Church was founded in 1985 with a simple yet powerful vision: to create a place where people could encounter God's love, grow in their faith, and build meaningful relationships with one another.</p>
                <p class="mb-4">What began as a small gathering of 20 believers in a rented hall has grown into a vibrant community of over 2,000 members, united by our shared commitment to following Jesus Christ and serving our community.</p>
                <p>Through decades of faithful ministry, we have witnessed countless lives transformed by the power of God's grace, and we continue to be amazed by His faithfulness in our midst.</p>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <img src="https://images.unsplash.com/photo-1438032005730-c779502df39b?w=600&h=400&fit=crop" 
                     alt="Church History" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Mission, Vision, Values -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="info-card">
                    <i class="fas fa-bullseye"></i>
                    <h4>Our Mission</h4>
                    <p>To make disciples of Jesus Christ who love God, love others, and serve the world with passion and purpose, transforming lives and communities through the power of the Gospel.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="info-card">
                    <i class="fas fa-eye"></i>
                    <h4>Our Vision</h4>
                    <p>To be a church where every person experiences God's love, discovers their purpose, and uses their gifts to make a positive impact in their community and beyond.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="info-card">
                    <i class="fas fa-heart"></i>
                    <h4>Our Values</h4>
                    <p>Love, Integrity, Excellence, Community, Service, and Growth. These core values guide everything we do and shape the character of our church family.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Leadership Team -->
<section class="section-padding">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2 class="font-display">Our Leadership Team</h2>
            <p>Meet the dedicated leaders who shepherd our church family with love and wisdom</p>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="leader-card">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=300&fit=crop" alt="Pastor David Adebayo">
                    <div class="leader-card-body">
                        <h5>Pastor David Adebayo</h5>
                        <p class="text-primary">Senior Pastor</p>
                        <p>Pastor David has been leading Grace Fellowship Church for over 15 years with wisdom, compassion, and a heart for God's people.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="leader-card">
                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b77c?w=400&h=300&fit=crop" alt="Pastor Grace Okafor">
                    <div class="leader-card-body">
                        <h5>Pastor Grace Okafor</h5>
                        <p class="text-primary">Associate Pastor</p>
                        <p>Pastor Grace oversees our women's ministry and community outreach programs, bringing hope to countless lives.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="leader-card">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=300&fit=crop" alt="Elder Samuel Johnson">
                    <div class="leader-card-body">
                        <h5>Elder Samuel Johnson</h5>
                        <p class="text-primary">Youth Pastor</p>
                        <p>Elder Samuel leads our vibrant youth ministry, mentoring the next generation of Christian leaders.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact & Service Info -->
<section class="section-padding bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-md-6" data-aos="fade-right">
                <h3 class="font-display mb-4">Visit Us</h3>
                <div class="mb-3">
                    <i class="fas fa-map-marker-alt me-3"></i>
                    <span>123 Faith Avenue, Victoria Island, Lagos, Nigeria</span>
                </div>
                <div class="mb-3">
                    <i class="fas fa-phone me-3"></i>
                    <span>+234 801 234 5678</span>
                </div>
                <div class="mb-3">
                    <i class="fas fa-envelope me-3"></i>
                    <span>info@gracefellowshipchurch.org</span>
                </div>
                <div class="mb-3">
                    <i class="fab fa-whatsapp me-3"></i>
                    <span>+234 802 345 6789</span>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <h3 class="font-display mb-4">Service Times</h3>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Sunday Worship:</strong> 9:00 AM & 11:30 AM</li>
                    <li class="mb-2"><strong>Wednesday Bible Study:</strong> 7:00 PM</li>
                    <li class="mb-2"><strong>Friday Night Prayer:</strong> 8:00 PM</li>
                    <li class="mb-2"><strong>Youth Service:</strong> Saturday 5:00 PM</li>
                </ul>
                <div class="mt-4">
                    <h5>Follow Us</h5>
                    <div class="social-icons">
                        <a href="https://facebook.com" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://twitter.com" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://youtube.com" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
