<?php
// Load the database connection and start the session used for authentication.
require_once __DIR__ . '/../db/db.php';
session_start();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read and validate the submitted credentials.
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Fyll i både användarnamn och lösenord.';
    } else {
        $stmt = mysqli_prepare($con, 'SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        // Regenerate the session ID after a successful login.
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: logged_in.php');
            exit;
        }

        $error = 'Fel användarnamn eller lösenord.';
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logga in</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">
    <main class="auth-shell auth-panel">
        <h1>Logga in</h1>
        <?php if ($error): ?>
            <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form class="auth-form" method="post">
            <label for="username">Användarnamn</label>
            <input id="username" name="username" required autofocus>
            <label for="password">Lösenord</label>
            <input id="password" name="password" type="password" required>
            <button type="submit">Logga in</button>
        </form>
        <p class="auth-footer">Har du inget konto? <a href="register.php">Skapa ett konto</a></p>
    </main>
</body>
</html>
