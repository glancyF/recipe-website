🍲 RecipeHub

A clean and responsive recipe website with user accounts, likes, categories, pagination, print-friendly views, and an admin panel.
Built with PHP (sessions + MySQLi) on the backend and Vanilla JS (ES modules) on the frontend.

🔗 Demo: RecipeHub Live

📦 Features

Home page with “Featured Recipe” hero

Browse: Most Popular, All Recipes, Categories

Individual recipe pages (image, title, description, ingredients, instructions)

Print-friendly layout (tidy typography, hides buttons/icons)

Registration & Login

Profile with sections: Overview, My Recipes, Favourites, Settings, Change Password, Admin Panel

Add, Edit, Delete your own recipes

Like/Unlike recipes

Image upload with validation (JPG/PNG, max 5 MB)

Inline form validation with error messages & aria attributes

Admin panel: user management, promote/demote users, CSRF protection

🚀 How to Run

Install PHP 8.0+, MySQL/MariaDB, and a web server (Apache/Nginx) or use PHP built-in server.

Make sure the uploads/ directory is writable by the web server.

Create a database and import the schema (tables: users, recipes, likes).

Configure your database connection in db.php:

$conn = new mysqli('localhost', 'username', 'password', 'dbname');
if ($conn->connect_error) {
    die('DB connection failed: ' . $conn->connect_error);
}


Run locally:

php -S localhost:8000


Open http://localhost:8000/main/index.php in your browser.

🎨 Styling

Mobile-first responsive design

Burger menu on small screens

Consistent card grid layout

Pagination with Prev / Next, page numbers, and jump-to-page

Print-friendly pages using @media print

⚠️ Limitations

Note: Free API-like functionality is not used here, but:

File uploads are limited to 5 MB and only JPG/PNG.

Secure cookies and HTTPS are recommended for deployment.

🛠️ Technologies Used

Backend: PHP 8+, MySQL (MySQLi), Sessions, Cookies

Frontend: Vanilla JS (ES modules), Fetch API

Styling: CSS, Font Awesome 6 (CDN)

Security: CSRF tokens, MIME type checks, unique filenames

📖 Documentation

See: /documentation/documentation.php
Includes:

Project requirements

End-user guide (CZ/EN)

Developer notes

✅ Deployment Checklist

Correct db.php credentials set

/uploads directory writable & not executable

HTTPS enabled + secure cookies

Cache static assets (CSS/JS/images) via server

👨‍💻 Author

Valentyn Deshel – GitHub
