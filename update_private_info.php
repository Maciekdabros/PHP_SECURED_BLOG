<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    if (empty($username) || empty($email)) {
        exit('Proszę wypełnić wszystkie wymagane pola.');
    }

    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username=?, password=?, email=? WHERE id=?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('sssi', $username, $password_hashed, $email, $_SESSION['user_id']);
        }
    }
    else {

        $query = "UPDATE users SET username=?, email=? WHERE id=?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('ssi', $username, $email, $_SESSION['user_id']);
        }
    }

    if ($stmt->execute()) {
        echo 'Dane zostały zaktualizowane.';
    } else {
        echo 'Wystąpił błąd podczas aktualizacji danych: ' . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>