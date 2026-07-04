<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
include_once 'koneksi.php';

$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    
    if (isset($_FILES['foto']) && !empty($id)) {
        $targetDir = "uploads/";
        $fileName = "product_" . $id . "_" . time() . ".jpg";
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFilePath)) {
            // Update database
            $stmt = $db->prepare("UPDATE products SET image_url = ? WHERE id = ?");
            if ($stmt->execute([$fileName, $id])) {
                echo json_encode(["status" => "success", "message" => "Foto berhasil diupload"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal update database"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal upload file"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "ID atau File tidak ditemukan"]);
    }
}
?>