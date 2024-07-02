<?php
session_start();
require 'db.php';

$username = $password = "";
$registration_error = '';

function isValidPassword($password) {
    $minLength = 8;
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    return strlen($password) >= $minLength && $uppercase && $lowercase && $number && $specialChars;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $captcha_input = $_POST['captcha_challenge'];

    if ($captcha_input != $_SESSION['captcha']) {
        $registration_error = "Nieprawidłowy kod CAPTCHA.";
    } else {
        if (isValidPassword($password)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                $stmt->close();

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $hashed_password);

                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['username'] = $username;
                    header('Location: index.php');
                    exit;
                } else {
                    $registration_error = 'Wystąpił błąd podczas rejestracji.';
                }
            } else {
                $registration_error = 'Użytkownik o takim loginie już istnieje.';
            }
            $stmt->close();
        } else {
            $registration_error = "Hasło musi zawierać co najmniej 8 znaków, w tym dużą literę, małą literę, cyfrę i znak specjalny.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja - Podatny Blog</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mt-5">Rejestracja</h2>
                <?php if ($registration_error != ''): ?>
                    <div class="alert alert-danger"><?php echo $registration_error; ?></div>
                <?php endif; ?>
                <form action="register.php" method="post">
                    <div class="form-group">
                        <label for="username">Nazwa użytkownika:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Hasło:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="captcha_challenge">Captcha:</label>
                        <input type="text" class="form-control" id="captcha_challenge" name="captcha_challenge" required>
                        <img src="captcha.php" alt="CAPTCHA Image">
                    </div>
                    <button type="submit" class="btn btn-primary">Zarejestruj się</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
