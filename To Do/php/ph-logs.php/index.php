// get_ph_data.php
<?php
include 'config.php';

$stmt = $conn->prepare("SELECT * FROM ph_logs ORDER BY timestamp DESC LIMIT 1");
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);
?>