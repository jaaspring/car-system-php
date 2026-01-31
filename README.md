# Car System PHP

A comprehensive PHP-based car management and analytics system, serving as **Part 2 - Integration Car System**. This project integrates enhancement features derived from the original Java command-line interface application, adapted and expanded for a web-based modular environment.

## Overview

This system allows users to browse cars, book test drives, compare models, and calculate loans, while administrators can manage the entire fleet, user base, and appointments. It has been recently restructured into a modular directory format for improved security and scalability.

## Features

### Core Features
- **User & Admin Dashboards**: Distinct interfaces for different user roles.
- **Car Management**: Admin can add, edit, and delete vehicles.
- **Test Drive Booking**: Users can schedule test drives; Admins can manage them.
- **Secure Authentication**: Prepared statements and role-based access control.

### Enhancement Features (Integration Part 2)
- **Modular Directory Structure**: Separate `/admin` and `/customer` folders for clean module isolation.
- **Loan Calculator**: Estimate monthly payments with history tracking.
- **Compare Models**: Side-by-side technical comparison of different Proton models.
- **Advanced Admin Analytics**:
    - **Status Filtering**: Manage appointments by Pending, Completed, or Cancelled status.
    - **Branch Analysis**: Filter customer reviews by **Location** and **Showroom**.
- **User Reviews & Ratings**: 
    - Integrated star system (★) in history view.
    - High-visibility status badges (Yellow for "Rated").
- **Dynamic UX**: AJAX-based availability checks and real-time Toast notifications.

## Project Structure

```text
├── admin/               # Administrative tools (Dashboard, Users, Cars, Reviews)
├── customer/            # Customer tools (Dashboard, Models, Booking, Loans)
├── Images/              # Shared vehicle visuals
├── db_connection.php    # Centralized database logic
├── navigation.php       # Dynamic path-aware navigation
├── toast.js/.css        # Global notification system
└── login.php            # Role-based secure portal
```

## Integration Notes

This project corresponds to the integration phase, bringing features from the standalone logic into a web-based structure. It builds upon the concepts found in the [Car Loan Calculation System (Java)](https://github.com/jaaspring/car-loan-calculation-system-java).

---
**Status**: Modular Restructuring Complete | Analytical Filters Deployed.
