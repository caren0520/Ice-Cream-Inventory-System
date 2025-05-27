<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retractable Sidebar</title>
    <link rel="stylesheet" href="../assets/nav.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .content {
            transition: margin-left 0.3s ease-in-out;
        }
        
        .sidebar.expanded + .content {
            margin-left: 250px; 
        }

        .sidebar:not(.expanded) + .content {
            margin-left: 60px; 
        }
        .icon {
            width: 20px;
            display: inline-block;
            text-align: center;
            margin-right: 10px;
            vertical-align: middle;
        }
        svg {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php function navigator() { ?>
    <div class="sidebar" id="sidebar">
        <a href="#" class="nav-item nav-link" data-page="dashboard">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 3.293l6 6V15h-4v-4H6v4H2V9.293l6-6zM7 13h2v-4h3v4h1V9.707l-5-5-5 5V13h1v-4h3v4z"/>
                </svg>
            </span>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <div class="dropdown">
            <div class="dropdown-toggle">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M6.5 0a.5.5 0 0 1 .5.5V1h2v-.5a.5.5 0 0 1 1 0V1h.5A1.5 1.5 0 0 1 12 2.5v11A1.5 1.5 0 0 1 10.5 15h-5A1.5 1.5 0 0 1 4 13.5v-11A1.5 1.5 0 0 1 5.5 1H6V.5a.5.5 0 0 1 .5-.5zM5 3v10.5a.5.5 0 0 0 .5.5h5a.5.5 0 0 0 .5-.5V3H5z"/>
                    </svg>
                </span>
                <span class="nav-text">Manage Product & Production</span>
            </div>
            <div class="dropdown-content">
                <a href="#" class="nav-item nav-link" data-page="flavor">Add Flavor & Price</a>
                <a href="#" class="nav-item nav-link" data-page="daily_production">Add Daily Production</a>
            </div>
        </div>


        <div class="dropdown">
            <div class="dropdown-toggle">
                <span class="icon">
                    <!-- Shopping cart icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 1a1 1 0 0 1 1-1h1.18a.5.5 0 0 1 .485.379L3.89 4H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.49-.402L1.61 2H1a1 1 0 0 1-1-1zm3.14 4l1.25 5.998h8.223l1.285-6H3.14zM5.5 13a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>
                    </svg>
                </span>
                <span class="nav-text">Manage Sales</span>
            </div>
            <div class="dropdown-content">
                <a href="#" class="nav-item nav-link" data-page="sales">Add Sales</a>
                <a href="#" class="nav-item nav-link" data-page="sales_logs">Sales Logs</a>
            </div>
        </div>


        <a href="#" class="nav-item nav-link" data-page="clients">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                </svg>
            </span>
            <span class="nav-text">List of Clients</span>
        </a>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            let sidebar = $("#sidebar");
            let content = $("#content");

            if (localStorage.getItem("sidebarExpanded") === "true") {
                sidebar.addClass('expanded');
                content.css("margin-left", "250px");
            } else {
                sidebar.removeClass('expanded');
                content.css("margin-left", "60px");
            }

            function toggleSidebar() {
                sidebar.toggleClass('expanded');
                let isExpanded = sidebar.hasClass('expanded');
                localStorage.setItem("sidebarExpanded", isExpanded);
                content.css("margin-left", isExpanded ? "250px" : "60px");
            }

            $(".dropdown-toggle").click(function () {
                if (sidebar.hasClass("expanded")) { 
                    $(".dropdown-content").not($(this).next()).slideUp(200);
                    $(this).next(".dropdown-content").slideToggle(200);
                }
            });

            $(".nav-link").click(function (e) {
                e.preventDefault();
                let page = $(this).data("page");

                $.ajax({
                    url: "fetch_page.php",
                    type: "GET",
                    data: { page: page },
                    success: function (response) {
                        $("#content").html(response);
                        $(".dropdown-content").slideUp(200); 
                        sidebar.removeClass('expanded');
                        localStorage.setItem("sidebarExpanded", "false");
                        content.css("margin-left", "60px");
                    },
                    error: function () {
                        $("#content").html("<p>Error loading page.</p>");
                    }
                });
            });
        });
    </script>
    <?php } ?>
</body>
</html>
