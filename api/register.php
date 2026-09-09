<?php
require_once 'db.php';
session_start();

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = $_POST['password'] ?? '';
	$passwordConfirmation = $_POST['password_confirmation'] ?? '';

	if ($username === '' || $password === '' || $passwordConfirmation === '') {
		$error = 'Fyll i alla fält.';
	} elseif (strlen($username) < 3) {
		$error = 'Användarnamnet måste vara minst 3 tecken.';
	} elseif ($password !== $passwordConfirmation) {
		$error = 'Lösenorden matchar inte.';
	} elseif (strlen($password) < 8) {
		$error = 'Lösenordet måste vara minst 8 tecken.';
	} else {
		$check = mysqli_prepare($con, 'SELECT id FROM users WHERE username = ? LIMIT 1');

		if (!$check) {
			$error = 'Registreringen misslyckades.';
		} else {
			mysqli_stmt_bind_param($check, 's', $username);
			mysqli_stmt_execute($check);
			$existingUser = mysqli_fetch_assoc(mysqli_stmt_get_result($check));
			mysqli_stmt_close($check);

			if ($existingUser) {
				$error = 'Användarnamnet används redan.';
			}
		}

		if ($error === null) {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			$stmt = mysqli_prepare($con, 'INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
			$role = 'user';

			if (!$stmt) {
				$error = 'Registreringen misslyckades.';
			} else {
				mysqli_stmt_bind_param($stmt, 'sss', $username, $hash, $role);

				if (mysqli_stmt_execute($stmt)) {
					$_SESSION['user_id'] = mysqli_insert_id($con);
					$_SESSION['username'] = $username;
					header('Location: logged_in.php');
					exit;
				} else {
					$error = 'Registreringen misslyckades.';
				}
				mysqli_stmt_close($stmt);
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Skapa konto</title>
	<link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">
	<main class="auth-shell">
	<section class="auth-panel">
	<h1>Skapa konto</h1>
	<?php if ($error !== null): ?>
		<p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
	<?php endif; ?>
	<form class="auth-form" method="post">
		<label for="username">Användarnamn</label>
		<input id="username" type="text" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" minlength="3" required autofocus>
		<label for="password">Lösenord</label>
		<input id="password" type="password" name="password" minlength="8" required>
		<label for="password_confirmation">Bekräfta lösenord</label>
		<input id="password_confirmation" type="password" name="password_confirmation" minlength="8" required>
		<button type="submit">Skapa konto</button>
	</form>
	<p class="auth-footer">Har du redan ett konto? <a href="index.php">Logga in</a></p>
	</section>
	</main>
</body>
</html>
