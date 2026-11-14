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
<title>Log in | Smart Hostel</title><link rel="stylesheet" href="auth.css?v=1"></head><body class="auth">
<main class="stage">
  <section class="hero">
    <div class="logo"><i>&#127968;</i> Smart Hostel</div>
    <h1>Your hostel,<br><span>made simple.</span></h1>
    <p>Book meals before the cut-off, see your room and track complaints, all in one place.</p>
    <ul class="points">
      <li><b>&#10003;</b> Book or cancel meals with a live countdown</li>
      <li><b>&#10003;</b> See your room and bills any time</li>
      <li><b>&#10003;</b> Send complaints and follow their status</li>
    </ul>
    <svg class="skyline" viewBox="0 0 520 220" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
<rect x="0" y="196" width="520" height="24" fill="#0a1d36" opacity=".6"/>
<rect x="40" y="70" width="170" height="126" rx="6" fill="#274a78"/><rect x="40" y="70" width="170" height="12" rx="6" fill="#335d93"/>
<rect x="230" y="30" width="190" height="166" rx="6" fill="#2d5584"/><rect x="230" y="30" width="190" height="12" rx="6" fill="#3a6aa3"/>
<rect x="436" y="100" width="56" height="96" rx="5" fill="#1d3b63"/>
<g fill="#ffd166"><rect class="win" x="58" y="96" width="22" height="26" rx="3"/><rect class="win" x="96" y="96" width="22" height="26" rx="3"/><rect class="win" x="134" y="96" width="22" height="26" rx="3"/><rect class="win" x="172" y="96" width="22" height="26" rx="3"/>
<rect class="win" x="58" y="140" width="22" height="26" rx="3"/><rect class="win" x="134" y="140" width="22" height="26" rx="3"/><rect class="win" x="172" y="140" width="22" height="26" rx="3"/>
<rect class="win" x="250" y="56" width="24" height="28" rx="3"/><rect class="win" x="290" y="56" width="24" height="28" rx="3"/><rect class="win" x="330" y="56" width="24" height="28" rx="3"/><rect class="win" x="370" y="56" width="24" height="28" rx="3"/>
<rect class="win" x="250" y="100" width="24" height="28" rx="3"/><rect class="win" x="330" y="100" width="24" height="28" rx="3"/><rect class="win" x="370" y="100" width="24" height="28" rx="3"/>
<rect class="win" x="290" y="144" width="24" height="28" rx="3"/><rect class="win" x="370" y="144" width="24" height="28" rx="3"/>
<rect class="win" x="448" y="118" width="14" height="18" rx="2"/><rect class="win" x="468" y="118" width="14" height="18" rx="2"/></g>
<rect x="100" y="150" width="30" height="46" rx="4" fill="#e0a100"/><rect x="250" y="148" width="26" height="48" rx="4" fill="#e0a100"/>
<circle cx="470" cy="40" r="16" fill="#fff6d6" opacity=".9"/>
<g fill="#fff" opacity=".7"><circle cx="30" cy="30" r="1.6"/><circle cx="90" cy="18" r="1.4"/><circle cx="170" cy="40" r="1.6"/><circle cx="430" cy="14" r="1.4"/><circle cx="360" cy="12" r="1.2"/></g>
</svg>
  </section>
  <section><div class="card">
    <h2>Welcome back</h2><p class="sub">Log in to continue.</p>
    <?php if ($flash): ?><div class="msg success"><?= e($flash[0]) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Registration no. / Employee ID / Staff ID</label>
      <input name="login_id" required value="<?= e($_POST['login_id'] ?? '') ?>">
      <label>Password</label><input name="password" type="password" required>
      <button type="submit">Log in</button>
    </form>
    <p class="alt">New student? <a href="register.php">Create an account</a></p>
  </div></section>
</main>
<script src="ui.js"></script></body></html>
