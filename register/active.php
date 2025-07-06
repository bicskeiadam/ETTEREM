<?php
require_once "config.php";
require_once "functions_def.php";

if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
}

if (!empty($token) and strlen($token) === 40) {

    $sql = "UPDATE users SET active = 1, registration_token = '', registration_expires = NULL
            WHERE binary registration_token = :token AND registration_expires>now()";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();


    if ($stmt->rowCount() > 0) {
        redirection('index.php?r=6');
    } else {
        redirection('index.php?r=12');
    }
} else {
    redirection('index.php?r=0');
}