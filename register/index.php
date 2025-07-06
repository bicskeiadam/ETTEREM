<?php
require_once 'config.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="js/script.js"></script>

    <link href="../styles/log.css" rel="stylesheet">

    <title>Register / login</title>
    <style>
        /* ...existing code... */
        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(90deg, #E1A140 60%, #f0c27b 100%);
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
            padding: 10px 22px;
            border-radius: 30px;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(225, 161, 64, 0.15);
            margin: 30px 0 20px 0;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            border: none;
            outline: none;
            position: relative;
            z-index: 10;
        }
        .back-home-btn:hover, .back-home-btn:focus {
            background: linear-gradient(90deg, #d89530 60%, #e1a140 100%);
            color: #fff;
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 8px 24px rgba(225, 161, 64, 0.25);
            text-decoration: none;
        }
        .back-arrow {
            font-size: 1.3em;
            margin-right: 4px;
            transition: margin-right 0.2s;
        }
        .back-home-btn:hover .back-arrow {
            margin-right: 10px;
        }
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .container
            {
                width: 100%;
                padding: 0 1rem !important;
            }
            .container > .row {
                flex-direction: column;
            }
            .col, .col#logincont {
                max-width: 100%;
                flex: 0 0 100%;
            }
        }
        @media (max-width: 600px) {
            .container {
                width: 100%;
                padding: 0 0.5rem !important;
            }
            .col, .col#logincont {
                padding: 1rem 0.5rem !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }
            h1 {
                font-size: 1.5rem;
            }
            .back-home-btn {
                width: 100%;
                justify-content: center;
                font-size: 1rem;
                padding: 10px 0;
            }
            .field label, .form-label {
                font-size: 1rem;
            }
            .btn, .btn-primary, .resetButton {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        /* ...existing code... */
    </style>
</head>
<body>
<div class="container">
    <div class="row m-2">
        <div class="col p-3">
            <h1>Register</h1>
            <form action="web.php" method="post" id="registerForm">
                <div class="pt-3 field">
                    <label for="registerFirstname" class="form-label">Firstname</label>
                    <input type="text" class="form-control" id="registerFirstname"
                           placeholder="Enter firstname" name="firstname">
                    <small></small>
                </div>

                <div class="pt-3 field">
                    <label for="registerLastname" class="form-label">Lastname</label>
                    <input type="text" class="form-control" id="registerLastname"
                           placeholder="Enter lastname" name="lastname">
                    <small></small>
                </div>

                <div class="pt-3 field">
                    <label for="registerEmail" class="form-label">E-mail address</label>
                    <input type="text" class="form-control" id="registerEmail"
                           placeholder="Enter valid e-mail address" name="email">
                    <small></small>
                </div>

                <div class="pt-3 field">
                    <label for="registerPassword" class="form-label">Password <i class="bi bi-eye-slash-fill"
                                                                                 id="passwordEye"></i></label>
                    <input type="password" class="form-control passwordVisibiliy" name="password" id="registerPassword"
                           placeholder="Password (min 8 characters)">
                    <small></small>
                    <span id="strengthDisp" class="badge displayBadge">Weak</span>
                </div>

                <div class="pt-3 field">
                    <label for="registerPasswordConfirm" class="form-label">Password confirm</label>
                    <input type="password" class="form-control" name="passwordConfirm" id="registerPasswordConfirm"
                           placeholder="Password again">
                    <small></small>
                </div>

                <div class="pt-3">
                    <input type="hidden" name="action" value="register">
                    <button type="submit" class="btn btn-primary">Register</button>
                    <button type="reset" class="btn btn-primary resetButton">Cancel</button>
                </div>
            </form>

            <?php
            $r = 0;

            if (isset($_GET["r"]) and is_numeric($_GET['r'])) {
                $r = (int)$_GET["r"];

                if (array_key_exists($r, $messages)) {
                    echo '
                    <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
                        ' . $messages[$r] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        </button>
                    </div>
                    ';
                }
            }
            ?>
        </div>

        <div class="col" id="logincont">
            <h1>Login</h1>
            <form action="web.php" method="post" id="loginForm">
                <div class="pt-3">
                    <label for="loginUsername" class="form-label">Username</label>
                    <input type="text" class="form-control" id="loginUsername"
                           placeholder="Enter username" name="username">
                    <small></small>
                </div>
                <div class="pt-3">
                    <label for="loginPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="loginPassword" placeholder="Password"
                           name="password">
                    <small></small>
                </div>
                <div class="pt-3">
                    <input type="hidden" name="action" value="login">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>


            <?php

            $l = 0;

            if (isset($_GET["l"]) and is_numeric($_GET['l'])) {
                $l = (int)$_GET["l"];

                if (array_key_exists($l, $messages)) {
                    echo '
                    <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
                        ' . $messages[$l] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        </button>
                    </div>
                    ';
                }
            }
            ?>
            <a href="#" id="fl">Have you forgotten your password?</a>
            <form action="web.php" method="post" name="forget" id="forgetForm">
                <div class="pt-3">
                    <label for="forgetEmail" class="form-label">E-mail</label>
                    <input type="text" class="form-control" id="forgetEmail" placeholder="Enter your e-mail address"
                           name="email">
                    <small></small>
                </div>
                <div class="pt-3">
                    <input type="hidden" name="action" value="forget">
                    <button type="submit" class="btn btn-primary">Reset password</button>
                </div>
            </form>

            <?php

            $f = 0;

            if (isset($_GET["f"]) and is_numeric($_GET['f'])) {
                $f = (int)$_GET["f"];

                if (array_key_exists($f, $messages)) {
                    echo '
                    <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
                        ' . $messages[$f] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        </button>
                    </div>
                    ';
                }
            }
            ?>
            <!-- Fancy Back to Home Button at the bottom of the right-hand side form -->
            <div class="d-flex justify-content-end mt-4">
                <a href="../views/guest_view.php" class="back-home-btn">
                    <span class="back-arrow">&#8592;</span> Back to Home
                </a>
            </div>
        </div>

    </div>
</div>
</body>
</html>