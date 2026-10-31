<?php
require "config.php"; require_role("Warden");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $act = $_POST["action"] ?? ""; $sid = (int)($_POST["student_id"] ?? 0);
    if ($act === "allocate") {
        $rid = (int)($_POST["room_id"] ?? 0);
        $c = $conn->prepare("SELECT capacity, (SELECT COUNT(*) FROM users WHERE room_id = rooms.id) AS occupied FROM rooms WHERE id = ?");
        $c->bind_param("i", $rid); $c->execute(); $room = $c->get_result()->fetch_assoc();
        if (!$room) flash("Choose a room.", "error");
        elseif ($room["occupied"] >= $room["capacity"]) flash("That room is full. Choose another room.", "error");
        else { $u = $conn->prepare("UPDATE users SET room_id = ? WHERE id = ? AND role = 'Student'"); $u->bind_param("ii", $rid, $sid); $u->execute(); flash("Room allocated."); }
    } elseif ($act === "vacate") {
        $u = $conn->prepare("UPDATE users SET room_id = NULL WHERE id = ? AND role = 'Student'"); $u->bind_param("i", $sid); $u->execute(); flash("Student removed from room.");
    } elseif ($act === "delete") {
        $d = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'Student'"); $d->bind_param("i", $sid); $d->execute(); flash("Student deleted.");
    }
    header("Location: warden_students.php"); exit;
}
$students = $conn->query("SELECT u.id, u.login_id, u.name, u.email, u.phone, r.room_number FROM users u LEFT JOIN rooms r ON r.id = u.room_id WHERE u.role = 'Student' ORDER BY u.name");
$rooms = $conn->query("SELECT r.id, r.room_number, r.capacity, (SELECT COUNT(*) FROM users u WHERE u.room_id = r.id) AS occupied FROM rooms r ORDER BY room_number")->fetch_all(MYSQLI_ASSOC);
page_start("Students");
?>
<div class="box"><h2>Students</h2>
<?php if ($students->num_rows === 0): ?><p>No students have registered yet.</p><?php else: ?>
<table><tr><th>Reg no</th><th>Name</th><th>Phone</th><th>Room</th><th>Actions</th></tr>
<?php foreach ($students as $s): ?>
  <tr><td><?= e($s["login_id"]) ?></td><td><?= e($s["name"]) ?></td><td><?= e($s["phone"]) ?></td>
  <td><?= $s["room_number"] ? e($s["room_number"]) : '<span class="tag warn">None</span>' ?></td>
  <td>
    <form method="post"><input type="hidden" name="action" value="allocate"><input type="hidden" name="student_id" value="<?= $s["id"] ?>">
      <select name="room_id"><option value="">Select room</option>
      <?php foreach ($rooms as $r): $left = $r["capacity"] - $r["occupied"]; ?>
        <option value="<?= $r["id"] ?>" <?= $left <= 0 ? "disabled" : "" ?>><?= e($r["room_number"]) ?> (<?= $left ?> free)</option>
      <?php endforeach; ?></select><button>Allocate</button></form>
    <?php if ($s["room_number"]): ?><form method="post"><input type="hidden" name="action" value="vacate"><input type="hidden" name="student_id" value="<?= $s["id"] ?>"><button class="light">Remove from room</button></form><?php endif; ?>
    <form method="post" onsubmit="return confirm('Delete this student?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="student_id" value="<?= $s["id"] ?>"><button class="danger">Delete</button></form>
  </td></tr>
<?php endforeach; ?></table><?php endif; ?></div>
<?php page_end(); ?>
