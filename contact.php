<?php
$page_title = 'Contact Us - Cosmos Sports';
$page_description = 'Get in touch with Cosmos Sports. We\'re here to help with your sports equipment needs.';

require_once 'includes/header.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // Send email (in a real application, you would implement proper email sending)
        $email_message = "Name: $name\n";
        $email_message .= "Email: $email\n";
        $email_message .= "Subject: $subject\n\n";
        $email_message .= "Message:\n$message";
        
        // For demo purposes, we'll just show a success message
        setFlashMessage('success', 'Thank you for your message! We\'ll get back to you soon.');
        header('Location: contact.php');
        exit();
    } else {
        setFlashMessage('error', implode('<br>', $errors));
    }
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Contact Us</h1>
            <p class="lead text-muted">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </div>
    
    <div class="row">
        <!-- Contact Form -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">Send us a Message</h3>
                    <form method="POST" action="contact.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Product Question" <?php echo ($_POST['subject'] ?? '') === 'Product Question' ? 'selected' : ''; ?>>Product Question</option>
                                <option value="Order Support" <?php echo ($_POST['subject'] ?? '') === 'Order Support' ? 'selected' : ''; ?>>Order Support</option>
                                <option value="Returns & Exchanges" <?php echo ($_POST['subject'] ?? '') === 'Returns & Exchanges' ? 'selected' : ''; ?>>Returns & Exchanges</option>
                                <option value="Technical Support" <?php echo ($_POST['subject'] ?? '') === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="Partnership" <?php echo ($_POST['subject'] ?? '') === 'Partnership' ? 'selected' : ''; ?>>Partnership</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      placeholder="Please provide as much detail as possible..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Get in Touch</h5>
                    <div class="contact-info">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-phone text-white"></i>
                            </div>
                            <div>
                                <strong>Phone</strong><br>
                                <a href="tel:<?php echo SITE_PHONE; ?>" class="text-decoration-none">
                                    <?php echo SITE_PHONE; ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-envelope text-white"></i>
                            </div>
                            <div>
                                <strong>Email</strong><br>
                                <a href="mailto:<?php echo SITE_EMAIL; ?>" class="text-decoration-none">
                                    <?php echo SITE_EMAIL; ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-map-marker-alt text-white"></i>
                            </div>
                            <div>
                                <strong>Address</strong><br>
                                COSMOS<br>
        Adimali,685561
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div>
                                <strong>Business Hours</strong><br>
                                Mon-Fri: 9:00 AM - 6:00 PM<br>
                                Sat: 10:00 AM - 4:00 PM<br>
                                Sun: Closed
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FAQ -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Frequently Asked Questions</h5>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What is your return policy?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer a 12-day return policy on all items. Items must be in original condition with tags attached.
                                </div>
                            </div>
                        </div>
                    
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How long does shipping take?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Standard shipping takes 3-7 business days. Express shipping options are available at checkout.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
