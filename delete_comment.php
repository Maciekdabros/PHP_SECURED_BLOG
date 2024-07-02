<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$comment_id = $_GET['id'] ?? null;

if ($comment_id) {
    $sql = "DELETE FROM comments WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $comment_id, $_SESSION['user_id']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Komentarz został usunięty.";
        } else {
            echo "Nie masz uprawnień do usunięcia tego komentarza lub komentarz nie istnieje.";
        }
        $stmt->close();
    } else {
        echo "Wystąpił błąd podczas próby usunięcia komentarza.";
    }
} else {
    echo "Nieprawidłowy identyfikator komentarza.";
}

$conn->close();
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>