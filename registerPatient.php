<?php
include 'db_connect.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patientUsername = $_POST['patientUsername'];
    $patientPassword = $_POST['patientPassword'];
    $patientName = $_POST['patientName'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    $conn->begin_transaction();

    try {
        $sqlPatients = "INSERT INTO patient (Patient_Name, Gender, Age, Contact_Number, Address) 
                        VALUES ('$patientName', '$gender', $age, '$contact', '$address')";

        if (!$conn->query($sqlPatients)) {
            throw new Exception("Error inserting into patients table: " . $conn->error);
        }

        $sqlAccount = "INSERT INTO account (username, password, role, full_name) 
                       VALUES ('$patientUsername', '$patientPassword', 'patient', '$patientName')";

        if (!$conn->query($sqlAccount)) {
            throw new Exception("Error inserting into account table: " . $conn->error);
        }

        $conn->commit();
        $message = "Patient registered successfully! You will be redirected shortly.";
        sleep(2);
        header("Location: generalData.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $message = "Registration failed: " . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register (Patient)</title>
    <link rel="stylesheet" href="css/registerPatient.css">
</head>
<body>
    <div class="main">
        <h1>Patient Register</h1>
        <?php if ($message): ?>
            <div class="message" style="color: slateblue; font-size: 23px; margin-bottom: 10px; font-weight: bold"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="addPopUp">
                <div class="addPopUpForm">
                    <div class="info">
                        <label for="patientUsername">Patient Username</label>
                        <label for="patientPassword">Patient Password</label>
                        <label for="patientName">Patient Name</label>
                        <label for="gender">Gender</label>
                        <label for="age">Age</label>
                        <label for="contact">Contact Number</label>
                        <label for="address">Address</label>
                    </div>

                    <div class="value">
                        <input type="text" name="patientUsername" id="patientUsername" required>
                        <input type="text" name="patientPassword" id="patientPassword" required>
                        <input type="text" name="patientName" id="patientName" required>
                        <input type="text" name="gender" id="gender" required>
                        <input type="text" name="age" id="age" required>
                        <input type="text" name="contact" id="contact" required>
                        <input type="text" name="address" id="address" required>
                    </div>
                </div>

                <div class="addPopUpButton">
                    <a class="cancelRegister" href="index.php">Cancel</a>
                    <button type="submit" class="register">Register</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>