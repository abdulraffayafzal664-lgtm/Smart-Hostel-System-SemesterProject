<?php
require "config.php";
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = trim($_POST["login_id"] ?? ""); $pass = $_POST["password"] ?? "";
    if ($id === "" || $pass === "") { $error = "Enter your ID and password."; }
    else {
        $s = $conn->prepare("SELECT id, name, role, password_hash FROM users WHERE login_id = ?");
        $s->bind_param("s", $id); $s->execute();
        $u = $s->get_result()->fetch_assoc();
        if ($u && password_verify($pass, $u["password_hash"])) {
            session_regenerate_id(true);
            $_SESSION["uid"] = $u["id"]; $_SESSION["name"] = $u["name"]; $_SESSION["role"] = $u["role"];
            header("Location: " . home_for($u["role"])); exit;
        }
        $error = "ID or password is incorrect.";
    }
}
$flash = $_SESSION["flash"] ?? null; unset($_SESSION["flash"]);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log in | Smart Hostel</title><link rel="stylesheet" href="style.css"></head><body>
<div class="wrap">
  <div class="side"><h1>Smart Hostel</h1><p>Students, wardens and mess staff log in here.</p></div>
  <div class="panel"><div class="card">
    <h2>Log in</h2>
    <?php if ($flash): ?><div class="msg success"><?= e($flash[0]) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Registration no. / Employee ID / Staff ID</label>
      <input name="login_id" required value="<?= e($_POST['login_id'] ?? '') ?>">
      <label>Password</label><input name="password" type="password" required>
      <button type="submit">Log in</button>
    </form>
    <p class="alt">New student? <a href="register.php">Create an account</a></p>
  </div></div>
</div></body></html>
