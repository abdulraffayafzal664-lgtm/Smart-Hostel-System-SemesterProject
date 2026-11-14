<?php
// Shared setup: session, database (Singleton), helpers, page layout (uses Factory)
session_start();
date_default_timezone_set("Asia/Karachi");
require __DIR__ . "/classes/Database.php";
require __DIR__ . "/classes/UserFactory.php";

try { $conn = Database::getInstance()->connection(); }
catch (Throwable $ex) { die("Database connection failed: " . $ex->getMessage()); }

const MEALS = ["Breakfast", "Lunch", "Dinner"];

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }
function flash($msg, $type = "success") { $_SESSION["flash"] = [$msg, $type]; }

function home_for($role) { return UserFactory::create(["role" => $role])->dashboard(); }   // Factory
function require_role($role) {
    if (empty($_SESSION["uid"]) || $_SESSION["role"] !== $role) { header("Location: login.php"); exit; }
}

// A meal can be booked/cancelled only before its cut-off time
function meal_deadline($conn, $date, $type) {
    $s = $conn->prepare("SELECT meal_time, cutoff_hours FROM meal_settings WHERE meal_type = ?");
    $s->bind_param("s", $type); $s->execute();
    $r = $s->get_result()->fetch_assoc();
    return strtotime("$date {$r['meal_time']}") - $r["cutoff_hours"] * 3600;
}
function meal_open($conn, $date, $type) { return time() < meal_deadline($conn, $date, $type); }

function page_start($title) {
    $links = []; $name = ""; $role = "";
    if (!empty($_SESSION["role"])) {
        $user = UserFactory::create(["role" => $_SESSION["role"], "name" => $_SESSION["name"] ?? ""]);   // Factory
        $links = array_filter($user->menu(), fn($f) => file_exists(__DIR__ . "/$f"), ARRAY_FILTER_USE_KEY);
        $name = $user->name(); $role = $user->role();
    }
    $icons = ["student_dashboard.php" => "&#127968;", "student_meals.php" => "&#127869;", "student_bill.php" => "&#128179;",
              "student_complaints.php" => "&#128221;", "warden_rooms.php" => "&#128719;", "warden_students.php" => "&#128101;",
              "warden_complaints.php" => "&#128221;", "warden_reports.php" => "&#128202;", "warden_settings.php" => "&#9881;&#65039;",
              "mess_counts.php" => "&#127869;"];
    $here = basename($_SERVER["SCRIPT_NAME"]);
    $h = (int)date("G"); $greet = $h < 12 ? "Good morning" : ($h < 18 ? "Good afternoon" : "Good evening");
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' | Smart Hostel</title><link rel="stylesheet" href="app.css?v=1"></head><body>';
    echo '<div class="app"><aside class="sidebar"><div class="brand"><span class="logo">&#127968;</span> Smart Hostel</div><nav>';
    foreach ($links as $href => $text)
        echo '<a href="' . $href . '"' . ($href === $here ? ' class="active"' : '') . '><i>' . ($icons[$href] ?? "&#9679;") . '</i>' . e($text) . '</a>';
    echo '</nav><div class="me"><div class="av">' . e(strtoupper(substr($name, 0, 1))) . '</div><div class="who"><strong>' . e($name)
       . '</strong><small>' . e($role) . '</small></div><a class="out" href="logout.php" title="Log out">Log out</a></div></aside>';
    echo '<section class="content"><header class="hdr"><div><h1>' . e($title) . '</h1><p>' . $greet . ', ' . e(explode(" ", $name)[0])
       . ' &middot; ' . date("l, d M Y") . '</p></div></header><div class="main">';
    if (!empty($_SESSION["flash"])) {
        [$m, $t] = $_SESSION["flash"]; unset($_SESSION["flash"]);
        echo '<div class="msg ' . e($t) . '">' . e($m) . '</div>';
    }
}
function page_end() { echo '</div></section></div><script src="validate.js"></script><script src="ui.js"></script></body></html>'; }
