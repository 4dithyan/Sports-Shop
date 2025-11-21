// The Athlete's Arsenal - Premium Sports Experience

document.addEventListener('DOMContentLoaded', function() {
    // Initialize premium micro-interactions
    initializeMicroInteractions();
    
    // Initialize staggered animations
    initializeStaggeredAnimations();
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Form validation enhancement
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Image lazy loading
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Search functionality
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Implement live search if needed
                console.log('Searching for:', this.value);
            }, 300);
        });
    }

    // Cart functionality
    initializeCart();
    
    // Wishlist functionality
    initializeWishlist();
    
    // Product image gallery
    initializeImageGallery();
    
    // Quantity selectors
    initializeQuantitySelectors();
    
    // Price range slider
    initializePriceSlider();
});

// Premium Micro-interactions
function initializeMicroInteractions() {
    // Add hover effects to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.classList.add('hover-lift');
        });
        
        button.addEventListener('mouseleave', function() {
            this.classList.remove('hover-lift');
        });
    });
    
    // Add hover effects to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('hover-lift');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hover-lift');
        });
    });
    
    // Add parallax effect to hero section
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        window.addEventListener('scroll', throttle(() => {
            const scrollPosition = window.scrollY;
            heroSection.style.backgroundPosition = `center ${scrollPosition * 0.5}px`;
        }, 10));
    }
}

// Staggered Animations
function initializeStaggeredAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, index * 100);
            }
        });
    }, {
        threshold: 0.1
    });
    
    document.querySelectorAll('.staggered-animation').forEach(el => {
        observer.observe(el);
    });
}

// Cart Functions
function initializeCart() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const quantity = this.dataset.quantity || 1;
            
            addToCart(productId, quantity);
        });
    });

    // Update quantity buttons
    document.querySelectorAll('.update-quantity').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const action = this.dataset.action;
            const input = this.parentElement.querySelector('input[type="number"]');
            let newQuantity = parseInt(input.value);
            
            if (action === 'increase') {
                newQuantity++;
            } else if (action === 'decrease' && newQuantity > 1) {
                newQuantity--;
            }
            
            input.value = newQuantity;
            updateCartQuantity(productId, newQuantity);
        });
    });

    // Remove from cart buttons
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeFromCart(productId);
        });
    });
}

function addToCart(productId, quantity = 1, sizeId = null) {
    showLoading(this);
    
    const requestData = {
        product_id: productId,
        quantity: quantity
    };
    
    // Add size_id to request if provided
    if (sizeId) {
        requestData.size_id = sizeId;
    }
    
    fetch('ajax/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(this);
        
        if (data.success) {
            updateCartCount(data.cart_count);
            showNotification('Added to your arsenal!', 'success');
            
            // Update button state
            const button = document.querySelector(`[data-product-id="${productId}"]`);
            if (button) {
                button.innerHTML = '<i class="fas fa-check"></i> Added';
                button.classList.add('btn-success');
                button.classList.remove('btn-primary');
                
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-cart-plus"></i>';
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }, 2000);
            }
        } else {
            showNotification(data.message || 'Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        hideLoading(this);
        console.error('Error:', error);
        showNotification('An error occurred while adding to cart', 'error');
    });
}

function updateCartQuantity(productId, quantity) {
    fetch('ajax/update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            if (data.total) {
                updateCartTotal(data.total);
            }
            showNotification('Cart updated!', 'success');
        } else {
            showNotification('Failed to update cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function removeFromCart(productId) {
    if (!confirm('Are you sure you want to remove this item from your arsenal?')) {
        return;
    }
    
    fetch('ajax/remove-from-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            // Remove the row from the table
            const row = document.querySelector(`[data-product-id="${productId}"]`).closest('tr');
            if (row) {
                row.remove();
            }
            showNotification('Item removed from cart', 'success');
        } else {
            showNotification('Failed to remove item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function updateCartCount(count) {
    const cartBadge = document.querySelector('.navbar .badge');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'inline' : 'none';
    }
}

function updateCartTotal(total) {
    const totalElement = document.querySelector('.cart-total');
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
    }
}

// Wishlist Functions
function initializeWishlist() {
    document.querySelectorAll('.wishlist-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const inWishlist = this.dataset.inWishlist === 'true';
            
            toggleWishlist(productId, inWishlist, this);
        });
    });
}

