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
<html lang="fr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Fish Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=20251223">
</head>
<body class="login">
    <div class="login-card">
        <h1 class="login-title">Fish Manager</h1>
        <p class="login-subtitle">Connectez-vous pour accéder au tableau de bord.</p>

        <form method="post">
            <div>
                <label for="username">Nom d'utilisateur</label>
                <input id="username" name="username" placeholder="Nom d'utilisateur" autocomplete="username" required>
            </div>

            <div>
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password" placeholder="Mot de passe" autocomplete="current-password" required>
            </div>

            <button type="submit" name="login">Connexion</button>

            <?php if($err): ?>
                <div class="alert alert-danger mt-4"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script src="js_main.js"></script>
</body>
</html>
