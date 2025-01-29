<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store_db";

header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log data diterima
    file_put_contents('debug.log', "Received POST: " . json_encode($_POST) . "\n", FILE_APPEND);

    // Ambil data dari POST
    $id = $_POST['id'] ?? null;
    $nama = $_POST['nama'] ?? null;
    $alamat = $_POST['alamat'] ?? null;
    $no = $_POST['no'] ?? null;
    $email = $_POST['email'] ?? null;
    $foto = null;

    // Pastikan semua data wajib ada
    if (!$id || !$nama || !$alamat || !$no || !$email) {
        echo json_encode(['success' => false, 'error' => 'Incomplete data. Please fill all fields.']);
        exit;
    }

    // Cek apakah user memiliki foto lama
    $stmt = $conn->prepare("SELECT foto FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($oldPhoto);
    $stmt->fetch();
    $stmt->close();

    // Jika ada file foto baru di-upload, simpan file foto
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fotoPath = 'uploads/' . basename($_FILES['foto']['name']);
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPath)) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload photo']);
            exit;
        }
        $foto = $fotoPath;
    } else {
        $foto = $oldPhoto; // Jika tidak ada upload, gunakan foto lama
    }

    // Update data pengguna di database
    $stmt = $conn->prepare("UPDATE users SET nama = ?, alamat = ?, no = ?, email = ?, foto = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nama, $alamat, $no, $email, $foto, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
    }

    $stmt->close();
}

$conn->close();
?>
