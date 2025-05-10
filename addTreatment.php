<?php
include('db_connect.php');

$patientQuery = "SELECT Patient_ID FROM patient";
$patientResult = $conn->query($patientQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addTreatment'])) {
    $patientId = $_POST['patientIdTreatment'];
    $diagnosis = $_POST['diagnosis'];
    $treatmentMethod = $_POST['treatmentMethod'];
    $treatmentDate = $_POST['treatmentDate'];

    $sql = "INSERT INTO treatmentrecord (Patient_ID, Diagnosis, Treatment_Method, Treatment_Date) 
            VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssss', $patientId, $diagnosis, $treatmentMethod, $treatmentDate);

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
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Treatment Record</title>
    <link rel="stylesheet" href="css/addTreatment.css">
</head>
<body>
<div class="main">
    <h1>Add Patient's Treatment Record</h1>
    <div class="addTreatmentPopUp">
        <form method="POST" action="addTreatment.php">
            <div class="addTreatmentPopUpForm">
                <div class="info">
                    <label for="patientIdTreatment">Patient ID</label>
                    <label for="diagnosis">Diagnosis</label>
                    <label for="treatmentMethod">Treatment Method</label>
                    <label for="treatmentDate">Treatment Date</label>
                </div>

                <div class="value">
                    <select class="dropdown" name="patientIdTreatment" id="patientIdTreatment" required>
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

                    <input type="text" name="diagnosis" id="diagnosis" required>
                    <input type="text" name="treatmentMethod" id="treatmentMethod" required>
                    <input type="date" name="treatmentDate" id="treatmentDate" required>
                </div>
            </div>

            <div class="addTreatmentPopUpButton">
                <a class="cancelAddTreatment" href="./medicalRecord.php">Cancel</a>
                <button type="submit" name="addTreatment" class="addTreatment">Add New</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
