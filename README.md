# Smart Hostel Management System

Semester project for Software Construction and Development.
Name: ABDUL RAFFAY  Registration No: FA-24-BSSE-003

## Technologies
HTML, CSS, JavaScript, PHP, MySQL (XAMPP)

## Features
- Student, Warden and Mess Staff roles with login and validated registration
- Room management and room allocation
- Meal booking and cancelling with cut-off time and live countdown
- Monthly bill, complaints, reports with CSV export, meal rate and cut-off settings
- Mess staff daily meal count

## Design patterns and concepts
- Singleton: classes/Database.php (one database connection)
- Factory: classes/UserFactory.php (creates Student, Warden or MessStaff by role)
- Thread: countdown_worker.js (Web Worker for the meal cut-off countdown)
- Lock: SELECT ... FOR UPDATE when allocating rooms (prevents race conditions)

## How to run
. Start Apache and MySQL in XAMPP.
. Open http://localhost/smart_hostel/login.php# Smart Hostel Management System



