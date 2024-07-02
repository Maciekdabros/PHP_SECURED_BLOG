<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$comment_id = $_GET['id'] ?? null;
$comment = null;

if ($comment_id) {
    $sql = "SELECT * FROM comments WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $comment_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $comment = $result->fetch_assoc();
        } else {
            echo "Nie masz uprawnień do edycji tego komentarza lub komentarz nie istnieje.";
            exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $comment) {
    $content = $_POST['content'];

    $update_sql = "UPDATE comments SET content = ? WHERE id = ? AND user_id = ?";
    if ($update_stmt = $conn->prepare($update_sql)) {
        $update_stmt->bind_param("sii", $content, $comment_id, $_SESSION['user_id']);
        if ($update_stmt->execute()) {
            header('Location: post.php?id=' . $comment['post_id']);
            exit;
        } else {
            echo "Wystąpił błąd podczas aktualizacji komentarza.";
        }
    }
}
?>

<?php if ($comment): ?>
<form action="edit_comment.php?id=<?php echo htmlspecialchars($comment_id); ?>" method="post">
    <label for="content">Treść komentarza:</label>
    <textarea name="content" id="content" required><?php echo htmlspecialchars($comment['content']); ?></textarea>
    <input type="submit" value="Zapisz zmiany">
</form>
<?php endif; ?>