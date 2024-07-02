<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $password = $email = "";

if ($stmt = $conn->prepare("SELECT username, password, email FROM users WHERE id = ?")) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($fetched_username, $fetched_password, $fetched_email);
    if ($stmt->fetch()) {
        $username = $fetched_username;
        $email = $fetched_email;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Prywatne Informacje</title>
</head>
<body>
    <div class="container">
        <h2>Prywatne informacje</h2>
        <form action="update_private_info.php" method="post">
            Login: <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required><br>
            Nowe hasło: <input type="password" name="password"><br>
            Email: <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br>
            <input type="submit" value="Aktualizuj">
        </form>
    </div>
</body>
</html>