# BrightStart

# 📚 Learning Management System (LMS)

This is a PHP & MySQL-based Learning Management System that allows instructors to upload courses, modules, and lessons (with videos), and enables students to view lessons and track their progress.

---

## 🚀 Features

- User authentication via email sessions.
- Admin panel to:
  - Create modules
  - Add multiple courses under a module
  - Upload lessons with video content and attachments
- Video uploads stored directly in the database as BLOBs.
- Students can:
  - View modules, courses, and lessons
  - Automatically track lesson progress
  - View progress per course and across the system
- Progress is updated automatically when a video is accessed.

---

## 🛠️ Setup Instructions

1. **Clone or download** this repository.

2. **Set up the database:**
   - Import the SQL schema from `brightstart.sql` or run the SQL manually in phpMyAdmin or any MySQL client.

3. **Database Configuration:**
   - Update your `db.php` file with your database credentials:
     ```php
     $pdo = new PDO("mysql:host=localhost;dbname=lms_db", "username", "password");
     ```

4. **Enable File Uploads:**
   - Make sure PHP's `post_max_size` and `upload_max_filesize` in `php.ini` are configured to allow large video files.

---

## ⚙️ Temporary Video Handling

Videos are stored as BLOBs and streamed to the browser using temporary files. The system:

- Generates a unique filename using `uniqid()`.
- Streams the file using `readfile()`.
- Cleans up the temporary file using `unlink()` or `register_shutdown_function()`.

### 🧹 Optional Cron Job for Cleanup

To automatically delete leftover temp files:

```bash
# Run every hour and delete temp files older than 1 hour
0 * * * * find /path/to/temp -type f -mmin +60 -delete
```

---

## 📊 Database Schema Highlights

- `users`: Stores user details
- `modules`: Group of courses
- `courses`: Belong to modules
- `lessons`: Belong to courses
- `progress`: Tracks each student's progress per lesson

---

## 👨‍💻 Author

Built by Josephine Ababio Nyankom, Emmanuel Opoku, Frank Kodi, Samuel Amoah