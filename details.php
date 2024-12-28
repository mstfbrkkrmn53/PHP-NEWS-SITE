<?php
// Start session
session_start();

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

// Fetch logged-in user details
$userName = null;
$userID = null;
if (isset($_SESSION['user_id'])) {
    $userID = $_SESSION['user_id'];
    $userQuery = "SELECT UserName FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($userQuery);
    if ($stmt) {
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($userName);
        $stmt->fetch();
        $stmt->close();
    }
}

// Fetch all news titles and IDs for the dropdown
$allNewsQuery = "SELECT NewsID, Title FROM News";
$allNewsResult = $conn->query($allNewsQuery);

// Fetch categories for the menu
$categoryQuery = "SELECT CategoryID, CategoryName FROM Category";
$categoryResult = $conn->query($categoryQuery);

// Fetch specific news based on selected NewsID (default is 1)
$selectedNewsID = isset($_GET['news_id']) ? intval($_GET['news_id']) : 1;
$newsQuery = "
    SELECT 
        News.NewsID,
        News.Title,
        News.Content,
        News.PublicationDate,
        News.StatusID,
        Category.CategoryName,
        Authors.AuthorName,
        Images.ImageURL
    FROM News
    LEFT JOIN Category ON News.CategoryID = Category.CategoryID
    LEFT JOIN Authors ON News.AuthorID = Authors.AuthorID
    LEFT JOIN Images ON News.ImageID = Images.ImageID
    WHERE News.NewsID = $selectedNewsID
";
$newsResult = $conn->query($newsQuery);

// Check if the news exists
if ($newsResult && $newsResult->num_rows > 0) {
    $news = $newsResult->fetch_assoc();
} else {
    die("News not found.");
}

// Check if the news is in the user's favorites
$isFavorite = false;
if ($userID) {
    $favoriteQuery = "SELECT COUNT(*) AS count FROM Favorites WHERE UserID = ? AND NewsID = ?";
    $stmt = $conn->prepare($favoriteQuery);
    if ($stmt) {
        $stmt->bind_param("ii", $userID, $selectedNewsID);
        $stmt->execute();
        $stmt->bind_result($favoriteCount);
        $stmt->fetch();
        $isFavorite = $favoriteCount > 0;
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['Title']) ?></title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #4a90e2;
            color: white;
            padding: 15px 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .header .logo img {
            height: 70px;
        }

        .header .menu {
            display: flex;
            gap: 15px;
        }

        .header .menu a {
            color: white;
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

        .header .login button {
            background-color: white;
            color: #4a90e2;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .header .login button:hover {
            background-color: #f1f1f1;
        }

        /* News Container Styles */
        .news-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .news-title {
            font-size: 28px;
            margin-bottom: 10px;
            color: #4a90e2;
        }

        .news-meta {
            font-size: 14px;
            color: #777;
            margin-bottom: 20px;
            text-align: left;
        }

        .news-content {
            font-size: 16px;
            line-height: 1.8;
        }

        .news-image {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 5px;
            object-fit: cover;
        }

        /* Dropdown Styles */
        .news-dropdown {
            margin: 20px auto;
            text-align: center;
        }

        .news-dropdown select {
            font-size: 16px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 60%;
            max-width: 300px;
        }

        .news-dropdown button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }

        .news-dropdown button:hover {
            background-color: #357abd;
        }

        /* Favorite Button */
        .favorite-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .favorite-button.add {
            background-color: #4caf50;
            color: white;
        }

        .favorite-button.add:hover {
            background-color: #45a049;
        }

        .favorite-button.remove {
            background-color: #e74c3c;
            color: white;
        }

        .favorite-button.remove:hover {
            background-color: #c0392b;
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
        <nav class="menu">
            <?php if ($categoryResult): ?>
                <?php while ($category = $categoryResult->fetch_assoc()): ?>
                    <a href="/category.php?category_id=<?= $category['CategoryID'] ?>">
                        <?= htmlspecialchars($category['CategoryName']) ?>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
            <a href="/favorites.php">Favorites</a>
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

    <!-- News Dropdown Section -->
    <div class="news-dropdown">
        <form method="GET">
            <label for="news_id">Select a News:</label>
            <select name="news_id" id="news_id">
                <?php if ($allNewsResult): ?>
                    <?php while ($row = $allNewsResult->fetch_assoc()): ?>
                        <option value="<?= $row['NewsID'] ?>" <?= $row['NewsID'] == $selectedNewsID ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['Title']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
            <button type="submit">Show Details</button>
        </form>
    </div>

    <!-- News Content Section -->
    <div class="news-container">
        <?php if ($news['StatusID'] == 1): ?>
            <h1 class="news-title"><?= htmlspecialchars($news['Title']) ?></h1>
            <?php if (!empty($news['ImageURL'])): ?>
                <img src="<?= htmlspecialchars($news['ImageURL']) ?>" alt="News Image" class="news-image">
            <?php endif; ?>
            <p class="news-meta">
                Published on: <?= htmlspecialchars(date("F j, Y", strtotime($news['PublicationDate']))) ?> |
                Category: <?= htmlspecialchars($news['CategoryName']) ?> |
                Author: <?= htmlspecialchars($news['AuthorName']) ?>
            </p>
            <div class="news-content">
                <?= nl2br(htmlspecialchars($news['Content'])) ?>
            </div>

            <!-- Favorite Button -->
            <?php if ($userID): ?>
                <?php if ($isFavorite): ?>
                    <button class="favorite-button remove" onclick="removeFromFavorites(<?= $selectedNewsID ?>)">Remove from Favorites</button>
                <?php else: ?>
                    <button class="favorite-button add" onclick="addToFavorites(<?= $selectedNewsID ?>)">Add to Favorites</button>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <p>This news has not yet been published.</p>
        <?php endif; ?>
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
<?php $conn->close(); ?>