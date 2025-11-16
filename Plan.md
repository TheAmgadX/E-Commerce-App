# 🚀 The 12-Day Roadmap to Graduation  
Aggressive but executable delivery plan for your Laravel E-Commerce project.

---

## **Phase 1: Foundations**  
**Day 1–2 | Nov 15–16**  
**Objective:** Infrastructure, security, RBAC.

### **Tasks**
- Initialize project  
  - `composer create-project laravel/laravel project-name`
- Configure `.env`
- Run migrations  
  - `php artisan migrate`
- Install Admin Panel  
  - `composer require laravel/breeze --dev`  
  - `php artisan breeze:install` (Blade)  
  - `npm install && npm run build`
- Install Sanctum  
  - `composer require laravel/sanctum`  
  - Publish + migrate
- Install Roles & Permissions  
  - `composer require spatie/laravel-permission`  
  - Publish + migrate  
- Setup Roles  
  - Add **HasRoles** to `User`  
  - Create `UserSeeder.php`  
  - Create roles: user/admin/super-admin  
  - Create Super Admin and assign role  
  - Run seeder
- Push to GitHub

---

## **Phase 2: API – Core Auth & User Flow**  
**Day 3–4 | Nov 17–18**  
**Objective:** Customer API for auth + profile.

### **Tasks**
- Define API routes  
  - `/register`, `/login`  
  - Protected group: `/profile`, `/orders`, `/logout`
- Controllers  
  - Register: create user + assign role + welcome mail  
  - Login: token via Sanctum  
- Mailing  
  - `WelcomeMail`  
  - `OrderStatusUpdateMail`
- Password Reset (API adaptation)
- Profile controller (show/update)
- Write PHPUnit API tests

---

## **Phase 3: Admin Panel – Core CRUD**  
**Day 5–7 | Nov 19–21**  
**Objective:** Web dashboard for admin operations.

### **Tasks**
- Admin route group (`/admin`, auth + role middleware)
- **User Management** (super-admin only)
  - List + filter  
  - Add + set role  
  - Activate/deactivate  
  - Delete  
- **Category Management**
  - CRUD  
  - Prevent delete if category has products
- **Product Management**
  - CRUD  
  - Photo upload via Storage  
  - Prevent delete if product is linked to orders
- Blade views for admin panel

---

## **Phase 4: API – E-Commerce Flow**  
**Day 8–9 | Nov 22–23**  
**Objective:** Public browsing + cart + checkout.

### **Tasks**
- Public API  
  - `/categories`  
  - `/products` + filters  
  - `/products/{id}`
- Cart API  
  - List, add, update qty, delete
- Orders API  
  - Place order (COD)  
  - List my orders
- Order creation logic  
  - Use DB transactions  
  - Move from cart → order items  
  - Empty cart
- Use API Resources for all responses

---

## **Phase 5: Admin – Order Management & Mails**  
**Day 10 | Nov 24**  
**Objective:** Full order lifecycle in dashboard.

### **Tasks**
- `/admin/orders` routes  
  - List + filter  
  - Show  
  - Update tracking + comment
- `OrderController` update method  
  - Update status, add comment  
  - Trigger `OrderStatusUpdateMail`
- Validate via manual test flow

---

## **Phase 6: Docume**
