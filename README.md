# Tamis

Tamis is a responsive recipe-sharing website for Filipino home cooks in a barangay community. Members can publish recipes, browse and search the collection, comment, edit their own submissions, and save favorites without reloading the page.

## Features

- Registration, login, logout, password hashing, and native PHP sessions.
- Recipe CRUD with ownership checks and transactional ingredient updates.
- Separate ingredient rows with dynamic add/remove inputs using vanilla JavaScript.
- Search by recipe title or description and category filtering.
- Recipe details with preserved cooking-instruction line breaks.
- Comment create, edit, delete, ownership validation, and edited markers.
- Asynchronous favorite add/remove through Fetch API and JSON.
- Comment counts and a three-item Popular Recipes section.
- Session-based CSRF protection on forms and the favorite endpoint.
- Responsive HTML5/CSS3 interface with no framework or build step.

## Technology Stack

Plain PHP 8+, MySQL/MariaDB, PDO, native sessions, HTML5, CSS3, and vanilla JavaScript.

## Folder Structure

```text
config/       Database connection and application bootstrap
classes/      Database, User, Category, Recipe, Comment, and Favorite classes
includes/     Auth guard, helpers, shared layout, recipe cards/forms
api/          JSON favorite endpoint
assets/       Responsive CSS and JavaScript
*.php         Traditional page handlers and views
database.sql  Six-table setup schema and category seed data
recipe-site.sql  Genuine phpMyAdmin export
```

## Database Relationships

- `users` create `recipes`, write `comments`, and save `favorites`.
- `categories` classify recipes and use `ON DELETE RESTRICT`.
- `recipes` own `ingredients` and `comments`; dependent records cascade on recipe deletion.
- `favorites` uses the composite primary key `(user_id, recipe_id)`.

Exactly six application tables are created: `users`, `categories`, `recipes`, `ingredients`, `comments`, and `favorites`.

## Local Setup

1. Install XAMPP and start Apache and MySQL.
2. Open [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
3. Import `database.sql`. It creates the `recipe_site` database, tables, Philippine timezone setting, and starter categories.
4. Open [http://localhost/MidtermMilestoneProj-Ang/](http://localhost/MidtermMilestoneProj-Ang/).
5. Register a member account, log in, and begin sharing recipes.

The default local connection is `localhost`, database `recipe_site`, user `root`, and an empty password. Update `classes/Database.php` only if your local XAMPP credentials differ. Production deployments should use HTTPS and set the session cookie `secure` option to `true`.

## Testing Checklist

Test registration, duplicate email rejection, login/logout, protected-page redirects, recipe creation/edit/delete, ingredient persistence, combined search and category filtering, comment ownership, favorite toggling without reload, CSRF rejection, and ownership with two separate accounts. PHP syntax can be checked with:

```text
C:\xampp\php\php.exe -l path\to\file.php
```

## Database Export Requirement

`recipe-site.sql` is the genuine phpMyAdmin export supplied for this project. It contains the final six-table schema and sample records. For a fresh schema-only setup, use `database.sql` instead.

1. Open `http://localhost/phpmyadmin/`.
2. Select `recipe_site`.
3. Choose **Export**.
4. Select **Quick** and **SQL**.
5. Click **Export** and save the file as `recipe-site.sql` in this project root.
6. Verify that the export contains the same six tables and foreign keys.

No GitHub repository is changed automatically by this project.
