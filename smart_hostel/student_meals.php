<?php
require "config.php"; require_role("Student");
$uid = (int)$_SESSION["uid"];
$today = date("Y-m-d"); $last = date("Y-m-d", strtotime("+7 days"));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"] ?? ""; $meal = $_POST["meal"] ?? ""; $act = $_POST["action"] ?? "";
    $okDate = preg_match("/^\d{4}-\d{2}-\d{2}$/", $date) && $date >= $today && $date <= $last;
    if (!$okDate || !in_array($meal, MEALS, true) || !in_array($act, ["book", "cancel"], true)) {
        flash("Invalid request.", "error");
    } elseif (!meal_open($conn, $date, $meal)) {
        flash("The cut-off time for $meal on $date has passed. You can no longer " . ($act === "book" ? "book" : "cancel") . " it.", "error");
    } elseif ($act === "book") {
        $q = $conn->prepare("INSERT INTO meal_bookings (student_id, meal_date, meal_type, status) VALUES (?,?,?, 'Booked')
                             ON DUPLICATE KEY UPDATE status = 'Booked'");
        $q->bind_param("iss", $uid, $date, $meal); $q->execute();
        flash("$meal booked for $date.");
    } else {
        $q = $conn->prepare("UPDATE meal_bookings SET status = 'Cancelled' WHERE student_id = ? AND meal_date = ? AND meal_type = ?");
        $q->bind_param("iss", $uid, $date, $meal); $q->execute();
        flash("$meal cancelled for $date.");
    }
    header("Location: student_meals.php?date=" . urlencode($date)); exit;
}

$date = $_GET["date"] ?? date("Y-m-d", strtotime("+1 day"));
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date) || $date < $today || $date > $last) $date = date("Y-m-d", strtotime("+1 day"));

$q = $conn->prepare("SELECT meal_type, status FROM meal_bookings WHERE student_id = ? AND meal_date = ?");
$q->bind_param("is", $uid, $date); $q->execute();
$state = [];
foreach ($q->get_result() as $r) $state[$r["meal_type"]] = $r["status"];

page_start("Meals");
?>
<div class="box"><h2>Choose a day</h2>
  <form method="get" class="row">
    <div><label>Date</label><input type="date" name="date" min="<?= $today ?>" max="<?= $last ?>" value="<?= e($date) ?>"></div>
    <div><button type="submit" style="margin-top:0">Show meals</button></div>
  </form></div>
<div class="box"><h2>Meals on <?= e(date("l, d M Y", strtotime($date))) ?></h2>
<table><tr><th>Meal</th><th>Book before</th><th>Status</th><th></th></tr>
<?php foreach (MEALS as $m):
  $deadline = meal_deadline($conn, $date, $m); $open = time() < $deadline; $st = $state[$m] ?? "Not booked"; ?>
  <tr>
    <td><?= e($m) ?></td>
    <td><?= e(date("D d M, h:i A", $deadline)) ?></td>
    <td><span class="tag <?= $st === 'Booked' ? '' : ($st === 'Cancelled' ? 'bad' : 'warn') ?>"><?= e($st) ?></span></td>
    <td><?php if (!$open): ?><span class="tag bad">Closed</span>
      <?php else: ?><form method="post">
        <input type="hidden" name="date" value="<?= e($date) ?>"><input type="hidden" name="meal" value="<?= e($m) ?>">
        <?php if ($st === "Booked"): ?><button name="action" value="cancel" class="danger">Cancel</button>
        <?php else: ?><button name="action" value="book">Book</button><?php endif; ?>
      </form><?php endif; ?></td>
  </tr>
<?php endforeach; ?></table></div>
<?php page_end(); ?>
