<?php
session_start();
require_once '../register/config.php';

// Only allow staff or admin
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    header('Location: ../views/user_view.php');
    exit;
}

$pdo = new PDO($dsn, PARAMS['USER'], PARAMS['PASS'], $pdoOptions);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'], $_POST['action'])) {
    $id = (int)$_POST['reservation_id'];
    $action = $_POST['action'];
    if ($action === 'accept') {
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'active' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'cancel') {
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: staff_dashboard.php');
    exit;
}

// Fetch pending reservations
$stmt = $pdo->query("
    SELECT r.*, u.firstname, u.lastname, t.name AS table_name
    FROM reservations r
    JOIN users u ON r.user_id = u.id_user
    JOIN tables t ON r.table_id = t.id
    WHERE r.status = 'pending'
    ORDER BY r.res_date, r.start_time
");
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="../styles/staff.css" />
</head>
<body>
<header class="header text-white">
    <h1 class="headertext fs-5 m-0" id="section-title">Staff Dashboard</h1>
    <div class="user-info">
        <span class="staff-name">
        <?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?>
        </span>
    </div>
</header>
<main class="container my-4">
    <h2 class="res-text mb-4 text-center">All Reservations</h2>
    <div class="row">
        <?php if (empty($reservations)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No reservations found.</div>
            </div>
        <?php else: ?>
            <?php foreach ($reservations as $res): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="reservation-card p-4 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0"><?= htmlspecialchars($res['firstname'] . ' ' . $res['lastname']) ?></h5>
                            <span class="status-badge status-<?= $res['status'] ?>">
                                <?= ucfirst($res['status']) ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Table:</strong> <?= htmlspecialchars($res['table_name']) ?><br>
                            <strong>Date:</strong> <?= htmlspecialchars($res['res_date']) ?><br>
                            <strong>Time:</strong> <?= htmlspecialchars($res['start_time']) ?> (<?= $res['duration_hours'] ?>h)<br>
                            <strong>Guests:</strong> <?= $res['guest_number'] ?><br>
                            <strong>Code:</strong> <span class="text-monospace"><?= htmlspecialchars($res['reservation_code']) ?></span>
                        </div>
                        <form method="post" class="d-flex">
                            <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                            <?php if ($res['status'] === 'pending'): ?>
                                <button name="action" value="accept" class="btn btn-success btn-sm action-btn">Accept</button>
                                <button name="action" value="cancel" class="btn btn-warning btn-sm action-btn">Cancel</button>
                            <?php endif; ?>
                            <button name="action" value="delete" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
<footer class="footer text-center text-white">
    <div class="d-flex justify-content-around align-items-center w-100">
        <a href="../views/user_view.php" class="text-white text-decoration-none">🏠 Home</a>
        <a id="contact-btn" class="a-contact-btn text-white text-decoration-none">📞 Contact</a>
    </div>
    <div id="contact-popup" class="contact-popup hidden">
        <p class="contact-phone">📞 +381244797655</p>
    </div>
</footer>

<script src="../js/functions.js"> </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>