RecipeHub (PHP + Vanilla JS)
available at https://recipetest.lovestoblog.com/
A clean, responsive recipe website with user accounts, likes, categories, pagination, print-friendly views, and an admin panel. Built with PHP (sessions + MySQLi) on the backend and vanilla ES modules on the frontend.

Features

Public pages

Home “featured” recipe hero

Most Popular, All Recipes, Categories 

Individual recipe page (image,title, description, ingredients, instructions)

Print-friendly layout (hides buttons/icons, tidy typography)

Auth & Profile

Registration + Login

Profile sections: Overview, My Recipes, Favourites, Settings, Change password,Admin panel

Like/Unlike recipes

Add/Edit/Delete your own recipes

Image upload with type/size validation (JPG/PNG, max 5 MB)

Accessible inline form validation (error messages, aria attributes)

Admin

Users table with pagination

Promote/demote user ↔ admin

CSRF token on sensitive actions

UX

Mobile-first responsive design

“Burger” menu on small screens

Consistent card grid, pagination with Prev / Next, page number, and jump-to-page

 Tech Stack

Backend: PHP 8+, MySQL (mysqli), sessions, cookies

Frontend: Vanilla JS (ES modules), Fetch API

Styling: CSS 

Icons: Font Awesome 6 (CDN)

Folder names reflect how the site routes (e.g., /mostPopular/mostPopular.php).

Setup
1) Requirements

PHP 8.0+

MySQL/MariaDB

Web server (Apache/Nginx) or PHP built-in server

uploads/ directory must be writable by the web server

2) Database

Create a database and import your schema (tables such as users, recipes, likes).

3) Configure DB connection

Edit db.php with your credentials (example):

<?php
$host = '127.0.0.1';
$user = 'root';
$pass = 'password';
$db   = 'recipes';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die('DB connection failed: ' . $conn->connect_error); }

5) Run locally

Using the PHP built-in server from the project root:

php -S localhost:8000

Open: http://localhost:8000/main/index.php

If using Apache/Nginx, point the docroot to the project root (or adjust paths accordingly).

 Security Notes

Auth via session + auth_token cookie verification.

CSRF tokens on admin and sensitive actions.

File uploads:

Server-side MIME check with finfo (image/jpeg, image/png)

Max size 5MB

Unique file names (uniqid('recipe_', true))

 Frontend Modules (key ones)

utils/recipeHelper.js

escapeHtml(text) — XSS-safe text rendering

renderPagination(container, total, currentPage, limit, onPageChange) — Prev/Next + page indicator + jump-to-page

likes/likeClicker.js

Attaches like/unlike handlers (updates heart icon + count)

utils/deleHandler.js

Confirmation + delete via fetch; callback to refresh the list

Pages fetch JSON from endpoints like:

/mostPopular/loadPopular.php?page=1

/profile/posts/post.php?page=1

/profile/favourites/loadFavourites.php?page=1

/profile/admin/list.php?page=1&limit=10

/profile/admin/update_status.php

/AddRecipe/add_recipe.php

Endpoints return {"status":"success", ...} or an error with message where applicable.

 UI/UX Details

Responsive

Grids collapse to a single column under ~900px

Burger menu for small screens (/burger-toggle.js)

Pagination

Buttons + page indicator + direct jump to N

Print

@media print across listing and recipe pages

Hides interactive elements (like buttons/icons)

Switches grid to a simple document flow so pages don’t break awkwardly


Usage Flow

Register / Login

Add a Recipe

Title, description, category, ingredients (validated), instructions

Upload image (JPG/PNG ≤ 5MB)

Browse

All / Categories / Most Popular

Like recipes, open details

Profile

Overview, My Recipes (edit/delete), Favourites

Settings (username/gender), Change Password

Admin (if admin)

User list with pagination

Update user roles

 Documentation Page

There’s a public Documentation / FAQ page at /documentation/faq.php where you can link:

Generated source docs

Full project task / requirements

End-user guide (CZ/EN)

Developer notes

Add more sections as needed.

 Deployment Checklist

Set correct db.php credentials on the server

Ensure /uploads is writable and not executable

Serve over HTTPS, set secure cookies if possible

Cache static assets (CSS/JS/images) via your web server
