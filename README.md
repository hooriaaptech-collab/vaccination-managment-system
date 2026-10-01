# E-VACCINATION MANAGEMENT SYSTEM

A College-Level PHP + MySQL Web Application for Child Immunization Scheduling, Clinic Appointment Booking, Administrative Auditing, and Digital Vaccination Certification.

---

## 🚀 How to Run in XAMPP

1. **Start Apache & MySQL** in the **XAMPP Control Panel**.
2. **Open phpMyAdmin**: Open your browser at `http://localhost/phpmyadmin`
3. **Import Database**: Click the **Import** tab, choose `database/vaccination_system.sql`, and click **Go / Import**.
4. **Open Application**: Navigate to `http://localhost/09c/vcaccination-managment-system/`

---

## 🔑 Default Login Credentials

| Role | Email | Password | Access URL |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin@evaccine.com` | `Admin@123` | `admin/dashboard.php` |
| **Parent Account** | `sarah.jenkins@gmail.com` | `Parent@123` | `parent/dashboard.php` |
| **Hospital Staff** | `contact@citygeneral.org` | `Hospital@123` | `hospital/dashboard.php` |

*(Quick 1-click credentials filler is also available directly on the login page!)*

---

## 🎨 Technology & Design Theme

- **Backend**: Pure PHP 8.x with PDO & Prepared Statements
- **Database**: MySQL (`vaccination_management_system`)
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5.3, Bootstrap Icons
- **Visual Identity**: Modern Deep Emerald / Teal (`#0A5C4E`, `#0F766E`, `#14B8A6`) with Off-White (`#F8FAF8`), Dark Charcoal (`#1E293B`), and Warm Coral accents (`#F97316`).
- **3D Effects**: Layered elevation cards, soft shadows, hover transitions, and glassmorphism.

---

## 📂 Project Structure

- `index.php`, `about.php`, `vaccines.php`, `hospitals.php`, `vaccination-schedule.php`, `how-it-works.php`, `contact.php` (Public Web Portal)
- `login.php`, `register.php`, `logout.php` (Authentication System)
- `config/database.php` (Database Connection)
- `includes/` (`header.php`, `navbar.php`, `footer.php`, `sidebar.php`, `topbar.php`, `auth.php`, `functions.php`)
- `admin/` (System Administration Portal)
- `parent/` (Parent Child Health & Booking Portal)
- `hospital/` (Hospital Clinic & Administration Portal)
- `database/vaccination_system.sql` (Database Dump & Seed Data)

