<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// 1. Tambahkan ini: Penting agar Flutter Web tidak terkena error CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

include_once 'koneksi.php';
$db = (new Database())->getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- 1. READ ---
if ($action == 'read') {
    $query = "SELECT * FROM products ORDER BY created_at DESC";
    $result = $db->query($query);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $products]);
}

// --- 2. ADD ---
elseif ($action == 'add') {
    $image_url = 'placeholder.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/";
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $fileName)) {
            $image_url = $fileName;
        }
    }
    // Gunakan 'd' untuk price jika harga desimal/double
    $stmt = $db->prepare("INSERT INTO products (name, category, price, stock, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $_POST['name'], $_POST['category'], $_POST['price'], $_POST['stock'], $image_url);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Produk berhasil ditambahkan"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal: " . $stmt->error]);
    }
}

// --- 3. EDIT ---
elseif ($action == 'edit') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Ambil nama file lama untuk dihapus
        $res = $db->query("SELECT image_url FROM products WHERE id = " . $_POST['id']);
        $old_data = $res->fetch_assoc();
        if ($old_data && $old_data['image_url'] != 'placeholder.png') {
            unlink("uploads/" . $old_data['image_url']); // Hapus file lama
        }

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $fileName);
        
        $stmt = $db->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, image_url=? WHERE id=?");
        $stmt->bind_param("ssdisi", $_POST['name'], $_POST['category'], $_POST['price'], $_POST['stock'], $fileName, $_POST['id']);
    } else {
        $stmt = $db->prepare("UPDATE products SET name=?, category=?, price=?, stock=? WHERE id=?");
        $stmt->bind_param("ssdii", $_POST['name'], $_POST['category'], $_POST['price'], $_POST['stock'], $_POST['id']);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Produk berhasil diupdate"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal: " . $stmt->error]);
    }
}

// --- 4. DELETE ---
elseif ($action == 'delete') {
    // Hapus file gambar sebelum hapus data
    $res = $db->query("SELECT image_url FROM products WHERE id = " . $_POST['id']);
    $data = $res->fetch_assoc();
    if ($data && $data['image_url'] != 'placeholder.png') {
        unlink("uploads/" . $data['image_url']);
    }

    $stmt = $db->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $_POST['id']);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Produk berhasil dihapus"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal hapus"]);
    }
}
?>