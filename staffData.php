<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['modify'])) {
        $id = $_POST['staff_id'];
        $contactNumber = $_POST['contact_number'];
        $address = $_POST['address'];

        $updateQuery = "UPDATE staffinformation SET Contact_Number='$contactNumber', Address='$address' WHERE Staff_ID='$id'";
        $conn->query($updateQuery);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['staff_id'];

        $conn->begin_transaction();
        try {
            $getStaffNameQuery = "SELECT Staff_Name FROM staffinformation WHERE Staff_ID = '$id'";
            $staffResult = $conn->query($getStaffNameQuery);

            if ($staffResult && $staffRow = $staffResult->fetch_assoc()) {
                $staffName = $staffRow['Staff_Name'];
                $deleteAccountQuery = "DELETE FROM account WHERE full_name = '$staffName'";
                if (!$conn->query($deleteAccountQuery)) {
                    throw new Exception("Error deleting account: " . $conn->error);
                }
            }

            $deleteQuery = "DELETE FROM staffinformation WHERE Staff_ID='$id'";
            if (!$conn->query($deleteQuery)) {
                throw new Exception("Error deleting staff record: " . $conn->error);
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

$sql = "SELECT * FROM staffinformation WHERE 
            Staff_Name LIKE '%$searchQuery%' OR 
            Contact_Number LIKE '%$searchQuery%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Information</title>
    <link rel="stylesheet" href="css/staffData.css">
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
            <a href="./generalData.php">
                <img class="icon" src="assets/generalData.png" alt="General Data Logo">
            </a>
            <a href="./medicalRecord.php">
                <img class="icon" src="assets/medicalRecord.png" alt="Medical Record Logo">
            </a>
            <a href="#">
                <img class="active" src="assets/staffData.png" alt="Staff Data Logo">
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
        <p>Staff's Information</p>
        <form method="post">
            <input type="text" name="search" id="search" placeholder="Search" value="<?php echo htmlspecialchars($searchQuery); ?>">
        </form>
        <div class="buttonGroup">
            <a class="application" href="staffApplication.php">Application</a>
            <button class="edit" type="button" onclick="toggleEditActions()">Edit Information</button>
            <a class="add" href="addStaff.php">Add New</a>
        </div>
        <table class="mainTable hideActions">
            <thead>
            <tr>
                <th>Staff ID</th>
                <th>Staff Name</th>
                <th>Gender</th>
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
                    echo "<td>" . $row['Staff_ID'] . "</td>";
                    echo "<td>" . $row['Staff_Name'] . "</td>";
                    echo "<td>" . $row['Gender'] . "</td>";
                    echo "<td>
                            <input type='text' id='contact_" . $row['Staff_ID'] . "' 
                                   name='contact_number' 
                                   value='" . $row['Contact_Number'] . "' 
                                   class='readonlyInput' 
                                   readonly>
                          </td>";
                    echo "<td>
                            <input type='text' id='address_" . $row['Staff_ID'] . "' 
                                   name='address' 
                                   value='" . $row['Address'] . "' 
                                   class='readonlyInput' 
                                   readonly>
                          </td>";
                    echo "<td>
                            <input type='hidden' name='staff_id' value='" . $row['Staff_ID'] . "'>
                            <button class='editTable' type='button' onclick='toggleEdit(" . $row['Staff_ID'] . ")'>Edit</button>
                            <button type='submit' class='saveInfo' name='modify' id='modify_" . $row['Staff_ID'] . "' disabled>Save</button>
                            <button type='submit' class='deleteInfo' name='delete' id='delete_" . $row['Staff_ID'] . "' disabled>Delete</button>
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
