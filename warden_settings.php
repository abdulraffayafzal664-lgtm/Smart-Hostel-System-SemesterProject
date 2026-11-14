<?php
require "config.php"; require_role("Warden");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $meal = $_POST["meal"] ?? ""; $hrs = trim($_POST["cutoff_hours"] ?? ""); $rate = trim($_POST["rate"] ?? "");
    if (!in_array($meal, MEALS, true)) flash("Invalid meal.", "error");
    elseif ($hrs === "" || $rate === "") flash("Cut-off hours and rate are required.", "error");
    elseif (!ctype_digit($hrs) || $hrs < 1 || $hrs > 48) flash("Cut-off must be whole hours from 1 to 48.", "error");
    elseif (!is_numeric($rate) || $rate <= 0 || $rate > 5000) flash("Rate must be a number between 1 and 5000.", "error");
    else { $u = $conn->prepare("UPDATE meal_settings SET cutoff_hours = ?, rate = ? WHERE meal_type = ?"); $u->bind_param("ids", $hrs, $rate, $meal); $u->execute(); flash("$meal settings saved."); }
    header("Location: warden_settings.php"); exit;
}
$rows = $conn->query("SELECT * FROM meal_settings ORDER BY FIELD(meal_type,'Breakfast','Lunch','Dinner')");
page_start("Meal settings");
?>
<div class="box"><h2>Meal rate and cut-off</h2><p>Students cannot book or cancel a meal after its cut-off. Bills use the rate per meal.</p>
<?php foreach ($rows as $r): ?>
<form class="vform row" method="post" novalidate style="border-top:1px solid var(--line);padding:14px 0"><input type="hidden" name="meal" value="<?= e($r["meal_type"]) ?>">
<div><strong><?= e($r["meal_type"]) ?></strong><br><small>Meal time <?= e(date("h:i A", strtotime($r["meal_time"]))) ?></small></div>
<div><label>Cut-off (hours before meal)</label><input name="cutoff_hours" data-rule="hours" data-digits maxlength="2" value="<?= (int)$r["cutoff_hours"] ?>"><div class="err"></div></div>
<div><label>Rate (Rs)</label><input name="rate" data-rule="rate" value="<?= e($r["rate"]) ?>"><div class="err"></div></div>
<div><button style="margin-top:0">Save</button></div></form>
<?php endforeach; ?></div>
<?php page_end(); ?>
