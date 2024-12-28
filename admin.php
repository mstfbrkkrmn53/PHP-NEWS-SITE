<?php
// Database connection
$servername = "localhost";
$username = "khasnews_usr";
$password = "20181701091";
$dbname = "khasnews";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch options
function fetchOptions($conn, $table, $idField, $nameField) {
    $options = [];
    $sql = "SELECT $idField, $nameField FROM $table";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
    }
    return $options;
}

// Fetch authors, categories, news titles, users, roles, images, and statuses
$authors = fetchOptions($conn, "Authors", "AuthorID", "AuthorName");
$categories = fetchOptions($conn, "Category", "CategoryID", "CategoryName");
$newsTitles = fetchOptions($conn, "News", "NewsID", "Title");
$users = fetchOptions($conn, "Users", "UserID", "UserName");
$roles = fetchOptions($conn, "Roles", "RoleID", "RoleName");
$images = fetchOptions($conn, "Images", "ImageID", "ImageName");
$statuses = fetchOptions($conn, "Status", "StatusID", "StatusName");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $categoryID = $_POST['category_id'] ?? 0;
    $authorID = $_POST['author_id'] ?? 0;
    $newsID = $_POST['news_id'] ?? 0;
    $imageID = $_POST['image_id'] ?? 0;
    $statusID = $_POST['status_id'] ?? 0;

    if ($action === 'upload_image') {
        // Upload news image
        $imageName = $_POST['image_name'] ?? '';
        $imageDescription = $_POST['image_description'] ?? '';
        $imageFile = $_FILES['image_file'];

        if ($imageFile['error'] === 0) {
            $imagePath = '/images/' . basename($imageFile['name']); // Add leading "/"
            if (move_uploaded_file($imageFile['tmp_name'], __DIR__ . $imagePath)) {
                $stmt = $conn->prepare("INSERT INTO Images (ImageName, ImageDescription, ImageURL) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $imageName, $imageDescription, $imagePath);
                $stmt->execute();
                $stmt->close();
                echo "News image uploaded and saved successfully!";
            } else {
                echo "Failed to upload news image.";
            }
        } else {
            echo "Error uploading news image.";
        }
    } elseif ($action === 'upload_slider') {
        // Upload slider image
        $imageName = $_POST['image_name'] ?? '';
        $imageDescription = $_POST['image_description'] ?? '';
        $sliderFile = $_FILES['slider_file'];

        if ($sliderFile['error'] === 0) {
            $sliderPath = '/slider/' . basename($sliderFile['name']); // Add leading "/"
            if (move_uploaded_file($sliderFile['tmp_name'], __DIR__ . $sliderPath)) {
                $stmt = $conn->prepare("INSERT INTO Images (ImageName, ImageDescription, ImageURL) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $imageName, $imageDescription, $sliderPath);
                $stmt->execute();
                $stmt->close();
                echo "Slider image uploaded and saved successfully!";
            } else {
                echo "Failed to upload slider image.";
            }
        } else {
            echo "Error uploading slider image.";
        }
    } elseif ($action === 'insert') {
        // Insert news
        $stmt = $conn->prepare("INSERT INTO News (Title, Content, CategoryID, AuthorID, ImageID, StatusID, PublicationDate) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssiiii", $title, $content, $categoryID, $authorID, $imageID, $statusID);
        $stmt->execute();
        $stmt->close();
        echo "News added successfully!";
    } elseif ($action === 'update') {
        // Update news
        $stmt = $conn->prepare("UPDATE News SET Title = ?, Content = ?, CategoryID = ?, AuthorID = ?, ImageID = ?, StatusID = ? WHERE NewsID = ?");
        $stmt->bind_param("ssiiiii", $title, $content, $categoryID, $authorID, $imageID, $statusID, $newsID);
        $stmt->execute();
        $stmt->close();
        echo "News updated successfully!";
    } elseif ($action === 'delete') {
        // Delete news
        $stmt = $conn->prepare("DELETE FROM News WHERE NewsID = ?");
        $stmt->bind_param("i", $newsID);
        $stmt->execute();
        $stmt->close();
        echo "News deleted successfully!";
    } elseif ($action === 'update_role') {
        // Update user role
        $selectedUserID = $_POST['user_id'] ?? 0;
        $selectedRoleID = $_POST['role_id'] ?? 0;

        if ($selectedUserID > 0 && $selectedRoleID > 0) {
            $stmt = $conn->prepare("UPDATE Users SET RoleID = ? WHERE UserID = ?");
            $stmt->bind_param("ii", $selectedRoleID, $selectedUserID);
            $stmt->execute();
            $stmt->close();
            echo "User role updated successfully!";
        } else {
            echo "Please select both a user and a role.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-section {
            margin-bottom: 40px;
        }
        .form-section h2 {
            cursor: pointer;
            background: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
        form {
            display: none;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background: #fefefe;
        }
        form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        form input[type="text"], form textarea, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
        }
        form button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const sections = document.querySelectorAll(".form-section h2");

            sections.forEach(section => {
                section.addEventListener("click", () => {
                    const form = section.nextElementSibling;
                    const isOpen = form.style.display === "block";
                    document.querySelectorAll("form").forEach(f => f.style.display = "none");
                    form.style.display = isOpen ? "none" : "block";
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Admin Management</h1>

        <!-- Role Update Form -->
        <div class="form-section">
            <h2>Update User Role</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_role">
                <label for="user_id">Select User:</label>
                <select name="user_id" id="user_id" required>
                    <option value="">-- Select a User --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['UserID']; ?>"><?= $user['UserName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="role_id">Select Role:</label>
                <select name="role_id" id="role_id" required>
                    <option value="">-- Select a Role --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['RoleID']; ?>"><?= $role['RoleName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Update Role</button>
            </form>
        </div>

        <!-- News Image Upload Form -->
        <div class="form-section">
            <h2>Upload News Image</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_image">
                <label for="image_name">Image Name:</label>
                <input type="text" name="image_name" id="image_name" required>
                <label for="image_description">Image Description:</label>
                <textarea name="image_description" id="image_description" rows="3"></textarea>
                <label for="image_file">Choose Image File:</label>
                <input type="file" name="image_file" id="image_file" accept="image/*" required>
                <button type="submit">Upload News Image</button>
            </form>
        </div>

        <!-- Slider Image Upload Form -->
        <div class="form-section">
            <h2>Upload Slider Image</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_slider">
                <label for="slider_image_name">Image Name:</label>
                <input type="text" name="image_name" id="slider_image_name" required>
                <label for="slider_image_description">Image Description:</label>
                <textarea name="image_description" id="slider_image_description" rows="3"></textarea>
                <label for="slider_file">Choose Slider File:</label>
                <input type="file" name="slider_file" id="slider_file" accept="image/*" required>
                <button type="submit">Upload Slider Image</button>
            </form>
        </div>

        <!-- Insert News Form -->
        <div class="form-section">
            <h2>Insert News</h2>
            <form method="POST">
                <input type="hidden" name="action" value="insert">
                <label for="title">Title:</label>
                <input type="text" name="title" id="title">
                <label for="content">Content:</label>
                <textarea name="content" id="content" rows="5"></textarea>
                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id">
                    <option value="">-- Select a Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['CategoryID']; ?>"><?= $category['CategoryName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="author_id">Author:</label>
                <select name="author_id" id="author_id">
                    <option value="">-- Select an Author --</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= $author['AuthorID']; ?>"><?= $author['AuthorName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="status_id">Status:</label>
                <select name="status_id" id="status_id" required>
                    <option value="">-- Select a Status --</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= $status['StatusID']; ?>"><?= $status['StatusName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="image_id">Select News Image:</label>
                <select name="image_id" id="image_id">
                    <option value="">-- Select an Image --</option>
                    <?php foreach ($images as $image): ?>
                        <option value="<?= $image['ImageID']; ?>"><?= $image['ImageName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Insert News</button>
            </form>
        </div>

        <!-- Update News Form -->
        <div class="form-section">
            <h2>Update News</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <label for="news_id">Select News to Update:</label>
                <select name="news_id" id="news_id" required>
                    <option value="">-- Select a News --</option>
                    <?php foreach ($newsTitles as $news): ?>
                        <option value="<?= $news['NewsID']; ?>"><?= $news['Title']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="title">New Title (Optional):</label>
                <input type="text" name="title" id="title_update">
                <label for="content">New Content (Optional):</label>
                <textarea name="content" id="content_update" rows="5"></textarea>
                <label for="category_id">New Category (Optional):</label>
                <select name="category_id" id="category_id_update">
                    <option value="">-- Select a Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['CategoryID']; ?>"><?= $category['CategoryName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="author_id">New Author (Optional):</label>
                <select name="author_id" id="author_id_update">
                    <option value="">-- Select an Author --</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= $author['AuthorID']; ?>"><?= $author['AuthorName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="status_id">New Status (Optional):</label>
                <select name="status_id" id="status_id_update">
                    <option value="">-- Select a Status --</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= $status['StatusID']; ?>"><?= $status['StatusName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="image_id">Select New News Image (Optional):</label>
                <select name="image_id" id="image_id_update">
                    <option value="">-- Select an Image --</option>
                    <?php foreach ($images as $image): ?>
                        <option value="<?= $image['ImageID']; ?>"><?= $image['ImageName']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Update News</button>
            </form>
        </div>

          <!-- Delete News Form -->
          <div class="form-section">
            <h2>Delete News</h2>
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <label for="news_id">Select News to Delete:</label>
                <select name="news_id" id="news_id" required>
                    <option value="">-- Select a News --</option>
                    <?php foreach ($newsTitles as $news): ?>
                        <option value="<?= $news['NewsID']; ?>"><?= $news['Title']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Delete News</button>
            </form>
        </div>
    </div>
</body>
</html>