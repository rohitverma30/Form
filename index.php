<?php
$insert = false; // Flag to indicate if a record was inserted
$update = false; // Flag to indicate if a record was updated
$delete = false; // Flag to indicate if a record was deleted
$error = false; // Flag to indicate if there was a validation error
$errorMsg = ""; // Variable to store error messages
$server = "localhost"; // Database server
$username = "root"; // Database username
$password = ""; // Database password
$database = "manalitrip"; // Database name

// Create a database connection
$con = mysqli_connect($server, $username, $password, $database);

// Check for connection success
if (!$con) {
    die("Connection to this database failed due to" . mysqli_connect_error());
}

// Initialize variables to store form data
$name = "";
$gender = "";
$age = "";
$email = "";
$phone = "";
$desc = "";

// Handle form submission for insert and update
if (isset($_POST['name'])) {
    // Collect post variables
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $desc = $_POST['desc'];

    // Validate inputs
    if (empty($name) || empty($gender) || empty($age) || empty($email) || empty($phone)) {
        $error = true;
        $errorMsg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $errorMsg = "Invalid email format.";
    } elseif (!is_numeric($age) || $age <= 0) {
        $error = true;
        $errorMsg = "Invalid age. Age must be a positive number.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = true;
        $errorMsg = "Invalid phone number. Phone number must be 10 digits.";
    }

    // Proceed if no validation errors
    if (!$error) {
        // Check if the email already exists in the database for updating
        $checkSql = "SELECT * FROM `trip` WHERE `Email`='$email'";
        $result = $con->query($checkSql);

        if ($result->num_rows > 0) {
            // Perform update if email exists
            $updateSql = "UPDATE `trip` SET `Name`='$name', `Age`='$age', `Gender`='$gender', `Phone`='$phone', `Other`='$desc' WHERE `Email`='$email'";

            if ($con->query($updateSql) === true) {
                $update = true; // Set update flag
            } else {
                echo "ERROR: $updateSql <br> $con->error";
            }
        } else {
            // Perform insert if email does not exist
            $insertSql = "INSERT INTO `trip` (`Name`, `Age`, `Gender`, `Email`, `Phone`, `Other`, `Date`) VALUES ('$name', '$age', '$gender', '$email', '$phone', '$desc', current_timestamp());";
            
            if ($con->query($insertSql) === true) {
                $insert = true; // Set insert flag
            } else {
                echo "ERROR: $insertSql <br> $con->error";
            }
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $email = $_GET['delete'];
    $sql = "DELETE FROM `trip` WHERE `Email`='$email'";
    if ($con->query($sql) === true) {
        $delete = true; // Set delete flag
    } else {
        echo "ERROR: $sql <br> $con->error";
    }
}

// Fetch all records to display
$result = $con->query("SELECT * FROM `trip`");

// Fetch a single record for editing
if (isset($_GET['edit'])) {
    $email = $_GET['edit'];
    $record = $con->query("SELECT * FROM `trip` WHERE `Email`='$email'");
    $data = $record->fetch_assoc();
    // Assign fetched values to variables
    $name = $data['Name'];
    $age = $data['Age'];
    $gender = $data['Gender'];
    $phone = $data['Phone'];
    $desc = $data['Other'];
}

// Close the database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Travel Form</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto|Sriracha&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <img class="bg" src="bg.jpg" alt="IET_DSMNRU">
    <div class="container">
        <h1>Welcome to IET-DSMNRU Manali Trip form</h1>
        <p>Enter your details and submit this form to confirm your participation in the trip</p>
        <?php
        if ($insert) {
            echo "<p class='submitMsg'>Thanks for submitting your form. We are happy to see you joining us for the Manali Trip</p>";
        }
        if ($update) {
            echo "<p class='submitMsg'>Record has been updated successfully.</p>";
        }
        if ($delete) {
            echo "<p class='submitMsg'>Record has been deleted successfully.</p>";
        }
        if ($error) {
            echo "<p class='errorMsg'>$errorMsg</p>";
        }
        ?>
        <form action="index.php" method="post">
            <input type="text" name="name" id="name" placeholder="Enter your name" value="<?php echo htmlspecialchars($name); ?>">
            <input type="text" name="age" id="age" placeholder="Enter your Age" value="<?php echo htmlspecialchars($age); ?>">
            <input type="text" name="gender" id="gender" placeholder="Enter your gender" value="<?php echo htmlspecialchars($gender); ?>">
            <input type="email" name="email" id="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" <?php if(isset($_GET['edit'])) echo "readonly"; ?>>
            <input type="phone" name="phone" id="phone" placeholder="Enter your phone" value="<?php echo htmlspecialchars($phone); ?>">
            <textarea name="desc" id="desc" cols="30" rows="10" placeholder="Enter any other information here"><?php echo htmlspecialchars($desc); ?></textarea>
            <?php if(isset($_GET['edit'])): ?>
                <button class="btn" name="update">Update</button>
            <?php else: ?>
                <button class="btn">Submit</button> 
            <?php endif; ?>
        </form>
        <table>
            <tr>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Other</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['Name']; ?></td>
                <td><?php echo $row['Age']; ?></td>
                <td><?php echo $row['Gender']; ?></td>
                <td><?php echo $row['Email']; ?></td>
                <td><?php echo $row['Phone']; ?></td>
                <td><?php echo $row['Other']; ?></td>
                <td>
                    <a href="index.php?edit=<?php echo $row['Email']; ?>">Edit</a>
                    <a href="index.php?delete=<?php echo $row['Email']; ?>">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <script>
        // JavaScript to populate the form with existing data for editing
        const urlParams = new URLSearchParams(window.location.search);
        const editEmail = urlParams.get('edit');
        if (editEmail) {
            fetch(`index.php?edit=${editEmail}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('name').value = data.Name;
                document.getElementById('age').value = data.Age;
                document.getElementById('gender').value = data.Gender;
                document.getElementById('email').value = data.Email;
                document.getElementById('phone').value = data.Phone;
                document.getElementById('desc').value = data.Other;
            });
        }
    </script>
</body>
</html>
