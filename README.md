# 🛒 Laravel E-Commerce API & Admin Dashboard

A robust, dual-interface E-Commerce application built with Laravel.  
It features a secure RESTful API for customer applications (Mobile/Frontend) and a modern MVC Admin Dashboard for store management.

---

## 🚀 Features

### 📱 Customer API (RESTful)

Built for mobile and frontend consumption, secured with Laravel Sanctum.

-   **Authentication**
    -   Register, Login, Logout
    -   Password Reset (OTP via Email)
-   **Profile**
    -   Manage personal info, Change password, Manage address book
-   **Products**
    -   Advanced filtering: Category, Price Range, Search
    -   View product details
-   **Shopping Cart**
    -   Add to cart, Update quantities, Remove items
-   **Orders**
    -   Place orders (COD), View order history, Cancel pending orders
-   **Documentation**
    -   Full interactive API docs using Swagger/OpenAPI

### 💻 Admin Dashboard (MVC)

A responsive, SaaS-style control panel built with Blade, Tailwind CSS, and Alpine.js.

-   **Dashboard**
    -   Real-time statistics: Revenue, Total Orders, Active Users
    -   Recent activity
-   **Role-Based Access Control**
    -   **Super Admin:** Full access + User Management (Promote/Demote admins, Toggle Activation)
    -   **Admin:** Manage catalog and orders
-   **Catalog Management**
    -   Full CRUD for Products (with image upload)
    -   Manage Categories
-   **Order Management**
    -   View order details
    -   Update status: Pending → Shipped → Delivered
    -   Automatic email notifications to customers on status changes

---

## 🛠️ Tech Stack

-   **Framework:** Laravel 12 (Latest)
-   **Database:** MySQL
-   **Frontend (Admin):** Blade Templates, Tailwind CSS (v4), Alpine.js, Vite
-   **API Auth:** Laravel Sanctum
-   **Permissions:** Spatie Laravel Permission
-   **Documentation:** L5-Swagger (OpenAPI)
-   **Mail:** SMTP (Gmail/Mailtrap) with Markdown Mailables
-   **Queue:** Database Queue

---

## ⚙️ Installation

Follow these steps to set up the project locally.

### 1. Clone the Repository

```bash
git clone <https://github.com/TheAmgadX/E-Commerce-App>
cd <E-Commerce-App>
```

### 2. Install Dependencies

```bash
# Backend dependencies
composer install

# Frontend dependencies
npm install
npm run build
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure your Database and Email settings:

```env
DB_DATABASE=e_commerce_app
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration (Required for OTP & Notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=ssl
```

### 4. Database Setup & Seeding

This command will create all tables and populate them with dummy data (Products, Categories, Users).

```bash
php artisan migrate:fresh --seed
```

> **Note:** The seeder automatically creates a Super Admin, Admin, and dummy Customers.

### 5. Generate API Documentation

```bash
php artisan l5-swagger:generate
```

### 6. Storage Link

Link the public storage folder to serve product images.

```bash
php artisan storage:link
```

### 7. Run the Application

You can use the convenience script to run everything (Server, Queue, Vite, Logs) in one terminal:

```bash
composer run dev
```

**Or** run them separately in different terminals:

```bash
# Terminal 1: Server
php artisan serve

# Terminal 2: Queue Worker
php artisan queue:work

# Terminal 3: Vite (for assets)
npm run dev
```

---

## 📖 Usage

### Admin Dashboard

Visit: `http://127.0.0.1:8000/login`

**Default Credentials (from Seeder):**

-   **Super Admin:** `admin@example.com` / `password`
-   **Admin:** Check the `users` table for generated admin email / `password`

### API Documentation

Visit: `http://127.0.0.1:8000/api/documentation`

You can test all endpoints directly from the browser using the Swagger UI.

1.  Use the **Login** endpoint to get a Bearer Token.
2.  Click **Authorize** at the top of the docs and paste the token: `Bearer <your-token>`
3.  Now you can test protected routes like `POST /api/orders`.

---

## 📂 Project Structure

```
app/Http/Controllers/Api       # RESTful controllers for the customer app
app/Http/Controllers/Admin     # MVC controllers for the dashboard
app/Services                   # Business logic layer
app/Models                     # Eloquent models
resources/views/admin          # Blade templates for the dashboard
```

---

## 🛡️ Security

-   **Sanctum:** Token-based authentication for API
-   **Spatie Permissions:** Role-based middleware protects admin routes
-   **Validation:** Strict FormRequests and Controller validation rules
-   **Transaction Safety:** DB Transactions used for critical operations
