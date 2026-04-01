<?php
$admin_u = "";
$admin_p = "";
$rememberChecked = "";
if (isset($_COOKIE["admin_username"]) && isset($_COOKIE["admin_password"])) {
    $admin_u = $_COOKIE["admin_username"];
    $admin_p = $_COOKIE["admin_password"];
    $rememberChecked = "checked";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Faculty Login — Degree Eligibility and Class Predictor</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=DM+Sans&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/signin.css?v=20260324"/>

</head>

<body>

<!-- LEFT PANEL (UNCHANGED) -->
<div class="panel-left">
  <div class="university-mark">
    <img src="../assets/images/Kelaniya.png" class="logo-main">
    <div>
      <div class="faculty-name">Faculty of Science</div>
      <div class="university-name">University of Kelaniya</div>
    </div>
  </div>
</div>

<!-- RIGHT PANEL -->
<div class="panel-right">
  <div class="login-top-title">Degree Eligibility and Class Predictor</div>
  <div class="login-card">
    <div class="login-heading">
      <h1>Sign In</h1>
    </div>

    <form id="loginForm">
      <div class="form-group">
        <label>Username</label>
        <div class="input-wrap">
          <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($admin_u); ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($admin_p); ?>" required>
          <button type="button" class="toggle-pass" id="togglePass">👁</button>
        </div>
      </div>

      <div class="remember-me">
        <input type="checkbox" id="remember" <?php echo $rememberChecked; ?>>
        <label for="remember">Remember me</label>
      </div>

      <button type="submit" class="btn-login">Sign In</button>
      <p id="msg" style="color:red;"></p>
    </form>
  </div>
</div>

<script>
document.getElementById("togglePass").onclick = () => {
  const p = document.getElementById("password");
  p.type = p.type === "password" ? "text" : "password";
};

// AJAX LOGIN
document.getElementById("loginForm").onsubmit = function(e){
  e.preventDefault();

  const form = new FormData();
  form.append("username", username.value);
  form.append("password", password.value);
  form.append("remember", remember.checked ? "1" : "0");

  fetch("loginProcess.php", {
    method: "POST",
    body: form
  })
  .then(res => res.text())
  .then(data => {
    if(data === "success"){
      window.location = "dashboard.php";
    } else {
      msg.innerText = data;
    }
  });
};
</script>

</body>
</html>