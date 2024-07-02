<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$post_id = $_GET['id'] ?? null;

$sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Post został usunięty.";
    } else {
        echo "Nie masz uprawnień do usunięcia tego posta lub post nie istnieje.";
    }
    $stmt->close();
} else {
    echo "Wystąpił błąd podczas próby usunięcia posta.";
}

$conn->close();
header('Location: index.php');
exit;
?>