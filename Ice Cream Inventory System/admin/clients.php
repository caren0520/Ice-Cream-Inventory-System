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
    <title>Document</title>
    <link rel="stylesheet" href="../assets/client.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<style>
    
</style>
<body>
    <div class="main-container">
        <div class="container1">
            <div class="add-client">
            <form id="clientForm">
                <h1>Add New Client</h1>
                <label>Firstname:</label>
                <input type="text" id="cfname" name="cfname" class="search-bar" maxlength="8"><br><br>

                <label>Lastname:</label>
                <input type="text" id="clname" name="clname" class="search-bar" maxlength="8"><br><br>

                <label>Age:</label>
                <input type="number" id="cage" name="cage" class="search-bar" style="margin-left: 43px;" min="18" max="100"><br><br>

                <label>Contact #:</label>
                <input type="text" id="ccontact" name="ccontact" class="search-bar" placeholder="Ex. 0921*** (11 Digits)" maxlength="11" pattern="^\d{11}$" title="Contact number should be 11 digits."><br><br>

                <label>Address:</label>
                <input type="text" id="caddress" name="caddress" class="search-bar" style="margin-left: 10px;" placeholder="Ex. Brgy 69 (15 Characters)" maxlength="15"><br><br>
                
                <input type="submit" id="addClient" value="Add Client" class="add-item-button">
            </form>
                <div id="message"></div><br>
            </div>
        </div>

        <div class="container2">
            <div class="inventory-table">
                <h2>List of Clients</h2>
                <div class="search-container">
                    <input type="text" id="clientFilter" placeholder="Search clients..." class="search-bars">
                    <i class="fas fa-search"></i>
                    <i id="sortIcon" class="fas fa-sort"></i> 
                    <i id="sortIcon" class="fas trash"></i> 
                    <i id="deleteSelected" class="fas fa-trash" style="cursor:pointer; color:red; margin-left: 10px; margin-right: 315px;" title="Delete Selected"></i>
                    <button id="downloadPdfBtn" class="btn btn-primary">Download Client List (PDF)</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Firstname</th>
                            <th>Lastname</th>
                            <th>Age</th>
                            <th>Contact #</th>
                            <th>Address</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody id="flavorTable"></tbody>
                </table>
                <div id="pagination" class="pagination"></div>
            </div>
        </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    let currentPage = 1;
    let sortDirection = 'asc';
    let sortColumn = 'cfname';

    function fetchClients(page = 1, filter = "", sort = "cfname", direction = "asc") {
        $.ajax({
            url: 'ajax-request/process_client.php',
            type: 'POST',
            data: { action: 'fetch', page: page, filter: filter, sort: sort, direction: direction },
            success: function (response) {
                let data = JSON.parse(response);
                let clients = data.clients;
                let hasNext = data.hasNext;
                let hasPrev = data.hasPrev;
                let rows = "";

                clients.forEach(client => {
                    rows += `<tr>
                        <td><input type="checkbox" class="select-client" value="${client.id}"></td>
                        <td>${client.cfname}</td>
                        <td>${client.clname}</td>
                        <td>${client.cage}</td>
                        <td>${client.ccontact}</td>
                        <td>${client.caddress}</td>
                        <td>${client.date_added}</td>
                    </tr>`;
                });

                $("#flavorTable").html(rows);

                let pagination = "";
                if (hasPrev) {
                    pagination += "<a href='#' class='pagination-link' data-page='" + (page - 1) + "'>&laquo; Prev</a> ";
                }
                if (hasNext) {
                    pagination += "<a href='#' class='pagination-link' data-page='" + (page + 1) + "'>Next &raquo;</a>";
                }
                $("#pagination").html(pagination);
            }
        });
    }

    fetchClients();

    $("#clientForm").submit(function (e) {
        e.preventDefault();

        let cfname = $("#cfname").val();
        let clname = $("#clname").val();
        let cage = $("#cage").val();
        let ccontact = $("#ccontact").val();
        let caddress = $("#caddress").val();

        if (!cfname || !clname || !cage || !ccontact || !caddress) {
            $("#message").html("Please fill up all fields.")
                .css({ "color": "red", "padding-top": "10px", "padding-left": "80px" })
                .show();
            setTimeout(() => $("#message").fadeOut(), 2000);
            return;
        }

        let formData = {
            cfname, clname, cage, ccontact, caddress, action: 'add'
        };

        $.ajax({
            url: 'ajax-request/process_client.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                let result = JSON.parse(response);
                $("#message").html(result.message)
                    .css({ "color": result.status ? "green" : "red", "padding-top": "10px", "padding-left": "80px" })
                    .show();
                setTimeout(() => $("#message").fadeOut(), 2000);

                if (result.status) {
                    $("#clientForm")[0].reset();
                    fetchClients();
                }
            }
        });
    });

    $(document).on("click", ".pagination-link", function (e) {
        e.preventDefault();
        currentPage = $(this).data("page");
        fetchClients(currentPage, $("#clientFilter").val(), sortColumn, sortDirection);
    });

    $("#clientFilter").on("input", function () {
        currentPage = 1;
        fetchClients(currentPage, $(this).val(), sortColumn, sortDirection);
    });

    // Sort column with proper ID
    $("#sortIcon").on("click", function () {
        sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';
        $(this)
            .toggleClass('fa-sort-up fa-sort-down');
        fetchClients(currentPage, $("#clientFilter").val(), sortColumn, sortDirection);
    });

    // Delete selected clients
    $("#deleteSelected").on("click", function () {
        let selectedIds = $(".select-client:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert("Please select at least one client.");
            return;
        }

        if (!confirm("Are you sure you want to delete the selected client(s)?")) return;

        $.ajax({
            url: 'ajax-request/process_client.php',
            type: 'POST',
            data: { action: 'delete_multiple', ids: selectedIds },
            success: function (response) {
                let result = JSON.parse(response);
                alert(result.message);
                fetchClients(currentPage, $("#clientFilter").val(), sortColumn, sortDirection);
            }
        });
    });

    $('#downloadPdfBtn').on('click', function () {
        window.open('pdf-request/generate_client_pdf.php', '_blank');
    });
});
</script>

</body>
</html>