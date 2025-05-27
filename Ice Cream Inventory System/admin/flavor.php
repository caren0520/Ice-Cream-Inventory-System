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
    <title>Manage Ice Cream</title>
    <link rel="stylesheet" href="../assets/flavor.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <div class="container1">
            <div class="add-flavor">
                <form id="flavorForm">
                    <h1>Add New Flavor</h1>
                    <label for="flavor_name">Flavor Name:</label>
                    <input type="text" id="flavor_name" name="flavor_name" class="search-bar" required><br>
                    <input type="submit" id="addFlavor" value="Add Flavor" class="add-item-button">
                </form>
            </div>
            <div id="message" style="margin-left: 20px"></div>
        </div>
            

        <div class="container2">
            <div class="inventory-table">
                <h2>Available Flavors</h2>
                <div class="search-container">
                    <input type="text" id="clientFilter" placeholder="Search flavor" class="search-bars">
                    <i class="fas fa-search"></i>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Flavor Name</th>
                            <th>Price</th>
                            <th>Action</th>
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
    let currentPage = 1;
    const itemsPerPage = 8;

    // ===== Initial Load =====
    loadFlavors();

    // ===== Add New Flavor =====
    $("#flavorForm").submit(function (e) {
        e.preventDefault();
        let flavorName = $("#flavor_name").val().trim();

        // Check for numbers in flavor name
        if (/\d/.test(flavorName)) {
            showMessage("Flavor name cannot contain numbers.", 'error');
            return;
        }

        if (flavorName === "") {
            showMessage("Please enter a flavor name.", 'error');
            return;
        }

        $.ajax({
            type: "POST",
            url: "ajax-request/process_flavor.php",
            data: {
                action: "addFlavor",
                flavor_name: flavorName
            },
            success: function (response) {
                let res = JSON.parse(response);
                if (res.status === "duplicate") {
                    showMessage("Flavor already exists.", 'error');
                } else {
                    showMessage("Flavor added successfully.", 'success');
                    $("#flavor_name").val("");  
                    loadFlavors(); 
                }
            }

        });
    });

    // ===== Load Flavors with Pagination and Filtering =====
    function loadFlavors(page = 1, filter = '') {
        $.ajax({
            type: "POST",
            url: "ajax-request/process_flavor.php",
            data: { action: "getFlavors", filter: filter },  // Pass filter to the server
            dataType: "json",
            success: function (data) {
                let flavorTable = $("#flavorTable");
                flavorTable.empty();

                // Pagination logic
                const start = (page - 1) * itemsPerPage;
                const paginatedItems = data.filter(flavor => flavor.name.toLowerCase().includes(filter.toLowerCase()))  // Apply the filter here
                    .slice(start, start + itemsPerPage);

                $.each(paginatedItems, function (index, flavor) {
                    flavorTable.append(`
                        <tr data-id="${flavor._id}">
                            <td><input type="text" class="edit-flavor-name" value="${flavor.name}" readonly></td>
                            <td><input type="number" class="edit-flavor-price" value="${flavor.price ?? ''}" placeholder="Enter Price"></td>
                            <td>
                                <button class="update-btn" data-id="${flavor._id}">Update Price</button>
                                <button class="delete-btn" data-id="${flavor._id}">Delete</button>
                            </td>
                        </tr>
                    `);
                });

                setupPagination(data.length, page, filter); // Pass filter to the pagination setup
            }
        });
    }

    // ===== Setup Pagination (Next & Previous Only) =====
    function setupPagination(totalItems, currentPage, filter) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const pagination = $("#pagination");
        pagination.empty();

        if (totalPages > 1) {
            if (currentPage > 1) {
                pagination.append(`<button class="page-btn" data-page="${currentPage - 1}">Previous</button>`);
            }

            if (currentPage < totalPages) {
                pagination.append(`<button class="page-btn" data-page="${currentPage + 1}">Next</button>`);
            }

            $(".page-btn").click(function () {
                const page = $(this).data("page");
                loadFlavors(page, filter);  // Pass the filter to reload flavors
            });
        }
    }

    // ===== Update Flavor (Name and Price) =====
    $(document).on("click", ".update-btn", function () {
        let flavorId = $(this).data("id");
        let newFlavorName = $(this).closest("tr").find(".edit-flavor-name").val().trim();
        let newFlavorPrice = parseFloat($(this).closest("tr").find(".edit-flavor-price").val().trim());

        // Check for numbers in flavor name
        if (/\d/.test(newFlavorName)) {
            showMessage("Flavor name cannot contain numbers.", 'error');
            return;
        }

        if (newFlavorName === "") {
            showMessage("Flavor name cannot be empty.", 'error');
            return;
        }

        if (isNaN(newFlavorPrice) || newFlavorPrice <= 0) {
            showMessage("Please enter a valid price.", 'error');
            return;
        }

        $.ajax({
            type: "POST",
            url: "ajax-request/process_flavor.php",
            data: {
                action: "updateFlavor",
                flavor_id: flavorId,
                flavor_name: newFlavorName,
                flavor_price: newFlavorPrice
            },
            success: function (response) {
                showMessage(response, 'success');
                loadFlavors(currentPage);
            }
        });
    });

    // ===== Delete Flavor =====
    $(document).on("click", ".delete-btn", function () {
        if (!confirm("Are you sure you want to delete this flavor?")) return;

        let flavorId = $(this).data("id");

        $.ajax({
            type: "POST",
            url: "ajax-request/process_flavor.php",
            data: {
                action: "deleteFlavor",
                flavor_id: flavorId
            },
            success: function (response) {
                showMessage(response, 'success');
                loadFlavors(currentPage);
            }
        });
    });

    // ===== Show Message with Styles =====
    function showMessage(message, type) {
        $("#message")
            .html(message)
            .css({
                "color": type === 'success' ? "green" : "red",
                "padding-top": "10px",
                "padding-left": "80px",
            })
            .show();
        
        setTimeout(() => $("#message").fadeOut(), 2000);
    }

    // ===== Handle Search =====
    $("#clientFilter").keyup(function () {
        const filter = $(this).val();
        loadFlavors(currentPage, filter);  // Reload with filter applied
    });
});

</script>


</body>
</html>