<?php
include_once 'koneksi.php';
$db = (new Database())->getConnection();

$user_id = $_POST['user_id'] ?? '';
$total_price = $_POST['total_price'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

$stmt = $db->prepare("INSERT INTO orders (user_id, total_price, payment_method) VALUES (?, ?, ?)");
$stmt->bind_param("ids", $user_id, $total_price, $payment_method);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Pesanan berhasil dibuat"]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal memproses pesanan"]);
}
?>