<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        */* Global Reset */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }

            /* Body Layout */
            body {
                display: flex;
                height: 100vh;
            }

            /* Sidebar */
            .sidebar {
                width: 70px;
                background: #6a0dad;
                padding: 20px 0;
                display: flex;
                flex-direction: column;
                gap: 20px;
                align-items: center;
                color: white;
            }
            .sidebar i {
                font-size: 24px;
                cursor: pointer;
            }

            /* Main Content */
            .main {
                flex: 1;
                padding: 20px;
                background: #f4f4f4;
            }

            /* Header */
            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            .header-right {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            /* Buttons */
            button {
                padding: 8px 16px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .btn-purple {
                background: #8e44ad;
                color: white;
            }
            .btn-light {
                background: #dcdcdc;
                color: #333;
            }

            /* Search Bar */
            .search-bar {
                display: flex;
                align-items: center;
                gap: 5px;
                margin-bottom: 20px;
            }
            .search-bar input {
                flex: 1;
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }

            /* Table Container */
            .table-container {
                background: white;
                border-radius: 10px;
                padding: 20px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            /* Table */
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            th, td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }

            /* Status Labels */
            .status {
                padding: 5px 10px;
                border-radius: 12px;
                color: white;
                display: inline-block;
            }
            .completed {
                background: #2ecc71;
            }
            .pending {
                background: #f1c40f;
                color: black;
            }
    </style>
</head>
<body>
    <div class="sidebar">
        <i class="fas fa-bars"></i>
        <i class="fas fa-tachometer-alt"></i>
        <i class="fas fa-box"></i>
        <i class="fas fa-shopping-cart"></i>
        <i class="fas fa-users"></i>
    </div>
    <div class="main">
        <div class="header">
            <h1>Orders</h1>
            <div class="header-right">
                <button class="btn-light">Export to Excel</button>
                <button class="btn-light">Import Orders</button>
                <button class="btn-purple">+ New Orders</button>
            </div>
        </div>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search order ID">
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Sales Channel</th>
                        <th>Destination</th>
                        <th>Items</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>#7676</td>
                        <td>06/30/2022</td>
                        <td>Ramesh Chaudhary</td>
                        <td>Store name</td>
                        <td>Lalitpur</td>
                        <td>3</td>
                        <td><span class="status completed">Completed</span></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>#7676</td>
                        <td>06/30/2022</td>
                        <td>Ramesh Chaudhary</td>
                        <td>Store name</td>
                        <td>Lalitpur</td>
                        <td>3</td>
                        <td><span class="status pending">Pending</span></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>#7676</td>
                        <td>06/30/2022</td>
                        <td>Ramesh Chaudhary</td>
                        <td>Store name</td>
                        <td>Lalitpur</td>
                        <td>3</td>
                        <td><span class="status completed">Completed</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
