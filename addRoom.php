<?php
include 'db_connect.php';

$patientQuery = "SELECT Patient_ID FROM patient";
$patientResult = $conn->query($patientQuery);

$doctorQuery = "SELECT Staff_ID, Staff_Name FROM staffinformation WHERE Profession = 'Doctor'";
$doctorResult = $conn->query($doctorQuery);

$nurseQuery = "SELECT Staff_ID, Staff_Name FROM staffinformation WHERE Profession = 'Nurse'";
$nurseResult = $conn->query($nurseQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patientId = mysqli_real_escape_string($conn, $_POST['patientId']);
    $roomType = mysqli_real_escape_string($conn, $_POST['roomType']);
    $roomNumber = mysqli_real_escape_string($conn, $_POST['roomNumber']);
    $bedNumber = mysqli_real_escape_string($conn, $_POST['bedNumber']);

    $assignedDoctor = mysqli_real_escape_string($conn, $_POST['assignedDoctor']);
    $assignedNurse = mysqli_real_escape_string($conn, $_POST['assignedNurse']);

    $insertQuery = "
        INSERT INTO roominformation (Patient_ID, Room_Type, Room_Number, Bed_Number, Assigned_Doctor, Assigned_Nurse) 
        VALUES ('$patientId', '$roomType', '$roomNumber', '$bedNumber', '$assignedDoctor', '$assignedNurse')
    ";

    if ($conn->query($insertQuery) === TRUE) {
        header('Location: roomData.php');
        exit();
    } else {
        echo "Error: " . $insertQuery . "<br>" . $conn->error;
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
    <title>Add Room Information</title>
    <link rel="stylesheet" href="css/addRoom.css">
</head>
<body>
<div class="main">
    <h1>Add Room Information</h1>
    <div class="addPopUp">
        <form method="post">
            <div class="addPopUpForm">
                <div class="info">
                    <label for="patientId">Patient ID</label>
                    <label for="roomType">Room Type</label>
                    <label for="roomNumber">Room Number</label>
                    <label for="bedNumber">Bed Number</label>
                    <label for="assignedDoctor">Assigned Doctor</label>
                    <label for="assignedNurse">Assigned Nurse</label>
                </div>

                <div class="value">
                    <select class="dropdown" name="patientId" id="patientId" required>
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

                    <input type="text" name="roomType" id="roomType" required>
                    <input type="text" name="roomNumber" id="roomNumber" required>
                    <input type="text" name="bedNumber" id="bedNumber" required>

                    <select style="margin-top: 5px" class="dropdown" name="assignedDoctor" id="assignedDoctor" required>
                        <option value="">Select Doctor</option>
                        <?php
                        if ($doctorResult->num_rows > 0) {
                            while ($row = $doctorResult->fetch_assoc()) {
                                echo "<option value='" . $row['Staff_ID'] . "'>" . $row['Staff_Name'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No doctors available</option>";
                        }
                        ?>
                    </select>

                    <select style="margin-top: 5px" class="dropdown" name="assignedNurse" id="assignedNurse" required>
                        <option value="">Select Nurse</option>
                        <?php
                        if ($nurseResult->num_rows > 0) {
                            while ($row = $nurseResult->fetch_assoc()) {
                                echo "<option value='" . $row['Staff_ID'] . "'>" . $row['Staff_Name'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No nurses available</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="addPopUpButton">
                <a class="cancelAddNew" href="./roomData.php">Cancel</a>
                <button type="submit" class="addNew">Add New</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>

<?php
$conn->close();
?>
