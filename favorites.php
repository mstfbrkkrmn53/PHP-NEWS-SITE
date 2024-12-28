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
if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];
    $userQuery = "SELECT UserName FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($userName);
    $stmt->fetch();
    $stmt->close();
} else {
    die("You must be logged in to view this page.");
}

// Fetch categories for the menu
$categoryQuery = "SELECT CategoryID, CategoryName FROM Category";
$categoryResult = $conn->query($categoryQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites</title>
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
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .news-content button:hover {
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
            <?php else: ?>
                <button onclick="window.location.href='/login.php'">Login</button>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <div class="category-header">
            <h1>Your Favorite News</h1>
        </div>

        <div class="news-grid">
            <?php
            $favoritesQuery = "
                SELECT News.NewsID, News.Title, News.Content, Images.ImageURL 
                FROM Favorites 
                INNER JOIN News ON Favorites.NewsID = News.NewsID 
                LEFT JOIN Images ON News.ImageID = Images.ImageID 
                WHERE Favorites.UserID = ? 
                ORDER BY Favorites.AddedDate DESC";
            $stmt = $conn->prepare($favoritesQuery);
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $favoritesResult = $stmt->get_result();

            if ($favoritesResult->num_rows > 0) {
                while ($news = $favoritesResult->fetch_assoc()) {
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
                            <button onclick="removeFromFavorites(<?= $news['NewsID'] ?>)">Remove from Favorites</button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-news'>You have no favorite news.</p>";
            }
            ?>
        </div>
    </div>

    <script>
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