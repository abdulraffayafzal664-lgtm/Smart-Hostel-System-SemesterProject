<?php
require "config.php"; require_role("Warden");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["id"] ?? 0); $st = $_POST["status"] ?? "";
    if (!in_array($st, ["Pending", "In Progress", "Resolved"], true)) flash("Invalid status.", "error");
    else { $u = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?"); $u->bind_param("si", $st, $id); $u->execute(); flash("Complaint status updated."); }
    header("Location: warden_complaints.php"); exit;
}
$list = $conn->query("SELECT c.*, u.name, u.login_id FROM complaints c JOIN users u ON u.id = c.student_id
  ORDER BY FIELD(c.status,'Pending','In Progress','Resolved'), c.id DESC");
page_start("Complaints");
?>
<div class="box"><h2>All complaints</h2>
<?php if ($list->num_rows === 0): ?><p>No complaints yet.</p><?php else: ?>
<table><tr><th>Student</th><th>Category</th><th>Description</th><th>Date</th><th>Status</th></tr>
<?php foreach ($list as $c): ?>
<tr><td><?= e($c["name"]) ?><br><small><?= e($c["login_id"]) ?></small></td><td><?= e($c["category"]) ?></td><td><?= e($c["description"]) ?></td>
<td><?= e(date("d M Y", strtotime($c["created_at"]))) ?></td>
<td><form method="post"><input type="hidden" name="id" value="<?= $c["id"] ?>">
  <select name="status"><?php foreach (["Pending", "In Progress", "Resolved"] as $s): ?><option <?= $c["status"] === $s ? "selected" : "" ?>><?= $s ?></option><?php endforeach; ?></select>
  <button>Update</button></form></td></tr>
<?php endforeach; ?></table><?php endif; ?></div>
<?php page_end(); ?>
