<?php
require "config.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");   $reg  = trim($_POST["login_id"] ?? "");
    $age  = trim($_POST["age"] ?? "");    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? ""); $pass = $_POST["password"] ?? ""; $conf = $_POST["confirm"] ?? "";

    if ($name === "" || $reg === "" || $age === "" || $email === "" || $phone === "" || $pass === "") {
        $error = "All fields are required.";
    } elseif (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", $name)) { $error = "Name must contain letters only.";
    } elseif (!preg_match("/^[A-Za-z0-9-]{5,20}$/", $reg)) { $error = "Registration number must be 5 to 20 letters, digits or hyphens.";
    } elseif (!ctype_digit($age) || $age < 16 || $age > 60) { $error = "Age must be a number between 16 and 60.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = "Enter a valid email address.";
    } elseif (!preg_match("/^\d{10,13}$/", $phone)) { $error = "Phone must be 10 to 13 digits.";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/", $pass)) { $error = "Password is too weak.";
    } elseif ($pass !== $conf) { $error = "Passwords do not match.";
    } else {
        $chk = $conn->prepare("SELECT id FROM users WHERE login_id = ? OR email = ?");
        $chk->bind_param("ss", $reg, $email); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { $error = "That registration number or email is already registered."; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (login_id, name, age, email, phone, password_hash, role) VALUES (?,?,?,?,?,?, 'Student')");
            $ins->bind_param("ssisss", $reg, $name, $age, $email, $phone, $hash);
            $ins->execute();
            flash("Account created. Log in with your registration number.");
            header("Location: login.php"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register | Smart Hostel</title><link rel="stylesheet" href="auth.css?v=1"></head><body class="auth">
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
  <section><div class="card wide">
    <h2>Create your account</h2><p class="sub">Students register here. It takes a minute.</p>
    <?php if ($error): ?><div class="msg error"><?= e($error) ?></div><?php endif; ?>
    <form class="vform" method="post" novalidate>
      <div class="grid2">
        <div><label>Full name</label><input name="name" data-rule="name" value="<?= e($_POST['name'] ?? '') ?>"><div class="err"></div></div>
        <div><label>Registration number</label><input name="login_id" data-rule="regno" value="<?= e($_POST['login_id'] ?? '') ?>"><div class="err"></div></div>
        <div><label>Age</label><input name="age" data-rule="age" data-digits maxlength="2" value="<?= e($_POST['age'] ?? '') ?>"><div class="err"></div></div>
        <div><label>Phone</label><input name="phone" data-rule="phone" data-digits maxlength="13" value="<?= e($_POST['phone'] ?? '') ?>"><div class="err"></div></div>
        <div class="full"><label>Email</label><input name="email" type="email" data-rule="email" value="<?= e($_POST['email'] ?? '') ?>"><div class="err"></div></div>
        <div><label>Password</label><input name="password" type="password" data-rule="password"><div class="err"></div></div>
        <div><label>Confirm password</label><input name="confirm" type="password" data-rule="confirm"><div class="err"></div></div>
      </div>
      <button type="submit">Create account</button>
    </form>
    <p class="alt">Already registered? <a href="login.php">Log in</a></p>
  </div></section>
</main>
<script src="validate.js"></script><script src="ui.js"></script></body></html>
