<?php
require "config.php"; require_role("Student");
$uid = (int)$_SESSION["uid"];
$s = $conn->prepare("SELECT u.login_id, u.email, u.phone, r.room_number, r.capacity,
  (SELECT COUNT(*) FROM users x WHERE x.room_id = r.id) AS occupied
  FROM users u LEFT JOIN rooms r ON r.id = u.room_id WHERE u.id = ?");
$s->bind_param("i", $uid); $s->execute(); $me = $s->get_result()->fetch_assoc();

$c = $conn->prepare("SELECT COUNT(*) n FROM meal_bookings WHERE student_id = ? AND status = 'Booked' AND meal_date >= CURDATE()");
$c->bind_param("i", $uid); $c->execute(); $upcoming = $c->get_result()->fetch_assoc()["n"];

page_start("My room");
?>
<div class="box"><h2>Room details</h2>
<?php if ($me["room_number"]): ?>
  <p class="big"><?= e($me["room_number"]) ?></p>
  <p>Capacity <?= (int)$me["capacity"] ?> &middot; currently <?= (int)$me["occupied"] ?> student(s) living here.</p>
<?php else: ?>
  <p>No room allocated yet. The warden will assign one to you.</p>
<?php endif; ?></div>
<div class="box"><h2>Your details</h2>
  <p>Registration no: <strong><?= e($me["login_id"]) ?></strong><br>Email: <?= e($me["email"]) ?><br>Phone: <?= e($me["phone"]) ?></p></div>
<div class="box"><h2>Meals</h2><p>You have <strong><?= (int)$upcoming ?></strong> upcoming booked meal(s).</p>
  <a class="btn" style="max-width:220px" href="student_meals.php">Book or cancel meals</a></div>
<?php page_end(); ?>
