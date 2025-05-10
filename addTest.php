<?php
include('db_connect.php');

$patientQuery = "SELECT Patient_ID FROM patient";
$patientResult = $conn->query($patientQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addTest'])) {
    $patientId = $_POST['patientIdTest'];
    $testName = $_POST['testName'];
    $testDate = $_POST['testDate'];
    $testResult = $_POST['testResult'];

    if (!empty($patientId) && !empty($testName) && !empty($testDate) && !empty($testResult)) {
        $sql = "INSERT INTO medicaltest (Patient_ID, Test_Name, Test_Date, Test_Result) 
                VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ssss', $patientId, $testName, $testDate, $testResult);

            if ($stmt->execute()) {
                header("Location: medicalRecord.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Please fill out all fields.";
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Medical Test</title>
    <link rel="stylesheet" href="css/addTest.css">
</head>
<body>
<div class="main">
    <h1>Add Medical Test</h1>
    <div class="addTestPopUp">
        <form method="POST" action="addTest.php">
            <div class="addTestPopUpForm">
                <div class="info">
                    <label for="patientIdTest">Patient ID</label>
                    <label for="testName">Test Name</label>
                    <label for="testDate">Test Date</label>
                    <label for="testResult">Test Result</label>
                </div>

                <div class="value">
                    <select class="dropdown" name="patientIdTest" id="patientIdTest" required>
                        <option value="">Select Patient ID</option>
                        <?php
                        if ($patientResult->num_rows > 0) {
                            while ($row = $patientResult->fetch_assoc()) {
                                echo "<option value='" . $row['Patient_ID'] . "'>" . $row['Patient_ID'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No patients available</option>";
                        }
                        ?>
                    </select>

                    <input type="text" name="testName" id="testName" required>
                    <input type="date" name="testDate" id="testDate" required>
                    <input type="text" name="testResult" id="testResult" required>
                </div>
            </div>

            <div class="addTestPopUpButton">
                <a class="cancelAddTestNew" href="./medicalRecord.php">Cancel</a>
                <button type="submit" name="addTest" class="addTestNew">Add New</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
