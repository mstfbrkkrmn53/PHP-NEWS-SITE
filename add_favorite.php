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

// Role kontrolü (RoleID 2 kontrolü yapılır)
$userQuery = "SELECT RoleID FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($roleID);
$stmt->fetch();
$stmt->close();

if ($roleID != 2) {
    echo json_encode(["success" => false, "message" => "User does not have the correct role."]);
    exit;
}

// JSON giriş verilerini oku
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['news_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid request: news_id is required."]);
    exit;
}

$newsID = intval($input['news_id']);
$addedDate = date('Y-m-d H:i:s');

// Favoriler tablosuna kayıt ekleme
$favoriteQuery = "INSERT INTO Favorites (NewsID, UserID, AddedDate) VALUES (?, ?, ?)";
$stmt = $conn->prepare($favoriteQuery);
if ($stmt) {
    $stmt->bind_param("iis", $newsID, $userID, $addedDate);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "News added to favorites."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add to favorites: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Database prepare error: " . $conn->error]);
}

$conn->close();
?>