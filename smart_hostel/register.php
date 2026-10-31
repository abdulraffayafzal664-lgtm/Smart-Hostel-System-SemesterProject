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
<title>Register | Smart Hostel</title><link rel="stylesheet" href="style.css"></head><body>
<div class="wrap">
  <div class="side"><h1>Smart Hostel</h1><p>Create your student account to see your room and book meals before the cut-off.</p></div>
  <div class="panel"><div class="card">
    <h2>Create student account</h2>
    <?php if ($error): ?><div class="msg error"><?= e($error) ?></div><?php endif; ?>
    <form class="vform" method="post" novalidate>
      <div><label>Full name</label><input name="name" data-rule="name" value="<?= e($_POST['name'] ?? '') ?>"><div class="err"></div></div>
      <div><label>Registration number</label><input name="login_id" data-rule="regno" value="<?= e($_POST['login_id'] ?? '') ?>"><div class="err"></div></div>
      <div><label>Age</label><input name="age" data-rule="age" data-digits maxlength="2" value="<?= e($_POST['age'] ?? '') ?>"><div class="err"></div></div>
      <div><label>Email</label><input name="email" type="email" data-rule="email" value="<?= e($_POST['email'] ?? '') ?>"><div class="err"></div></div>
      <div><label>Phone</label><input name="phone" data-rule="phone" data-digits maxlength="13" value="<?= e($_POST['phone'] ?? '') ?>"><div class="err"></div></div>
      <div><label>Password</label><input name="password" type="password" data-rule="password"><div class="err"></div></div>
      <div><label>Confirm password</label><input name="confirm" type="password" data-rule="confirm"><div class="err"></div></div>
      <button type="submit">Create account</button>
    </form>
    <p class="alt">Already registered? <a href="login.php">Log in</a></p>
  </div></div>
</div>
<script src="validate.js"></script></body></html>
