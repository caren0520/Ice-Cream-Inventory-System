<?php
    require('../config.php');
    session_start();
    if (!isset($_SESSION["user_id"])) {
        header("Location: ../index.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Log</title>
    <link rel="stylesheet" href="../assets/sales_log.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="main-container">
    <div class="container2">
        <div class="inventory-table">
            <h2>List of Sales</h2>
            <div class="search-container">
                <input type="text" id="clientFilter" placeholder="Search clients..." class="search-bars">
                <i class="fas fa-search"></i>
                <label for="dateFilter">Filter by Date:</label>
                <input type="date" id="dateFilter" class="search-bar"><br><br>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Flavor</th>
                        <th>Total Price</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="flavorTable"></tbody>
            </table>
            <div id="pagination" class="pagination"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let allSales = [];

    function fetchSales() {
        $.ajax({
            url: 'ajax-request/process_sales_log.php',
            method: 'POST',
            data: { action: 'getSalesData' },
            dataType: 'json',
            success: function (response) {
                allSales = response.sales;
                renderSales(allSales);
            },
            error: function () {
                alert("Failed to load sales data.");
            }
        });
    }

    function renderSales(sales) {
    const tableBody = $('#flavorTable');
    tableBody.empty();

    if (sales.length === 0) {
        tableBody.append('<tr><td colspan="5">No records found.</td></tr>');
        return;
    }

    sales.forEach(sale => {
        const row = `
            <tr>
                <td>${sale.client_name}</td>
                <td>${sale.flavor_string}</td>
                <td>${parseFloat(sale.total_price).toFixed(2)}</td>
                <td>${sale.timestamp}</td>
            </tr>
        `;
        tableBody.append(row);
    });
}

    function filterSales() {
        const clientFilter = $('#clientFilter').val().toLowerCase();
        const dateFilter = $('#dateFilter').val();

        const filtered = allSales.filter(sale => {
            const matchesClient = sale.client_name.toLowerCase().includes(clientFilter);
            const matchesDate = dateFilter ? sale.timestamp.startsWith(dateFilter) : true;
            return matchesClient && matchesDate;
        });

        renderSales(filtered);
    }

    $('#clientFilter').on('input', filterSales);
    $('#dateFilter').on('change', filterSales);

    fetchSales();
});
</script>
</body>
</html>
