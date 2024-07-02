<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$post_id = $_GET['id'] ?? null;
$post = null;

if ($post_id) {
    $sql = "SELECT * FROM posts WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $post = $result->fetch_assoc();
        } else {
            echo "Nie masz uprawnień do edycji tego posta lub post nie istnieje.";
            exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $post) {

    $title = $_POST['title'];
    $content = $_POST['content'];

    $update_sql = "UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?";
    if ($update_stmt = $conn->prepare($update_sql)) {
        $update_stmt->bind_param("ssii", $title, $content, $post_id, $_SESSION['user_id']);
        if ($update_stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            echo "Wystąpił błąd podczas aktualizacji posta.";
        }
    }
}
?>


<form action="edit_post.php?id=<?php echo htmlspecialchars($post_id); ?>" method="post">
    <label for="title">Tytuł:</label>
    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required>

    <label for="content">Treść:</label>
    <textarea name="content" id="content" required><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>

    <input type="submit" value="Zapisz zmiany">
</form>