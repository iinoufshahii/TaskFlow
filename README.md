# TaskFlow

> Simple PHP task manager (school project) — manage tasks, notes, archives, calendar and timer.

## Features
- User registration and login
- Add, edit, delete and archive tasks
- Notes on dashboard
- Task filtering, sorting and search
- Notifications and timer pages (UI present)
- Responsive front-end using CSS and Font Awesome

## Quick Start (Local)
Requirements: PHP (7.4+), MySQL/MariaDB, a web server (Apache via XAMPP/LAMP/WAMP)

1. Clone or copy the project into your web server document root (e.g. `htdocs/TaskFlow`).
2. Import or create the database:

   - Default database connection is configured in [TaskFlowDB.php](TaskFlowDB.php). Update credentials if needed.

   - Create database and tables (example SQL):

```sql
CREATE DATABASE taskflow;
USE taskflow;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_name VARCHAR(255),
  category VARCHAR(100),
  due_date DATE,
  priority VARCHAR(20),
  status VARCHAR(50),
  description TEXT,
  users_id INT,
  FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE archives LIKE tasks;

CREATE TABLE notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  note_text TEXT,
  users_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE
);
```

3. Ensure `TaskFlowDB.php` has correct DB credentials (default: `localhost`, `root`, `1234`, `taskflow`).
4. Start your web server and open the app in a browser, e.g. `http://localhost/TaskFlow/`.

## Important Files
- [TaskFlowDB.php](TaskFlowDB.php): database connection
- [dashboard.php](dashboard.php), [Tasks.php](Tasks.php), [AddTask.php](AddTask.php): main app pages
- CSS files: `*.css` for styling
- `images/` contains logos and assets

## Notes & Next Steps
- Add a `.gitignore` to exclude local configs and credentials.
- A database schema is included in [schema.sql](schema.sql) for easy setup/import.
- Consider adding an example environment file (e.g. `.env.example`) and a `schema.sql` import guide.
- Improve security: validate/sanitize inputs, use prepared statements consistently, add CSRF protection and input rate-limiting.

## Contributing
Contributions are welcome — thank you!

- Fork the repo and create a feature branch: `git checkout -b feature/my-change`.
- Run the app locally and make your changes.
- Keep changes focused and add tests where appropriate.
- Commit with clear messages and open a pull request to `main`.

To import the included schema locally:

```bash
mysql -u root -p < schema.sql
```

Then update database credentials in [TaskFlowDB.php](TaskFlowDB.php) as needed.

---
Made with ❤️ by Nouf — Fictional university project.
