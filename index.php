<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM account WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result_account = $stmt->get_result();

    if ($result_account->num_rows > 0) {
        header("location: generalData.php");
        exit();
    } else {
        $stmt = $conn->prepare("SELECT * FROM verification WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result_verification = $stmt->get_result();

        if ($result_verification->num_rows > 0) {
            header("Location: staffNotVerified.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="card">
    <img src="./assets/hospitalLogo.png" alt="Hospital Logo">
    <hr>
    <div class="aside">
        <form method="post">
            <div class="loginInfo">
                <label for="username">Username :</label>
                <input type="text" name="username" id="username" required>
                <br>
                <label for="password">Password :</label>
                <input type="password" name="password" id="password" required>
                <?php if ($error): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <button type="submit" class="loginButton">Login</button>
            </div>
        </form>
        <p>Don't have an account? <a href="./roles.php">Sign Up</a></p>
    </div>
</div>
</body>
</html>
