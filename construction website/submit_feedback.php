<?php
$conn = new mysqli("localhost", "root", "", "construction_site");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$type = $_POST['type'];
$message = $_POST['message'];

$sql = "INSERT INTO feedback (type, message) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $type, $message);

if ($stmt->execute()) {
    echo "Feedback submitted successfully.";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
