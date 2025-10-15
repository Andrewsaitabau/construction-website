<?php  
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "construction_site");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle booking status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle booking deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_booking'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle feedback reply
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_feedback'])) {
    $fid = intval($_POST['feedback_id']);
    $reply = $conn->real_escape_string($_POST['admin_reply']);
    $stmt = $conn->prepare("UPDATE feedback SET admin_reply=? WHERE id=?");
    $stmt->bind_param("si", $reply, $fid);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']."#feedbacks");
    exit;
}

// Search and filter inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$status_filter = in_array($status_filter, ['Pending','Completed','Rejected']) ? $status_filter : '';

// Build bookings query with search and status filter
$whereClauses = [];
if ($search !== '') {
    $searchEscaped = $conn->real_escape_string($search);
    $whereClauses[] = "(full_name LIKE '%$searchEscaped%' OR service_requested LIKE '%$searchEscaped%')";
}
if ($status_filter !== '') {
    $whereClauses[] = "status = '".$conn->real_escape_string($status_filter)."'";
}
$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

$bookings = $conn->query("SELECT * FROM bookings $whereSql ORDER BY created_at DESC");

// Fetch feedback
$feedbacks = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");

// Fetch ratings
$ratings = $conn->query("SELECT * FROM ratings ORDER BY created_at DESC");

// Booking stats
$totalBookings = $conn->query("SELECT COUNT(*) as cnt FROM bookings")->fetch_assoc()['cnt'];
$pendingBookings = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE status='Pending'")->fetch_assoc()['cnt'];
$completedBookings = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE status='Completed'")->fetch_assoc()['cnt'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard - Bookings</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
    h1, h2 { text-align: center; }
    table { width: 100%; margin: 20px 0; border-collapse: collapse; background: white; }
    th, td { padding: 10px; border: 1px solid #ddd; vertical-align: top; }
    th { background: #333; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .status-btn { padding: 5px 8px; border: none; border-radius: 4px; color: white; cursor: pointer; }
    .completed { background: green; }
    .rejected { background: red; }
    .pending { background: orange; }
    .reply-box textarea { width: 100%; height: 60px; }
    .reply-box input[type="submit"] { margin-top: 5px; padding: 5px 10px; cursor: pointer; }
    .stats { text-align: center; margin-bottom: 20px; }
    .stats span { margin: 0 15px; font-weight: bold; }
    .filter-form { text-align: center; margin-bottom: 20px; }
    .filter-form input[type="text"] { padding: 5px; width: 200px; }
    .filter-form select { padding: 5px; }
    .filter-form button { padding: 5px 10px; }
    form.inline { display: inline; }
    .delete-btn { background: darkred; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; }
    a.logout { float: right; margin-bottom: 10px; text-decoration: none; color: #333; font-weight: bold; }
  </style>
</head>
<body>

<a href="logout.php" class="logout">Logout</a>

<h1>Admin Dashboard - Bookings</h1>

<div class="stats">
  <span>Total Bookings: <?= $totalBookings ?></span>
  <span style="color:orange;">Pending: <?= $pendingBookings ?></span>
  <span style="color:green;">Completed: <?= $completedBookings ?></span>
</div>

<form method="GET" class="filter-form">
  <input type="text" name="search" placeholder="Search by Name or Service" value="<?= htmlspecialchars($search) ?>" />
  <select name="status">
    <option value="">All Statuses</option>
    <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Pending</option>
    <option value="Completed" <?= $status_filter == 'Completed' ? 'selected' : '' ?>>Completed</option>
    <option value="Rejected" <?= $status_filter == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
  </select>
  <button type="submit">Filter</button>
  <a href="<?= $_SERVER['PHP_SELF'] ?>" style="margin-left:10px;">Reset</a>
</form>

<form method="POST" action="export_csv.php" style="text-align:right;">
  <button type="submit">Export Bookings as CSV</button>
</form>

<?php if ($bookings && $bookings->num_rows > 0): ?>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Booking Date</th>
      <th>Service</th>
      <th>Message</th>
      <th>Status</th>
      <th>Submitted</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $bookings->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id']; ?></td>
        <td><?= htmlspecialchars($row['full_name']); ?></td>
        <td><?= htmlspecialchars($row['email']); ?></td>
        <td><?= htmlspecialchars($row['phone']); ?></td>
        <td><?= htmlspecialchars($row['booking_date']); ?></td>
        <td><?= htmlspecialchars($row['service_requested']); ?></td>
        <td><?= nl2br(htmlspecialchars($row['message'])); ?></td>
        <td>
          <span class="<?= strtolower($row['status']); ?>">
            <?= htmlspecialchars($row['status']); ?>
          </span>
        </td>
        <td><?= $row['created_at']; ?></td>
        <td>
          <?php if ($row['status'] != 'Completed'): ?>
            <form method="POST" class="inline">
              <input type="hidden" name="id" value="<?= $row['id']; ?>">
              <input type="hidden" name="status" value="Completed">
              <button class="status-btn completed" name="update_status" title="Mark as Completed">Complete</button>
            </form>
          <?php endif; ?>
          <?php if ($row['status'] != 'Rejected'): ?>
            <form method="POST" class="inline">
              <input type="hidden" name="id" value="<?= $row['id']; ?>">
              <input type="hidden" name="status" value="Rejected">
              <button class="status-btn rejected" name="update_status" title="Mark as Rejected">Reject</button>
            </form>
          <?php endif; ?>
          <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this booking?');">
            <input type="hidden" name="id" value="<?= $row['id']; ?>">
            <button class="delete-btn" name="delete_booking" title="Delete Booking">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php else: ?>
  <p style="text-align:center;">No bookings found.</p>
<?php endif; ?>


<h2 id="feedbacks">Client Feedback</h2>
<?php if ($feedbacks && $feedbacks->num_rows > 0): ?>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Message</th>
      <th>Submitted</th>
      <th>Admin Reply</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $feedbacks->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id']; ?></td>
      <td><?= htmlspecialchars($row['full_name'] ?? 'N/A'); ?></td>
<td><?= htmlspecialchars($row['email'] ?? 'N/A'); ?></td>

      <td><?= nl2br(htmlspecialchars($row['message'])); ?></td>
      <td><?= $row['created_at']; ?></td>
      <td>
        <form method="POST" class="reply-box">
          <input type="hidden" name="feedback_id" value="<?= $row['id']; ?>">
          <textarea name="admin_reply"><?= htmlspecialchars($row['admin_reply'] ?? ''); ?></textarea>
          <input type="submit" name="reply_feedback" value="Send Reply">
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php else: ?>
  <p style="text-align:center;">No feedback available.</p>
<?php endif; ?>


<h2>Client Ratings & Reviews</h2>
<?php if ($ratings && $ratings->num_rows > 0): ?>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Rating</th>
      <th>Review</th>
      <th>Submitted</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $ratings->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id']; ?></td>
      <td><?= htmlspecialchars($row['full_name']); ?></td>
      <td><?= htmlspecialchars($row['email']); ?></td>
      <td><?= floatval($row['rating']); ?> / 5</td>
      <td><?= nl2br(htmlspecialchars($row['review'])); ?></td>
      <td><?= $row['created_at']; ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php else: ?>
  <p style="text-align:center;">No ratings available.</p>
<?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
