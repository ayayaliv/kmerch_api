<?php
// 1. 🛠️ TAMBAHAN CORS: Wajib lengkap untuk Flutter Web agar tidak diblokir Browser Chrome
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Jika browser mengirimkan request OPTIONS (Preflight), langsung izinkan dan berhenti di sini
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(0); 

include_once 'koneksi.php';
$conn = (new Database())->getConnection();

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// 2. 🛠️ PERBAIKAN: Validasi ekstra untuk mengantisipasi jika Flutter mengirim teks string "null" murni
if (!empty($user_id) && $user_id !== 'null' && $user_id !== 'undefined') {
    
    // Proteksi keamanan dari SQL Injection
    $user_id = $conn->real_escape_string($user_id);

    // 3. 🛠️ PERBAIKAN QUERY: Ambil nama asli 'id' & 'name' BESERTA aliasnya ('user_id' & 'nama')
    // Langkah ini untuk berjaga-jaga jika di UI Flutter kamu memanggil data['name'] atau data['nama'] agar tidak null
    $sql = "SELECT id, id as user_id, name, name as nama, email, telepon, alamat, foto FROM users WHERE id = '$user_id'";
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
        exit();
    }

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        if (empty($user_data['telepon'])) $user_data['telepon'] = "Belum diatur";
        if (empty($user_data['alamat'])) $user_data['alamat'] = "Belum diatur";
        if (empty($user_data['foto'])) $user_data['foto'] = ""; // Beri string kosong jika tidak ada foto

        echo json_encode([
            "success" => true,
            "user" => $user_data
        ]);
    } else {
        // Ditambahkan variabel ID agar kamu tahu ID berapa yang dikirim oleh Flutter dan gagal dicari
        echo json_encode(["success" => false, "message" => "User dengan ID '" . $user_id . "' tidak ditemukan"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "ID tidak valid atau bernilai null/kosong"]);
}

$conn->close();
?>