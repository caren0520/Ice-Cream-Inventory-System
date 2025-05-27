
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="../assets/nav.css">
</head>
    <div class="navbar">
        <div class="logo">
            <span class="menu-icon" onclick="toggleSidebar()">☰</span>
            Ice Cream Inventory
        </div>

        <div class="profile">
            <a style="color: white;" href="../logout.php">Log out</a>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            
            // Toggle expanded class
            sidebar.classList.toggle("expanded");

            // Close all open dropdowns when collapsing the sidebar
            if (!sidebar.classList.contains("expanded")) {
                document.querySelectorAll(".dropdown-content").forEach(function (dropdown) {
                    dropdown.style.display = "none";
                });
            }
        }
    </script>

    <style>
    .navbar {
        background-color:rgb(9, 117, 122);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        position: fixed;
        width: 100%;
        top: 0;
        left: 0;
        z-index: 1000;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }

    .logo {
        font-size: 22px;
        font-weight: bold;
        display: flex;
        align-items: center;
    }

    .menu-icon {
        font-size: 26px;
        cursor: pointer;
        margin-right: 15px;
    }

    .profile a {
        text-decoration: none;
        font-size: 16px;
        background-color: #E74C3C; /* Red button */
        padding: 8px 12px;
        margin-right: 60px;
        border-radius: 5px;
        transition: 0.3s;
    }

    .profile a:hover {
        background-color: #C0392B; /* Darker red */
    }
    </style>