# COSMOS Sports - Premium E-commerce Platform

A sophisticated sports e-commerce platform with a dark-themed UI and kinetic orange accents. Designed for discerning athletes and performance gear enthusiasts, this platform delivers a premium digital shopping experience.

## Design Philosophy

### Aesthetic & Mood
- **Minimalist & Architectural**: Clean lines, deliberate whitespace, and art-directed layouts
- **Sophisticated Dark Theme**: Deep charcoal and tungsten grey foundations with vibrant accent colors
- **Premium Typography**: Sharp geometric sans-serif for headings, highly legible fonts for body copy

### Color Palette
- **Foundation**: Deep Charcoal (#121212) and Tungsten Grey (#333333)
- **Accent**: Kinetic Orange (#FF4500) for calls-to-action and interactive elements
- **Text**: Off-White (#F5F5F5) for headlines, warm grey (#AFAFAF) for body copy

## Key Features

### Premium Product Presentation
- Editorial-style product pages with storytelling elements
- High-fidelity imagery with dramatic lighting
- Detailed specification views and feature highlights

### Advanced Shopping Experience
- **Product Reviews**: Customers can review and rate products they've purchased
- **Size Management**: Comprehensive size selection system for apparel and footwear
- **Wishlist**: Save favorite items for later purchase
- **Shopping Cart**: Intuitive cart management with quantity adjustments

### User Account Management
- Personal dashboard with order history
- Profile management and address book
- Order tracking and cancellation (for eligible orders)

### Admin Panel
- Complete product management system
- Order processing and status updates
- User account management
- Category and size management

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **Backend**: PHP 8.0+
- **Database**: MySQL 5.7+
- **Security**: CSRF protection, input sanitization, prepared statements, password hashing, XSS escaping

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)
- Modern web browser with CSS3 and JavaScript ES6+ support

## Installation

### 1. Clone or Download
Place the project files in your web server directory (e.g., `htdocs` for XAMPP).

### 2. Database Setup
1. Open phpMyAdmin or your MySQL client
2. Create a new database named `project`
3. Import the `setup-database.sql` file to create tables and sample data

### 3. Configuration
1. Open `config.php` and update the database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'project');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

2. Update the site URL in `config.php`:
   ```php
   define('SITE_URL', 'http://localhost/PROJECTS/project');
   ```

### 4. File Permissions
Ensure the following directories are writable:
- `assets/images/products/` (for product image uploads)

### 5. Access the Website
- **Frontend**: Visit `http://localhost/PROJECTS/project`
- **Admin Panel**: Visit `http://localhost/PROJECTS/project/admin/dashboard.php`
- **Admin Login**: 
  - Email: `admin@cosmossports.com`
  - Password: `admin123`

## File Structure

```
project/
├── index.php                 # Homepage
├── products.php              # Product catalog
├── product-detail.php        # Individual product pages
├── cart.php                  # Shopping cart
├── checkout.php              # Checkout process
├── login.php                 # User login
├── register.php              # User registration
├── my-account.php            # User dashboard
├── wishlist.php              # User wishlist
├── categories.php            # Product categories
├── about.php                 # About page
├── contact.php               # Contact form
├── config.php                # Site configuration
├── setup-database.sql        # Database setup script
├── includes/                 # Shared PHP files
│   ├── header.php           # Site header
│   ├── footer.php           # Site footer
│   ├── functions.php        # Utility functions
│   ├── db.php               # Database connection
│   └── classes/             # PHP classes
│       ├── Product.class.php
│       ├── User.class.php
│       ├── Order.class.php
│       ├── Review.class.php
│       └── Size.class.php
├── admin/                   # Admin panel
│   ├── dashboard.php        # Admin dashboard
│   ├── products.php         # Product management
│   ├── orders.php           # Order management
│   ├── users.php            # User management
│   └── sizes.php            # Product size management
├── ajax/                    # AJAX endpoints
│   ├── add-to-cart.php
│   ├── update-cart.php
│   ├── remove-from-cart.php
│   ├── wishlist.php
│   └── add-review.php
└── assets/                  # Static assets
    ├── css/
    │   └── style.css        # Main stylesheet
    ├── js/
    │   └── script.js        # Main JavaScript
    └── images/              # Images and media
```

## Usage

### For Customers
1. **Browse Products**: Visit the homepage or product categories to discover gear
2. **Add to Cart**: Select sizes where applicable and add products to your cart
3. **Checkout**: Proceed through the secure checkout process
4. **Create Account**: Register for an account to track orders and save wishlist items
5. **Review Products**: After logging in, customers can review products they've purchased

### For Administrators
1. **Login**: Use the admin credentials to access the admin panel
2. **Manage Products**: Add, edit, or delete products and their sizes
3. **Process Orders**: Update order statuses and view order details
4. **Manage Users**: View and manage customer accounts
5. **Manage Sizes**: Configure product sizes for apparel and footwear

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **CSRF Protection**: Forms include CSRF tokens
- **Input Sanitization**: All user inputs are sanitized
- **SQL Injection Prevention**: Uses prepared statements
- **XSS Protection**: Output is escaped with `htmlspecialchars()`
- **Session Management**: Secure session handling with regeneration

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Modern browsers with CSS3 and JavaScript ES6+ support