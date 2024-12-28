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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kullanıcıdan gelen veriler
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Kullanıcı bilgilerini kontrol et
    $query = "SELECT UserID, RoleID FROM Users WHERE UserName = '$username' AND Password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userID = $row['UserID'];
        $roleID = $row['RoleID'];

        // Kullanıcıyı oturuma kaydet
        $_SESSION['user_id'] = $userID;

        // Giriş zamanını güncelle
        $currentDate = date('Y-m-d H:i:s');
        $updateQuery = "UPDATE Users SET LoginDate = '$currentDate' WHERE UserID = $userID";
        $conn->query($updateQuery);

        // RoleID'ye göre yönlendir
        if ($roleID == 1) {
            echo "<script>alert('Login successful!'); window.location.href='admin.php';</script>";
        } elseif ($roleID == 2) {
            echo "<script>alert('Login successful!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Login successful! No specific role assigned.'); window.location.href='index.php';</script>";
        }
    } else {
        // Hatalı kullanıcı adı veya şifre
        echo "<script>alert('Invalid username or password.');</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page - News Portal</title>
    <style>
        /* CSS kodları */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(to right, #4a90e2, #9013fe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .login-container {
            background: #ffffff;
            color: #333;
            width: 400px;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
        }

        .login-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #4a90e2;
        }

        .login-container form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #4a90e2;
            outline: none;
        }

        .login-btn {
            background-color: #4a90e2;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 321px;
        }

        .login-btn:hover {
            background-color: #357abd;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #4a90e2;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 230px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="/images/logo.png" alt="ekhas logo" />
        </div>
        <h1>Login</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <br /><br />
        <div class="form-group">
            <a href="register.php" class="login-btn" style="text-decoration: none; display: inline-block; text-align: center;">You're not a member?</a>
        </div>
    </div>
</body>
</html>