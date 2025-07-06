<?php
require_once '../register/config.php';
require_once '../register/functions_def.php';

$conn = new mysqli(PARAMS['HOST'], PARAMS['USER'], PARAMS['PASS'], PARAMS['DBNAME']);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- Handle form submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Edit user role
    if (isset($_POST['edit_user_role'])) {
        $stmt = $conn->prepare("UPDATE users SET role=? WHERE id_user=?");
        $stmt->bind_param("si", $_POST['role'], $_POST['id_user']);
        $stmt->execute();
    }

    // Edit reservation status
    if (isset($_POST['edit_reservation_status'])) {
        $stmt = $conn->prepare("UPDATE reservations SET status=? WHERE id=?");
        $stmt->bind_param("si", $_POST['status'], $_POST['id']);
        $stmt->execute();
    }

    // Add new table
    if (isset($_POST['add_table'])) {
        $stmt = $conn->prepare("INSERT INTO tables (name, location_description, seats, is_smoking) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $_POST['name'], $_POST['location'], $_POST['seats'], $_POST['is_smoking']);
        $stmt->execute();
    }

    // Edit table
    if (isset($_POST['edit_table'])) {
        $stmt = $conn->prepare("UPDATE tables SET name=?, location_description=?, seats=?, is_smoking=? WHERE id=?");
        $stmt->bind_param("ssiii", $_POST['name'], $_POST['location'], $_POST['seats'], $_POST['is_smoking'], $_POST['id']);
        $stmt->execute();
    }
}

// --- Handle deletions ---
if (isset($_GET['delete_user'])) {
    $conn->query("DELETE FROM users WHERE id_user=" . intval($_GET['delete_user']));
}
if (isset($_GET['delete_reservation'])) {
    $conn->query("DELETE FROM reservations WHERE id=" . intval($_GET['delete_reservation']));
}
if (isset($_GET['delete_table'])) {
    $conn->query("DELETE FROM tables WHERE id=" . intval($_GET['delete_table']));
}

