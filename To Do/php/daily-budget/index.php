<?php
// MATIKAN SEMUA OUTPUT ERROR
ini_set('display_errors', 0);
error_reporting(0);

// Buffer output untuk mencegah output tidak diinginkan
ob_start();

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Cek apakah config ada
    $configPath = '../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception('Config file not found');
    }
    
    require_once($configPath);
    
    // Cek koneksi database
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection failed');
    }

    // GET - Ambil data (exclude soft deleted)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            // Cek apakah ada parameter ID untuk get single record
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if ($id) {
                // Get single record
                $sql = "SELECT id, waktu, pengeluaran_untuk, kategori, harga, note, created_at 
                       FROM daily_budget 
                       WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$result) {
                    ob_clean();
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'msg' => 'Data tidak ditemukan'
                    ]);
                    exit;
                }
                
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
                exit;
            } else {
                // Get all records (exclude soft deleted)
                $sql = "SELECT id, waktu, pengeluaran_untuk, kategori, harga, note, created_at 
                       FROM daily_budget 
                       WHERE (is_deleted = 0 OR is_deleted IS NULL) 
                       ORDER BY waktu DESC, id DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Format data untuk konsistensi
                foreach ($result as &$row) {
                    if (isset($row['waktu'])) {
                        $row['waktu'] = date('Y-m-d H:i:s', strtotime($row['waktu']));
                    }
                    if (isset($row['harga'])) {
                        $row['harga'] = (int)$row['harga'];
                    }
                }
                
                ob_clean();
                echo json_encode($result ?: []);
                exit;
            }
            
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'msg' => 'Database query failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    // POST - Tambah data baru
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !is_array($input)) {
                throw new Exception('Invalid JSON input');
            }
            
            // Validasi fields
            $required = ['waktu', 'pengeluaran_untuk', 'kategori', 'harga'];
            foreach ($required as $field) {
                if (!isset($input[$field]) || trim($input[$field]) === '') {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Validasi kategori
            $validCategories = ['Food', 'Traveling', 'Other'];
            if (!in_array($input['kategori'], $validCategories)) {
                throw new Exception('Kategori tidak valid');
            }
            
            // Validasi harga
            $harga = (float)$input['harga'];
            if ($harga <= 0) {
                throw new Exception('Harga harus lebih dari 0');
            }
            
            $sql = "INSERT INTO daily_budget (waktu, pengeluaran_untuk, kategori, harga, note, is_deleted) 
                   VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            
            $result = $stmt->execute([
                $input['waktu'],
                $input['pengeluaran_untuk'],
                $input['kategori'],
                $harga,
                $input['note'] ?? ''
            ]);
            
            if (!$result) {
                throw new Exception('Failed to insert data');
            }
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'msg' => 'Data berhasil disimpan',
                'id' => $conn->lastInsertId()
            ]);
            exit;
            
        } catch (Exception $e) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // PUT - Update data
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !is_array($input)) {
                throw new Exception('Invalid JSON input');
            }
            
            // Validasi ID
            if (!isset($input['id']) || !is_numeric($input['id'])) {
                throw new Exception('ID is required and must be numeric');
            }
            
            $id = (int)$input['id'];
            
            // Cek apakah data exists dan tidak soft deleted
            $checkSql = "SELECT id FROM daily_budget WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$id]);
            
            if (!$checkStmt->fetch()) {
                throw new Exception('Data tidak ditemukan atau sudah dihapus');
            }
            
            // Validasi fields
            $required = ['waktu', 'pengeluaran_untuk', 'kategori', 'harga'];
            foreach ($required as $field) {
                if (!isset($input[$field]) || trim($input[$field]) === '') {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Validasi kategori
            $validCategories = ['Food', 'Traveling', 'Other'];
            if (!in_array($input['kategori'], $validCategories)) {
                throw new Exception('Kategori tidak valid');
            }
            
            // Validasi harga
            $harga = (float)$input['harga'];
            if ($harga <= 0) {
                throw new Exception('Harga harus lebih dari 0');
            }
            
            $sql = "UPDATE daily_budget 
                   SET waktu = ?, pengeluaran_untuk = ?, kategori = ?, harga = ?, note = ?, updated_at = NOW()
                   WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
            $stmt = $conn->prepare($sql);
            
            $result = $stmt->execute([
                $input['waktu'],
                $input['pengeluaran_untuk'],
                $input['kategori'],
                $harga,
                $input['note'] ?? '',
                $id
            ]);
            
            if (!$result || $stmt->rowCount() === 0) {
                throw new Exception('Failed to update data atau data tidak ditemukan');
            }
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'msg' => 'Data berhasil diupdate',
                'id' => $id
            ]);
            exit;
            
        } catch (Exception $e) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // DELETE - Soft delete data
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            // Parse ID dari URL atau JSON body
            $id = null;
            
            // Cek dari URL parameter
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
            } else {
                // Cek dari JSON body
                $input = json_decode(file_get_contents('php://input'), true);
                if ($input && isset($input['id'])) {
                    $id = (int)$input['id'];
                }
            }
            
            if (!$id || !is_numeric($id)) {
                throw new Exception('ID is required and must be numeric');
            }
            
            // Cek apakah data exists dan belum soft deleted
            $checkSql = "SELECT id FROM daily_budget WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$id]);
            
            if (!$checkStmt->fetch()) {
                throw new Exception('Data tidak ditemukan atau sudah dihapus');
            }
            
            // Soft delete: set is_deleted = 1
            $sql = "UPDATE daily_budget 
                   SET is_deleted = 1, deleted_at = NOW() 
                   WHERE id = ?";
            $stmt = $conn->prepare($sql);
            
            $result = $stmt->execute([$id]);
            
            if (!$result || $stmt->rowCount() === 0) {
                throw new Exception('Failed to delete data');
            }
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'msg' => 'Data berhasil dihapus',
                'id' => $id
            ]);
            exit;
            
        } catch (Exception $e) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'msg' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // Method not allowed
    ob_clean();
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'msg' => 'Method not allowed'
    ]);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'msg' => 'Server error: ' . $e->getMessage()
    ]);
}
?>