<?php
session_start();
require 'db.php';

$login_error = '';
$allowedAttempts = 3;
$lockoutTime = 20;
$showCaptcha = false;
$accountLocked = false;

if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 2) {
    $showCaptcha = true;
}

if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= $allowedAttempts) {
    if (isset($_SESSION['lockout_time']) && time() - $_SESSION['lockout_time'] < $lockoutTime) {
        $accountLocked = true;
        $login_error = "Twoje konto jest zablokowane. Spróbuj ponownie później.";
    } else {
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);
        $showCaptcha = false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$accountLocked) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if ($showCaptcha && (!isset($_POST['captcha']) || $_POST['captcha'] !== $_SESSION['captcha'])) {
        $login_error = 'Niepoprawny kod CAPTCHA.';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $username;
                unset($_SESSION['login_attempts']);
                unset($_SESSION['lockout_time']);
                header("Location: index.php");
                exit;
            } else {
                $login_error = 'Niepoprawna nazwa użytkownika lub hasło.';
            }
        } else {
            $login_error = 'Niepoprawna nazwa użytkownika lub hasło.';
        }
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= $allowedAttempts) {
            $_SESSION['lockout_time'] = time();
            $showCaptcha = true;
            $accountLocked = true;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - Podatny Blog</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mt-5">Logowanie</h2>
            <?php if ($login_error != ''): ?>
                <div class="alert alert-danger"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Nazwa użytkownika:</label>
                    <input type="text" class="form-control" id="username" name="username" required <?php echo $accountLocked ? 'disabled' : ''; ?>>
                </div>
                <div class="form-group">
                    <label for="password">Hasło:</label>
                    <input type="password" class="form-control" id="password" name="password" required <?php echo $accountLocked ? 'disabled' : ''; ?>>
                </div>
                <?php if ($showCaptcha): ?>
                    <div class="form-group">
                        <label for="captcha">Wpisz CAPTCHA:</label>
                        <input type="text" class="form-control" id="captcha" name="captcha" required <?php echo $accountLocked ? 'disabled' : ''; ?>>
                        <img src="captcha.php" alt="CAPTCHA Image">
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary" <?php echo $accountLocked ? 'disabled' : ''; ?>>Zaloguj się</button>
            </form>
        </div>
    </div>
</div>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>