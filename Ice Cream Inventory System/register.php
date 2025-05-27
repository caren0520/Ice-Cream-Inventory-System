<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Secure password

    // Check if username already exists
    $existingUser = $usersCollection->findOne(["usn" => $username]);

    if ($existingUser) {
        echo json_encode(["status" => "error", "message" => "Username already taken!"]);
        exit;
    }

    // Insert new user
    $insertResult = $usersCollection->insertOne([
        "usn" => $username,
        "password" => $hashedPassword,
        "usertype_id" => new MongoDB\BSON\ObjectId() // Assign a user type
    ]);

    if ($insertResult->getInsertedCount() > 0) {
        echo json_encode(["status" => "success", "message" => "Registration successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed!"]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="register-form">
        <h1>Register</h1>
        <form id="registerForm">
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <div id="register-error" class="error-container"></div>
            <button type="submit">Register</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $("#registerForm").submit(function(e) {
                e.preventDefault(); 

                let username = $("#username").val();
                let password = $("#password").val();

                $.ajax({
                    type: "POST",
                    url: "register.php",
                    data: { username: username, password: password },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            alert(response.message);
                            window.location.href = "index.html"; // Redirect to login page
                        } else {
                            $("#register-error").text(response.message).show();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>

