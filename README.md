# Car Loan Calculation System (Integration Part 2)

A comprehensive PHP-based car management and analytics system, serving as **Part 2 - Integration Car System**. This project is the web-based evolution of the original [Car Loan Calculation System (Java)](https://github.com/jaaspring/car-loan-calculation-system-java), adapted and expanded for a modular web environment.

## Features

### Core Functionality
- **Car Management**: Admin can add, update, and manage car details including images, pricing, and specifications.
- **Car Comparison**: Compare different car models side-by-side to see differences in performance and features.
- **Loan Calculator**: Calculate monthly installments based on car price, interest rates, and down payments, with history tracking.
- **User Dashboard**: Role-based access for Admins and Customer Users.
- **Test Drive Scheduling**: System for users to book test drives for their preferred models; Admins can manage these appointments.
- **User Authentication**: Secure login and registration system with **Bcrypt** password hashing.

### Recent Enhancements (Web Exclusive)
- **Account Management**:
    - **Profile Customization**: Users can upload and manage profile pictures.
    - **Settings**: Dedicated pages for updating contact info and changing passwords.
- **Security & UX**:
    - **Forgot Password**: integrated **PHPMailer** for secure email-based password resets.
    - **Password Visibility**: Interactive "Show/Hide" toggle for credential fields.
- **Advanced Admin Analytics**:
    - Filter appointments by status (Pending, Completed, Cancelled).
    - Analyze customer reviews by Branch and Location.
- **User Reviews & Ratings**: Integrated star rating system with visual status badges.

## Project Structure

```text
├── admin/               # Administrative tools (Dashboard, Users, Cars, Reviews)
├── customer/            # Customer tools (Dashboard, Models, Booking, Loans, Settings)
├── Images/              # Shared vehicle visuals
├── vendor/              # Composer dependencies (PHPMailer)
├── db_connection.php    # Centralized database logic
├── navigation.php       # Dynamic path-aware navigation
├── forgot_password.php  # Password recovery system
└── login.php            # Secure entry portal
```

## System Requirements
- **Server**: Apache (via XAMPP/WAMP) or compatible web server.
- **Database**: MySQL.
- **PHP**: 7.4+ (8.x recommended).
- **Composer**: For managing dependencies (PHPMailer).

## Integration Notes
This project corresponds to the integration phase, bringing features from the standalone logic into a web-based structure. It builds upon the concepts found in the original Java command-line interface application.
