    </main>

    <!-- Footer -->
    <?php 
    // Check if we're in the admin area
    $is_admin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    
    if ($is_admin): ?>
                </div>
            </div>
        </div>
    <?php else: ?>
    <footer class="py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-infinity me-2"></i>COSMOS
                    </h5>
                    <p class="text-muted">
                        The Digital Universe of Peak Performance. Your premier destination for high-quality sports equipment and gear in Adimali, Kerala. 
                        We're committed to helping athletes of all levels achieve their best performance.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-dark me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-dark me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-dark me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-dark me-3"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="products.php" class="text-muted text-decoration-none">Collections</a></li>
                        <li><a href="categories.php" class="text-muted text-decoration-none">By Category</a></li>
                        <li><a href="about.php" class="text-muted text-decoration-none">The Observatory</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Customer Service</h6>
                    <ul class="list-unstyled">
                        <li><a href="contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
                        <li><a href="returns.php" class="text-muted text-decoration-none">Returns</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Shipping Info</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Size Guide</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">My Account</h6>
                    <ul class="list-unstyled">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="my-account.php" class="text-muted text-decoration-none">My Account</a></li>
                            <li><a href="wishlist.php" class="text-muted text-decoration-none">Wishlist</a></li>
                            <li><a href="logout.php" class="text-muted text-decoration-none">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="text-muted text-decoration-none">Login</a></li>
                            <li><a href="register.php" class="text-muted text-decoration-none">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-phone me-2"></i><?php echo SITE_PHONE; ?></li>
                        <li><i class="fas fa-envelope me-2"></i><?php echo SITE_EMAIL; ?></li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>COSMOS<br>Adimali, Kerala 685521</li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; <?php echo date('Y'); ?> COSMOS - The Digital Universe of Peak Performance. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-muted text-decoration-none me-3">Terms of Service</a>
                    <a href="#" class="text-muted text-decoration-none">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($is_admin): ?>
    <script src="../assets/js/admin.js"></script>
    <?php else: ?>
    <script src="assets/js/script.js"></script>
    <?php endif; ?>
    
    <!-- Additional page-specific scripts -->
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
</body>
</html>