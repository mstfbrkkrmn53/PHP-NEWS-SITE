<?php
session_start();

// Veritabanı bağlantısı
$servername = "localhost";
$username = "khasnews_usr";
$password = "20181701091";
$dbname = "khasnews";

$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı hatası kontrolü
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

$userID = $_SESSION['user_id'];

// JSON giriş verilerini oku
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['news_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid request: news_id is required."]);
    exit;
}

$newsID = intval($input['news_id']);

// Favorilerden silme sorgusu
$removeFavoriteQuery = "DELETE FROM Favorites WHERE NewsID = ? AND UserID = ?";
$stmt = $conn->prepare($removeFavoriteQuery);
if ($stmt) {
    $stmt->bind_param("ii", $newsID, $userID);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "News removed from favorites."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to remove from favorites: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Database prepare error: " . $conn->error]);
}

$conn->close();
?>