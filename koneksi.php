<?php
// Header CORS untuk mengizinkan akses dari Flutter Web
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Langsung setujui dan matikan proses jika browser mengirim request uji coba (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

class Database {
    private $host = "localhost";
    private $db_name = "kmerch_hub"; // Sudah benar sesuai di phpMyAdmin kamu!
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            // Tambahan: Validasi manual jika koneksi gagal (aman untuk semua versi PHP)
            if ($this->conn->connect_error) {
                echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $this->conn->connect_error]);
                exit(); // Matikan proses agar tidak membuat file lain crash
            }
        } catch(Exception $e) {
            echo json_encode(["status" => "error", "message" => "Terjadi kesalahan: " . $e->getMessage()]);
            exit(); // Matikan proses di sini juga jika terkena exception
        }
        return $this->conn;
    }
}
?>