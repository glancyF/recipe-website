🍲 RecipeHub

RecipeHub is a clean, responsive recipe website with user accounts, likes, categories, pagination, print-friendly views, and an admin panel.

✨ Built with PHP (sessions + MySQLi) on the backend and Vanilla JS (ES modules) on the frontend.

🚀 Features
🌐 Public Pages

Home with “Featured Recipe” hero

Most Popular, All Recipes, Categories

Individual recipe page (image, title, description, ingredients, instructions)

Print-friendly layout (hides buttons/icons, tidy typography)

👤 Authentication & Profile

Registration & Login

Profile sections: Overview, My Recipes, Favourites, Settings, Change Password, Admin Panel

Like/Unlike recipes

Add, Edit, Delete your own recipes

Image upload with type/size validation (JPG/PNG, max 5 MB)

Accessible inline form validation (error messages, aria attributes)

🛠 Admin

User management with pagination

Promote/demote users ↔ admin

CSRF token protection on sensitive actions

📱 UX & Design

Mobile-first responsive design

Burger menu on small screens

Consistent recipe card grid

Pagination with Prev / Next, page number, and jump-to-page

🧰 Tech Stack

Backend: PHP 8+, MySQL (MySQLi), sessions, cookies

Frontend: Vanilla JavaScript (ES Modules), Fetch API

Styling: CSS

Icons: Font Awesome 6 (CDN)

📂 Folder names reflect routing structure (e.g., /mostPopular/mostPopular.php).

⚙️ Setup
Requirements

PHP 8.0+

MySQL/MariaDB

Web server (Apache/Nginx) or PHP built-in server

uploads/ directory must be writable

Database

Create a database and import schema (tables: users, recipes, likes).

Configure DB connection in db.php:

$conn = new mysqli('localhost', 'username', 'password', 'dbname');
if ($conn->connect_error) {
    die('DB connection failed: ' . $conn->connect_error);
}

Run locally
php -S localhost:8000


Open: http://localhost:8000/main/index.php

For Apache/Nginx, point docroot to project root (adjust paths if needed).

🔒 Security

Session + auth_token cookie verification

CSRF tokens on admin/sensitive actions

File uploads:

MIME check via finfo (image/jpeg, image/png)

Max size: 5 MB

Unique file names via uniqid('recipe_', true)

🖥 UI / UX Details

Responsive grids collapse to 1 column < 900px

Burger menu via /burger-toggle.js

Pagination: buttons + page indicator + jump-to-page

Print mode (@media print):

Hides interactive elements

Converts grids to simple flow to avoid page breaks

📚 Usage Flow

Register / Login

Add a Recipe (title, description, category, ingredients, instructions, image)

Browse All / Categories / Most Popular

Like recipes, open details

Profile → Overview, My Recipes (edit/delete), Favourites, Settings, Change Password

Admin (if admin): user list + role updates

📖 Documentation

Available at: /documentation/documentation.php
Includes:

Generated source docs

Full project tasks / requirements

End-user guide (CZ/EN)

Developer notes

✅ Deployment Checklist

Set correct db.php credentials on server

Ensure /uploads is writable and not executable

Serve over HTTPS (set secure cookies if possible)

Cache static assets (CSS/JS/images) on web server
