<?php
// Load the database connection and start the authenticated session.
require_once __DIR__ . '/../db/db.php';
session_start();

// Redirect visitors who do not have a session user ID.
if (empty($_SESSION['user_id'])) {
	header('Location: ../index.php');
	exit;
}

$stmt = mysqli_prepare($con, 'SELECT users.username, role FROM users WHERE id = ? LIMIT 1');
$user = null;

// Revalidate the session user against the database.
if ($stmt) {
	$userId = (int) $_SESSION['user_id'];
	mysqli_stmt_bind_param($stmt, 'i', $userId);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$user = mysqli_fetch_assoc($result);
	mysqli_stmt_close($stmt);
}

if (!$user) {
	// Clear invalid session data before redirecting to login.
	session_unset();
	session_destroy();
	header('Location: ../index.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Min sida</title>
	<link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">
	<main class="auth-shell">
	<section class="auth-panel">
	<h1>Välkommen, <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>!</h1>
	<p class="auth-intro">Du är inloggad på Bildsök API.</p>
	<p><strong>Användarnamn:</strong> <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></p>
	<p><strong>Roll:</strong> <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></p>
	<a class="button-link" href="../index.php">Gå till bildsökningen</a>
	<p class="auth-footer"><a href="logout.php">Logga ut</a></p>
	</section>
	</main>
</body>
</html>