<?php
include('db_connect.php');

$patientQuery = "SELECT Patient_ID FROM patient";
$patientResult = $conn->query($patientQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addPharmacy'])) {
    $patientId = $_POST['patientIdPharmacy'];
    $medsDetail = $_POST['medsDetail'];
    $issuedDate = $_POST['issuedDate'];
    $quantity = $_POST['Quantity'];

    if (!empty($patientId) && !empty($medsDetail) && !empty($issuedDate) && !empty($quantity)) {
        $sql = "INSERT INTO pharmacyrecord (Patient_ID, Medication_Detail, Issued_Date, Quantity) 
                VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ssss', $patientId, $medsDetail, $issuedDate, $quantity);

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
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Pharmacy Record</title>
    <link rel="stylesheet" href="css/addPharmacy.css">
</head>
<body>
<div class="main">
    <h1>Add Pharmacy Record</h1>
    <div class="addPharmacyPopUp">
        <form method="POST" action="addPharmacy.php">
            <div class="addPharmacyPopUpForm">
                <div class="info">
                    <label for="patientIdPharmacy">Patient ID</label>
                    <label for="medsDetail">Medication Detail</label>
                    <label for="issuedDate">Issued Date</label>
                    <label for="Quantity">Quantity</label>
                </div>

                <div class="value">
                    <select class="dropdown" name="patientIdPharmacy" id="patientIdPharmacy" required>
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

                    <input type="text" name="medsDetail" id="medsDetail" required>
                    <input type="date" name="issuedDate" id="issuedDate" required>
                    <input type="text" name="Quantity" id="Quantity" required>
                </div>
            </div>

            <div class="addPharmacyPopUpButton">
                <a class="cancelAddPharmacyNew" href="medicalRecord.php">Cancel</a>
                <button type="submit" name="addPharmacy" class="addPharmacyNew">Add New</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
