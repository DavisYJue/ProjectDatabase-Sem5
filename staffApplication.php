<?php
include 'db_connect.php';

$sql = "SELECT * FROM verification";
$result = $conn->query($sql);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>New Staff Application</title>
    <link rel="stylesheet" href="css/staffApplication.css">
</head>
<body>
<div class="main">
    <div class="applicationPopUp">
        <table class="applicationTable">
            <thead>
            <tr>
                <th>Staff ID</th>
                <th>Staff Name</th>
                <th>Gender</th>
                <th>Profession</th>
                <th>Department</th>
                <th>Contact Number</th>
                <th>Address</th>
                <th>Username</th>
                <th>Password</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["Staff_ID"] . "</td>";
                    echo "<td>" . $row["Staff_Name"] . "</td>";
                    echo "<td>" . $row["Gender"] . "</td>";
                    echo "<td>" . $row["Profession"] . "</td>";
                    echo "<td>" . $row["Department"] . "</td>";
                    echo "<td>" . $row["Contact_Number"] . "</td>";
                    echo "<td>" . $row["Address"] . "</td>";
                    echo "<td>" . $row["Username"] . "</td>";
                    echo "<td>" . $row["Password"] . "</td>";
                    echo "<td>";
                    echo '<div class="permissionButton">';
                    echo '<a class="declineApplication" href="staffApplication.php?action=decline&id=' . $row["Staff_ID"] . '">Decline</a>';
                    echo '<a class="acceptApplication" href="staffApplication.php?action=accept&id=' . $row["Staff_ID"] . '">Accept</a>';
                    echo '</div>';
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No applications found.</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <a class="cancelApplication" href="./staffData.php">Cancel</a>
    </div>
</div>

<?php
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if ($action == 'accept') {
        // Fetch the data to be moved
        $select_sql = "SELECT * FROM verification WHERE Staff_ID = $id";
        $select_result = $conn->query($select_sql);

        if ($select_result->num_rows > 0) {
            $row = $select_result->fetch_assoc();

            $insert_staff_sql = "INSERT INTO staffInformation (Staff_Name, Gender, Profession, Department, Contact_Number, Address) 
                                     VALUES ('" . $row['Staff_Name'] . "', '" . $row['Gender'] . "', '" . $row['Profession'] . "', 
                                     '" . $row['Department'] . "', '" . $row['Contact_Number'] . "', '" . $row['Address'] . "')";

            if ($conn->query($insert_staff_sql) === TRUE) {
                $insert_account_sql = "INSERT INTO account (Username, Password, Role, Full_Name) 
                                           VALUES ('" . $row['Username'] . "', '" . $row['Password'] . "', 'staff', '" . $row['Staff_Name'] . "')";

                if ($conn->query($insert_account_sql) === TRUE) {
                    $delete_sql = "DELETE FROM verification WHERE Staff_ID = $id";
                    if ($conn->query($delete_sql) === TRUE) {
                        echo "<script>alert('Application Accepted'); window.location.href = 'staffApplication.php';</script>";
                    } else {
                        echo "<script>alert('Error deleting from verification table: " . $conn->error . "');</script>";
                    }
                } else {
                    echo "<script>alert('Error inserting into account table: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('Error inserting into staffInformation table: " . $conn->error . "');</script>";
            }
        }
    } elseif ($action == 'decline') {
        $delete_sql = "DELETE FROM verification WHERE Staff_ID = $id";
        if ($conn->query($delete_sql) === TRUE) {
            echo "<script>alert('Application Declined'); window.location.href = 'staffApplication.php';</script>";
        } else {
            echo "<script>alert('Error declining the application: " . $conn->error . "');</script>";
        }
    }
}

$conn->close();
?>
</body>
</html>
