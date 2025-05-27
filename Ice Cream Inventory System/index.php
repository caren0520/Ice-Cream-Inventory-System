<?php
    include 'config.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ice Cream Inventory</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="login-form">
        <img src="assets/img/logo.png" class="form-logo">
        <h1>Inventory</h1>
        <form id="loginForm">
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <div id="error-container" class="error-container"></div>
            <button type="submit">Login</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $("#loginForm").submit(function(e) {
                e.preventDefault(); 

                let username = $("#username").val();
                let password = $("#password").val();

                $.ajax({
                    type: "POST",
                    url: "ajax/ajax_login.php",
                    data: {
                        ajaxLogin: true,
                        username: username,
                        password: password
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            window.location.href = response.redirect; 
                        } else {
                            $("#error-container").text(response.message).show().css("animation", "fadeIn 0.3s ease-in-out");
                            
                            setTimeout(function() {
                                $("#error-container").css("animation", "fadeOut 0.3s ease-in-out");
                                setTimeout(() => $("#error-container").hide(), 300); 
                            }, 2000);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>


