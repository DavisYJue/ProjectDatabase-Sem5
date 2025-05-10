<?php
include 'db_connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['modify'])) {
        $roomNumber = $_POST['room_number'];
        $diagnose = isset($_POST['diagnose']) ? $_POST['diagnose'] : '';
        $roomType = $_POST['room_type'];
        $bedNumber = $_POST['bed_number'];
        $assignedDoctor = $_POST['assigned_doctor'];
        $assignedNurse = $_POST['assigned_nurse'];

        $updateQuery = "
            UPDATE roominformation 
            SET Diagnose = ?, Room_Type = ?, Bed_Number = ?, Assigned_Doctor = ?, Assigned_Nurse = ? 
            WHERE Room_Number = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('ssssss', $diagnose, $roomType, $bedNumber, $assignedDoctor, $assignedNurse, $roomNumber);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        $roomNumber = $_POST['room_number'];

        $deleteQuery = "DELETE FROM roominformation WHERE Room_Number = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param('s', $roomNumber);
        $stmt->execute();
        $stmt->close();
    }
}

$roomCounts = [];
$countQuery = "SELECT * FROM roomcounts";
$countResult = $conn->query($countQuery);
if ($countResult->num_rows > 0) {
    while ($row = $countResult->fetch_assoc()) {
        $roomCounts[$row['Room_Type']] = $row['Total_Room'];
    }
}

$usedCounts = ['ICU' => 0, 'OR' => 0];
$usedQuery = "SELECT Room_Type, COUNT(*) as used_count FROM roominformation GROUP BY Room_Type";
$usedResult = $conn->query($usedQuery);
if ($usedResult->num_rows > 0) {
    while ($row = $usedResult->fetch_assoc()) {
        $usedCounts[$row['Room_Type']] = $row['used_count'];
    }
}

$remainingICURooms = $roomCounts['ICU'] - $usedCounts['ICU'];
$remainingORRooms = $roomCounts['OR'] - $usedCounts['OR'];

$sql = "
    SELECT ri.Room_Number, ri.Patient_ID, tr.Diagnosis, ri.Room_Type, ri.Bed_Number, ri.Assigned_Doctor, ri.Assigned_Nurse
    FROM roominformation ri
    LEFT JOIN treatmentrecord tr ON ri.Patient_ID = tr.Patient_ID";

if ($search) {
    $searchEscaped = $conn->real_escape_string($search);
    $sql = "
        SELECT ri.Room_Number, ri.Patient_ID, tr.Diagnosis, ri.Room_Type, ri.Bed_Number, ri.Assigned_Doctor, ri.Assigned_Nurse
        FROM roominformation ri
        LEFT JOIN treatmentrecord tr ON ri.Patient_ID = tr.Patient_ID
        WHERE ri.Room_Number LIKE ?
           OR ri.Patient_ID LIKE ?
           OR ri.Room_Type LIKE ? 
           OR ri.Bed_Number LIKE ? 
           OR ri.Assigned_Doctor LIKE ? 
           OR ri.Assigned_Nurse LIKE ?";

    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $searchEscaped . "%";

    $stmt->bind_param('ssssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Information</title>
    <link rel="stylesheet" href="css/roomData.css">

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editButton = document.querySelector('.edit');
            let actionsVisible = false;

            editButton.addEventListener('click', () => {
                const actionCells = document.querySelectorAll('td:last-child');
                const actionHeader = document.querySelector('th:last-child');
                actionsVisible = !actionsVisible;

                actionHeader.style.display = actionsVisible ? 'table-cell' : 'none';
                actionCells.forEach(cell => {
                    cell.style.display = actionsVisible ? 'table-cell' : 'none';
                });
            });

            const actionCells = document.querySelectorAll('td:last-child');
            const actionHeader = document.querySelector('th:last-child');
            actionHeader.style.display = 'none';
            actionCells.forEach(cell => {
                cell.style.display = 'none';
            });
        });

        function toggleEdit(roomNumber) {
            const fields = ['diagnose', 'room_type', 'bed_number', 'assigned_doctor', 'assigned_nurse'];
            const buttons = ['modify', 'delete'];

            fields.forEach(field => {
                const input = document.querySelector(`#${field}_${roomNumber}`);

                input.removeAttribute('readonly');
                input.disabled = false;

                input.classList.toggle('readonlyInput');

                input.style.cursor = 'text';
            });

            buttons.forEach(button => {
                const btn = document.querySelector(`#${button}_${roomNumber}`);
                btn.disabled = !btn.disabled;
            });

            const doctorDropdown = document.querySelector(`#assigned_doctor_${roomNumber}`);
            const nurseDropdown = document.querySelector(`#assigned_nurse_${roomNumber}`);
            doctorDropdown.disabled = false;
            nurseDropdown.disabled = false;

            doctorDropdown.style.cursor = 'pointer';
            nurseDropdown.style.cursor = 'pointer';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search');
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const searchValue = searchInput.value;
                    window.location.href = "?search=" + encodeURIComponent(searchValue);
                }
            });
        });
    </script>
