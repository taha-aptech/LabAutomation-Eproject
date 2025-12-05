# ElectraLab - CPRI Approved Electrical Products E-Commerce 

### Project Overview
A complete customer-facing e-commerce website for an electrical products company specializing in CPRI-approved products. Customers can browse certified products, view testing details, add to cart, and place orders.


## Live Demo
### Customer Portal: 
    `http://localhost/LabAutomation/frontend/`
### Admin Dashboard: 
    `http://localhost/LabAutomation/backend/dashboard.php`


### Technology Stack
- Frontend: HTML5, CSS3, Bootstrap 5, JavaScript, SweetAlert2
- Backend: PHP 8.1+, MySQLi
- Database: MariaDB 10.4+
- PDF Generation: mPDF 6.1
- Icons: Font Awesome 6


### Project Structure
```
LabAutomation/
├── README.md
│
├── backend
│   ├── auth_check.php
│   ├── cpri.php
│   ├── dashboard.php
│   ├── db.php
│   ├── financial_tracking.php
│   ├── footer.php
│   ├── header.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── manufacture.php
│   ├── orders.php
│   ├── products.php
│   ├── sidebar.php
│   ├── signup.php
│   ├── testing_types.php
│   ├── test_records.php
│   ├── users.php
│   ├── user_update.php
│   │
│   └── assets
│       ├── css
│       │   └── style.css
│       ├── js
│       │   └── java.js
│       └── uploads
│
├── database
│   ├── LabAutomationdb.txt
│   └── lab_automation.sql
│
└── frontend
    ├── about.php
    ├── add-to-cart.php
    ├── auth_check.php
    ├── cart.php
    ├── checkout.php
    ├── contact.php
    ├── db.php
    ├── footer.php
    ├── header.php
    ├── index.php
    ├── login.php
    ├── logout.php
    ├── my-orders.php
    ├── order-details.php
    ├── order-summary.php
    ├── product-details.php
    ├── products.php
    ├── profile.php
    ├── services.php
    ├── signup.php
    │
    ├── assets
    │   ├── css
    │   │   └── style.css
    │   ├── images
    │   │   ├── capacitor.jfif
    │   │   ├── fuse.jfif
    │   │   └── resistor.jfif
    │   └── js
    │       └── script.js
    │
    └── database
        ├── LabAutomationdb.txt
        └── lab_automation.sql


```


##  Database Schema
### Main Tables:
- users - User accounts with roles
- roles - User roles (Admin, Manufacturer, CPRI, Customer)
- products - Product catalog with CPRI approval status
- financial_tracking - Product pricing and approval tracking
- test_records - Product testing results
- testing_type - Test types (Thermal, Mechanical, Insulation)
- orders - Order headers
- order_items - Order line items
- cart - Shopping cart items



## Installation & Setup
### Prerequisites:
- XAMPP/WAMP/MAMP (Apache, MySQL, PHP)
- PHP 8.1 or higher


### Step 1: Database Setup
```
-- Import the database
mysql -u root -p lab_automation < database/lab_automation.sql
```


### Step 2: Configure Database
```
$host = '127.0.0.1';
$username = 'root';      // Your MySQL username
$password = '';          // Your MySQL password
$database = 'lab_automation';

```


## User Roles & Credentials

Role	        Username	    Password	 Access                         <br>
Customer	    ali	            cust123	     Frontend shopping              <br>
Manufacturer	mfg01	        maf123	     Backend dashboard              <br>
CPRI	        cpri01	        cpri123	     Backend dashboard              <br>
Admin	        admin	        admin123	 Full system access             <br>


## Product Flow
Product Creation → CPRI Testing → Approval → Customer Listing → Add to Cart → Checkout → Order Processing → Invoice Generation


## Key Features
### Customer Features:
- User registration and authentication
- Browse CPRI-approved products
- View product testing details
- Shopping cart with session/database persistence
- Checkout with order processing
- Order history and tracking
- Invoice generation (PDF)
- User profile management
- SweetAlert notifications

### Admin Features:
- Role-based access control
- Product management
- Order management
- Customer management
- Testing record management


### License
This project is developed for educational purposes. Commercial use requires proper licensing.