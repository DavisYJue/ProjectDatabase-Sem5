<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['staffUsername'];
    $password = $_POST['staffPassword'];
    $staffName = $_POST['staffName'];
    $gender = $_POST['gender'];
    $profession = $_POST['profession'];
    $department = $_POST['department'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    if (empty($username) || empty($password) || empty($staffName) || empty($gender) || empty($profession) || empty($department) || empty($contact) || empty($address)) {
        echo "Please fill all the fields!";
    } else {
        $sql = "INSERT INTO verification (username, password, staff_name, gender, profession, department, contact_number, address)
                VALUES ('$username', '$password', '$staffName', '$gender', '$profession', '$department', '$contact', '$address')";

        if ($conn->query($sql) === TRUE) {
            echo "Registration submitted for verification.";
            header("Location: addStaffNotVerified.php");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register (Staff)</title>
    <link rel="stylesheet" href="css/addStaff.css">
</head>
<body>
<div class="main">
    <h1>Staff Register</h1>
    <div class="addPopUp">
        <form method="POST" action="addStaff.php">
            <div class="addPopUpForm">
                <div class="info">
                    <label for="staffUsername">Staff Username</label>
                    <label for="staffPassword">Staff Password</label>
                    <label for="staffName">Staff Name</label>
                    <label for="gender">Gender</label>
                    <label for="profession">Profession</label>
                    <label for="department">Department</label>
                    <label for="contact">Contact Number</label>
                    <label for="address">Address</label>
                </div>

                <div class="value">
                    <input type="text" name="staffUsername" id="staffUsername" required>
                    <input type="text" name="staffPassword" id="staffPassword" required>
                    <input type="text" name="staffName" id="staffName" required>
                    <input type="text" name="gender" id="gender" required>
                    <input type="text" name="profession" id="profession" required>
                    <input type="text" name="department" id="department" required>
                    <input type="text" name="contact" id="contact" required>
                    <input type="text" name="address" id="address" required>
                </div>
            </div>

            <div class="addPopUpButton">
                <a class="cancelRegister" href="staffData.php">Cancel</a>
                <button type="submit" class="register">Register</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
