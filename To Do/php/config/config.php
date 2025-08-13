// config.php
<?php
$servername = "sql212.infinityfree.com";
$username = "if0_39531579"; // Ganti dengan username Anda
$password = "hNJgafVjR6"; // Ganti dengan password Andas
$dbname = "if0_39531579_dashboard"; // Nama database Anda

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>