<?php
session_start();
require 'db.php';

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755);
}

if (isset($_POST['submit'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "Proszę się zalogować, aby kontynuować.";
        return;
    }
    $userId = $_SESSION['user_id'];

    if (isset($_FILES['xmlfile']) && $_FILES['xmlfile']['error'] === 0) {
        $xmlFile = __DIR__ . '/uploads/' . basename($_FILES['xmlfile']['name']);

        if (move_uploaded_file($_FILES['xmlfile']['tmp_name'], $xmlFile)) {
            $xml = simplexml_load_file($xmlFile);
            if ($xml) {
                $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
                foreach ($xml->post as $post) {
                    $title = $post->title;
                    $content = $post->content;
                    $stmt->bind_param("iss", $userId, $title, $content);
                    $stmt->execute();
                }
                $stmt->close();
                echo "Posty zostały zaimportowane.";
            } else {
                echo "Nie udało się wczytać pliku XML.";
            }
        } else {
            echo "Nie udało się przenieść pliku.";
        }
    } else {
        echo "Błąd przesyłania pliku: " . $_FILES['xmlfile']['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Importuj Posty XML</title>
</head>
<body>
    <h1>Importuj Posty XML</h1>
    <form action="import_xml.php" method="post" enctype="multipart/form-data">
        Wybierz plik XML do importu:
        <input type="file" name="xmlfile" id="xmlfile">
        <input type="submit" value="Importuj" name="submit">
    </form>
</body>
</html>
