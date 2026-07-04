<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

include_once 'koneksi.php';
$db = (new Database())->getConnection();

$user_id = $_POST['user_id'] ?? '';
$name = $_POST['name'] ?? '';
$telepon = $_POST['telepon'] ?? '';
$alamat = $_POST['alamat'] ?? '';

if (empty($user_id)) {
    echo json_encode(["status" => "error", "message" => "ID User tidak valid"]);
    exit();
}

$foto_name = null;

// Cek jika user mengunggah foto baru
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $target_dir = "uploads/";
    // Buat folder 'uploads' otomatis jika belum ada
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
    $foto_name = "profile_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $foto_name;

    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
        // Query jika ada foto baru
        $stmt = $db->prepare("UPDATE users SET name = ?, telepon = ?, alamat = ?, foto = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $telepon, $alamat, $foto_name, $user_id);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan file foto di server"]);
        exit();
    }
} else {
    // Query jika hanya update teks saja (tanpa ganti foto)
    $stmt = $db->prepare("UPDATE users SET name = ?, telepon = ?, alamat = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $telepon, $alamat, $user_id);
}

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success", 
        "message" => "Profil berhasil diperbarui!",
        "foto" => $foto_name
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal memperbarui database: " . $stmt->error]);
}
?>