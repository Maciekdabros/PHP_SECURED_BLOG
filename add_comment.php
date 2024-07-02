<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'], $_POST['content'], $_POST['post_id'])) {
    // Przekierowanie, jeśli dane nie są kompletnie przesłane
    header('Location: post.php?id=' . $_POST['post_id']);
    exit;
}

$content = htmlspecialchars($_POST['content']);
$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $post_id, $content);

if ($stmt->execute()) {
    header('Location: post.php?id=' . $post_id);
} else {
    echo "Nie udało się dodać komentarza: " . $conn->error;
}
$stmt->close();
?>