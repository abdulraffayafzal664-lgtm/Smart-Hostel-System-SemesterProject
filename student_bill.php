<?php
require "config.php"; require_role("Student");
$uid = (int)$_SESSION["uid"];
$month = $_GET["month"] ?? date("Y-m");
if (!preg_match("/^\d{4}-(0[1-9]|1[0-2])$/", $month)) $month = date("Y-m");
$from = "$month-01"; $to = date("Y-m-t", strtotime($from));

// Bill = booked meals x meal rate
$q = $conn->prepare("SELECT s.meal_type, s.rate, COUNT(b.id) AS n FROM meal_settings s
  LEFT JOIN meal_bookings b ON b.meal_type = s.meal_type AND b.student_id = ? AND b.status = 'Booked' AND b.meal_date BETWEEN ? AND ?
  GROUP BY s.meal_type, s.rate ORDER BY FIELD(s.meal_type,'Breakfast','Lunch','Dinner')");
$q->bind_param("iss", $uid, $from, $to); $q->execute(); $rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);
$total = 0; $meals = 0; foreach ($rows as $r) { $total += $r["n"] * $r["rate"]; $meals += $r["n"]; }
page_start("Bill");
?>
<div class="box"><h2>Choose a month</h2>
<form method="get" class="row"><div><label>Month</label><input type="month" name="month" value="<?= e($month) ?>"></div>
<div><button style="margin-top:0">Show bill</button></div></form></div>
<div class="box"><h2>Mess bill for <?= e(date("F Y", strtotime($from))) ?></h2>
<table><tr><th>Meal</th><th>Rate (Rs)</th><th>Booked meals</th><th>Amount (Rs)</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r["meal_type"]) ?></td><td><?= number_format($r["rate"], 2) ?></td><td><?= (int)$r["n"] ?></td><td><?= number_format($r["n"] * $r["rate"], 2) ?></td></tr><?php endforeach; ?>
<tr><th colspan="2">Total</th><th><?= $meals ?></th><th><span class="big" style="font-size:1.3rem">Rs <?= number_format($total, 2) ?></span></th></tr></table></div>
<?php page_end(); ?>
