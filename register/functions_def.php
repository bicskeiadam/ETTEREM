<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require_once "config.php";

$GLOBALS['pdo'] = connectDatabase($dsn, $pdoOptions);

/**
 * Connects to the database using PDO.
 *
 * @param string $dsn Data Source Name
 * @param array $pdoOptions PDO options
 * @return PDO The PDO instance for database interaction
 */

function connectDatabase(string $dsn, array $pdoOptions): PDO
{

    try {
        $pdo = new PDO($dsn, PARAMS['USER'], PARAMS['PASS'], $pdoOptions);
    } catch (\PDOException $e) {
        var_dump($e->getCode());
        throw new \PDOException($e->getMessage());
    }

    return $pdo;
}


/**
 * Redirects the user to the given URL.
 *
 * @param string $url URL to redirect to
 * @return void
 */
function redirection(string $url): void
{
    header("Location: $url");
    exit();
}


/**
 * Verifies user login credentials.
 *
 * @param string $email The user's email address
 * @param string $enteredPassword The entered plaintext password
 * @return array Associative array with 'id_user' if successful, empty array otherwise
 */

function checkUserLogin(string $email, string $enteredPassword): array
{
    $sql = "SELECT id_user, password FROM users WHERE email=:email AND active=1 AND is_banned = 0 LIMIT 1";
    $stmt = $GLOBALS['pdo']->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);

    $data = [];
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stmt->rowCount() > 0) {

        $registeredPassword = $result['password'];

        if (password_verify($enteredPassword, $registeredPassword)) {
            $data['id_user'] = $result['id_user'];
        }
    }

    return $data;
}


/**
 * Checks if a user with the given email exists and is either active or pending activation.
 *
 * @param PDO $pdo The PDO database connection
 * @param string $email The email to check
 * @return bool True if user exists, false otherwise
 */

function existsUser(PDO $pdo, string $email): bool
{

    $sql = "SELECT id_user FROM users WHERE email=:email AND (registration_expires>now() OR active ='1') LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stmt->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}


/**
 * Registers a new user and returns the newly created user's ID.
 *
 * @param PDO $pdo The PDO database connection
 * @param string $password Plaintext password
 * @param string $firstname First name of the user
 * @param string $lastname Last name of the user
 * @param string $email Email address of the user
 * @param string $token Registration token
 * @return int The ID of the newly created user
 */
function registerUser(PDO $pdo, string $password, string $firstname, string $lastname, string $email, string $token): int
{

    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users(password,firstname,lastname,email,registration_token, registration_expires,active)
            VALUES (:passwordHashed,:firstname,:lastname,:email,:token,DATE_ADD(now(),INTERVAL 1 DAY),0)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':passwordHashed', $passwordHashed, PDO::PARAM_STR);
    $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
    $stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    // http://dev.mysql.com/doc/refman/5.6/en/date-and-time-functions.html

    return $pdo->lastInsertId();

}


/**
 * Generates a secure random token of given byte length.
 *
 * @param int $length The number of bytes (not characters!)
 * @return string|null The generated token in hex format or null on failure
 */

function createToken(int $length): ?string
{
    try {
        return bin2hex(random_bytes($length));
    } catch (\Exception $e) {
        // c:xampp/apache/logs/
        error_log("****************************************");
        error_log($e->getMessage());
        error_log("file:" . $e->getFile() . " line:" . $e->getLine());
        return null;
    }
}

/**
 * Generates a random string of the specified length using a-z letters (mixed case).
 *
 * @param int $length Desired length of the generated code
 * @return string The generated code
 */

function createCode(int $length): string
{
    $down = 97;
    $up = 122;
    $i = 0;
    $code = "";

    /*
      48-57  = 0 - 9
      65-90  = A - Z
      97-122 = a - z
    */

    $div = mt_rand(3, 9); // 3

    while ($i < $length) {
        if ($i % $div == 0)
            $character = strtoupper(chr(mt_rand($down, $up)));
        else
            $character = chr(mt_rand($down, $up)); // mt_rand(97,122) chr(98)
        $code .= $character; // $code = $code.$character; //
        $i++;
    }
    return $code;
}


/**
 * Sends an email using PHPMailer with the provided content and handles errors.
 *
 * @param PDO $pdo The PDO connection, used for logging failures
 * @param string $email Recipient email address
 * @param array $emailData Associative array with 'subject' and 'altBody' keys
 * @param string $body HTML email content
 * @param int $id_user ID of the user the email is intended for
 * @return void
 */

function sendEmail(PDO $pdo, string $email, array $emailData, string $body, int $id_user): void
{
    $phpmailer = new PHPMailer(true);

    try {
        $phpmailer->isSMTP();

        // Use Gmail SMTP for real email sending
        $phpmailer->Host = 'smtp.gmail.com';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = 587;
        $phpmailer->Username = 'adambickei12@gmail.com'; // <-- your Gmail address
        $phpmailer->Password = 'aeyh udlx kknh bgax'; // <-- your Gmail App Password (not your Gmail login password)
        $phpmailer->setFrom('adambickei12@gmail.com', 'Lapmesterek');
        $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $phpmailer->addAddress($email);
        $phpmailer->isHTML(true);
        $phpmailer->Subject = $emailData['subject'];
        $phpmailer->Body = $body;
        $phpmailer->AltBody = $emailData['altBody'];

        $phpmailer->send();
    } catch (Exception $e) {
        $message = "Message could not be sent  ($email). Mailer Error: {$phpmailer->ErrorInfo} ";
        addEmailFailure($pdo, $id_user, $message);
    }

}


/**
 * Logs an email sending failure in the database.
 *
 * @param PDO $pdo The PDO database connection
 * @param int $id_user ID of the user
 * @param string $message Error message to log
 * @return void
 */

function addEmailFailure(PDO $pdo, int $id_user, string $message): void
{
    $sql = "INSERT INTO user_email_failures (id_user, message, date_time_added)
            VALUES (:id_user,:message, now())";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->execute();

}


/**
 * Retrieves a specific field value from the users table.
 *
 * @param PDO $pdo The PDO connection
 * @param string $data The column name to retrieve
 * @param string $field The field to match against
 * @param string $value The value to search for
 * @return string The value of the requested field, or empty string if not found
 */
function getUserData(PDO $pdo, string $data, string $field, string $value): string
{
    $sql = "SELECT $data as data FROM users WHERE $field=:value LIMIT 0,1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':value', $value, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $data = '';

    if ($stmt->rowCount() > 0) {
        $data = $result['data'];
    }

    return $data;
}

/**
 * Sets a forgotten password token and expiration for a user.
 *
 * @param PDO $pdo The PDO database connection
 * @param string $email Email of the user
 * @param string $token Token to be stored
 * @return void
 */

function setForgottenToken(PDO $pdo, string $email, string $token): void
{
    $sql = "UPDATE users SET forgotten_password_token = :token, forgotten_password_expires = DATE_ADD(now(),INTERVAL 6 HOUR) WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
}