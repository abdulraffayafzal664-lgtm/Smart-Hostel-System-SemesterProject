<?php
require "config.php"; require_role("Warden");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $act = $_POST["action"] ?? "";
    if ($act === "add") {
        $no = trim($_POST["room_number"] ?? ""); $cap = trim($_POST["capacity"] ?? "");
        if ($no === "" || $cap === "") flash("All fields are required.", "error");
        elseif (!preg_match("/^[A-Za-z0-9-]{2,10}$/", $no)) flash("Room number must be 2 to 10 letters, digits or hyphens.", "error");
        elseif (!ctype_digit($cap) || $cap < 1 || $cap > 6) flash("Capacity must be a number from 1 to 6.", "error");
        else {
            $x = $conn->prepare("SELECT id FROM rooms WHERE room_number = ?"); $x->bind_param("s", $no); $x->execute(); $x->store_result();
            if ($x->num_rows) flash("Room $no already exists.", "error");
            else { $i = $conn->prepare("INSERT INTO rooms (room_number, capacity) VALUES (?,?)"); $i->bind_param("si", $no, $cap); $i->execute(); flash("Room $no added."); }
        }
    } elseif ($act === "edit") {
        $id = (int)($_POST["room_id"] ?? 0); $cap = trim($_POST["capacity"] ?? "");
        $occ = $conn->prepare("SELECT COUNT(*) n FROM users WHERE room_id = ?"); $occ->bind_param("i", $id); $occ->execute();
        $n = $occ->get_result()->fetch_assoc()["n"];
        if (!ctype_digit($cap) || $cap < 1 || $cap > 6) flash("Capacity must be a number from 1 to 6.", "error");
        elseif ($cap < $n) flash("Capacity cannot be lower than the $n student(s) already in the room.", "error");
        else { $u = $conn->prepare("UPDATE rooms SET capacity = ? WHERE id = ?"); $u->bind_param("ii", $cap, $id); $u->execute(); flash("Room updated."); }
    } elseif ($act === "delete") {
        $id = (int)($_POST["room_id"] ?? 0);
        $occ = $conn->prepare("SELECT COUNT(*) n FROM users WHERE room_id = ?"); $occ->bind_param("i", $id); $occ->execute();
        if ($occ->get_result()->fetch_assoc()["n"] > 0) flash("Remove the students from this room before deleting it.", "error");
        else { $d = $conn->prepare("DELETE FROM rooms WHERE id = ?"); $d->bind_param("i", $id); $d->execute(); flash("Room deleted."); }
    }
    header("Location: warden_rooms.php"); exit;
}
$rooms = $conn->query("SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.room_id = r.id) AS occupied FROM rooms r ORDER BY room_number");
page_start("Rooms");
?>
<div class="box"><h2>Add a room</h2>
  <form class="vform row" method="post" novalidate>
    <input type="hidden" name="action" value="add">
    <div><label>Room number</label><input name="room_number" data-rule="roomno"><div class="err"></div></div>
    <div><label>Capacity</label><input name="capacity" data-rule="capacity" data-digits maxlength="1"><div class="err"></div></div>
    <div><button type="submit" style="margin-top:0">Add room</button></div>
  </form></div>
<div class="box"><h2>All rooms</h2>
<table><tr><th>Room</th><th>Capacity</th><th>Occupied</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($rooms as $r): $full = $r["occupied"] >= $r["capacity"]; ?>
  <tr><td><?= e($r["room_number"]) ?></td><td><?= (int)$r["capacity"] ?></td><td><?= (int)$r["occupied"] ?></td>
  <td><span class="tag <?= $full ? 'bad' : '' ?>"><?= $full ? "Full" : "Vacant" ?></span></td>
  <td>
    <form method="post"><input type="hidden" name="action" value="edit"><input type="hidden" name="room_id" value="<?= $r["id"] ?>">
      <input name="capacity" value="<?= (int)$r["capacity"] ?>" style="width:60px" maxlength="1"><button class="light">Save</button></form>
    <form method="post" onsubmit="return confirm('Delete this room?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="room_id" value="<?= $r["id"] ?>"><button class="danger">Delete</button></form>
  </td></tr>
<?php endforeach; ?></table></div>
<?php page_end(); ?>
