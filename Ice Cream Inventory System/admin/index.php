<?php  
    require('../config.php');
    session_start();
    if (!isset($_SESSION["user_id"])) {
        header("Location: ../index.php");
        exit;
    }
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="design/index.css">
	<title>Inventory Management</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-RXf+QSDCUQs6B6Lp7z0K8AD5Or6AiDIDrK6YF35R1F+8+yUnZl+Mx4BKVlvu9Nk4z3+zzgG6Ld18uKx7N0X5xg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
	<?php
		error_reporting(1);
		include('../include/topbar.php');
		include('../include/sidebar.php');
		navigator(); 
	?>
	<div class="content" id="content">
		<?php include('dashboard.php'); ?> 
	</div>

	<script>
    $(document).ready(function () {
        let savedPage = localStorage.getItem("lastPage"); 

        if (savedPage) {
            loadPage(savedPage);
        }

        $(".nav-link").click(function (e) {
            e.preventDefault();
            
            let page = $(this).data("page");
            localStorage.setItem("lastPage", page); // Save the last selected page
            loadPage(page);
        });

        function loadPage(page) {
            $.ajax({
                url: "fetch_page.php",
                type: "GET",
                data: { page: page },
                success: function (response) {
                    $("#content").html(response);
                },
                error: function () {
                    $("#content").html("<p>Error loading page.</p>");
                }
            });
        }
    });
</script>

</body>
</html>
