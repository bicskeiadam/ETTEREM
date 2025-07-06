<?php
session_start();
require_once "config.php";
require_once "functions_def.php";

error_log("REFERER: " . $referer);
error_log("SITE: " . SITE);

$password = "";
$passwordConfirm = "";
$firstname = "";
$lastname = "";
$email = "";
$action = "";

$referer = $_SERVER['HTTP_REFERER'];
//var_dump($referer);
//var_dump($_SERVER);
//exit();
// http://localhost/wp_2025/05/1-register/index.php
// http://localhost/wp_2025/05/1-register/

$action = $_POST["action"];

if ($action != "" and in_array($action, $actions) and str_contains($referer, SITE)) {


    switch ($action) {
        case "login":

            $username = trim($_POST["username"]);
            $password = trim($_POST["password"]);

            if (!empty($username) and !empty($password)) {
                //$data = checkUserLogin($pdo, $username, $password);
                $data = checkUserLogin($username, $password);

                if ($data and is_int($data['id_user'])) {
                    $_SESSION['username'] = $username;
                    $_SESSION['id_user'] = $data['id_user'];
                    redirection('../views/user_view.php');
                } else {
                    redirection('index.php?l=1');
                }

            } else {
                redirection('index.php?l=1');
            }
            break;


        case "register" :

            if (isset($_POST['firstname'])) {
                $firstname = trim($_POST["firstname"]);
            }

            if (isset($_POST['lastname'])) {
                $lastname = trim($_POST["lastname"]);
            }

            if (isset($_POST['password'])) {
                $password = trim($_POST["password"]);
            }

            if (isset($_POST['passwordConfirm'])) {
                $passwordConfirm = trim($_POST["passwordConfirm"]);
            }

            if (isset($_POST['email'])) {
                $email = trim($_POST["email"]);
            }

            if (empty($firstname)) {
                redirection('index.php?r=4');
            }

            if (empty($lastname)) {
                redirection('index.php?r=4');
            }

            if (empty($password)) {
                redirection('index.php?r=9');
            }

            if (!preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $password)) {
                redirection('index.php?l=10');
            }

            if (empty($passwordConfirm)) {
                redirection('index.php?r=9');
            }

            if ($password !== $passwordConfirm) {
                redirection('index.php?r=7');
            }

            if (empty($email) or !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                redirection('index.php?r=8');
            }

            if (!existsUser($pdo, $email)) {
                $token = createToken(20);
                if ($token) {
                    $id_user = registerUser($pdo, $password, $firstname, $lastname, $email, $token);
                    try {
                        $body = "Your username is $email. To activate your account click on the <a href=" . SITE . "active.php?token=$token>link</a>";
                        sendEmail($pdo, $email, $emailMessages['register'], $body, $id_user);
                        redirection("index.php?r=3");
                    } catch (Exception $e) {
                        error_log("****************************************");
                        error_log($e->getMessage());
                        error_log("file:" . $e->getFile() . " line:" . $e->getLine());
                        redirection("index.php?r=11");
                    }
                }
            } else {
                redirection('index.php?r=2');
            }

            break;

        case "forget" :
            $email = trim($_POST["email"]);
            if (!empty($email) and getUserData($pdo, 'id_user', 'email', $email)) {
                $token = createToken(20);
                if ($token) {
                    setForgottenToken($pdo, $email, $token);
                    $id_user = getUserData($pdo, 'id_user', 'email', $email);
                    try {
                        $body = "To start the process of changing password, visit <a href=" . SITE . "forget.php?token=$token>link</a>.";
                        sendEmail($pdo, $email, $emailMessages['forget'], $body, $id_user);
                        redirection('index.php?f=13');
                    } catch (Exception $e) {
                        error_log("****************************************");
                        error_log($e->getMessage());
                        error_log("file:" . $e->getFile() . " line:" . $e->getLine());
                        redirection("index.php?f=11");
                    }
                } else {
                    redirection('index.php?f=14');
                }
            } else {
                sleep(2);
                redirection('index.php?f=13');
            }
            break;

        default:
            redirection('index.php?l=0');
            break;
    }

} else {
    redirection('index.php?l=0');
}
