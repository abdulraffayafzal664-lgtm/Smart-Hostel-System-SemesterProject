<?php
require "config.php"; require_role("Student");
$uid = (int)$_SESSION["uid"];
$cats = ["Maintenance", "Cleanliness", "Mess", "Electricity", "Other"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cat = $_POST["category"] ?? ""; $desc = trim($_POST["description"] ?? "");
    if ($cat === "" || $desc === "") flash("Category and description are required.", "error");
    elseif (!in_array($cat, $cats, true)) flash("Choose a valid category.", "error");
    elseif (strlen($desc) < 10 || strlen($desc) > 500) flash("Description must be 10 to 500 characters.", "error");
    else {
        $i = $conn->prepare("INSERT INTO complaints (student_id, category, description) VALUES (?,?,?)");
        $i->bind_param("iss", $uid, $cat, $desc); $i->execute();
        flash("Complaint submitted. The warden will update its status.");
    }
    header("Location: student_complaints.php"); exit;
}
$q = $conn->prepare("SELECT category, description, status, created_at FROM complaints WHERE student_id = ? ORDER BY id DESC");
$q->bind_param("i", $uid); $q->execute(); $list = $q->get_result();
page_start("Complaints");
?>
<div class="box"><h2>Submit a complaint</h2>
<form class="vform" method="post" novalidate>
  <label>Category</label>
  <select name="category"><?php foreach ($cats as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select>
  <div><label>Description</label><textarea name="description" data-rule="desc" rows="4" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:6px;font:inherit"></textarea><div class="err"></div></div>
  <button type="submit" style="max-width:220px">Submit complaint</button>
</form></div>
<div class="box"><h2>Your complaints</h2>
<?php if ($list->num_rows === 0): ?><p>You have not submitted any complaints.</p><?php else: ?>
<table><tr><th>Date</th><th>Category</th><th>Description</th><th>Status</th></tr>
<?php foreach ($list as $c): $cls = $c["status"] === "Resolved" ? "" : ($c["status"] === "Pending" ? "bad" : "warn"); ?>
<tr><td><?= e(date("d M Y", strtotime($c["created_at"]))) ?></td><td><?= e($c["category"]) ?></td><td><?= e($c["description"]) ?></td><td><span class="tag <?= $cls ?>"><?= e($c["status"]) ?></span></td></tr>
<?php endforeach; ?></table><?php endif; ?></div>
<?php page_end(); ?>
