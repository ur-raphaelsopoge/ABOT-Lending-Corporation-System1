# ABOT Lending Corporation System

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

Capstone Members/Contributors:
John Raphael D. Abino
Roselyn Daguiso
Mervin Aquis
Adrian Arcabos

<img width="1680" height="1050" alt="SYSTEM1" src="https://github.com/user-attachments/assets/5db2b6a1-126f-488b-babb-c99b78b336de" />
<img width="1680" height="1050" alt="SYSTEM2" src="https://github.com/user-attachments/assets/66ab6f84-14fd-4982-b4fc-7287216330cc" />
<img width="1680" height="1050" alt="SYSTEM4" src="https://github.com/user-attachments/assets/b2f0c227-aadf-46f1-9c5b-a4a9a40725f0" />
<img width="1680" height="1050" alt="SYSTEM6" src="https://github.com/user-attachments/assets/5b1d9297-440b-4e56-bf63-ba4dadc65a2a" />
<img width="1680" height="1050" alt="SYSTEM7" src="https://github.com/user-attachments/assets/e0ec0da2-2674-4c9d-9b78-9ab343b0f131" />
<img width="1680" height="1050" alt="SYSTEM9" src="https://github.com/user-attachments/assets/d09a2bf1-be14-4a73-8742-415f0f8b3de8" />
<img width="1680" height="1050" alt="SYSTEM10" src="https://github.com/user-attachments/assets/4d2840db-f204-4b76-9f4e-45171ab0eecd" />
<img width="1680" height="1050" alt="SYSTEM11" src="https://github.com/user-attachments/assets/73f3eb45-afe5-4284-9979-d725e4bf1ef9" />
<img width="1680" height="1050" alt="SYSTEM13" src="https://github.com/user-attachments/assets/28f03d06-f9fd-4192-93e4-34c7e58d9922" />
<img width="1680" height="1050" alt="SYSTEM14" src="https://github.com/user-attachments/assets/28315193-85ea-4c68-8cb8-25d265fcda9c" />
<img width="1680" height="1050" alt="SYSTEM15" src="https://github.com/user-attachments/assets/cdd2f453-a7cf-45e5-89f9-7bac2389f925" />
<img width="1680" height="1050" alt="SYSTEM17" src="https://github.com/user-attachments/assets/750ea151-9c00-415d-b908-885f634da065" />
<img width="1680" height="1050" alt="SYSTEM18" src="https://github.com/user-attachments/assets/5f5f9642-9e33-4e29-bb14-bd3bc60943de" />
