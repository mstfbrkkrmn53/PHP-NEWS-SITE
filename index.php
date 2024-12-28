<?php
session_start(); // Oturum başlat

// Database connection
$servername = "localhost";
$username = "khasnews_usr";
$password = "20181701091";
$dbname = "khasnews";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories
$categoryQuery = "SELECT CategoryID, CategoryName FROM Category";
$categoryResult = $conn->query($categoryQuery);

// Fetch slider images (supporting multiple formats)
$sliderImages = array_merge(
    glob("images/*.jpg"),
    glob("images/*.jpeg"),
    glob("images/*.png"),
    glob("images/*.webp")
);
sort($sliderImages); // Optional: Sort images alphabetically

// Kullanıcının giriş durumunu kontrol et ve kullanıcı bilgilerini getir
$userName = null;
$userID = null;
$userRoleID = null;
if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];
    $userQuery = "SELECT UserName, RoleID FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($userName, $userRoleID);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Portal</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        /* Header Styles */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            background-color: #ffffff;
            color: #333;
            padding: 10px 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header .logo {
            display: flex;
            align-items: center;
        }

        .header .logo img {
            height: 70px;
        }

        .header .menu-container {
            flex: 1;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .header .menu a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .header .menu a:hover {
            background-color: #4a90e2;
            color: white;
        }

        .header .login {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header .login .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header .login .user-info img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }

        .header .login button {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .header .login button:hover {
            background-color: #357abd;
        }

        /* Slider Styles */
        .slider {
            position: relative;
            width: 100%;
            max-width: 1200px;
            height: auto;
            max-height: 400px;
            overflow: hidden;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
        }

        .slider img {
            width: 100%;
            height: auto;
            object-fit: cover;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            position: absolute;
        }

        .slider img.active {
            opacity: 1;
            position: relative;
        }

        /* News Block Styles */
        .news-block {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .news-item {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
        }

        .news-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .news-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 5px;
        }

        .news-item h3 {
            margin: 15px 0;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .news-item h3 a {
            color: #4a90e2;
            text-decoration: none;
        }

        .news-item h3 a:hover {
            text-decoration: underline;
        }

        .news-item button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
            margin-top: 10px;
            align-self: center;
        }

        .news-item button:hover {
            background-color: #45a049;
        }

        .news-item .remove-button {
            background-color: #e74c3c;
        }

        .news-item .remove-button:hover {
            background-color: #c0392b;
        }

        .news-item .disabled-button {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        /* Footer Styles */
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 40px;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .slider {
                max-height: 250px;
            }

            .news-item img {
                height: 150px;
            }

            .header .menu-container {
                justify-content: center;
            }

            .header .login {
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="logo">
            <a href="/index.php">
            <img src="/images/logo.png" alt="Logo">
            </a>
        </div>
        <div class="menu-container">
            <nav class="menu">
                <?php
                if ($categoryResult->num_rows > 0) {
                    while ($row = $categoryResult->fetch_assoc()) {
                        echo "<a href='/category.php?category_id=" . $row['CategoryID'] . "'>" . $row['CategoryName'] . "</a>";
                    }
                }
                ?>
            </nav>
        </div>
        <div class="login">
            <?php if ($userName): ?>
                <div class="user-info">
                    <img src="/images/user-icon.png" alt="User Icon">
                    <span><?= htmlspecialchars($userName) ?></span>
                </div>
                <button onclick="window.location.href='/logout.php'">Logout</button>
                <button onclick="window.location.href='/favorites.php'">Favorites</button>
            <?php else: ?>
                <button onclick="window.location.href='/login.php'">Login</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Slider Section -->
    <main>
        <div class="slider">
            <?php foreach ($sliderImages as $image): ?>
                <img src="/slider/<?= basename($image) ?>" alt="Slider Image" class="slider-image">
            <?php endforeach; ?>
        </div>

        <!-- News Block Section -->
        <div class="news-block">
            <?php
            $newsQuery = "
                SELECT News.NewsID, News.Title, Images.ImageURL 
                FROM News 
                LEFT JOIN Images 
                ON News.ImageID = Images.ImageID 
                WHERE News.StatusID = 1
                ORDER BY News.PublicationDate DESC 
                LIMIT 4";
            $newsResult = $conn->query($newsQuery);

            while ($news = $newsResult->fetch_assoc()):
                // Check if the news is already in the user's favorites
                $isFavoriteQuery = "SELECT COUNT(*) AS count FROM Favorites WHERE UserID = ? AND NewsID = ?";
                $stmt = $conn->prepare($isFavoriteQuery);
                $stmt->bind_param("ii", $userID, $news['NewsID']);
                $stmt->execute();
                $stmt->bind_result($isFavorite);
                $stmt->fetch();
                $stmt->close();
                ?>
                <div class="news-item">
                    <img src="<?= $news['ImageURL'] ?>" alt="News Image">
                    <h3><a href="/details.php?news_id=<?= $news['NewsID'] ?>"><?= htmlspecialchars($news['Title']) ?></a></h3>
                    <?php if ($userID && $userRoleID == 2): ?>
                        <?php if ($isFavorite > 0): ?>
                            <button class="remove-button" onclick="removeFromFavorites(<?= $news['NewsID'] ?>)">Remove from Favorites</button>
                        <?php else: ?>
                            <button onclick="addToFavorites(<?= $news['NewsID'] ?>)">Add to Favorites</button>
                        <?php endif; ?>
                    <?php elseif (!$userID): ?>
                        <button class="disabled-button">Please Log In</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <!-- Footer Section -->
    <footer>
        © <?= date('Y') ?> KHAS NEWS. All rights reserved.
    </footer>

    <!-- JavaScript Section -->
    <script>
        let currentIndex = 0;
        const images = document.querySelectorAll('.slider-image');
        const totalImages = images.length;

        function showNextImage() {
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % totalImages;
            images[currentIndex].classList.add('active');
        }

        setInterval(showNextImage, 5000); 
        images[0].classList.add('active');

        function addToFavorites(newsID) {
            fetch('http://10.1.7.100:7777/st004.site/add_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ news_id: newsID })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Added to favorites!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function removeFromFavorites(newsID) {
            fetch('http://10.1.7.100:7777/st004.site/remove_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ news_id: newsID })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Removed from favorites!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>

<?php
// Add Favorite Logic (/add_favorite.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['news_id']) && $userID && $userRoleID == 2) {
        $newsID = intval($input['news_id']);
        $addedDate = date('Y-m-d H:i:s');

        $favoriteQuery = "INSERT INTO Favorites (NewsID, UserID, AddedDate) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($favoriteQuery);

        if ($stmt) {
            $stmt->bind_param("iis", $newsID, $userID, $addedDate);
            $stmt->execute();
            $stmt->close();
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request or permissions."]);
    }
}

// Remove Favorite Logic (/remove_favorite.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['SCRIPT_NAME']) === 'remove_favorite.php') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['news_id']) && $userID && $userRoleID == 2) {
        $newsID = intval($input['news_id']);

        $removeFavoriteQuery = "DELETE FROM Favorites WHERE NewsID = ? AND UserID = ?";
        $stmt = $conn->prepare($removeFavoriteQuery);

        if ($stmt) {
            $stmt->bind_param("ii", $newsID, $userID);
            $stmt->execute();
            $stmt->close();
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request or permissions."]);
    }
}
?>