</head>
<body>
<div class="main">
    <div class="navBar">
        <img class="logo" src="assets/hospitalLogo.png" alt="Hospital Logo">
        <div class="iconGroup">
            <a href="generalData.php">
                <img class="icon" src="assets/generalData.png" alt="General Data Logo">
            </a>
            <a href="medicalRecord.php">
                <img class="icon" src="assets/medicalRecord.png" alt="Medical Record Logo">
            </a>
            <a href="./staffData.php">
                <img class="icon" src="assets/staffData.png" alt="Staff Data Logo">
            </a>
            <a href="#">
                <img class="active" src="assets/roomData.png" alt="Room Data Logo">
            </a>
            <a href="index.php">
                <img class="icon" src="assets/logoutLogo.png" alt="Logout Logo">
            </a>
        </div>
    </div>

    <div class="content">
        <p>Room Information</p>
        <input type="text" name="search" id="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
        <div class="buttonGroup">
            <button class="edit">Edit Information</button>
            <a class="add" href="addRoom.php">Add new</a>
        </div>

        <div class="remainingRoom">
            <h3 class="icuRoomLeft">Remaining Room in ICU : <?php echo $remainingICURooms; ?></h3>
            <h3 class="orRoomLeft">Remaining Room in OR : <?php echo $remainingORRooms; ?></h3>
        </div>

        <table class="mainTable">
            <thead>
            <tr>
                <th>Room Number</th>
                <th>Patient ID</th>
                <th>Diagnose</th>
                <th>Room Type</th>
                <th>Bed Number</th>
                <th>Assigned Doctor</th>
                <th>Assigned Nurse</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<form method='post'>";
                    echo "<td>" . htmlspecialchars($row['Room_Number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Patient_ID']) . "</td>";
                    echo "<td><span id='diagnose_" . htmlspecialchars($row['Room_Number']) . "'>" . htmlspecialchars($row['Diagnosis'] ?? 'No Diagnosis') . "</span></td>";

                    echo "<td><input type='text' name='room_type' id='room_type_" . htmlspecialchars($row['Room_Number']) . "' value='" . htmlspecialchars($row['Room_Type']) . "' readonly disabled></td>";
                    echo "<td><input type='text' name='bed_number' id='bed_number_" . htmlspecialchars($row['Room_Number']) . "' value='" . htmlspecialchars($row['Bed_Number']) . "' readonly disabled></td>";

                    echo "<td><select name='assigned_doctor' id='assigned_doctor_" . htmlspecialchars($row['Room_Number']) . "' disabled>";
                    $doctorResult = $conn->query("SELECT Staff_ID, Staff_Name FROM staffinformation WHERE profession = 'doctor'");
                    while ($doctor = $doctorResult->fetch_assoc()) {
                        echo "<option value='" . $doctor['Staff_ID'] . "' " . ($row['Assigned_Doctor'] == $doctor['Staff_ID'] ? 'selected' : '') . ">" . $doctor['Staff_Name'] . "</option>";
                    }
                    echo "</select></td>";

                    echo "<td><select name='assigned_nurse' id='assigned_nurse_" . htmlspecialchars($row['Room_Number']) . "' disabled>";
                    $nurseResult = $conn->query("SELECT Staff_ID, Staff_Name FROM staffinformation WHERE profession = 'nurse'");
                    while ($nurse = $nurseResult->fetch_assoc()) {
                        echo "<option value='" . $nurse['Staff_ID'] . "' " . ($row['Assigned_Nurse'] == $nurse['Staff_ID'] ? 'selected' : '') . ">" . $nurse['Staff_Name'] . "</option>";
                    }
                    echo "</select></td>";

                    echo "<td>
                            <button class='editTable' type='button' onclick='toggleEdit(\"" . htmlspecialchars($row['Room_Number']) . "\")'>Edit</button>
                            <button class='modifyInfo' type='submit' name='modify' id='modify_" . htmlspecialchars($row['Room_Number']) . "' disabled>Save</button>
                            <button class='deleteInfo' type='submit' name='delete' id='delete_" . htmlspecialchars($row['Room_Number']) . "' disabled>Delete</button>
                            <input type='hidden' name='room_number' value='" . htmlspecialchars($row['Room_Number']) . "'>
                          </td>";
                    echo "</form>";
                    echo "</tr>";
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
