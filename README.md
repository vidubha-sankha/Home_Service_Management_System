# Home Service Management System

A comprehensive web-based platform designed to manage and book home services efficiently. This system connects customers with skilled technicians while providing administrators with a robust dashboard to manage users, services, and bookings.

## 🚀 Features

### 👤 Customer Portal
- User registration and profile management.
- Browse available home services (e.g., Plumbing, Electrical, Cleaning).
- Book services, select preferred dates, and track booking status.
- Receive notifications on service updates.

### 🛠️ Technician Portal
- Manage profile and view assigned service requests.
- Track new, pending, and completed tasks.
- Update service status upon completion.

### 👑 Admin Dashboard
- Centralized dashboard with key metrics and analytics.
- Manage all users (Customers, Technicians, and other Admins).
- Manage service categories and pricing.
- Monitor all system bookings, assignments, and transactions.

## 🛠️ Technology Stack
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (Core)
- **Database:** MySQL

## ⚙️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/vidubha-sankha/Home_Service_Management_System.git
   ```

2. **Database Configuration:**
   - Create a new MySQL database (e.g., `home_service_db`).
   - Import the database schema from the `sql/` directory.
   - Copy the `.env.example` file to `.env` and update it with your database credentials.

3. **Run the Application:**
   - Move the project folder to your local web server's root directory (e.g., `htdocs` for XAMPP or `www` for WAMP).
   - Open your browser and navigate to `http://localhost/Home_Service_Management_System` (or your configured local URL).

## 📁 Project Structure
- `/admin` - Administrator dashboard and management scripts
- `/customer` - Customer interface and booking scripts
- `/technician` - Technician dashboard for managing tasks
- `/config` - Database and environment configurations
- `/includes` - Reusable PHP components and templates
- `/assets` - CSS, JavaScript, and image files
- `/sql` - Database schema files

## 📜 License
This project is open-source.
