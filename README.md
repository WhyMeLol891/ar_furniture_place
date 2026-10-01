# Web-Based AR Furniture Catalog & Visualization System

A high-performance, web-based 3D Spatial Furniture Catalog and Augmented Reality (AR) visualization platform. Built using modern PHP 8+, MySQL (PDO), Bootstrap 5, and Google's `<model-viewer>` WebXR component.

This system allows desktop and mobile users to explore photorealistic 3D furniture models, inspect real-world physical dimensions, and project life-sized (1:1 scale) furniture directly into their physical living spaces using smartphone cameras without installing third-party native apps.

---

## 📁 Complete Project Structure

```
ar_furniture_place/
│
├── .htaccess                      # Root Apache rules: MIME types for .glb/.usdz & CORS headers
├── index.php                      # Public Storefront & 3D Furniture Catalog
├── product.php                    # Product Details & Interactive 3D / AR Spatial Viewport
├── README.md                      # Complete system documentation & deployment guide
│
├── admin/                         # Admin Management Portal
│   ├── index.php                  # Product inventory dashboard & statistics
│   ├── login.php                  # Secure admin authentication
│   ├── logout.php                 # Session destruction & cookie cleanup
│   ├── product_edit.php           # Add & Edit Product controller with 3D/image uploads
│   ├── product_delete.php         # Product deletion handler with file cleanup & CSRF
│   ├── categories.php             # Category management & linked product counter
│   ├── category_edit.php          # Add & Edit Category controller
│   └── category_delete.php        # Category deletion handler with CSRF protection
│
├── api/                           # RESTful JSON API
│   └── products.php               # Public API endpoint for products, dimensions, & 3D models
│
├── config/                        # Application Configuration
│   ├── .htaccess                  # Access restriction (Require all denied)
│   └── config.php                 # Database credentials, constants, and dynamic Base URL
│
├── css/                           # Stylesheets
│   ├── style.css                  # Custom modern design, cards, badges & 3D viewer styles
│   └── styles.css                 # Import alias ensuring zero broken stylesheet links
│
├── includes/                      # Backend Core Business Logic
│   ├── admin_layout.php           # Shared Admin Header/Footer UI & flash messaging
│   ├── auth.php                   # Authentication, session security & CSRF tokens
│   ├── categories.php             # Category database queries & CRUD operations
│   ├── config.php                 # Forwarding shim for backward compatibility
│   ├── db.php                     # PDO MySQL database singleton connection
│   └── products.php               # Product queries, dimensions & secure file uploads
│
├── js/                            # Client-side Scripts
│   ├── app.js                     # 3D viewport controls, auto-rotation & AR listeners
│   └── qr.js                      # Dynamic QR code generator for desktop-to-mobile AR handoff
│
├── models/                        # 3D Asset Storage
│   ├── .htaccess                  # Script execution restriction for security
│   ├── sample_chair.glb           # Bundled 3D GLB Nordic Accent Lounge Chair model
│   └── sample_lamp.glb            # Bundled 3D GLB Industrial Brass Lantern Lamp model
│
├── sql/                           # Database Schema & Data
│   └── database.sql               # Clean schema, tables, verified admin hash & initial seeds
│
└── uploads/                       # Product Image & Media Storage
    ├── .htaccess                  # Script execution restriction for security
    └── thumbs/                    # Product thumbnail images
        ├── sample_chair.jpg
        ├── sample_lamp.jpg
        ├── sample_table.jpg
        └── sample_sofa.jpg
```

---

## 🗄️ Database Structure (`sql/database.sql`)

The database utilizes InnoDB with `utf8mb4` character encoding and foreign key constraints:

1. **`admins`**: Stores administrative credentials with bcrypt password hashing (`password_hash`).
2. **`categories`**: Stores product categories, URL slugs, and display sort priorities.
3. **`products`**: Stores furniture specs, pricing, physical dimensions (cm and meters), 3D GLB/USDZ file paths, thumbnail paths, and public visibility flags (`is_active`).

---

## 🚀 Installation & Setup Instructions

### Option A: Local Development on XAMPP (Windows / macOS)

1. **Copy Project Folder**:
   - Ensure the project directory is placed in your XAMPP web root:
     `C:\xampp\htdocs\ar_furniture_place`
2. **Start Apache & MySQL**:
   - Launch the **XAMPP Control Panel** and start both **Apache** and **MySQL**.
3. **Import Database**:
   - Open your browser and go to **phpMyAdmin**: `http://localhost/phpmyadmin/`
   - Create a new database named `ar_furniture`.
   - Click the **Import** tab, choose `sql/database.sql` from the project folder, and click **Import**.
4. **Verify Database Configuration**:
   - Open `config/config.php` and confirm:
     ```php
     define('DB_HOST', '127.0.0.1');
     define('DB_NAME', 'ar_furniture');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```
5. **Access the Application**:
   - **Public Storefront**: `http://localhost/ar_furniture_place/`
   - **Admin Portal**: `http://localhost/ar_furniture_place/admin/`
   - **REST API**: `http://localhost/ar_furniture_place/api/products.php`

---

### Option B: cPanel / Live Web Hosting Deployment

1. **Create MySQL Database & User**:
   - In cPanel, navigate to **MySQL® Databases**.
   - Create a database (e.g., `user_ar_furniture`).
   - Create a user with a strong password and assign **ALL PRIVILEGES** to the database.
2. **Import Database Schema**:
   - Open **phpMyAdmin** from cPanel, select your database, and import `sql/database.sql`.
