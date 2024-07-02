<?php
session_start();
require 'db.php';
function initializeTestUser($conn) {
    $username = 'testuser';
    $password = 'testpassword';
    $email = 'test@example.com';

    $sql = "SELECT id FROM users WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $stmt->close();

            $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sss", $username, $password, $email);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
}

initializeTestUser($conn);

$search_term = isset($_GET['q']) ? $_GET['q'] : '';
$posts = [];

if ($search_term) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE title LIKE ?");
    $search_term_like = "%" . $search_term . "%";
    $stmt->bind_param('s', $search_term_like);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
}

$theme = isset($_GET['theme']) ? $_GET['theme'] : 'default';
$themePath = "themes/" . $theme;

if (file_exists($themePath . '.css')) {
    $themePath .= '.css';
} elseif (file_exists($themePath . '.txt')) {
    echo nl2br(file_get_contents($themePath . '.txt'));
    $themePath = '';
} else {
    $themePath .= '.css';
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Blog</title>
    <link rel="stylesheet" href="<?php echo $themePath; ?>">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { padding-top: 80px; }
        .post { margin-bottom: 70px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">Blog</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav mr-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="create_post.php">Utwórz nowy post</a>
                    </li>
                <?php endif; ?>
            </ul>
            <form class="form-inline my-2 my-lg-0" action="index.php" method="get">
                <input class="form-control mr-sm-2" type="search" name="q" placeholder="Szukaj..." aria-label="Search">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Szukaj</button>
            </form>
            
            <ul class="navbar-nav ml-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Wyloguj</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Logowanie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Rejestracja</a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="upload.php">Prześlij plik</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="import_xml.php">Importuj posty XML</a>
                        </li>
                        <li class="nav-item">
                            <form action="upload_avatar.php" method="post" enctype="multipart/form-data" class="form-inline">
                                <label for="avatar" class="btn btn-info">Wybierz awatar</label>
                                <input type="file" name="avatar" id="avatar" onchange="this.form.submit();" style="display: none;">
                                <input type="submit" value="Prześlij Avatar" name="submit" class="btn btn-info">
                            </form>
                        </li>
                    </ul>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="text-center mb-4">
        <?php if (isset($_SESSION['username'])): ?>
            <h1>Witaj <?php echo htmlspecialchars($_SESSION['username']); ?> na blogu!</h1>
        <?php else: ?>
            <h1>Witaj na moim blogu!</h1>
        <?php endif; ?>
        <p>Tutaj możesz przeczytać najnowsze wpisy.</p>
    </div>

    <?php if ($search_term): ?>
        <div>Wyniki wyszukiwania dla: <?php echo htmlspecialchars($search_term); ?></div>
    <?php endif; ?>
    <div class="row">
    <div class="col-lg-12">
        <?php foreach ($posts as $row): ?>
            <div class='post'>
                <h3><a href="post.php?id=<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['title']); ?></a></h3>
                <p><?php echo htmlspecialchars($row['content']); ?></p>
                <small>Opublikowano: <?php echo $row['created_at']; ?></small>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                    | <a href='edit_post.php?id=<?php echo $row['id']; ?>'>Edytuj</a>
                    | <a href='delete_post.php?id=<?php echo $row['id']; ?>' onclick='return confirm("Czy na pewno chcesz usunąć ten post?");'>Usuń</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


</body>
</html>