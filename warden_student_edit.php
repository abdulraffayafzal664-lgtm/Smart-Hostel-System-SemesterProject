<?php
require "config.php"; require_role("Warden");
$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);
$g = $conn->prepare("SELECT id, login_id, name, age, email, phone FROM users WHERE id = ? AND role = 'Student'");
$g->bind_param("i", $id); $g->execute(); $st = $g->get_result()->fetch_assoc();
if (!$st) { flash("Student not found.", "error"); header("Location: warden_students.php"); exit; }
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? ""); $age = trim($_POST["age"] ?? ""); $email = trim($_POST["email"] ?? ""); $phone = trim($_POST["phone"] ?? "");
    if ($name === "" || $age === "" || $email === "" || $phone === "") $error = "All fields are required.";
    elseif (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", $name)) $error = "Name must contain letters only.";
    elseif (!ctype_digit($age) || $age < 16 || $age > 60) $error = "Age must be a number between 16 and 60.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Enter a valid email address.";
    elseif (!preg_match("/^\d{10,13}$/", $phone)) $error = "Phone must be 10 to 13 digits.";
    else {
        $c = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?"); $c->bind_param("si", $email, $id); $c->execute(); $c->store_result();
        if ($c->num_rows) $error = "That email belongs to another user.";
        else { $u = $conn->prepare("UPDATE users SET name=?, age=?, email=?, phone=? WHERE id=?"); $u->bind_param("sissi", $name, $age, $email, $phone, $id); $u->execute();
               flash("Student details updated."); header("Location: warden_students.php"); exit; }
    }
    $st = array_merge($st, ["name" => $name, "age" => $age, "email" => $email, "phone" => $phone]);
}
page_start("Edit student");
?>
<div class="box" style="max-width:520px"><h2>Edit <?= e($st["login_id"]) ?></h2>
<?php if ($error): ?><div class="msg error"><?= e($error) ?></div><?php endif; ?>
<form class="vform" method="post" novalidate><input type="hidden" name="id" value="<?= $id ?>">
<div><label>Full name</label><input name="name" data-rule="name" value="<?= e($st["name"]) ?>"><div class="err"></div></div>
<div><label>Age</label><input name="age" data-rule="age" data-digits maxlength="2" value="<?= e($st["age"]) ?>"><div class="err"></div></div>
<div><label>Email</label><input name="email" type="email" data-rule="email" value="<?= e($st["email"]) ?>"><div class="err"></div></div>
<div><label>Phone</label><input name="phone" data-rule="phone" data-digits maxlength="13" value="<?= e($st["phone"]) ?>"><div class="err"></div></div>
<button type="submit">Save changes</button></form>
<p class="alt"><a href="warden_students.php">Back to students</a></p></div>
<?php page_end(); ?>
