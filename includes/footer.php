    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <h5 class="">Grace Fellowship Church</h5>
                    <p class="mb-3">Building Faith, Strengthening Community, Serving with Love</p>
                    <div class="social-icons">
                        <a href="https://facebook.com" target="_blank" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://twitter.com" target="_blank" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/about.php">About Us</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/sermons.php">Sermons</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/index.php#events">Events</a></li> 
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/gallery.php">Gallery</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>/pages/blog.php">Blog</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123 Faith Avenue, Victoria Island, Lagos, Nigeria
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +234 801 234 5678
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            info@gracefellowshipchurch.org
                        </li>
                        <li class="mb-2">
                            <i class="fab fa-whatsapp me-2"></i>
                            +234 802 345 6789
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-12 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <h5>Service Times</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">Sunday Worship: 9:00 AM & 11:30 AM</li>
                        <li class="mb-2">Wednesday Bible Study: 7:00 PM</li>
                        <li class="mb-2">Friday Night Prayer: 8:00 PM</li>
                        <li class="mb-2">Youth Service: Saturday 5:00 PM</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Grace Fellowship Church. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span>Developed by </span><a href="https://www.emmanuelayanleye.com.ng" class="me-3">Emmanuel Ayanleye</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });
    </script>
</body>
</html>