# Loan Management System

A web application for borrowers to apply for loans, track repayments, and manage their account profile. Administrators can review applications, manage active loans and payments, and view an audit ledger.

## Features

### Borrowers

- Create an account, sign in, and reset a forgotten password.
- Maintain personal, employment, income, address, and emergency contact details.
- Submit loan applications for ₱1,000 to ₱50,000, with terms of 3, 6, 9, 12, 18, 24, or 36 months.
- View application decisions, approved loan details, repayment schedule amounts, and outstanding balance.
- Submit payments against an active loan using cash, GCash, bank transfer, or another method. Payments remain pending until reviewed by an administrator.

### Administrators

- View the admin dashboard and member list.
- Review pending loan applications and approve or reject them. Approved loans can have an approved amount up to the amount requested.
- Monitor active loans and outstanding balances.
- Confirm or reject borrower payment submissions.
- Review the audit ledger of account, application, and payment activity.

## Loan calculations

The application uses a fixed annual interest rate of 8%. Interest is calculated as simple interest based on the loan principal and term:

```text
interest = principal × 8% × term in months ÷ 12
total payable = principal + interest
monthly amortization = total payable ÷ term in months
```

## Technology

- PHP 8.3 or later
- Laravel 13
- Composer
- Node.js and npm
- A database supported by Laravel

## Setup

1. Install PHP dependencies and create the local environment file:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. Configure the database connection in `.env`, then run migrations:

   ```bash
   php artisan migrate
   ```

3. Install and build frontend assets:

   ```bash
   npm install
   npm run build
   ```

4. Start the application:

   ```bash
   php artisan serve
   ```

Open the local URL printed by Artisan. Borrowers can register through the site. Administrators sign in through `/admin/login`; an administrator account must be configured in the application database.
