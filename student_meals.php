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
$rows = []; $items = [];
foreach (MEALS as $m) {
    $d = meal_deadline($conn, $date, $m);
    $rows[$m] = ["deadline" => $d, "open" => time() < $d];
    if (time() < $d) $items[] = ["id" => $m, "deadline" => $d * 1000];
}
?>
<div class="box"><h2>Choose a day</h2>
  <form method="get" class="row">
    <div><label>Date</label><input type="date" name="date" min="<?= $today ?>" max="<?= $last ?>" value="<?= e($date) ?>"></div>
    <div><button type="submit" style="margin-top:0">Show meals</button></div>
  </form></div>
<div class="box"><h2>Meals on <?= e(date("l, d M Y", strtotime($date))) ?></h2>
<table><tr><th>Meal</th><th>Book before</th><th>Time left</th><th>Status</th><th></th></tr>
<?php foreach (MEALS as $m): $deadline = $rows[$m]["deadline"]; $open = $rows[$m]["open"]; $st = $state[$m] ?? "Not booked"; ?>
  <tr>
    <td><?= e($m) ?></td>
    <td><?= e(date("D d M, h:i A", $deadline)) ?></td>
    <td><span class="count <?= $open ? '' : 'closed' ?>" id="cd-<?= e($m) ?>"><?= $open ? "..." : "Closed" ?></span></td>
    <td><span class="tag <?= $st === 'Booked' ? '' : ($st === 'Cancelled' ? 'bad' : 'warn') ?>"><?= e($st) ?></span></td>
    <td id="act-<?= e($m) ?>"><?php if (!$open): ?><span class="tag bad">Closed</span>
      <?php else: ?><form method="post">
        <input type="hidden" name="date" value="<?= e($date) ?>"><input type="hidden" name="meal" value="<?= e($m) ?>">
        <?php if ($st === "Booked"): ?><button name="action" value="cancel" class="danger">Cancel</button>
        <?php else: ?><button name="action" value="book">Book</button><?php endif; ?>
      </form><?php endif; ?></td>
  </tr>
<?php endforeach; ?></table></div>
<script>
// The countdown runs in a Web Worker (a separate thread) so the page never freezes.
const items = <?= json_encode($items) ?>, serverNow = <?= time() * 1000 ?>;
function fmt(ms) { const t = Math.floor(ms / 1000), h = Math.floor(t / 3600), m = Math.floor(t % 3600 / 60), s = t % 60;
  return (h ? h + "h " : "") + m + "m " + String(s).padStart(2, "0") + "s"; }
if (items.length && window.Worker) {
  const worker = new Worker("countdown_worker.js");
  worker.onmessage = ev => ev.data.forEach(r => {
    const c = document.getElementById("cd-" + r.id); if (!c) return;
    if (r.ms <= 0) {
      c.textContent = "Closed"; c.className = "count closed";
      const a = document.getElementById("act-" + r.id); if (a) a.innerHTML = '<span class="tag bad">Closed</span>';
    } else { c.textContent = fmt(r.ms); c.className = "count" + (r.ms < 3600000 ? " soon" : ""); }
  });
  worker.postMessage({ serverNow, items });
}
</script>
<?php page_end(); ?>
