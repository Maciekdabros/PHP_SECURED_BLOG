<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = $_GET['id'];
$post_result = $conn->query("SELECT * FROM posts WHERE id = $post_id");

if ($post_result->num_rows == 0) {
    echo "Post nie istnieje.";
    exit;
}

$post = $post_result->fetch_assoc();

$search_term = isset($_GET['q']) ? $_GET['q'] : '';
$comments_query = "SELECT comments.*, users.username, users.avatar FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ?";

if ($search_term) {
    $comments_query .= " AND comments.content LIKE ?";
    $stmt = $conn->prepare($comments_query);
    $search_term_like = "%" . $search_term . "%";
    $stmt->bind_param("is", $post_id, $search_term_like);
} else {
    $stmt = $conn->prepare($comments_query);
    $stmt->bind_param("i", $post_id);
}

$stmt->execute();
$comments_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <p><?php echo htmlspecialchars($post['content']); ?></p>

    <!-- Formularz wyszukiwania komentarzy -->
    <form action="post.php?id=<?php echo htmlspecialchars($post_id); ?>" method="get">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($post_id); ?>">
        <input type="text" name="q" placeholder="Szukaj komentarzy..." value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit">Szukaj</button>
    </form>

    <h2>Komentarze</h2>
<?php if ($comments_result->num_rows > 0): ?>
    <?php while($comment = $comments_result->fetch_assoc()): 
        $user_id = $comment['user_id'];
        $user_query = "SELECT username, avatar FROM users WHERE id = $user_id";
        $user_result = $conn->query($user_query);
        $user = $user_result->fetch_assoc();

        $avatar_path = !empty($user['avatar']) ? $user['avatar'] : 'path/to/default/avatar.jpg';
    ?>
        <div class="comment">
            <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Avatar" class="rounded-circle" style="width: 50px; height: 50px;">
            <p><?php echo htmlspecialchars($user['username']); ?> napisał(a):</p>
            <p><?php echo htmlspecialchars($comment['content']); ?></p>
            <small>Opublikowano: <?php echo $comment['created_at']; ?></small>
            <?php if ($_SESSION['user_id'] == $comment['user_id']): ?>
                <a href="edit_comment.php?id=<?php echo $comment['id']; ?>">Edytuj</a>
                <a href="delete_comment.php?id=<?php echo $comment['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć ten komentarz?');">Usuń</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>Brak komentarzy. Bądź pierwszy!</p>
<?php endif; ?>

    <h3>Dodaj komentarz</h3>
    <form action="add_comment.php" method="post">
        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">
        <textarea name="content" required></textarea>
        <button type="submit">Dodaj komentarz</button>
    </form>
</body>
</html>
