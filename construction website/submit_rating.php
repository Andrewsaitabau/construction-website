<?php
$conn = new mysqli("localhost", "root", "", "construction_site");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$rating = $_POST['rating'];
$review = $_POST['review'];

$sql = "INSERT INTO ratings (rating, review) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ds", $rating, $review);

if ($stmt->execute()) {
    echo "Thank you for your rating!";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
