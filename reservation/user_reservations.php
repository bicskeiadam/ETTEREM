<?php
session_start();
require_once '../register/config.php';

if (!isset($_SESSION['id_user']) || !is_int($_SESSION['id_user'])) {
    header("Location: ../register/index.php?l=0");
    exit();
}

$pdo = new PDO($dsn, PARAMS['USER'], PARAMS['PASS'], $pdoOptions);
$user_id = $_SESSION['id_user'];

// Delete past reservations (where end time is before now)
$deleteStmt = $pdo->prepare("
    DELETE FROM reservations
    WHERE user_id = :user_id
      AND TIMESTAMP(res_date, ADDTIME(start_time, SEC_TO_TIME(duration_hours*3600))) < NOW()
");
$deleteStmt->execute(['user_id' => $user_id]);

// Fetch upcoming reservations
$resStmt = $pdo->prepare("
    SELECT r.*, t.name AS table_name, t.location_description
    FROM reservations r
    JOIN tables t ON r.table_id = t.id
    WHERE r.user_id = :user_id
    ORDER BY r.res_date ASC, r.start_time ASC
");
$resStmt->execute(['user_id' => $user_id]);
$reservations = $resStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>My Reservations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../styles/userstyle.css">
    <style>
        .reservations-container {
            max-width: 900px;
            margin: 60px auto 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(225,161,64,0.10);
            padding: 2rem 2.5rem;
        }
        .reservations-title {
            font-size: 2.2rem;
            font-weight: bold;
            color: #E1A140;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .reservation-card {
            border: 1.5px solid #E1A140;
            border-radius: 14px;
            margin-bottom: 1.5rem;
            padding: 1.2rem 1.5rem;
            background: #fff8ef;
            box-shadow: 0 2px 8px rgba(225,161,64,0.07);
            transition: box-shadow 0.2s;
        }
        .reservation-card:last-child {
            margin-bottom: 0;
        }
        .reservation-card:hover {
            box-shadow: 0 6px 18px rgba(225,161,64,0.18);
        }
        .reservation-table {
            font-size: 1.2rem;
            font-weight: bold;
            color: #c98b30;
        }
        .reservation-details {
            margin-top: 0.5rem;
            color: #444;
        }
        .reservation-status {
            display: inline-block;
            padding: 0.3em 1em;
            border-radius: 20px;
            font-size: 0.95em;
            font-weight: 600;
            margin-top: 0.7em;
        }
        .status-active { background: #4CAF50; color: #fff; }
        .status-cancelled { background: #f1c40f; color: #222; }
        .status-completed { background: #aaa; color: #fff; }
        .status-pending { background: #e87e01; color: #fff; }
        .no-reservations {
            text-align: center;
            color: #888;
            font-size: 1.2rem;
            margin: 2.5rem 0;
        }
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
            margin: 30px 0 0 0;
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
        .map-embed-container {
            max-width: 900px;
            margin: 40px auto 0 auto;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(225,161,64,0.10);
            background: #fff;
            padding: 0;
        }
        .map-embed-container iframe {
            display: block;
            width: 100%;
            height: 400px;
            border: none;
        }

        /* Responsive styles */
        @media (max-width: 1000px) {
            .map-embed-container, .reservations-container {
                max-width: 98vw;
            }
            .map-embed-container iframe {
                height: 250px;
            }
        }
        @media (max-width: 700px) {
            .reservations-container {
                padding: 1.2rem 0.5rem;
            }
            .reservations-title {
                font-size: 1.5rem;
            }
            .reservation-card {
                padding: 0.8rem 0.7rem;
                font-size: 0.98rem;
            }
            .reservation-table {
                font-size: 1.05rem;
            }
            .back-home-btn {
                font-size: 1rem;
                padding: 8px 14px;
            }
        }
        @media (max-width: 480px) {
            .reservations-container {
                padding: 0.5rem 0.1rem;
            }
            .reservation-card {
                padding: 0.6rem 0.3rem;
                font-size: 0.93rem;
            }
            .map-embed-container iframe {
                height: 160px;
            }
            .back-home-btn {
                font-size: 0.95rem;
                padding: 7px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="reservations-container">
        <div class="reservations-title">My Reservations</div>
        <?php if (empty($reservations)): ?>
            <div class="no-reservations">You have no upcoming reservations.</div>
        <?php else: ?>
            <?php foreach ($reservations as $res): ?>
                <div class="reservation-card">
                    <div class="reservation-table">
                        Table: <?= htmlspecialchars($res['table_name']) ?>
                    </div>
                    <div class="reservation-details">
                        <div><b>Date:</b> <?= htmlspecialchars($res['res_date']) ?></div>
                        <div><b>Time:</b> <?= htmlspecialchars(substr($res['start_time'], 0, 5)) ?> &ndash;
                            <?php
                                $start = strtotime($res['start_time']);
                                $end = date('H:i', $start + $res['duration_hours'] * 3600);
                                echo $end;
                            ?>
                        </div>
                        <div><b>Guests:</b> <?= (int)$res['guest_number'] ?></div>
                        <div><b>Location:</b> <?= htmlspecialchars($res['location_description']) ?></div>
                        <div><b>Reservation code:</b> <?= htmlspecialchars($res['reservation_code']) ?></div>
                        <span class="reservation-status status-<?= htmlspecialchars($res['status']) ?>">
                            <?= ucfirst($res['status']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div style="text-align:right;">
            <a href="../views/user_view.php" class="back-home-btn">
                <span class="back-arrow">&#8592;</span> Back to Home
            </a>
        </div>
    </div>
    <div class="map-embed-container">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d264.1550360076456!2d19.668211868216392!3d46.10143325799806!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2srs!4v1751483109587!5m2!1sen!2srs"
            width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</body>
</html>
