<?php
require "config.php"; require_role("MessStaff");
$days = [date("Y-m-d") => "Today", date("Y-m-d", strtotime("+1 day")) => "Tomorrow"];
page_start("Meal count");
foreach ($days as $d => $label):
    $q = $conn->prepare("SELECT meal_type, COUNT(*) n FROM meal_bookings WHERE meal_date = ? AND status = 'Booked' GROUP BY meal_type");
    $q->bind_param("s", $d); $q->execute();
    $count = array_fill_keys(MEALS, 0);
    foreach ($q->get_result() as $r) $count[$r["meal_type"]] = (int)$r["n"];
?>
<div class="box"><h2><?= e($label) ?> (<?= e(date("D d M Y", strtotime($d))) ?>)</h2>
<table><tr><th>Meal</th><th>Confirmed bookings</th></tr>
<?php foreach (MEALS as $m): ?><tr><td><?= e($m) ?></td><td><span class="big" style="font-size:1.3rem"><?= $count[$m] ?></span></td></tr><?php endforeach; ?>
</table></div>
<?php endforeach; page_end(); ?>
