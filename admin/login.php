<?php
session_start();
$config = require __DIR__ . '/../inc/config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$user = isset($_POST['username']) ? trim($_POST['username']) : '';
	$pass = isset($_POST['password']) ? trim($_POST['password']) : '';
	if ($user === $config['admin_username'] && $pass === $config['admin_password']) {
		$_SESSION['admin_logged_in'] = true;
		header('Location: ./');
		exit;
	} else {
		$error = 'Username atau password salah';
	}
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
	<div class="container">
		<div class="header">
			<h2>Login Admin</h2>
			<a href="../" class="button secondary">Kembali</a>
		</div>
		<div class="card" style="max-width: 420px; margin: 0 auto;">
			<?php if ($error): ?>
				<div style="color: #b91c1c;"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>
			<form method="post">
				<label>Username
					<input class="input" type="text" name="username" required>
				</label>
				<div class="mt-2"></div>
				<label>Password
					<input class="input" type="password" name="password" required>
				</label>
				<div class="mt-4"></div>
				<button class="button" type="submit">Masuk</button>
			</form>
		</div>
	</div>
</body>
</html>
