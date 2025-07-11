<?php
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP default no password
$database = "construction_site";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Booking Error</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f8d7da; color: #721c24; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .container { background: #f5c6cb; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
    h1 { margin-bottom: 20px; }
    a.button { background: #721c24; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
    a.button:hover { background: #501217; }
  </style>
</head>
<body>
  <div class='container'>
    <h1>Database Connection Failed</h1>
    <p>Sorry, we couldn't connect to the database at this time. Please try again later.</p>
    <a href='booking.html' class='button'>Back to Booking</a>
  </div>
</body>
</html>");
}

// Get POST data safely (add more validation/sanitization as needed)
$full_name = $conn->real_escape_string($_POST['full_name'] ?? '');
$email = $conn->real_escape_string($_POST['email'] ?? '');
$phone = $conn->real_escape_string($_POST['phone'] ?? '');
$booking_date = $conn->real_escape_string($_POST['booking_date'] ?? '');
$service_requested = $conn->real_escape_string($_POST['service_requested'] ?? '');
$message = $conn->real_escape_string($_POST['message'] ?? '');

$sql = "INSERT INTO bookings (full_name, email, phone, booking_date, service_requested, message) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $full_name, $email, $phone, $booking_date, $service_requested, $message);

$success = $stmt->execute();

$stmt->close();
$conn->close();
// Send email notification
$to = "saa143879@gmail.com"; // Your email here
$subject = "New Booking Request";
$message = "You have a new booking from $full_name\n\nService: $service_requested\nDate: $booking_date\nMessage: $message";
$headers = "From: $email";

mail($to, $subject, $message, $headers);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Booking Status</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: <?php echo $success ? '#d4edda' : '#f8d7da'; ?>;
      color: <?php echo $success ? '#155724' : '#721c24'; ?>;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      padding: 20px;
    }
    .message-box {
      background: <?php echo $success ? '#c3e6cb' : '#f5c6cb'; ?>;
      border: 1px solid <?php echo $success ? '#155724' : '#f5c6cb'; ?>;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      max-width: 450px;
      text-align: center;
    }
    .message-box h1 {
      margin-bottom: 15px;
      font-size: 2rem;
    }
    .message-box p {
      font-size: 1.2rem;
      margin-bottom: 25px;
    }
    .message-box a {
      display: inline-block;
      padding: 12px 25px;
      font-weight: 600;
      color: white;
      background-color: <?php echo $success ? '#28a745' : '#dc3545'; ?>;
      border-radius: 6px;
      text-decoration: none;
      box-shadow: 0 3px 8px rgba(0,0,0,0.15);
      transition: background-color 0.3s ease;
    }
    .message-box a:hover {
      background-color: <?php echo $success ? '#218838' : '#c82333'; ?>;
    }
  </style>
</head>
<body>
  <div class="message-box">
    <?php if ($success): ?>
      <h1>Booking Submitted!</h1>
      <p>Thank you, <strong><?php echo htmlspecialchars($full_name); ?></strong>. Your booking request has been received successfully.</p>
      <a href="booking.html">Make Another Booking</a>
    <?php else: ?>
      <h1>Oops! Something Went Wrong.</h1>
      <p>We could not process your booking at this time. Please try again later.</p>
      <a href="booking.html">Try Again</a>
    <?php endif; ?>
  </div>
</body>
</html>
