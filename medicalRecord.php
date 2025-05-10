<?php
include('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['modify'])) {
        if (isset($_POST['recordID'])) {
            $recordID = $_POST['recordID'];
            $table = $_POST['table'];

            if ($table == 'treatment') {
                $diagnosis = $_POST['diagnosis'];
                $treatmentMethod = $_POST['treatmentMethod'];
                $treatmentDate = $_POST['treatmentDate'];

                $sql = "UPDATE treatmentrecord SET Diagnosis=?, Treatment_Method=?, Treatment_Date=? WHERE Treatment_ID=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssi', $diagnosis, $treatmentMethod, $treatmentDate, $recordID);
            } elseif ($table == 'medicaltest') {
                $testName = $_POST['testName'];
                $testDate = $_POST['testDate'];
                $testResult = $_POST['testResult'];

                $sql = "UPDATE medicaltest SET Test_Name=?, Test_Date=?, Test_Result=? WHERE MedicalTest_ID=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssi', $testName, $testDate, $testResult, $recordID);
            } elseif ($table == 'pharmacy') {
                $medication = $_POST['medication'];
                $issuedDate = $_POST['issuedDate'];
                $quantity = $_POST['quantity'];

                $sql = "UPDATE pharmacyrecord SET Medication_Detail=?, Issued_Date=?, Quantity=? WHERE Pharmacy_ID=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssi', $medication, $issuedDate, $quantity, $recordID);
            }

            $stmt->execute();
        }
    }

    if (isset($_POST['delete'])) {
        if (isset($_POST['recordID'])) {
            $recordID = $_POST['recordID'];
            $table = $_POST['table'];

            if ($table == 'treatment') {
                $sql = "DELETE FROM treatmentrecord WHERE Treatment_ID=?";
            } elseif ($table == 'medicaltest') {
                $sql = "DELETE FROM medicaltest WHERE MedicalTest_ID=?";
            } elseif ($table == 'pharmacy') {
                $sql = "DELETE FROM pharmacyrecord WHERE Pharmacy_ID=?";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $recordID);
            $stmt->execute();
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$searchQuery = isset($_GET['search']) && !empty(trim($_GET['search'])) ? "%" . $conn->real_escape_string($_GET['search']) . "%" : null;

if ($searchQuery) {
    $treatmentQuery = "SELECT * FROM treatmentrecord WHERE Patient_ID LIKE ? OR Treatment_Date LIKE ? OR Diagnosis LIKE ?";
    $medicalTestQuery = "SELECT * FROM medicaltest WHERE Patient_ID LIKE ? OR Test_Name LIKE ? OR Test_Date LIKE ?";
    $pharmacyQuery = "SELECT * FROM pharmacyrecord WHERE Patient_ID LIKE ? OR Medication_Detail LIKE ? OR Issued_Date LIKE ?";

    $treatmentStmt = $conn->prepare($treatmentQuery);
    $treatmentStmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
    $treatmentStmt->execute();
    $treatmentResult = $treatmentStmt->get_result();

    $medicalTestStmt = $conn->prepare($medicalTestQuery);
    $medicalTestStmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
    $medicalTestStmt->execute();
    $medicalTestResult = $medicalTestStmt->get_result();

    $pharmacyStmt = $conn->prepare($pharmacyQuery);
    $pharmacyStmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
    $pharmacyStmt->execute();
    $pharmacyResult = $pharmacyStmt->get_result();
} else {
    $treatmentQuery = "SELECT * FROM treatmentrecord";
    $medicalTestQuery = "SELECT * FROM medicaltest";
    $pharmacyQuery = "SELECT * FROM pharmacyrecord";

    $treatmentResult = $conn->query($treatmentQuery);
    $medicalTestResult = $conn->query($medicalTestQuery);
    $pharmacyResult = $conn->query($pharmacyQuery);
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Patient Medical Information</title>
    <link rel="stylesheet" href="css/medicalRecord.css">
</head>
<body>
<div class="main">
    <div class="navBar">
        <img class="logo" src="assets/hospitalLogo.png" alt="Hospital Logo">
        <div class="iconGroup">
            <a href="generalData.php">
                <img class="icon" src="assets/generalData.png" alt="General Data Logo">
            </a>
            <a href="#">
                <img class="active" src="assets/medicalRecord.png" alt="Medical Record Logo">
            </a>
            <a href="./staffData.php">
                <img class="icon" src="assets/staffData.png" alt="Staff Data Logo">
            </a>
            <a href="./roomData.php">
                <img class="icon" src="assets/roomData.png" alt="Room Data Logo">
            </a>
            <a href="index.php">
                <img class="icon" src="assets/logoutLogo.png" alt="Logout Logo">
            </a>
        </div>
    </div>

    <div class="content">
        <p>Patient's Medical Record</p>
        <input type="text" name="search" id="search" placeholder="Search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

        <div class="buttonGroup">
            <button class="editAction">Edit Information</button>
            <a class="add" href="addRecordType.php">Add New</a>
        </div>

        <h2>Patient's Treatment Record</h2>
        <table class="mainTable">
            <thead>
            <tr>
                <th>Treatment Record ID</th>
                <th>Patient ID</th>
                <th>Diagnosis</th>
                <th>Treatment Method</th>
                <th>Treatment Date</th>
                <th class="actionColumn">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($treatmentResult->num_rows > 0) {
                while ($row = $treatmentResult->fetch_assoc()) {
                    echo "<tr id='treatment_".$row['Treatment_ID']."'>
                            <form method='POST'>
                                <td>" . $row['Treatment_ID'] . "</td>
                                <td>" . $row['Patient_ID'] . "</td>
                                <td><input type='text' name='diagnosis' value='" . $row['Diagnosis'] . "' disabled></td>
                                <td><input type='text' name='treatmentMethod' value='" . $row['Treatment_Method'] . "' disabled></td>
                                <td><input type='date' name='treatmentDate' value='" . $row['Treatment_Date'] . "' disabled></td>
                                <td class='actionColumn'> <!-- Add class to the action cells -->
                                    <input type='hidden' name='recordID' value='" . htmlspecialchars($row['Treatment_ID']) . "'>
                                    <input type='hidden' name='table' value='treatment'>
                                    <button class='editTable' type='button' onclick='toggleEdit(" . htmlspecialchars($row['Treatment_ID']) . ", \"treatment\")'>Edit</button>
                                    <button type='submit' class='saveInfo' name='modify' id='save_treatment_" . htmlspecialchars($row['Treatment_ID']) . "' disabled>Save</button>
                                    <button type='submit' class='deleteInfo' name='delete' id='delete_treatment_" . htmlspecialchars($row['Treatment_ID']) . "' disabled>Delete</button>
                                </td>
                            </form>
                        </tr>";
                }
            }
            ?>
            </tbody>
        </table>

        <br>

        <h2>Patient's Medical Test</h2>
        <table class="mainTable">
            <thead>
            <tr>
                <th>Medical Test ID</th>
                <th>Patient ID</th>
                <th>Test Name</th>
                <th>Test Date</th>
                <th>Test Result</th>
                <th class="actionColumn">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($medicalTestResult->num_rows > 0) {
                while ($row = $medicalTestResult->fetch_assoc()) {
                    echo "<tr id='medicaltest_".$row['MedicalTest_ID']."'>
                            <form method='POST'>
                                <td>" . $row['MedicalTest_ID'] . "</td>
                                <td>" . $row['Patient_ID'] . "</td>
                                <td><input type='text' name='testName' value='" . $row['Test_Name'] . "' disabled></td>
                                <td><input type='date' name='testDate' value='" . $row['Test_Date'] . "' disabled></td>
                                <td><input type='text' name='testResult' value='" . $row['Test_Result'] . "' disabled></td>
                                <td class='actionColumn'>
                                    <input type='hidden' name='recordID' value='" . htmlspecialchars($row['MedicalTest_ID']) . "'>
                                    <input type='hidden' name='table' value='medicaltest'>
                                    <button class='editTable' type='button' onclick='toggleEdit(" . htmlspecialchars($row['MedicalTest_ID']) . ", \"medicaltest\")'>Edit</button>
                                    <button type='submit' class='saveInfo' name='modify' id='save_medicaltest_" . htmlspecialchars($row['MedicalTest_ID']) . "' disabled>Save</button>
                                    <button type='submit' class='deleteInfo' name='delete' id='delete_medicaltest_" . htmlspecialchars($row['MedicalTest_ID']) . "' disabled>Delete</button>
                                </td>
                            </form>
                        </tr>";
                }
            }
            ?>
            </tbody>
        </table>

        <br>

        <h2>Patient's Pharmacy Record</h2>
        <table class="mainTable">
            <thead>
            <tr>
                <th>Pharmacy Record ID</th>
                <th>Patient ID</th>
                <th>Medication</th>
                <th>Issued Date</th>
                <th>Quantity</th>
                <th class="actionColumn">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($pharmacyResult->num_rows > 0) {
                while ($row = $pharmacyResult->fetch_assoc()) {
                    echo "<tr id='pharmacy_".$row['Pharmacy_ID']."'>
                            <form method='POST'>
                                <td>" . $row['Pharmacy_ID'] . "</td>
                                <td>" . $row['Patient_ID'] . "</td>
                                <td><input type='text' name='medication' value='" . $row['Medication_Detail'] . "' disabled></td>
                                <td><input type='date' name='issuedDate' value='" . $row['Issued_Date'] . "' disabled></td>
                                <td><input type='number' name='quantity' value='" . $row['Quantity'] . "' disabled></td>
                                <td class='actionColumn'>
                                    <input type='hidden' name='recordID' value='" . htmlspecialchars($row['Pharmacy_ID']) . "'>
                                    <input type='hidden' name='table' value='pharmacy'>
                                    <button class='editTable' type='button' onclick='toggleEdit(" . htmlspecialchars($row['Pharmacy_ID']) . ", \"pharmacy\")'>Edit</button>
                                    <button type='submit' class='saveInfo' name='modify' id='save_pharmacy_" . htmlspecialchars($row['Pharmacy_ID']) . "' disabled>Save</button>
                                    <button type='submit' class='deleteInfo' name='delete' id='delete_pharmacy_" . htmlspecialchars($row['Pharmacy_ID']) . "' disabled>Delete</button>
                                </td>
                            </form>
                        </tr>";
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleEdit(recordId, recordType) {
        const row = document.getElementById(recordType + "_" + recordId);
        const inputs = row.querySelectorAll('input');
        const buttons = row.querySelectorAll('button');

        const isDisabled = inputs[0].disabled;

        inputs.forEach(input => input.disabled = !isDisabled);

        buttons.forEach(button => {
            if (button.classList.contains('editTable')) {
                button.disabled = false;
            } else {
                button.disabled = !isDisabled;
            }
        });
    }

    function toggleActions() {
        const actionColumns = document.querySelectorAll('.actionColumn');
        actionColumns.forEach(col => {
            if (col.style.display === 'none' || col.style.display === '') {
                col.style.display = 'table-cell';
            } else {
                col.style.display = 'none';
            }
        });
    }

    document.querySelector('.editAction').addEventListener('click', toggleActions);

    document.getElementById('search').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    });

    function performSearch() {
        const searchValue = document.getElementById('search').value.trim();
        if (searchValue) {
            window.location.href = `?search=${encodeURIComponent(searchValue)}`;
        } else {
            window.location.href = window.location.pathname;
        }
    }
</script>
</body>
</html>
