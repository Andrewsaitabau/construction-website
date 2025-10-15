<?php
$mysqli = new mysqli("localhost", "root", "", "construction_site");

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=bookings.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Full Name', 'Email', 'Phone', 'Booking Date', 'Service', 'Message', 'Status', 'Created At']);

$result = $mysqli->query("SELECT * FROM bookings ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit;
