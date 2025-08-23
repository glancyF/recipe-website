# RecipeHub

A clean and responsive recipe website with user accounts, likes, categories, pagination, print-friendly views, and an admin panel.  
Built with **Vanilla PHP (sessions + MySQLi)** on the backend and **Vanilla JS (ES modules)** on the frontend.

Demo: [RecipeHub Live](https://recipetest.lovestoblog.com/)

---


## Table of Contents
- [Features](#-features)
- [How to Run](#-how-to-run)
- [Styling](#-styling)
- [Limitations](#️-limitations)
- [Technologies Used](#️-technologies-used)
- [Documentation](#-documentation)
- [Deployment Checklist](#-deployment-checklist)
- [Author](#-author)

---

## Features
- Home page with “Featured Recipe” hero
- Browse: Most Popular, All Recipes, Categories
- Individual recipe pages (image, title, description, ingredients, instructions)
- Print-friendly layout (tidy typography, hides buttons/icons)
- Registration & Login
- Profile with sections: Overview, My Recipes, Favourites, Settings, Change Password, Admin Panel
- Add, Edit, Delete your own recipes
- Like/Unlike recipes
- Image upload with validation (JPG/PNG, max 5 MB)
- Accessible inline form validation (error messages, ARIA attributes)
- Admin panel: user management, promote/demote users, CSRF protection

---

## How to Run

1. Install **PHP 8.0+**, **MySQL/MariaDB**, and a web server (Apache/Nginx) or use the PHP built-in server.
2. Ensure the `uploads/` directory is writable by the web server.
3. Create a database and import the schema (tables: `users`, `recipes`, `likes`).
4. Configure your database connection in `db.php`:

   ```php
   <?php
   $conn = new mysqli('localhost', 'username', 'password', 'dbname');
   if ($conn->connect_error) {
       die('DB connection failed: ' . $conn->connect_error);
   }
   ?>
   ```

5. Run locally:

   ```bash
   php -S localhost:8000
   ```

6. Open http://localhost:8000/index.php in your browser.

---

## Styling
- Mobile-first responsive design
- Burger menu on small screens
- Consistent card grid layout
- Pagination with Prev / Next, page numbers, and jump-to-page
- Print-friendly pages using `@media print`

---

## Limitations
**Note:**
- File uploads are limited to 5 MB and only JPG/PNG.
- Secure cookies and HTTPS are strongly recommended for deployment.

---

## Technologies Used
- **Backend:** PHP 8+, MySQL (MySQLi), Sessions, Cookies
- **Frontend:** Vanilla JS (ES modules), Fetch API
- **Styling:** CSS, Font Awesome 6 (CDN)
- **Security:** CSRF tokens, MIME type checks (`finfo` for `image/jpeg`, `image/png`), unique filenames via `uniqid('recipe_', true)`

---

## Documentation
Available at: `/documentation/documentation.php`  
Includes:
   (CZ/EN)
- Project requirements
- End-user guide
- Developer notes

---

## Deployment Checklist
- Correct `db.php` credentials set
- `/uploads` directory writable & not executable
- Serve over **HTTPS** and set secure cookies if possible
- Cache static assets (CSS/JS/images) via your web server

---

## Author
**Valentyn Deshel** — [GitHub](https://github.com/glancyF)
