<?php
session_start();
include "db.php";

$err = "";

if(isset($_POST['login'])){
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$u' AND password='$p'";
    $r = mysqli_query($conn, $sql);

    if($r && mysqli_num_rows($r) == 1){
        $row = mysqli_fetch_assoc($r);
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        header("Location:index.php");
        exit;
    } else {
        $err = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Connexion Fish Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="login">
<form method="post" class="login-box">
<h2>Fish Manager</h2>
<input name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button name="login">Connexion</button>
<p style="color:red;"><?= htmlspecialchars($err) ?></p>
</form>
</body>
</html>
