<?php
// Database connection + shared helpers
session_start();
date_default_timezone_set("Asia/Karachi");

$conn = new mysqli("localhost", "root", "", "smart_hostel");   // XAMPP defaults
if ($conn->connect_error) { die("Database connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

const MEALS = ["Breakfast", "Lunch", "Dinner"];

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }
function flash($msg, $type = "success") { $_SESSION["flash"] = [$msg, $type]; }

function home_for($role) {
    return ["Student" => "student_dashboard.php", "Warden" => "warden_rooms.php", "MessStaff" => "mess_counts.php"][$role];
}
function require_role($role) {
    if (empty($_SESSION["uid"]) || $_SESSION["role"] !== $role) { header("Location: login.php"); exit; }
}

// Business rule: a meal can be booked/cancelled only before its cut-off time
function meal_deadline($conn, $date, $type) {
    $s = $conn->prepare("SELECT meal_time, cutoff_hours FROM meal_settings WHERE meal_type = ?");
    $s->bind_param("s", $type); $s->execute();
    $r = $s->get_result()->fetch_assoc();
    return strtotime("$date {$r['meal_time']}") - $r["cutoff_hours"] * 3600;
}
function meal_open($conn, $date, $type) { return time() < meal_deadline($conn, $date, $type); }

// Page layout with a role-based menu
function page_start($title) {
    $role = $_SESSION["role"] ?? "";
    $links = [
        "Student"   => ["student_dashboard.php" => "My room", "student_meals.php" => "Meals"],
        "Warden"    => ["warden_rooms.php" => "Rooms", "warden_students.php" => "Students"],
        "MessStaff" => ["mess_counts.php" => "Meal count"],
    ][$role] ?? [];
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' | Smart Hostel</title><link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="top"><strong>Smart Hostel</strong><nav>';
    foreach ($links as $href => $label) echo '<a href="' . $href . '">' . e($label) . '</a>';
    echo '</nav><span>' . e($_SESSION["name"] ?? "") . ' (' . e($role) . ') <a href="logout.php">Log out</a></span></div><div class="main">';
    if (!empty($_SESSION["flash"])) {
        [$m, $t] = $_SESSION["flash"]; unset($_SESSION["flash"]);
        echo '<div class="msg ' . e($t) . '">' . e($m) . '</div>';
    }
}
function page_end() { echo '</div><script src="validate.js"></script></body></html>'; }
