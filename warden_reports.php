<?php
require "config.php"; require_role("Warden");
$month = $_GET["month"] ?? date("Y-m");
if (!preg_match("/^\d{4}-(0[1-9]|1[0-2])$/", $month)) $month = date("Y-m");
$from = "$month-01"; $to = date("Y-m-t", strtotime($from));

$q = $conn->prepare("SELECT u.login_id, u.name, COUNT(b.id) AS meals, COALESCE(SUM(s.rate),0) AS amount
  FROM users u LEFT JOIN meal_bookings b ON b.student_id = u.id AND b.status = 'Booked' AND b.meal_date BETWEEN ? AND ?
  LEFT JOIN meal_settings s ON s.meal_type = b.meal_type WHERE u.role = 'Student' GROUP BY u.id ORDER BY u.name");
$q->bind_param("ss", $from, $to); $q->execute(); $rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_GET["csv"])) {   // export
    header("Content-Type: text/csv"); header("Content-Disposition: attachment; filename=meal_report_$month.csv");
    $o = fopen("php://output", "w"); fputcsv($o, ["Reg no", "Name", "Meals booked", "Bill (Rs)"]);
    foreach ($rows as $r) fputcsv($o, [$r["login_id"], $r["name"], $r["meals"], $r["amount"]]);
    exit;
}
$byMeal = $conn->prepare("SELECT meal_type, COUNT(*) n FROM meal_bookings WHERE status = 'Booked' AND meal_date BETWEEN ? AND ? GROUP BY meal_type");
$byMeal->bind_param("ss", $from, $to); $byMeal->execute(); $mc = array_fill_keys(MEALS, 0);
foreach ($byMeal->get_result() as $r) $mc[$r["meal_type"]] = (int)$r["n"];
$cc = ["Pending" => 0, "In Progress" => 0, "Resolved" => 0];
foreach ($conn->query("SELECT status, COUNT(*) n FROM complaints GROUP BY status") as $r) $cc[$r["status"]] = (int)$r["n"];
$sum = array_sum(array_column($rows, "amount"));
page_start("Reports");
?>
<div class="box"><h2>Monthly report</h2>
<form method="get" class="row"><div><label>Month</label><input type="month" name="month" value="<?= e($month) ?>"></div>
<div><button style="margin-top:0">Show</button></div>
<div><a class="btn" style="margin-top:0" href="warden_reports.php?month=<?= e($month) ?>&csv=1">Export CSV</a></div></form></div>
<div class="box"><h2>Meals booked in <?= e(date("F Y", strtotime($from))) ?></h2>
<table><tr><?php foreach (MEALS as $m): ?><th><?= $m ?></th><?php endforeach; ?></tr><tr><?php foreach (MEALS as $m): ?><td><span class="big" style="font-size:1.3rem"><?= $mc[$m] ?></span></td><?php endforeach; ?></tr></table></div>
<div class="box"><h2>Bills</h2>
<table><tr><th>Reg no</th><th>Name</th><th>Meals</th><th>Bill (Rs)</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r["login_id"]) ?></td><td><?= e($r["name"]) ?></td><td><?= (int)$r["meals"] ?></td><td><?= number_format($r["amount"], 2) ?></td></tr><?php endforeach; ?>
<tr><th colspan="3">Total</th><th>Rs <?= number_format($sum, 2) ?></th></tr></table></div>
<div class="box"><h2>Complaints</h2><p>Pending: <strong><?= $cc["Pending"] ?></strong> &middot; In progress: <strong><?= $cc["In Progress"] ?></strong> &middot; Resolved: <strong><?= $cc["Resolved"] ?></strong></p></div>
<?php page_end(); ?>
