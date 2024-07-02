<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $target_dir = "uploads/";
    $file_extension = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array(strtolower($file_extension), $allowed_types)) {
        $new_filename = uniqid() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "Nieprawidłowy typ pliku. Dozwolone są tylko: JPG, PNG, GIF.";
    }
}

header('Location: index.php');
exit;
?>