// --- Queries ---
$users = $conn->query("SELECT * FROM users");
$reservations = $conn->query("SELECT r.*, u.firstname, u.lastname, t.name AS table_name FROM reservations r
                              JOIN users u ON r.user_id = u.id_user
                              JOIN tables t ON r.table_id = t.id");
$tables = $conn->query("SELECT * FROM tables");
$today = date('Y-m-d');
$today_reservations = $conn->query("SELECT r.*, u.firstname, u.lastname, t.name AS table_name FROM reservations r
                                    JOIN users u ON r.user_id = u.id_user
                                    JOIN tables t ON r.table_id = t.id
                                    WHERE r.res_date = '$today'");

$users_by_role = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$staff_users = $conn->query("SELECT * FROM users WHERE role = 'staff'");

$res_by_status = $conn->query("SELECT status, COUNT(*) as count FROM reservations GROUP BY status");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>
<div style="display: flex; flex-direction: row; align-items: center; gap: 20px;">
    <h1>Admin Dashboard</h1>
    <a href="./user_view.php" class="back-home-btn">
        <span class="back-arrow">&#8592;</span> Back to Home
    </a>

</div>


<h2>Users</h2>
<table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
    <?php foreach ($users as $u): ?>
        <tr class="<?= $u['is_banned'] ? 'banned' : '' ?>">
            <td><?= $u['id_user'] ?></td>
            <td><?= $u['firstname'] . ' ' . $u['lastname'] ?></td>
            <td><?= $u['email'] ?></td>
            <td>
                <form method="POST" class="inline">
                    <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                    <select name="role">
                        <option value="user" <?= $u['role'] == 'user' ? 'selected' : '' ?>>user</option>
                        <option value="staff" <?= $u['role'] == 'staff' ? 'selected' : '' ?>>staff</option>
                        <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>admin</option>
                    </select>
                    <button name="edit_user_role">Save</button>
                </form>
            </td>
            <td><a href="?delete_user=<?= $u['id_user'] ?>" onclick="return confirm('Delete this user?')">Delete</a></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Staff Members</h2>
<table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
    <?php foreach ($staff_users as $u): ?>
        <tr class="<?= $u['is_banned'] ? 'banned' : '' ?>">
            <td><?= $u['id_user'] ?></td>
            <td><?= $u['firstname'] . ' ' . $u['lastname'] ?></td>
            <td><?= $u['email'] ?></td>
            <td>
                <form method="POST" class="inline">
                    <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                    <select name="role">
                        <option value="user" <?= $u['role'] == 'user' ? 'selected' : '' ?>>user</option>
                        <option value="staff" <?= $u['role'] == 'staff' ? 'selected' : '' ?>>staff</option>
                        <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>admin</option>
                    </select>
                    <button name="edit_user_role">Save</button>
                </form>
            </td>
            <td><a href="?delete_user=<?= $u['id_user'] ?>" onclick="return confirm('Delete this user?')">Delete</a></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Today's Reservations (<?= $today ?>)</h2>
<table>
    <tr><th>Code</th><th>Name</th><th>Table</th><th>Time</th><th>Guests</th></tr>
    <?php foreach ($today_reservations as $r): ?>
        <tr>
            <td><?= $r['reservation_code'] ?></td>
            <td><?= $r['firstname'] . ' ' . $r['lastname'] ?></td>
            <td><?= $r['table_name'] ?></td>
            <td><?= $r['start_time'] ?></td>
            <td><?= $r['guest_number'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>All Reservations</h2>
<button onclick="exportCSV()">Export to CSV</button>
<table id="reservationTable">
    <tr><th>Code</th><th>Name</th><th>Table</th><th>Date</th><th>Time</th><th>Guests</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($reservations as $r): ?>
        <tr>
            <td><?= $r['reservation_code'] ?></td>
            <td><?= $r['firstname'] . ' ' . $r['lastname'] ?></td>
            <td><?= $r['table_name'] ?></td>
            <td><?= $r['res_date'] ?></td>
            <td><?= $r['start_time'] ?></td>
            <td><?= $r['guest_number'] ?></td>
            <td>
                <form method="POST" class="inline">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <select name="status">
                        <option value="pending" <?= $r['status'] == 'pending' ? 'selected' : '' ?>>pending</option>
                        <option value="active" <?= $r['status'] == 'active' ? 'selected' : '' ?>>active</option>
                        <option value="cancelled" <?= $r['status'] == 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                        <option value="completed" <?= $r['status'] == 'completed' ? 'selected' : '' ?>>completed</option>
                    </select>
                    <button name="edit_reservation_status">Save</button>
                </form>
            </td>
            <td><a href="?delete_reservation=<?= $r['id'] ?>" onclick="return confirm('Delete this reservation?')">Delete</a></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Tables</h2>
<table>
    <tr><th>Name</th><th>Description</th><th>Seats</th><th>Smoking</th><th>Actions</th><th>Delete</th></tr>
    <?php foreach ($tables as $t): ?>
        <tr>
            <form method="POST">
                <td><input type="text" name="name" value="<?= $t['name'] ?>"></td>
                <td><textarea name="location"><?= $t['location_description'] ?></textarea></td>
                <td><input name="seats" type="number" value="<?= $t['seats'] ?>"></td>
                <td>
                    <select name="is_smoking">
                        <option value="0" <?= !$t['is_smoking'] ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= $t['is_smoking'] ? 'selected' : '' ?>>Yes</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button name="edit_table">Save</button>
                </td>
                <td><a href="?delete_table=<?= $t['id'] ?>" onclick="return confirm('Delete this table?')">Delete</a></td>
            </form>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Add New Table</h3>
<form method="POST">
    <label for="name">Name:</label>
    <input name="name" id="name" required>

    <label for="location">Description:</label>
    <input name="location" id="location" required>

    <label for="seats">Seats:</label>
    <input type="number" name="seats" id="seats" required>

    <label for="is_smoking">Smoking:</label>
    <select name="is_smoking" id="is_smoking">
        <option value="0">No</option>
        <option value="1">Yes</option>
    </select>

    <button name="add_table">Add</button>
</form>


<script>


    // Export table to CSV
    function exportCSV() {
        let csv = [];
        const rows = document.querySelectorAll("#reservationTable tr");
        rows.forEach(row => {
            const cols = row.querySelectorAll("td, th");
            const line = Array.from(cols).map(td => `"${td.innerText}"`).join(",");
            csv.push(line);
        });
        const blob = new Blob([csv.join("\n")], { type: 'text/csv' });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "reservations.csv";
        link.click();
    }
</script>

</body>
</html>
<?php $conn->close(); ?>
