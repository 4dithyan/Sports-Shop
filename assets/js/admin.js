// Admin Dashboard JavaScript - Modern Version

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', function() {
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            animation: true,
            delay: { "show": 500, "hide": 100 }
        });
    });
    
    // Enable Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            animation: true,
            delay: { "show": 500, "hide": 100 }
        });
    });
    
    // Confirm before deleting with modern alert
    var deleteForms = document.querySelectorAll('form[method="POST"][onsubmit*="confirm"]');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Create custom confirmation modal
            if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                form.submit();
            }
        });
    });
    
    // Auto-hide flash messages with smooth animation
    var flashMessages = document.querySelectorAll('.alert');
    flashMessages.forEach(function(alert) {
        // Add entrance animation
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        alert.style.transition = 'all 0.3s ease';
        
        setTimeout(function() {
            alert.style.opacity = '1';
            alert.style.transform = 'translateY(0)';
        }, 100);
        
        // Auto hide after 5 seconds with exit animation
        setTimeout(function() {
            alert.style.transition = 'all 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Enhanced sidebar toggle for mobile
    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-toggled');
            document.querySelector('.sidebar').classList.toggle('toggled');
            
            // Add animation class
            var sidebar = document.querySelector('.sidebar');
            if (document.body.classList.contains('sidebar-toggled')) {
                sidebar.style.transform = 'translateX(-100%)';
            } else {
                sidebar.style.transform = 'translateX(0)';
            }
        });
    }
    
    // Handle product form submission
    var productForm = document.querySelector('#productModal form');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            // Form validation
            var nameField = document.getElementById('name');
            var priceField = document.getElementById('price');
            var categoryIdField = document.getElementById('category_id');
            var stockQuantityField = document.getElementById('stock_quantity');
            
            var isValid = true;
            var errorMessage = '';
            
            // Reset validation classes
            [nameField, priceField, categoryIdField, stockQuantityField].forEach(function(field) {
                if (field) {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Validate required fields
            if (nameField && !nameField.value.trim()) {
                nameField.classList.add('is-invalid');
                isValid = false;
                errorMessage += 'Product name is required. ';
            }
            
            if (priceField && (!priceField.value || parseFloat(priceField.value) <= 0)) {
                priceField.classList.add('is-invalid');
                isValid = false;
                errorMessage += 'Valid price is required. ';
            }
            
            if (categoryIdField && !categoryIdField.value) {
                categoryIdField.classList.add('is-invalid');
                isValid = false;
                errorMessage += 'Category is required. ';
            }
            
            if (stockQuantityField && (stockQuantityField.value === '' || parseInt(stockQuantityField.value) < 0)) {
                stockQuantityField.classList.add('is-invalid');
                isValid = false;
                errorMessage += 'Valid stock quantity is required. ';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors: ' + errorMessage);
                return false;
            }
            
            // Add loading state to submit button
            var submitButton = productForm.querySelector('button[type="submit"]');
            if (submitButton) {
                var originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
                submitButton.disabled = true;
                
                // Re-enable button if form doesn't submit (for debugging)
                setTimeout(function() {
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                }, 5000);
            }
        });
    }
    
    // Add loading animation to all buttons on click
    var buttons = document.querySelectorAll('button, .btn');
    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Skip if it's a form submit button (handled separately)
            if (this.type === 'submit' && this.form) return;
            
            // Add loading state
            var originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
            button.disabled = true;
            
            // Reset after 2 seconds (in a real app, this would be reset after form submission)
            setTimeout(function() {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        });
    });
    
    // Add animation to stat cards on hover
    var statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add animation to table rows on hover
    var tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});

// Function to edit category (used in categories.php)
function editCategory(id) {
    // Add animation before navigation
    document.body.style.opacity = '0.8';
    document.body.style.transition = 'opacity 0.3s ease';
    
    setTimeout(function() {
        window.location.href = "categories.php?edit=" + id;
    }, 300);
}

// Function to add a new product (clears the modal form)
function addProduct() {
    // Redirect to the clean page
    window.location.href = "products.php";
}

// Function to format currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Function to validate form with visual feedback
function validateForm(form) {
    var requiredFields = form.querySelectorAll('[required]');
    var isValid = true;
    
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            // Add shake animation
            field.style.animation = 'shake 0.5s';
            setTimeout(function() {
                field.style.animation = '';
            }, 500);
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Add shake animation for form validation
var style = document.createElement('style');
style.innerHTML = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);