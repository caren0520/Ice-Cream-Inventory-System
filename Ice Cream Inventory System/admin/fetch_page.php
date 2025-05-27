<?php
if (isset($_GET['page'])) {
    $page = $_GET['page'];

    $allowed_pages = ['dashboard', 'daily_production', 'flavor', 'production_logs', 'sales', 'sales_logs','clients'];

    if (in_array($page, $allowed_pages)) {
        include "$page.php"; 
    } else {
        echo "<p>Page not found!</p>";
    }
} else {
    echo "<p>No page selected.</p>";
}
?>
