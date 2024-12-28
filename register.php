<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Page - News Portal</title>
    <style>
      /* CSS kodları aynı şekilde bırakılmıştır */
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
      <h1>Register</h1>
      <form action="" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required />
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <button type="submit" class="login-btn">Register</button>
      </form>
    </div>

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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve form data
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);
        $role = 2; // Default RoleID = 2

        // Insert data into Users table
        $query = "INSERT INTO Users (UserName, Email, Password, RoleID) VALUES ('$username', '$email', '$password', $role)";

        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Registration successful! You can now log in.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }

    $conn->close();
    ?>
  </body>
</html>