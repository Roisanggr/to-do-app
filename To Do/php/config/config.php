// config.php
<?php
$servername = "sql305.infinityfree.com";
$username = "if0_39693878";
$password = "234g7BFLmYFag";
$dbname = "if0_39693878_to_do_app";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

header('Content-Type: application/json');

echo json_encode([
    'status' => 'debug active',
    'php_version' => PHP_VERSION,
    'current_dir' => __DIR__,
    'config_exists' => file_exists('../config/config.php'),
    'config_path' => realpath('../config/config.php')
]);

try {
    require_once('../config/config.php');
    echo json_encode([
        'database_connection' => 'success',
        'pdo_available' => class_exists('PDO')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'database_connection' => 'failed',
        'error' => $e->getMessage()
    ]);
}
?>