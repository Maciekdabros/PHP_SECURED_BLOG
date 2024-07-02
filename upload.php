<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['fileToUpload'])) {
    $uploadedFile = $_FILES['fileToUpload']['tmp_name'];
    $uploadedName = escapeshellarg($_FILES['fileToUpload']['name']);

    $command = "echo " . $uploadedName;
    $output = shell_exec($command);

    $message = "<pre>$output</pre>";
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Prześlij plik - Symulacja</title>
</head>
<body>
    <h1>Prześlij plik</h1>
    <p>Ta funkcja pozwala na przesyłanie plików do archiwizacji</p>
    <?php if ($message) echo $message; ?>

    <form action="upload.php" method="post" enctype="multipart/form-data">
        Wybierz plik do przesłania:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Prześlij plik" name="submit">
    </form>
</body>
</html>
