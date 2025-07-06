<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../register/config.php';
$pdo = new PDO($dsn, PARAMS['USER'], PARAMS['PASS'], $pdoOptions);

// --- AJAX endpoint for reservation state ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'table_states') {
    $date = $_GET['date'] ?? '';
    $startTime = $_GET['startTime'] ?? '';
    $endTime = $_GET['endTime'] ?? '';
    $result = [];

    if ($date && $startTime && $endTime) {
        // Get all tables
        $stmt = $pdo->query("SELECT id, name FROM tables");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tables[$row['name']] = [
                'id' => $row['id'],
                'status' => 'available'
            ];
        }

        // Get reservations for the selected time
        $resStmt = $pdo->prepare("
            SELECT t.name, r.status
            FROM reservations r
            JOIN tables t ON t.id = r.table_id
            WHERE r.res_date = ?
            AND (
                (r.start_time <= ? AND ADDTIME(r.start_time, SEC_TO_TIME(r.duration_hours*3600)) > ?)
                OR
                (r.start_time < ? AND ADDTIME(r.start_time, SEC_TO_TIME(r.duration_hours*3600)) >= ?)
                OR
                (r.start_time >= ? AND r.start_time < ?)
            )
            AND r.status IN ('active', 'cancelled')
        ");
        $resStmt->execute([
            $date,
            $startTime, $startTime,
            $endTime, $endTime,
            $startTime, $endTime
        ]);
        while ($row = $resStmt->fetch(PDO::FETCH_ASSOC)) {
            // 'active' means reserved, 'cancelled' means cancelled
            $tables[$row['name']]['status'] = $row['status'] === 'active' ? 'reserved' : 'cancelled';
        }
        foreach ($tables as $name => $data) {
            $result[$name] = $data['status'];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// --- AJAX endpoint for latest reservation end time ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'latest_end_time') {
    $tableName = $_GET['table'] ?? '';
    $date = $_GET['date'] ?? '';
    $result = ['latest_end_time' => null];
    if ($tableName && $date) {
        $stmt = $pdo->prepare("
            SELECT ADDTIME(start_time, SEC_TO_TIME(duration_hours*3600)) AS end_time
            FROM reservations r
            JOIN tables t ON t.id = r.table_id
            WHERE t.name = ? AND r.res_date = ? AND r.status = 'active'
            ORDER BY end_time DESC LIMIT 1
        ");
        $stmt->execute([$tableName, $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['end_time']) {
            $result['latest_end_time'] = $row['end_time'];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
// Reservation logic
$resMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['id_user']) || !is_int($_SESSION['id_user'])) {
        $resMsg = '<div class="error-msg">You must be logged in to make a reservation.</div>';
    } else {
        $tableName = $_POST['selectedTable'] ?? '';
        $date = $_POST['date'] ?? '';
        $startTime = $_POST['startTime'] ?? '';
        $endTime = $_POST['endTime'] ?? '';
        $guests = (int)($_POST['guests'] ?? 0);

        // Validate
        if (!$tableName || !$date || !$startTime || !$endTime || $guests < 1) {
            $resMsg = '<div class="error-msg">Please fill all fields correctly.</div>';
        } else {
            // Get table id
            $stmt = $pdo->prepare("SELECT id, seats FROM tables WHERE name = ?");
            $stmt->execute([$tableName]);
            $table = $stmt->fetch();
            if (!$table) {
                $resMsg = '<div class="error-msg">Invalid table selected.</div>';
            } elseif ($guests > $table['seats']) {
                $resMsg = '<div class="error-msg">Too many guests for this table.</div>';
            } else {
                // Calculate duration in hours
                $start = strtotime($startTime);
                $end = strtotime($endTime);
                $duration = ($end - $start) / 3600;

                // --- Enforce 15 min gap after latest reservation ---
                $latestEndStmt = $pdo->prepare("
                    SELECT ADDTIME(start_time, SEC_TO_TIME(duration_hours*3600)) AS end_time
                    FROM reservations
                    WHERE table_id = ? AND res_date = ? AND status = 'active'
                    ORDER BY end_time DESC LIMIT 1
                ");
                $latestEndStmt->execute([$table['id'], $date]);
                $latestEnd = $latestEndStmt->fetchColumn();
                if ($latestEnd) {
                    $minStart = date('H:i', strtotime($latestEnd) + 15*60);
                    if ($startTime < $minStart) {
                        $resMsg = '<div class="error-msg">You can only book this table from ' . htmlspecialchars($minStart) . ' or later, due to a previous reservation.</div>';
                    }
                }
                if (empty($resMsg)) {
                    if ($duration < 1 || $duration > 6) {
                        $resMsg = '<div class="error-msg">Reservation must be 1-6 hours.</div>';
                    } else {
                        // Check for overlapping reservations
                        $check = $pdo->prepare("
                            SELECT 1 FROM reservations
                            WHERE table_id = ? AND res_date = ?
                            AND (
                                (start_time <= ? AND ADDTIME(start_time, SEC_TO_TIME(duration_hours*3600)) > ?)
                                OR
                                (start_time < ? AND ADDTIME(start_time, SEC_TO_TIME(duration_hours*3600)) >= ?)
                                OR
                                (start_time >= ? AND start_time < ?)
                            )
                            AND status = 'active'
                        ");
                        $check->execute([
                            $table['id'], $date,
                            $startTime, $startTime,
                            $endTime, $endTime,
                            $startTime, $endTime
                        ]);
                        if ($check->fetch()) {
                            $resMsg = '<div class="error-msg">This table is already reserved for the selected time.</div>';
                        } else {
                            // Insert reservation
                            $reservation_code = substr(md5(uniqid(mt_rand(), true)), 0, 12);
                            $user_id = $_SESSION['id_user'];

                            $ins = $pdo->prepare("
                                INSERT INTO reservations (user_id, table_id, reservation_code, res_date, start_time, duration_hours, guest_number, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                            ");
                            $ins->execute([
                                $user_id,
                                $table['id'],
                                $reservation_code,
                                $date,
                                $startTime,
                                $duration,
                                $guests
                            ]);
                            $resMsg = '<div class="success-msg">Reservation successful! Your code: <b>' . htmlspecialchars($reservation_code) . '</b></div>';
                        }
                    }
                }
            }
        }
    }
}

// Fetch tables and their latest reservation status
$stmt = $pdo->query("
    SELECT t.id, t.name, t.location_description,
    COALESCE(r.status, 'available') AS status
FROM tables t
LEFT JOIN (
    SELECT table_id, MIN(status) AS status
    FROM reservations
    WHERE res_date = CURDATE()
    AND start_time <= CURTIME()
    AND ADDTIME(start_time, SEC_TO_TIME(duration_hours*3600)) >= CURTIME()
    GROUP BY table_id
) r ON t.id = r.table_id
");
$tables = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $tables[$row['name']] = [
        'description' => $row['location_description'],
        'status' => $row['status']
    ];
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reservation</title>
    <link rel="stylesheet" href="../styles/table.css">
</head>
<body>
<div class="reservation-layout">
    <div class="map-container">
        <img src="../img/asztalok.png" alt="Étterem alaprajz">
    </div>
    <div id="tableTooltip" class="table-tooltip"></div>
    <form class="form-container" method="post">
        <h2>Reservation</h2>
        <?php if (!empty($resMsg)) echo $resMsg; ?>

        <div class="legend">
            <span class="available">Available</span>
            <span class="reserved">Reserved</span>
            <span class="cancelled">Cancelled</span>
        </div>

        <div class="selected-table">
            <label for="selectedTable">Selected table:</label>
            <input type="text" id="selectedTable" name="selectedTable" readonly placeholder="Nincs kiválasztva">
        </div>

        <div class="form-grid">
            <div class="row-4">
                <?php foreach (['V1','V2','V3','V4'] as $t):
                    $status = isset($tables[$t]['status']) ? $tables[$t]['status'] : 'available';
                    $desc = isset($tables[$t]['description']) ? htmlspecialchars($tables[$t]['description']) : '';
                    $statusClass = ($status === 'active' || $status === 'reserved') ? 'reserved' : ($status === 'cancelled' ? 'cancelled' : 'available');
                    $disabled = ($statusClass === 'reserved') ? 'disabled' : '';
                ?>
                <button type="button" class="table-button <?= $statusClass ?>" data-name="<?= $t ?>" data-desc="<?= $desc ?>" <?= $disabled ?>><?= $t ?></button>
                <?php endforeach; ?>
            </div>
            <div class="row-grid">
                <?php foreach (['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'] as $t):
                    $status = isset($tables[$t]['status']) ? $tables[$t]['status'] : 'available';
                    $desc = isset($tables[$t]['description']) ? htmlspecialchars($tables[$t]['description']) : '';
                    $statusClass = ($status === 'active' || $status === 'reserved') ? 'reserved' : ($status === 'cancelled' ? 'cancelled' : 'available');
                    $disabled = ($statusClass === 'reserved') ? 'disabled' : '';
                ?>
                <button type="button" class="table-button <?= $statusClass ?>" data-name="<?= $t ?>" data-desc="<?= $desc ?>" <?= $disabled ?>><?= $t ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="time-section">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date">

            <label for="startTime">Start time:</label>
            <input type="time" id="startTime" name="startTime" step="900">

            <label for="endTime">End time:</label>
            <input type="time" id="endTime" name="endTime" step="900">

            <label for="guests">Guest number:</label>
            <input type="number" id="guests" name="guests" min="1" max="8" placeholder="Pl: 4">
        </div>

        <button type="submit" class="submit-btn">RESERVATION</button>
        <button type="reset" class="reset-btn">RESET</button>
        <a href="../views/user_view.php" class="back-home-btn">
            <span class="back-arrow">&#8592;</span> Back to Home
        </a>
    </form>
</div>

<script>
    // Tooltip logic
    const tooltip = document.getElementById('tableTooltip');
    document.querySelectorAll('.table-button').forEach(button => {
        button.addEventListener('mouseenter', function(e) {
            const desc = this.getAttribute('data-desc');
            if (desc) {
                tooltip.textContent = desc;
                tooltip.style.display = 'block';
                const rect = this.getBoundingClientRect();
                tooltip.style.left = (rect.left + window.scrollX + rect.width/2 - tooltip.offsetWidth/2) + 'px';
                tooltip.style.top = (rect.top + window.scrollY - tooltip.offsetHeight - 8) + 'px';
            }
        });
        button.addEventListener('mousemove', function(e) {
            tooltip.style.left = (e.pageX + 10) + 'px';
            tooltip.style.top = (e.pageY - 40) + 'px';
        });
        button.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
        button.addEventListener('click', function (e) {
            e.preventDefault();
            if (this.disabled) return;
            const name = this.getAttribute('data-name');
            document.getElementById('selectedTable').value = name;
            document.querySelectorAll('.table-button').forEach(btn => btn.classList.remove('selected'));
            this.classList.add('selected');
            fetchLatestEndTime();
        });
    });

    // --- Async fetch reservation state ---
    const dateInput = document.getElementById('date');
    const startInput = document.getElementById('startTime');
    const endInput = document.getElementById('endTime');
    const submitBtn = document.querySelector('.submit-btn');
    let minStartTime = null;

    function fetchTableStates() {
        const date = dateInput.value;
        const startTime = startInput.value;
        const endTime = endInput.value;
        if (!date || !startTime || !endTime) return;
        fetch(`asztalfoglalas.php?ajax=table_states&date=${encodeURIComponent(date)}&startTime=${encodeURIComponent(startTime)}&endTime=${encodeURIComponent(endTime)}`)
            .then(res => res.json())
            .then(states => {
                document.querySelectorAll('.table-button').forEach(btn => {
                    const name = btn.getAttribute('data-name');
                    btn.classList.remove('reserved', 'cancelled', 'available');
                    btn.disabled = false;
                    if (states[name] === 'reserved') {
                        btn.classList.add('reserved');
                        btn.disabled = true;
                        if (btn.classList.contains('selected')) {
                            btn.classList.remove('selected');
                            document.getElementById('selectedTable').value = '';
                        }
                    } else if (states[name] === 'cancelled') {
                        btn.classList.add('cancelled');
                    } else {
                        btn.classList.add('available');
                    }
                });
            });
    }
    dateInput.addEventListener('change', () => {
        fetchTableStates();
        fetchLatestEndTime();
    });
    startInput.addEventListener('change', fetchTableStates);
    endInput.addEventListener('change', fetchTableStates);

    // --- Enforce min start time logic ---
    function pad(num) { return num.toString().padStart(2, '0'); }
    function addMinutesToTime(timeStr, mins) {
        const [h, m, s] = timeStr.split(':');
        let date = new Date();
        date.setHours(parseInt(h), parseInt(m), s ? parseInt(s) : 0, 0);
        date.setMinutes(date.getMinutes() + mins);
        return pad(date.getHours()) + ':' + pad(date.getMinutes());
    }
    function fetchLatestEndTime() {
        const table = document.getElementById('selectedTable').value;
        const date = dateInput.value;
        if (!table || !date) {
            startInput.min = '';
            minStartTime = null;
            validateTimeInputs();
            return;
        }
        fetch(`asztalfoglalas.php?ajax=latest_end_time&table=${encodeURIComponent(table)}&date=${encodeURIComponent(date)}`)
            .then(res => res.json())
            .then(data => {
                if (data.latest_end_time) {
                    // Add 15 minutes
                    minStartTime = addMinutesToTime(data.latest_end_time, 15);
                    startInput.min = minStartTime;
                } else {
                    minStartTime = null;
                    startInput.min = '';
                }
                validateTimeInputs();
            });
    }
    startInput.addEventListener('change', validateTimeInputs);
    endInput.addEventListener('change', validateTimeInputs);
    dateInput.addEventListener('change', validateTimeInputs);
    document.getElementById('selectedTable').addEventListener('change', fetchLatestEndTime);

    function validateTimeInputs() {
        const startVal = startInput.value;
        const endVal = endInput.value;
        let valid = true;
        if (!startVal || !endVal) valid = false;
        if (minStartTime && startVal && startVal < minStartTime) valid = false;
        if (startVal && endVal && startVal >= endVal) valid = false;
        submitBtn.disabled = !valid;
    }
    // Disable submit by default
    submitBtn.disabled = true;
</script>

</body>
</html>
