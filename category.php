<?php
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

// Start session for login/logout
session_start();
$userName = null;
$userID = null;
if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];
    $userQuery = "SELECT UserName FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($userName);
    $stmt->fetch();
    $stmt->close();
}

// Fetch categories for the menu
$categoryQuery = "SELECT CategoryID, CategoryName FROM Category";
$categoryResult = $conn->query($categoryQuery);

// CategoryID'yi URL'den al
$categoryID = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Kategori bilgisi ve ilgili haberleri çek
$categoryName = "Unknown Category";
if ($categoryID > 0) {
    $specificCategoryQuery = "SELECT CategoryName FROM Category WHERE CategoryID = $categoryID";
    $specificCategoryResult = $conn->query($specificCategoryQuery);
    if ($specificCategoryResult->num_rows > 0) {
        $categoryName = $specificCategoryResult->fetch_assoc()['CategoryName'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category: <?= htmlspecialchars($categoryName) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #4a90e2;
            color: #333;
            padding: 10px 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header .logo img {
            height: 70px;
        }

        .header .menu {
            display: flex;
            gap: 15px;
        }

        .header .menu a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }

        .header .menu a:hover {
            text-decoration: underline;
        }

        .header .login {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header .login button {
            background-color: white;
            color: #4a90e2;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .header .login .user-info {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .header .login .user-info img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .category-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .category-header h1 {
            font-size: 28px;
            color: #333;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .news-item {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .news-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .news-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .news-content {
            padding: 15px;
        }

        .news-content h2 {
            font-size: 18px;
            color: #4a90e2;
            margin-bottom: 10px;
        }

        .news-content h2 a {
            text-decoration: none;
            color: #4a90e2;
        }

        .news-content h2 a:hover {
            text-decoration: underline;
        }

        .news-content p {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        .news-content button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .news-content button:hover {
            background-color: #45a049;
        }

        .news-content .remove-button {
            background-color: #e74c3c;
        }

        .news-content .remove-button:hover {
            background-color: #c0392b;
        }

        .no-news {
            text-align: center;
            font-size: 18px;
            color: #888;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="/index.php">
            <img src="/images/logo.png" alt="Logo">
            </a>
        </div>
        <nav class="menu">
            <?php while ($row = $categoryResult->fetch_assoc()): ?>
                <a href="/category.php?category_id=<?= $row['CategoryID'] ?>">
                    <?= htmlspecialchars($row['CategoryName']) ?>
                </a>
            <?php endwhile; ?>
        </nav>
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

    <div class="container">
        <div class="category-header">
            <h1>News in Category: <?= htmlspecialchars($categoryName) ?></h1>
        </div>

        <div class="news-grid">
            <?php
            $newsQuery = "
                SELECT News.NewsID, News.Title, News.Content, Images.ImageURL 
                FROM News 
                LEFT JOIN Images ON News.ImageID = Images.ImageID 
                WHERE News.CategoryID = $categoryID AND News.StatusID = 1 
                ORDER BY News.PublicationDate DESC";
            $newsResult = $conn->query($newsQuery);

            if ($newsResult->num_rows > 0) {
                while ($news = $newsResult->fetch_assoc()) {
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
                        <?php if (!empty($news['ImageURL'])): ?>
                            <img src="<?= htmlspecialchars($news['ImageURL']) ?>" alt="News Image">
                        <?php endif; ?>
                        <div class="news-content">
                            <h2>
                                <a href="details.php?news_id=<?= $news['NewsID'] ?>">
                                    <?= htmlspecialchars($news['Title']) ?>
                                </a>
                            </h2>
                            <p><?= htmlspecialchars(substr($news['Content'], 0, 150)) ?>...</p>
                            <?php if ($userID): ?>
                                <?php if ($isFavorite > 0): ?>
                                    <button class="remove-button" onclick="removeFromFavorites(<?= $news['NewsID'] ?>)">Remove from Favorites</button>
                                <?php else: ?>
                                    <button onclick="addToFavorites(<?= $news['NewsID'] ?>)">Add to Favorites</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-news'>No news found in this category.</p>";
            }
            ?>
        </div>
    </div>

    <script>
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
$conn->close();
?>