<?php
require_once __DIR__ . '/../includes/config.php'; // Ensure this path is correct
$page_title = "About Us - Christ performing Christian Centre";
$page_description = "Learn about Christ performing Christian Centre - Our history, mission, vision, and leadership team.";
$current_page = 'about'; 
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

        .info-card i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .info-card h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        /* Further adjustments for mobile */
        @media (max-width: 768px){
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
            border-radius: 0;
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
            color: white;
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

        /* Responsive adjustments for smaller screens */
        @media (max-width: 992px) {
            .section-padding {
                padding: 40px 0;
            }
            .leader-card img {
                height: 180px;
            }
        }

        @media (max-width: 767.98px) {
            .section-padding {
                padding: 25px 0;
            }
            .leader-card img {
                height: 140px;
            }
            .info-card {
                padding: 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .row {
                margin-left: 0;
                margin-right: 0;
            }
            .leader-card img {
                height: 100px;
            }
            .info-card {
                padding: 0.7rem;
            }
        }
    </style>

<!-- Page Header -->
 <section class="page-header" style="background: linear-gradient(rgba(30, 58, 138, 0.8), rgba(30, 58, 138, 0.8)),
                        url('<?php echo BASE_URL; ?>/images/about.jpeg') center/cover;">
  <div class="container">
    <div>
      <h1>About us</h1>
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
                <p class="mb-4">Christ Performing Christian Centre is a vibrant community built on a powerful vision: to be a place where people encounter God's love, grow in their faith, and build meaningful relationships with one another.</p>
                <p class="mb-4">Under the leadership of Adekunle Emmanuel, our lead pastor, the church has grown into a thriving body of believers, united by a shared commitment to following Jesus Christ and serving our community.</p>
                <p>Through years of faithful ministry, we have witnessed countless lives transformed by the power of God's grace and continue to stand in awe of His faithfulness in our midst.</p>
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


<!-- Contact & Service Info -->
<section class="section-padding bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-md-6" data-aos="fade-right">
                <h3 class="font-display mb-4">Visit Us</h3>
                <div class="mb-3">
                    <i class="fas fa-map-marker-alt me-3"></i>
                    <span>23 Ayofayemi Street, off Princess Abiola Street, Nobex bus stop, Idimu Ikotun Road, Lagos, Nigeria</span>
                </div>
                <div class="mb-3">
                    <i class="fas fa-phone me-3"></i>
                    <span>+234 916 661 6862</span>
                </div>
                <div class="mb-3">
                    <i class="fas fa-envelope me-3"></i>
                    <span>christperformingcentre@gmail.com</span>
                </div>
                <div class="mb-3">
                    <i class="fab fa-whatsapp me-3"></i>
                    <span>+234 916 664 8407</span>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <h3 class="font-display mb-4">Service Times</h3>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Sunday Worship:</strong> 8:00 AM</li>
                    <li class="mb-2"><strong>Thursday Midweek Service:</strong> 6:00 PM</li>
                    <li class="mb-2"><strong>1st Day of the Month:</strong> Hour of Praise 6:00 PM</li>
                </ul>
                <div class="mt-4">
                    <h5>Follow Us</h5>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/share/16UDk38kkw/" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/christperforming?igsh=MWJoeXJkMHBlYzZicA==" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://youtube.com" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
