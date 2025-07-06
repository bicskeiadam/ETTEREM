<?php
require_once '../register/config.php';
require_once '../register/functions_def.php';
session_start();

$pdo = new PDO($dsn, PARAMS['USER'], PARAMS['PASS'], $pdoOptions);

// --- Ensure email is set in session for logged-in users ---
if (isset($_SESSION['id_user']) && empty($_SESSION['email'])) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id_user = ?");
    $stmt->execute([$_SESSION['id_user']]);
    $user = $stmt->fetch();
    if ($user && !empty($user['email'])) {
        $_SESSION['email'] = $user['email'];
    }
}

// Accept dish_id from GET or POST, and order_number from GET if present
$dish_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['dish_id']) ? (int)$_POST['dish_id'] : 0);
$order_number = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';

$dish = null;
$orderSuccess = false;
$orderError = '';

// Pre-fill user data if logged in (but phone is always empty)
$userData = [
    'name' => '',
    'email' => '',
    'phone' => ''
];
if (isset($_SESSION['id_user'])) {
    $userData['name'] = trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''));
    $userData['email'] = $_SESSION['email'] ?? '';
}

if ($dish_id > 0) {
    $stmt = $pdo->prepare("SELECT m.id, m.name, m.description, m.price, m.image_url, c.name AS category_name
                           FROM menu_items m
                           LEFT JOIN menu_categories c ON m.category_id = c.id
                           WHERE m.id = ?");
    $stmt->execute([$dish_id]);
    $dish = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dish_id = (int)$_POST['dish_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $quantity = max(1, (int)$_POST['quantity']);
    $notes = trim($_POST['notes']);
    $order_number = isset($_POST['order_number']) ? trim($_POST['order_number']) : $order_number;

    // Address fields
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $country = 'Serbia'; // Always Serbia

    // Fetch dish again for email
    $stmt = $pdo->prepare("SELECT m.id, m.name, m.description, m.price, m.image_url, c.name AS category_name
                           FROM menu_items m
                           LEFT JOIN menu_categories c ON m.category_id = c.id
                           WHERE m.id = ?");
    $stmt->execute([$dish_id]);
    $dish = $stmt->fetch();

    // Validate required fields (phone is now required)
    if ($dish && filter_var($email, FILTER_VALIDATE_EMAIL) && $name && $phone && $street && $city && $zip) {
        // Compose email
        $subject = "Order Confirmation - " . htmlspecialchars($dish['name']);
        $body = "<h2>Thank you for your order!</h2>
            <p><b>Dish:</b> " . htmlspecialchars($dish['name']) . "</p>
            <p><b>Quantity:</b> " . $quantity . "</p>
            <p><b>Name:</b> " . htmlspecialchars($name) . "</p>
            <p><b>Email:</b> " . htmlspecialchars($email) . "</p>
            <p><b>Phone:</b> " . htmlspecialchars($phone) . "</p>
            <p><b>Delivery Address:</b><br>"
                . htmlspecialchars($street) . "<br>"
                . htmlspecialchars($zip) . " " . htmlspecialchars($city) . "<br>"
                . "Serbia</p>
            <p><b>Notes:</b> " . nl2br(htmlspecialchars($notes)) . "</p>";
        if ($order_number !== '') {
            $body .= "<p><b>Order Number:</b> " . htmlspecialchars($order_number) . "</p>";
        }
        $body .= "<p>Your order will arrive in 25–30 minutes.</p>";

        $emailData = [
            'subject' => $subject,
            'altBody' => strip_tags($body)
        ];

        // Send email
        sendEmail($pdo, $email, $emailData, $body, isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0);

        $orderSuccess = true;
        // Save address for map display
        $orderAddress = $street . ', ' . $zip . ' ' . $city . ', Serbia';
    } else {
        $orderError = "Please fill all required fields with valid data (including phone and address).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Dish</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../styles/userstyle.css">
    <style>
        .order-container {
            max-width: 500px;
            margin: 60px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(225,161,64,0.10);
            padding: 2rem 2.5rem;
        }
        .order-title {
            font-size: 2rem;
            font-weight: bold;
            color: #E1A140;
            margin-bottom: 1.2rem;
            text-align: center;
        }
        .order-form label {
            font-weight: 500;
            margin-top: 1rem;
        }
        .order-form input, .order-form textarea {
            width: 100%;
            padding: 0.6em;
            margin-top: 0.3em;
            border-radius: 8px;
            border: 1px solid #e1a14077;
            margin-bottom: 1em;
        }
        .order-form button {
            background: linear-gradient(90deg, #E1A140 60%, #f0c27b 100%);
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
            padding: 10px 22px;
            border-radius: 30px;
            border: none;
            outline: none;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            width: 100%;
        }
        .order-form button:hover {
            background: linear-gradient(90deg, #d89530 60%, #e1a140 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .order-success {
            color: #4CAF50;
            font-weight: bold;
            text-align: center;
            margin: 2em 0;
        }
        .order-error {
            color: #e74c3c;
            font-weight: bold;
            text-align: center;
            margin: 1em 0;
        }
        .map-embed-container {
            margin-top: 2em;
        }
        @media (max-width: 600px) {
            .order-container {
                padding: 1.2rem 0.5rem;
            }
            .order-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
<!-- Header -->
<header class="header text-white">
    <h1 class="headertext fs-5 m-0" id="section-title">Lapmesterek</h1>
    <div class="user-info">
        <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" alt="User" width="24" height="24" />
    </div>
</header>
<div id="user-popup" class="user-popup hidden">
    <div class="user-popup-content">
        <?php
        if (isset($_SESSION['id_user'])) {
            $firstname = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : '';
            $lastname = isset($_SESSION['lastname']) ? $_SESSION['lastname'] : '';
            $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
            if (!$firstname || !$lastname || !$role) {
                require_once '../register/config.php';
                if (!isset($dsn) || !isset($pdoOptions) || !defined('PARAMS')) {
                    die('Database configuration variables are missing in config.php');
                }
                try {
                    $pdo2 = new PDO($dsn, PARAMS['USER'], PARAMS['PASS'], $pdoOptions);
                } catch (PDOException $e) {
                    die('Database connection failed: ' . $e->getMessage());
                }
                $stmt = $pdo2->prepare("SELECT firstname, lastname, role FROM users WHERE id_user = ?");
                $stmt->execute([$_SESSION['id_user']]);
                $user = $stmt->fetch();
                if ($user) {
                    $firstname = $user['firstname'];
                    $lastname = $user['lastname'];
                    $role = $user['role'];
                    $_SESSION['firstname'] = $firstname;
                    $_SESSION['lastname'] = $lastname;
                    $_SESSION['role'] = $role;
                }
            }
            echo '<p id="welcome-text">Hello, ' . htmlspecialchars($firstname . ' ' . $lastname) . '!</p>';
            echo '<button id="logout-btn" onclick="location.href=\'../register/logout.php\'">Log out</button>';
            if ($role === 'staff') {
                echo '<button id="admin-btn" onclick="location.href=\'../views/staff_dashboard.php\'" style="margin-top:2px;">Staff</button>';
            }
            if ($role === 'admin') {
                echo '<button id="admin-btn" onclick="location.href=\'../views/admin_dashbord.php\'" style="margin-top:2px;">Admin</button>';
            }
        } else {
            echo '<p id="welcome-text">Hello, Guest!</p>';
            echo '<button id="login-btn" onclick="location.href=\'../register/index.php\'">Log in</button>';
        }
        ?>
    </div>
</div>

    <div class="order-container">
        <?php if ($orderSuccess): ?>
            <div class="order-success">
                Thank you for your order! A confirmation email has been sent.
            </div>
            <?php if (!empty($orderAddress)): ?>
                <div class="map-embed-container" style="margin-top:2em;">
                    <h5 class="mb-2" style="color:#E1A140;">Your delivery address on map:</h5>
                    <iframe
                        width="100%"
                        height="350"
                        style="border:0;border-radius:12px;box-shadow:0 2px 12px rgba(225,161,64,0.10);"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=<?= urlencode($orderAddress) ?>&output=embed">
                    </iframe>
                </div>
            <?php endif; ?>
        <?php elseif ($dish): ?>
            <div class="order-title">Order: <?= htmlspecialchars($dish['name']) ?></div>
            <div style="text-align:center;margin-bottom:1em;">
                <?php if (!empty($dish['image_url'])): ?>
                    <img src="<?= htmlspecialchars($dish['image_url']) ?>" alt="<?= htmlspecialchars($dish['name']) ?>" style="max-width:180px;max-height:120px;border-radius:10px;">
                <?php endif; ?>
                <div style="margin-top:0.5em;">
                    <span style="font-weight:bold; color:#E1A140;">
                        <?= htmlspecialchars($dish['category_name']) ?>
                    </span>
                    <span style="margin-left:1em; color:#444;">
                        €<?= number_format($dish['price'] / 100, 2) ?>
                    </span>
                </div>
                <div style="font-size:0.98em; color:#666; margin-top:0.5em;">
                    <?= htmlspecialchars($dish['description']) ?>
                </div>
            </div>
            <?php if ($orderError): ?>
                <div class="order-error"><?= htmlspecialchars($orderError) ?></div>
            <?php endif; ?>
            <form class="order-form" method="post">
                <input type="hidden" name="dish_id" value="<?= (int)$dish['id'] ?>">
                <?php if ($order_number !== ''): ?>
                    <input type="hidden" name="order_number" value="<?= htmlspecialchars($order_number) ?>">
                <?php endif; ?>
                <label for="name">Your Name*</label>
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($userData['name']) ?>" readonly>
                <label for="email">Email*</label>
                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($userData['email']) ?>" readonly>
                <label for="phone">Phone*</label>
                <input type="text" name="phone" id="phone" required value="">
                <label for="quantity">Quantity*</label>
                <input type="number" name="quantity" id="quantity" min="1" value="1" required>
                <label for="street">Street Address*</label>
                <input type="text" name="street" id="street" required>
                <label for="city">City*</label>
                <input type="text" name="city" id="city" required>
                <label for="zip">ZIP Code*</label>
                <input type="text" name="zip" id="zip" required>
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="2"></textarea>
                <?php if ($order_number !== ''): ?>
                    <div style="margin-bottom:1em;"><b>Order Number:</b> <?= htmlspecialchars($order_number) ?></div>
                <?php endif; ?>
                <button type="submit">Place Order</button>
            </form>
        <?php else: ?>
            <div class="order-error">Dish not found.</div>
        <?php endif; ?>
    </div>

<!-- Footer -->
<footer class="footer text-center text-white">
    <div class="d-flex justify-content-around align-items-center w-100">
        <a href="../views/user_view.php#introCarousel" class="text-white text-decoration-none">🏠 Home</a>
        <?php if (isset($_SESSION['id_user'])): ?>
            <a href="../reservation/user_reservations.php" class="a-res-btn text-white text-decoration-none">📅 Reservations</a>
        <?php else: ?>
            <a id="res-btn" class="a-res-btn text-white text-decoration-none" href="javascript:void(0);">📅 Reservations</a>
        <?php endif; ?>
        <a id="contact-btn" class="a-contact-btn text-white text-decoration-none">📞 Contact</a>
    </div>
    <div id="contact-popup" class="contact-popup hidden">
        <p class="contact-phone">📞 +3819669193222</p>
    </div>
    <div id="res-popup" class="res-popup hidden">
        <p class="res-text">Register or Log in first!</p>
    </div>
</footer>

<script src="../js/functions.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
