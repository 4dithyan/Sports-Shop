# COSMOS Sports - Premium Sports E-commerce Platform

COSMOS Sports is a premium sports e-commerce platform built with PHP, MySQL, and modern web technologies. The platform features a dark-themed UI with a complete shopping experience including product catalog, shopping cart, wishlist, user accounts, and admin panel.

## Features

### Frontend Features
- Responsive design with mobile-first approach
- Product catalog with filtering and sorting capabilities
- Product details page with images, descriptions, and reviews
- Shopping cart functionality with quantity adjustment
- Wishlist management
- User account system with order history
- Secure checkout process
- Newsletter subscription
- Search functionality

### Admin Panel Features
- Dashboard with sales analytics and statistics
- Product management (add, edit, delete, featured status)
- Category management
- Order management with status updates
- User management
- Review management
- Size management for products
- Sales reporting and analytics

### Technical Features
- Secure user authentication and authorization
- CSRF protection
- Input sanitization and validation
- Prepared statements for database queries
- Password hashing
- XSS prevention
- Responsive design using Bootstrap 5
- Modern UI with animations and transitions

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **Security**: CSRF protection, input sanitization, prepared statements, password hashing, XSS escaping
- **Web Server**: Apache/Nginx

## Project Structure

```
.
├── admin/                 # Admin panel files
│   ├── categories.php
│   ├── dashboard.php
│   ├── index.php
│   ├── order-details.php
│   ├── orders.php
│   ├── products.php
│   ├── sizes.php
│   └── users.php
├── ajax/                  # AJAX handlers
│   ├── add-review.php
│   ├── add-to-cart.php
│   ├── remove-from-cart.php
│   ├── update-cart.php
│   └── wishlist.php
├── assets/                # Static assets
│   ├── css/
│   │   ├── admin-modern.css
│   │   └── style.css
│   └── js/
│       ├── admin.js
│       └── script.js
├── includes/              # Shared components
│   ├── admin-sidebar.php
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── about.php
├── cart.php
├── categories.php
├── checkout.php
├── config.php
├── contact.php
├── create-sizes-table-web.php
├── index.php
├── login.php
├── logout.php
├── my-account.php
├── order-confirmation.php
├── order-details.php
├── product-detail.php
├── products.php
├── quick-setup.php
├── register.php
└── wishlist.php
```

## Database Schema

The platform uses the following database tables:
- `categories` - Product categories
- `users` - Customer and admin accounts
- `products` - Product information
- `orders` - Customer orders
- `order_items` - Items within orders
- `wishlist` - User wishlist items
- `reviews` - Product reviews
- `product_sizes` - Product size variations

## Setup Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- Composer (optional, for dependency management)

### Installation Steps

1. Clone or download the repository to your web server directory
2. Create a MySQL database named `project`
3. Update the database configuration in `config.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'project');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. Run the setup script by accessing `quick-setup.php` in your browser or command line
5. This will create all necessary tables and insert sample data
6. Access the website at your configured URL
7. Access the admin panel at `/admin/dashboard.php`

### Default Admin Credentials
- **Email**: admin@cosmossports.com
- **Password**: admin123

## Configuration

Key configuration settings can be found in `config.php`:
- Database connection settings
- Site name and URL
- Email and phone contact information
- Security settings (secret key, encryption method)
- E-commerce settings (currency, tax rate, shipping costs)
- File upload settings
- Pagination settings

## Security Measures

- Passwords are securely hashed using PHP's password_hash() function
- Prepared statements are used for all database queries to prevent SQL injection
- Input validation and sanitization on all user-submitted data
- CSRF protection on forms
- XSS prevention through output escaping
- Secure session configuration

## Customization

### Styling
- Main frontend styles: `assets/css/style.css`
- Admin panel styles: `assets/css/admin-modern.css`

### Functionality
- Shared functions: `includes/functions.php`
- Database connection: `includes/db.php`
- Header and footer: `includes/header.php` and `includes/footer.php`

## API Endpoints

AJAX endpoints are available in the `ajax/` directory:
- `add-to-cart.php` - Add products to cart
- `remove-from-cart.php` - Remove products from cart
- `update-cart.php` - Update cart item quantities
- `wishlist.php` - Manage wishlist items
- `add-review.php` - Submit product reviews

## Contributing

1. Fork the repository
2. Create a new branch for your feature
3. Commit your changes
4. Push to your branch
5. Create a pull request

## License

This project is proprietary and intended for educational purposes. All rights reserved.

## Support

For support, please contact the development team or create an issue in the repository.