3. **Upload Files**:
   - Compress the project files and upload to `public_html/` (or a subdomain folder) using **cPanel File Manager** or FTP.
   - Extract the files.
4. **Update `config/config.php`**:
   - Set `DB_NAME`, `DB_USER`, and `DB_PASS` to match your cPanel credentials.
5. **HTTPS & MIME Types**:
   - Ensure SSL is active (HTTPS).
   - The included `.htaccess` automatically configures MIME types for `.glb` (`model/gltf-binary`) and `.usdz` (`model/vnd.usdz+zip`) and enables CORS.

---

## 🔑 Admin Login Credentials

| Attribute | Value |
| :--- | :--- |
| **Login URL** | `http://localhost/ar_furniture_place/admin/login.php` |
| **Username** | `admin` |
| **Password** | `adminpassword123` |

---

## ⚙️ Important Configuration Settings (`config/config.php`)

| Setting | Default Value | Description |
| :--- | :--- | :--- |
| `APP_NAME` | `AR Spatial Furniture` | System branding displayed in header, title, and footer |
| `APP_CURRENCY` | `RM` | Default currency prefix for product prices |
| `MAX_FILE_SIZE_MB` | `50` | Maximum upload size for 3D models and images (in MB) |
| `ALLOWED_MODEL_EXTS` | `['glb', 'gltf', 'usdz']` | Permitted 3D model file extensions |
| `ALLOWED_IMAGE_EXTS` | `['jpg', 'jpeg', 'png', 'webp']` | Permitted image upload extensions |
| `getBaseUrl()` | Dynamic function | Auto-resolves URL across XAMPP subdirectories and cPanel domains |

---

## 📱 How the Augmented Reality (AR) Feature Works

The AR furniture placement feature operates without requiring users to download an app from Google Play or Apple App Store:

```
                  ┌──────────────────────────────────────────────┐
                  │          Desktop User Browsing Item          │
                  └──────────────────────┬───────────────────────┘
                                         │
                               Scan Dynamic QR Code
                                         │
                                         ▼
                  ┌──────────────────────────────────────────────┐
                  │      Mobile Browser (Chrome / Safari)        │
                  │   Renders <model-viewer> 3D Viewport         │
                  └──────────────────────┬───────────────────────┘
                                         │
                            Tap "Place in Your Room"
                                         │
                    ┌────────────────────┴────────────────────┐
                    ▼                                         ▼
         [Android Devices]                            [Apple iOS Devices]
  WebXR / Google Scene Viewer                      Apple AR Quick Look (.usdz)
  Surface plane detection                          LiDAR / ARKit plane tracking
  1:1 True-to-scale placement                      1:1 True-to-scale placement
```

1. **3D WebGL Rendering**:
   - Google's `<model-viewer>` component renders the 3D `.glb` model directly inside standard WebGL-capable web browsers.
   - Users can orbit, pan, zoom, and inspect furniture textures and materials in 360 degrees.
2. **Android WebXR & Scene Viewer**:
   - On modern Android devices running Chrome, clicking **Place in Your Room** invokes the native WebXR device API / Google Scene Viewer.
   - The device camera detects floor planes and projects the 3D furniture model into the room at **1:1 physical scale** (`ar-scale="fixed"`).
3. **Apple iOS Quick Look**:
   - On iPhones and iPads running Safari, tapping the AR button triggers Apple's built-in **AR Quick Look** using the optional `.usdz` asset or automatic GLB conversion.
4. **Desktop-to-Mobile Handoff**:
   - When a user explores furniture on a desktop computer, `js/qr.js` dynamically generates a live QR code containing the exact product URL.
   - Scanning this QR code with a smartphone camera instantly opens the product page on the phone to start AR room placement.

---

## ✅ System Verification & Testing Checklist

- [x] **1. PHP Syntax Verification**: All PHP files checked with `php -l` — zero syntax errors.
- [x] **2. Database Queries**: All PDO prepared statements tested with proper parameter binding.
- [x] **3. Navigation & Links**: Dynamic `getBaseUrl()` resolves paths across all subfolders and domains.
- [x] **4. Forms & Validation**: Input sanitization, required field checks, and CSRF tokens on all POST requests.
- [x] **5. Authentication**: Secure session cookies, `password_verify` with bcrypt, and protected admin endpoints.
- [x] **6. File Uploads**: Extension whitelisting, file size validation, unique file renaming (`random_bytes`), and directory creation.
- [x] **7. Product & Category CRUD**: Full Add, Read, Update, and Delete operations with image/model asset cleanup.
- [x] **8. Responsive Design**: Fully responsive layout with Bootstrap 5 across desktop, tablet, and mobile screens.
- [x] **9. 3D Model Loading**: `<model-viewer>` embeds `.glb` models with interactive zoom, rotate, and reset camera controls.
- [x] **10. AR Configuration**: Configured with `ar`, `ar-modes="webxr scene-viewer quick-look"`, and `ar-scale="fixed"`.
- [x] **11. QR Code Handoff**: Dynamic client-side QR generation for smooth desktop-to-mobile AR handoff.
- [x] **12. XAMPP Compatibility**: Default root MySQL connection, subdirectory routing, and Windows path normalization.
- [x] **13. cPanel Compatibility**: Standard PDO MySQL, Apache `.htaccess` MIME types, and clean relative path handling.
- [x] **14. Security Protections**: Protection against SQL Injection, XSS (`sanitize`), CSRF, and execution prevention in `uploads/` and `models/`.