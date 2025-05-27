<?php
    session_start();  // Start session at the top
    include '../config.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajaxLogin'])) {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        // Find user in the MongoDB collection
        $user = $usersCollection->findOne(["usn" => $username]);

        if ($user && password_verify($password, $user["password"])) {
            // Set session variables
            $_SESSION["user_id"] = (string) $user["_id"];
            $_SESSION["username"] = $user["usn"];
            $_SESSION["usertype_id"] = (string) $user["usertype_id"];

            // Send success response
            echo json_encode(["status" => "success", "redirect" => "admin/index.php"]);
        } else {
            // Invalid login
            echo json_encode(["status" => "error", "message" => "Invalid username or password!"]);
        }
    }
?>