function toggleWishlist(productId, inWishlist, button) {
    fetch('ajax/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: productId,
            action: inWishlist ? 'remove' : 'add'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = button.querySelector('i');
            if (inWishlist) {
                icon.className = 'fas fa-heart-o';
                button.dataset.inWishlist = 'false';
                showNotification('Removed from wishlist', 'info');
            } else {
                icon.className = 'fas fa-heart';
                button.dataset.inWishlist = 'true';
                showNotification('Added to wishlist', 'success');
            }
        } else {
            showNotification('Failed to update wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Image Gallery Functions
function initializeImageGallery() {
    const mainImage = document.getElementById('main-image');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                const newSrc = this.dataset.fullSize || this.src;
                mainImage.src = newSrc;
                
                // Update active thumbnail
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
}

// Quantity Selector Functions
function initializeQuantitySelectors() {
    document.querySelectorAll('.quantity-selector').forEach(selector => {
        const input = selector.querySelector('input[type="number"]');
        const increaseBtn = selector.querySelector('.increase');
        const decreaseBtn = selector.querySelector('.decrease');
        
        if (increaseBtn) {
            increaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value);
                const maxValue = parseInt(input.max) || 999;
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
        
        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value);
                const minValue = parseInt(input.min) || 1;
                if (currentValue > minValue) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
}

// Price Range Slider
function initializePriceSlider() {
    const priceSlider = document.getElementById('price-range');
    const priceDisplay = document.getElementById('price-display');
    
    if (priceSlider && priceDisplay) {
        priceSlider.addEventListener('input', function() {
            const value = this.value;
            const max = this.max;
            const percentage = (value / max) * 100;
            
            priceDisplay.textContent = formatPrice(value);
            this.style.background = `linear-gradient(to right, #FF4500 0%, #FF4500 ${percentage}%, #ddd ${percentage}%, #ddd 100%)`;
        });
    }
}

// Utility Functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function showLoading(element) {
    if (element) {
        element.disabled = true;
        const originalText = element.innerHTML;
        element.innerHTML = '<span class="spinner"></span> Loading...';
        element.dataset.originalText = originalText;
    }
}

function hideLoading(element) {
    if (element && element.dataset.originalText) {
        element.disabled = false;
        element.innerHTML = element.dataset.originalText;
        delete element.dataset.originalText;
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Form Enhancement Functions
function enhanceFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    let isValid = true;
    let message = '';
    
    if (required && !value) {
        isValid = false;
        message = 'This field is required';
    } else if (value) {
        switch (type) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    message = 'Please enter a valid email address';
                }
                break;
            case 'tel':
                const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                    isValid = false;
                    message = 'Please enter a valid phone number';
                }
                break;
            case 'password':
                if (value.length < 6) {
                    isValid = false;
                    message = 'Password must be at least 6 characters long';
                }
                break;
        }
    }
    
    // Update field appearance
    field.classList.remove('is-valid', 'is-invalid');
    field.classList.add(isValid ? 'is-valid' : 'is-invalid');
    
    // Update feedback message
    let feedback = field.parentNode.querySelector('.invalid-feedback');
    if (!isValid && message) {
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
    } else if (feedback) {
        feedback.remove();
    }
    
    return isValid;
}

// Initialize form validation when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    enhanceFormValidation();
});

// Export functions for global use
window.CosmosSports = {
    addToCart,
    updateCartQuantity,
    removeFromCart,
    toggleWishlist,
    showNotification,
    formatPrice,
    debounce,
    throttle
};