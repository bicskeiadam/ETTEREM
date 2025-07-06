<?php

const PARAMS = [
    "SITE" => 'https://lm.stud.vts.su.ac.rs/ETTEREM/register/',
    "HOST" => 'localhost',
    "USER" => 'lm',
    "PASS" => 'QsMvuBdmB3sS74j',
    "DBNAME" => 'lm',
    "CHARSET" => 'utf8mb4'
];

const MAILTRAP = [
    "USERNAME" => '0dd68b82818fbc', // your MailTrap username
    "PASSWORD" => '4033644b873be2',  // your MailTrap password
    "HOST" => 'sandbox.smtp.mailtrap.io',
    "FROM_EMAIL" => 'webmaster@example.com',
    "FROM_NAME" => 'Webmaster'
];

//Looking to send emails in production? Check out our Email API/SMTP product!
//$phpmailer = new PHPMailer();
//$phpmailer->isSMTP();
//$phpmailer->Host = 'live.smtp.mailtrap.io';
//$phpmailer->SMTPAuth = true;
//$phpmailer->Port = 587;
//$phpmailer->Username = 'api';
//$phpmailer->Password = '94cbd2ae215d0365f2248e42f52ede4e';

const SMTP = [
    "HOST" => 'smtp.gmail.com', // e.g., smtp.gmail.com
    "USERNAME" => 'adambickei12@gmail.com',
    "PASSWORD" => 'aeyh udlx kknh bgax',
    "FROM_EMAIL" => 'adambickei12@gmail.com',
    "FROM_NAME" => 'Lapmesterek',
];

const SITE = 'https://lm.stud.vts.su.ac.rs/ETTEREM/register/'; // enter your path on localhost

$dsn = "mysql:host=" . PARAMS['HOST'] . ";dbname=" . PARAMS['DBNAME'] . ";charset=" . PARAMS['CHARSET'];

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

$actions = ['login', 'register', 'forget'];

$messages = [
    0 => 'No direct access!',
    1 => 'Unknown user!',
    2 => 'User with this name already exists, choose another one!',
    3 => 'Check your email to active your account!',
    4 => 'Fill all the fields!',
    5 => 'You are logged out!!',
    6 => 'Your account is activated, you can login now!',
    7 => 'Passwords are not equal!',
    8 => 'Format of e-mail address is not valid!',
    9 => 'Password is too short! It must be minimum 8 characters long!',
    10 => 'Password is not enough strong! (min 8 characters, at least 1 lowercase character, 1 uppercase character, 1 number, and 1 special character',
    11 => 'Something went wrong with mail server. We will try to send email later!',
    12 => 'Your account is already activated!',
    13 => 'If you have account on our site, email with instructions for reset password is sent to you.',
    14 => 'Something went wrong with server.',
    15 => 'Token or other data are invalid!',
    16 => 'Your new password is set and you can <a href="index.php" class="text-white">login</a>'
];

$emailMessages = [
    'register' => [
        'subject' => 'Register on web site',
        'altBody' => 'This is the body in plain text for non-HTML mail clients'
    ],
    'forget' => [
        'subject' => 'Forgotten password - create new password',
        'altBody' => 'This is the body in plain text for non-HTML mail clients'
    ]
];