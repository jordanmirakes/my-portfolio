<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    try {
        $pdo = new PDO("sqlite:/home/jordan/haha/bank.db");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "❌ Invalid email or password.";
        }
    } catch (PDOException $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}
?>

<!-- Keep your PHP block at the top as it is -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - VARO Bank</title>
    <style>
        /* [your existing styles remain unchanged] */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
        }

        .login-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 40px;
            background: #ffffff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo img {
            width: 100px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            width: 100%;
            background-color: #2d89ef;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #1e5fbf;
        }

        .error {
            color: red;
            margin-top: 15px;
        }

        .footer {
            margin-top: 25px;
            font-size: 14px;
            color: #999;
        }

        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        }

        #loader-logo {
            width: 120px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- 1. Loader HTML -->
    <div id="loader">
        <img src="uploads/VARO.jpg" alt="Loading..." id="loader-logo">
    </div>

    <div class="login-container">
        <div class="logo">
            <img src="uploads/VARO.jpg" alt="Logo">
        </div>
        <h2>Login</h2>

        <form method="post">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>

        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> VARO. All rights reserved.
        </div>
    </div>

    <!-- 2. Script goes at the bottom of body -->
    <script>
        const form = document.querySelector("form");
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            document.getElementById("loader").style.display = "flex";
            setTimeout(() => {
                form.submit();
            }, 2000);
        });
    </script>
</body>
</html>

