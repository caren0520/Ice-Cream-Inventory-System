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
    <link rel="stylesheet" href="../assets/sales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        .qty-input {
            padding: 5px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 60px;
        }
        table {
            width: 100%;
        }
        th, td {
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="container1">
            <h1>Add Sale</h1>
            <form id="saleForm" method="POST">
                <label for="client_id">Select Client:</label>
                <select id="client_id" name="client_id" class="search-bar1" required>
                    <option value="" disabled selected>Select a client</option>
                </select><br><br>

                <label for="flavor_id">Select Flavor:</label>
                <select id="flavor_id" class="search-bar1">
                    <option value="" disabled selected>Select flavor</option>
                </select>
                <button type="button" id="addFlavorBtn">Add Flavor</button><br><br>

                <label>Selected Flavors:</label>
                <table id="selectedFlavorsList">
                    <thead>
                        <tr>
                            <th>Flavor</th>
                            <th>Quantity</th>
                            <th>Remove</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table><br>

                <button type="submit" name="submit" class="add-item-button">Add</button>
                <div id="message"></div><br>
            </form>
        </div>

        <div class="container2">
            <div class="inventory-table">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Client Name</th>
                            <th>Flavors</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="flavorTable"></tbody>
                </table>
                <div id="pagination" class="pagination"></div>
                <div>
                    <button id="clearLocalStorageBtn" style="background-color: red; color: white;">Clear All Data</button>
                    <button id="addIceSaltBtn" style="background-color: blue; color: white;">Add Ice & Salt Price</button>
                    <button id="saveSalesBtn" style="background-color: green; color: white;">Save</button>
                </div>
            </div>
        </div>

<script>
$(document).ready(function () {
    let selectedFlavors = [];

    function saveTableToLocalStorage() {
        const tableData = [];
        $('#flavorTable tr').each(function () {
            const tds = $(this).find('td');
            if (tds.length > 0) {
                tableData.push({
                    clientName: tds.eq(1).text(),
                    flavors: tds.eq(2).text(),
                    quantity: tds.eq(3).text(),
                    price: tds.eq(4).text()
                });
            }
        });
        localStorage.setItem('salesTable', JSON.stringify(tableData));
    }

    function loadTableFromLocalStorage() {
        const tableData = JSON.parse(localStorage.getItem('salesTable')) || [];
        tableData.forEach(row => {
            $('#flavorTable').append(`
                <tr>
                    <td><i class="fas fa-trash-alt delete-row" style="cursor:pointer;color:red;"></i></td>
                    <td>${row.clientName}</td>
                    <td>${row.flavors}</td>
                    <td>${row.quantity}</td>
                    <td>${row.price}</td>
                </tr>
            `);
        });
    }

    function clearTableStorage() {
        localStorage.removeItem('salesTable');
        $('#flavorTable').empty();
    }

    $.ajax({
        url: 'ajax-request/process_sales.php',
        type: 'POST',
        data: { action: 'getDropdownData' },
        dataType: 'json',
        success: function (response) {
            if (response.clients && response.flavors) {
                response.clients.forEach(client => {
                    $('#client_id').append(`<option value="${client._id}">${client.name}</option>`);
                });

                response.flavors.forEach(flavor => {
                    $('#flavor_id').append(`<option value="${flavor._id}" data-price="${flavor.price}">${flavor.name}</option>`);
                });

                $('#client_id').select2({ placeholder: "Select a client", allowClear: true });
                $('#flavor_id').select2({ placeholder: "Select flavor", allowClear: true });
            }
        }
    });

    loadTableFromLocalStorage();

    $('#addFlavorBtn').click(function () {
        const flavorId = $('#flavor_id').val();
        const flavorName = $('#flavor_id option:selected').text();
        const flavorPrice = parseFloat($('#flavor_id option:selected').data('price'));

        if (!flavorId) {
            $('#message').text("Please select a flavor.").css("color", "red");
            return;
        }

        if (selectedFlavors.some(f => f.id === flavorId)) {
            $('#message').text("Flavor already added.").css("color", "orange");
            return;
        }

        selectedFlavors.push({ id: flavorId, name: flavorName, qty: 1, price: flavorPrice });

        $('#selectedFlavorsList tbody').append(`
            <tr data-id="${flavorId}">
                <td>${flavorName}</td>
                <td><input type="number" class="qty-input" value="1" min="1" /></td>
                <td><span class="remove-flavor" style="cursor:pointer;color:red;">&times;</span></td>
                <td class="flavor-price">${flavorPrice}</td>
            </tr>
        `);

        $('#flavor_id').val(null).trigger('change');
        $('#message').text("");
    });

    $('#selectedFlavorsList').on('input', '.qty-input', function () {
        const flavorId = $(this).closest('tr').data('id');
        const qty = parseInt($(this).val()) || 1;
        selectedFlavors = selectedFlavors.map(f => f.id === flavorId ? { ...f, qty } : f);
        const flavor = selectedFlavors.find(f => f.id === flavorId);
        const price = flavor.price * qty;
        $(this).closest('tr').find('.flavor-price').text(price.toFixed(2));
    });

    $('#selectedFlavorsList').on('click', '.remove-flavor', function () {
        const flavorId = $(this).closest('tr').data('id');
        selectedFlavors = selectedFlavors.filter(f => f.id !== flavorId);
        $(this).closest('tr').remove();
    });

    $('#saleForm').on('submit', function (e) {
        e.preventDefault();
        const clientId = $('#client_id').val();
        const clientName = $('#client_id option:selected').text();

        if (!clientId || selectedFlavors.length === 0) {
            $('#message').text("Please select a client and at least one flavor.").css("color", "red");
            return;
        }

        $.ajax({
            url: 'ajax-request/process_sales.php',
            type: 'POST',
            data: {
                action: 'validateStock',
                flavors: selectedFlavors
            },
            dataType: 'json',
            success: function (response) {
                if (!response.valid) {
                    $('#message').text(response.message).css("color", "red");
                    return;
                }

                const flavorText = selectedFlavors.map(f => `${f.name} (${f.qty})`).join(', ');
                const totalQuantity = selectedFlavors.reduce((sum, f) => sum + f.qty, 0);
                const totalPrice = selectedFlavors.reduce((sum, f) => sum + (f.qty * f.price), 0).toFixed(2);

                $('#flavorTable').append(`
                    <tr>
                        <td><i class="fas fa-trash-alt delete-row" style="cursor:pointer;color:red;"></i></td>
                        <td>${clientName}</td>
                        <td>${flavorText}</td>
                        <td>${totalQuantity}</td>
                        <td>${totalPrice}</td>
                    </tr>
                `);

                saveTableToLocalStorage();
                selectedFlavors.length = 0;
                $('#selectedFlavorsList tbody').empty();
                $('#client_id').val(null).trigger('change');
                $('#flavor_id').val(null).trigger('change');
                $('#message').text("Sale added locally.").css("color", "green");
            },
            error: function () {
                $('#message').text("Error checking stock.").css("color", "red");
            }
        });
    });

    $('#flavorTable').on('click', '.delete-row', function () {
        $(this).closest('tr').remove();
        saveTableToLocalStorage();
    });

    $('#clearLocalStorageBtn').click(clearTableStorage);

    $('#addIceSaltBtn').click(function () {
        $('#flavorTable tr').each(function () {
            const row = $(this);
            if (row.hasClass('ice-salt-added')) return;

            const qty = parseInt(row.find('td').eq(3).text()) || 0;
            const basePrice = parseFloat(row.find('td').eq(4).text()) || 0;
            const iceSaltCost = (2 + 5) * qty;
            const newTotal = (basePrice + iceSaltCost).toFixed(2);

            row.find('td').eq(4).text(newTotal);
            row.addClass('ice-salt-added');
        });

        saveTableToLocalStorage();
        $('#message').text("Ice and salt cost added.").css("color", "blue");
    });

    $('#saveSalesBtn').click(function () {
        const salesData = [];

        $('#flavorTable tr').each(function () {
            const row = $(this);
            const clientName = row.find('td').eq(1).text();
            const flavorText = row.find('td').eq(2).text();
            const quantity = parseInt(row.find('td').eq(3).text()) || 0;
            const totalPrice = parseFloat(row.find('td').eq(4).text()) || 0;

            const flavorPairs = flavorText.split(',').map(f => {
                const match = f.trim().match(/^(.+)\s+\((\d+)\)$/);
                return match ? { name: match[1], qty: parseInt(match[2]) } : null;
            }).filter(Boolean);

            salesData.push({ clientName, flavorPairs, quantity, totalPrice });
        });

        $.ajax({
            url: 'ajax-request/process_sales.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'saveSales', sales: salesData }),
            success: function (response) {
                const res = JSON.parse(response);
                $('#message').text(res.message).css("color", res.success ? "green" : "red");
                if (res.success) {
                    $('#flavorTable').empty();
                    localStorage.removeItem('salesTable');
                }
            },
            error: function () {
                $('#message').text("Failed to save sales.").css("color", "red");
            }
        });
    });
});
</script>


    </div>
</body>
</html>
