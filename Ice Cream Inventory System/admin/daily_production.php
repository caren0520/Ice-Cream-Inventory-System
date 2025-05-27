<?php
require('../config.php');
session_start();
error_reporting(1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Production</title>
    <link rel="stylesheet" href="../assets/daily_production.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="main-container">
        <div class="container1">
            <h1>Daily Production</h1>
            <div id="message" style="text-align: center;"></div>
            <form id="productionForm" method="POST">
                <label for="flavor_id">Select Flavor:</label>
                <select id="flavor_id" name="flavor_id" class="search-bar1" required>
                </select><br><br>

                <label for="quantity_made">Quantity Made:</label>
                <input type="number" id="quantity_made" name="quantity_made" class="search-bar" required><br><br>

                <button type="submit" name="submit" class="add-item-button">Save</button>
                <div id="message"></div><br>
            </form>
        </div>
    
        <div class="container2">    
            <div class="inventory-table">
                <h2>Production Records</h2>
                <label for="flavorFilter">Filter by Flavor:</label>
                <select id="flavorFilter" class="search-bar">
                </select>
                
                <label for="dateFilter">Filter by Date:</label>
                <input type="date" id="dateFilter" class="search-bar"><br><br>
                <form id="pdfForm" action="pdf-request/generate_production_pdf.php" method="GET" target="_blank">
                    <label for="pdfDatePicker">Select Date for PDF:</label>
                    <input type="date" name="date" id="pdfDatePicker" class="search-bar" required>
                    <button type="submit" class="btn btn-primary">Download Production List (PDF)</button>
                </form>


                <table>
                        <thead>
                            <tr>
                                <th>Flavor</th>
                                <th>Quantity Made</th>
                                <th>Date & Time Produced</th>
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
    let productionData = [];
    let currentPage = 1;
    const rowsPerPage = 7;

    // Fetch flavors
    $.ajax({
        url: 'ajax-request/process_production.php',
        type: 'GET',
        data: { action: 'fetch_flavors' },
        dataType: 'json',
        success: function (data) {
            $('#flavor_id').html('<option disabled selected value="">-- Select flavor --</option>');
            $('#flavorFilter').html('<option value="">-- Filter by flavor --</option>');

            if (data.length > 0) {
                data.forEach(function (flavor) {
                    $('#flavor_id').append(`<option value="${flavor._id}">${flavor.name}</option>`);
                    $('#flavorFilter').append(`<option value="${flavor.name}">${flavor.name}</option>`);
                });
            } else {
                $('#flavor_id').append('<option disabled>No flavors found</option>');
            }
        }
    });

    // Form submission
    $('#productionForm').on('submit', function (e) {
        e.preventDefault();

        const formData = {
            action: 'insert_production',
            flavor_id: $('#flavor_id').val(),
            quantity_made: $('#quantity_made').val()
        };

        $.ajax({
            url: 'ajax-request/process_production.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#message').html(`<p style="color: green;">${response}</p>`);
                $('#productionForm')[0].reset();
                $('#flavor_id').val('');
                fetchProductionData();
            },
            error: function () {
                $('#message').html(`<p style="color: red;">Failed to insert data.</p>`);
            }
        });
    });

    function fetchProductionData() {
        const flavor = $('#flavorFilter').val();
        const date = $('#dateFilter').val();

        $.ajax({
            url: 'ajax-request/process_production.php',
            type: 'POST',
            data: {
                action: 'fetch_production',
                flavor: flavor,
                date: date
            },
            dataType: 'json',
            success: function (data) {
                productionData = data;
                currentPage = 1;
                displayPage(currentPage);
                renderPagination();
            }
        });
    }

    function displayPage(page) {
        const tableBody = $('#flavorTable');
        tableBody.empty();

        const startIndex = (page - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        const pageData = productionData.slice(startIndex, endIndex);

        if (pageData.length > 0) {
            pageData.forEach(function (row) {
                let timestamp = new Date(row.timestamp);
                timestamp.setHours(timestamp.getHours() + 8); // PH Time

                const months = [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ];

                const monthName = months[timestamp.getMonth()];
                const day = timestamp.getDate();
                const year = timestamp.getFullYear();

                let hours = timestamp.getHours();
                let minutes = timestamp.getMinutes();
                const ampm = hours >= 12 ? 'pm' : 'am';
                hours = hours % 12 || 12;
                minutes = minutes < 10 ? '0' + minutes : minutes;
                const formattedTime = `${hours}:${minutes}${ampm}`;

                const formattedDateTime = `${monthName} ${day}, ${year} ${formattedTime}`;

                tableBody.append(`
                    <tr>
                        <td>${row.flavor}</td>
                        <td>${row.quantity_made}</td>
                        <td>${formattedDateTime}</td>
                    </tr>
                `);
            });
        } else {
            tableBody.append('<tr><td colspan="3">No records found</td></tr>');
        }
    }

    function renderPagination() {
        const pagination = $('#pagination');
        pagination.empty();

        const pageCount = Math.ceil(productionData.length / rowsPerPage);

        if (pageCount <= 1) return;

        const prevBtn = `<a class="pagination-link" href="#" id="prevBtn">Previous</a>`;
        const nextBtn = `<a class="pagination-link" href="#" id="nextBtn">Next</a>`;

        pagination.append(prevBtn);
        pagination.append(`<span class="pagination-link" style="pointer-events: none;">Page ${currentPage} of ${pageCount}</span>`);
        pagination.append(nextBtn);

        $('#prevBtn').on('click', function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                displayPage(currentPage);
                renderPagination();
            }
        });

        $('#nextBtn').on('click', function (e) {
            e.preventDefault();
            if (currentPage < pageCount) {
                currentPage++;
                displayPage(currentPage);
                renderPagination();
            }
        });
    }

    // Initial data
    fetchProductionData();

    // Filters
    $('#flavorFilter, #dateFilter').on('change', fetchProductionData);
});
</script>



</body>
</html>
