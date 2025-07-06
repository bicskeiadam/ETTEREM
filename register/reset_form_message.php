<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link href="../styles/log.css" rel="stylesheet">
    <script src="js/script.js"></script>

    <title>Reset password</title>
    <style>
        h2{
            font-weight: bold;
            font-size: 2rem;
            color: #f5deb3;
            margin-bottom: 10px;
            border-bottom: 3px solid #f5deb3;
            padding-bottom: 5px;
            text-transform: uppercase;
        }
        .alert{
            background-color: #333333;
            font-weight: bold;
            font-size: 1rem;
            color: #f5deb3;
            margin-bottom: 10px;
            border-bottom: 3px solid #f5deb3;
            padding-bottom: 5px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<div class="container mt-3">
    <h2>Reset password</h2>
    <div class="alert">

        <?php
        include_once 'config.php';
        $rf = 0;

        if (isset($_GET["rf"]) and is_numeric($_GET['rf'])) {
            $rf = (int)$_GET["rf"];

            if (array_key_exists($rf, $messages)) {
                echo '<p>'.$messages[$rf].'</p>';
            }
        }
        ?>
    </div>
</div>
</body>
</html>