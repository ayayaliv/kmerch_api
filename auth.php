<?php
// 1. 🛠️ TAMBAHAN CORS: Wajib lengkap agar tidak diblokir Browser Chrome saat running Flutter Web!
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Tangani request OPTIONS dari browser (Preflight Request) sebelum POST dikirim
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(0); 

include_once 'koneksi.php';
$db = (new Database())->getConnection();

$action = $_POST['action'] ?? '';

if ($action == 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Mengambil juga kolom telepon dan alamat saat login berhasil
    $stmt = $db->prepare("SELECT id, name, email, role, telepon, alamat FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Jaga-jaga jika nilainya null di database, kita beri teks default
        if (empty($user['telepon'])) $user['telepon'] = "Belum diatur";
        if (empty($user['alamat'])) $user['alamat'] = "Belum diatur";

        // 2. 🛠️ PERBAIKAN KRUSIAL: Tambahkan 'user_id' dan 'id' langsung di tingkat paling atas (root) JSON
        // Ini menjamin login_page.dart kamu bisa langsung membaca data['user_id'] tanpa memicu teks "null" murni!
        echo json_encode([
            "status" => "success", 
            "user_id" => $user['id'], // Datar di root JSON
            "id" => $user['id'],      // Datar di root JSON
            "data" => $user
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email atau Password salah"]);
    }
    
} else if ($action == 'register') {
    // Menangkap input telepon dan alamat dari Flutter
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    // Jaga-jaga jika di Flutter input telepon/alamat tidak diisi, set default atau null
    if (empty($telepon)) $telepon = null;
    if (empty($alamat)) $alamat = null;

    // Query INSERT sekarang memasukkan data name, email, password, telepon, dan alamat
    $stmt = $db->prepare("INSERT INTO users (name, email, password, telepon, alamat, role) VALUES (?, ?, ?, ?, ?, 'customer')");
    $stmt->bind_param("sssss", $name, $email, $password, $telepon, $alamat);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registrasi berhasil"]);
    } else {
        // Jika gagal, tampilkan pesan error dari database agar mudah didebug jika ada ketidakcocokan
        echo json_encode([
            "status" => "error", 
            "message" => "Gagal mendaftar. Email mungkin sudah digunakan atau terjadi kesalahan sistem.",
            "error_detail" => $stmt->error
        ]);
    }
}
?>