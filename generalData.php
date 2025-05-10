<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['modify'])) {
        $id = $_POST['patient_id'];
        $contactNumber = $_POST['contact_number'];
        $address = $_POST['address'];

        $updateQuery = "UPDATE patient SET Contact_Number='$contactNumber', Address='$address' WHERE Patient_ID='$id'";
        $conn->query($updateQuery);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['patient_id'];

        $conn->begin_transaction();
        try {
            $getPatientNameQuery = "SELECT Patient_Name FROM patient WHERE Patient_ID = '$id'";
            $patientResult = $conn->query($getPatientNameQuery);

            if ($patientResult && $patientRow = $patientResult->fetch_assoc()) {
                $patientName = $patientRow['Patient_Name'];
                $deleteAccountQuery = "DELETE FROM account WHERE full_name = '$patientName'";
                if (!$conn->query($deleteAccountQuery)) {
                    throw new Exception("Error deleting account: " . $conn->error);
                }
            }

            $deleteQuery = "DELETE FROM patient WHERE Patient_ID='$id'";
            if (!$conn->query($deleteQuery)) {
                throw new Exception("Error deleting patient record: " . $conn->error);
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }
}

$searchQuery = '';

if (isset($_POST['search']) && !empty($_POST['search'])) {
    $searchQuery = $_POST['search'];
}

$sql = "SELECT * FROM patient WHERE 
            Patient_Name LIKE '%$searchQuery%' OR 
            Contact_Number LIKE '%$searchQuery%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient General Information</title>
    <link rel="stylesheet" href="css/generalData.css">
    <style>
        .hideActions td:last-child,
        .hideActions th:last-child {
            display: none;
        }

        .readonlyInput {
            border: none;
            background: none;
            color: inherit;
            font: inherit;
            padding: 0;
            cursor: default;
        }
    </style>
    <script>
        function toggleEditActions() {
            const table = document.querySelector(".mainTable");
            table.classList.toggle("hideActions");
        }

        function toggleEdit(rowId) {
            const contactField = document.querySelector(`#contact_${rowId}`);
            const addressField = document.querySelector(`#address_${rowId}`);
            const modifyButton = document.querySelector(`#modify_${rowId}`);
            const deleteButton = document.querySelector(`#delete_${rowId}`);

            if (contactField.readOnly) {
                contactField.readOnly = false;
                contactField.classList.remove('readonlyInput');
                addressField.readOnly = false;
                addressField.classList.remove('readonlyInput');
                modifyButton.disabled = false;
                deleteButton.disabled = false;
            } else {
                contactField.readOnly = true;
                contactField.classList.add('readonlyInput');
                addressField.readOnly = true;
                addressField.classList.add('readonlyInput');
                modifyButton.disabled = true;
                deleteButton.disabled = true;
            }
        }
    </script>
</head>
<body>
<div class="main">
    <div class="navBar">
        <img class="logo" src="assets/hospitalLogo.png" alt="Hospital Logo">
        <div class="iconGroup">
            <a href="#">
                <img class="active" src="assets/generalData.png" alt="General Data Logo">
            </a>
            <a href="./medicalRecord.php">
                <img class="icon" src="assets/medicalRecord.png" alt="Medical Record Logo">
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
        <p>Patient's Information</p>
        <form method="post">
            <input type="text" name="search" id="search" placeholder="Search" value="<?php echo htmlspecialchars($searchQuery); ?>">
        </form>
        <div class="buttonGroup">
            <button class="edit" type="button" onclick="toggleEditActions()">Edit Information</button>
            <a class="add" href="addGeneralData.php">Add New</a>
        </div>
        <table class="mainTable hideActions">
            <thead>
            <tr>
                <th>Patient ID</th>
                <th>Patient Name</th>
                <th>Gender</th>
                <th>Age</th>
                <th>Contact Number</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<form method='post'>";
                    echo "<td>" . $row['Patient_ID'] . "</td>";
                    echo "<td>" . $row['Patient_Name'] . "</td>";
                    echo "<td>" . $row['Gender'] . "</td>";
                    echo "<td>" . $row['Age'] . "</td>";
                    echo "<td>
                            <input type='text' id='contact_" . $row['Patient_ID'] . "' 
                                   name='contact_number' 
                                   value='" . $row['Contact_Number'] . "' 
                                   class='readonlyInput' 
                                   readonly>
                          </td>";
                    echo "<td>
                            <input type='text' id='address_" . $row['Patient_ID'] . "' 
                                   name='address' 
                                   value='" . $row['Address'] . "' 
                                   class='readonlyInput' 
                                   readonly>
                          </td>";
                    echo "<td>
                            <input type='hidden' name='patient_id' value='" . $row['Patient_ID'] . "'>
                            <button class='editTable' type='button' onclick='toggleEdit(" . $row['Patient_ID'] . ")'>Edit</button>
                            <button type='submit' class='saveInfo' name='modify' id='modify_" . $row['Patient_ID'] . "' disabled>Save</button>
                            <button type='submit' class='deleteInfo' name='delete' id='delete_" . $row['Patient_ID'] . "' disabled>Delete</button>
                          </td>";
                    echo "</form>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No records found</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<?php
$conn->close();
?>
