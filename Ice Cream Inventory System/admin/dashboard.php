<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ice Cream Dashboard</title>
  <link rel="stylesheet" href="../assets/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
  <div class="top-row">
    <div class="box"><canvas id="barChart"></canvas></div>
    <div class="box"><canvas id="pieChart"></canvas></div>
    <div class="box"><canvas id="listofclients"></canvas></div>
  </div>
  <div class="bottom-box"><canvas id="lineChart"></canvas></div>
</div>

<script>
  $(document).ready(function () {
    // Initialize bar chart
    let barChart = new Chart(document.getElementById("barChart"), {
        type: 'bar',
        data: {
            labels: [],  // Flavor names will be added dynamically
            datasets: [{
                label: 'Production Quantity',
                data: [],  // Quantity values will be added dynamically
                backgroundColor: ['#3182ce', '#63b3ed', '#90cdf4', '#48bb78', '#ed8936'], // Customize colors as needed
            }]
        }
    });

    // Initialize line chart for sales over time
    let lineChart = new Chart(document.getElementById("lineChart"), {
        type: 'line',
        data: {
            labels: [],  // Timestamps will be added dynamically
            datasets: [{
                label: 'Total Sales',
                data: [],  // Total sales values will be added dynamically
                borderColor: '#FF5733',
                fill: false,
                borderWidth: 2
            }]
        }
    });

    // Fetch production data
    function fetchProductionData() {
        $.ajax({
            url: 'ajax-request/process_production.php',
            type: 'POST',
            data: {
                action: 'fetch_production',
                flavor: '', // You can filter by flavor if needed
                date: '' // You can filter by date if needed
            },
            dataType: 'json',
            success: function (data) {
                let flavorData = {};

                // Group data by flavor and sum the quantities
                data.forEach(function (row) {
                    if (!flavorData[row.flavor]) {
                        flavorData[row.flavor] = 0;
                    }
                    flavorData[row.flavor] += row.quantity_made;
                });

                // Prepare chart data
                let labels = Object.keys(flavorData);
                let quantities = Object.values(flavorData);

                // Update the bar chart with the new data
                barChart.data.labels = labels;
                barChart.data.datasets[0].data = quantities;
                barChart.update();
            }
        });
    }

    // Fetch sales data and update line chart
    function fetchSalesData() {
        $.ajax({
            url: 'ajax-request/process_sales_log.php',
            method: 'POST',
            data: { action: 'getSalesData' },
            dataType: 'json',
            success: function (response) {
                let sales = response.sales;

                let timestamps = [];
                let totalSales = [];

                // Prepare data for line chart
                sales.forEach(sale => {
                    timestamps.push(sale.timestamp); // Add the timestamp
                    totalSales.push(sale.total_price); // Add the total price for each timestamp
                });

                // Update the line chart with the new data
                lineChart.data.labels = timestamps;
                lineChart.data.datasets[0].data = totalSales;
                lineChart.update();
            },
            error: function () {
                alert("Failed to load sales data.");
            }
        });
    }

    // Initialize pie chart for age distribution
    function fetchAgeDistribution() {
        $.ajax({
            url: 'ajax-request/process_client.php',
            type: 'POST',
            data: { action: 'fetch' },
            success: function (response) {
                let data = JSON.parse(response);
                let clients = data.clients;

                // Prepare age group data
                let ageGroups = {
                    '18-25': 0,
                    '26-35': 0,
                    '36-45': 0,
                    '46-60': 0,
                    '60+': 0
                };

                clients.forEach(client => {
                    let age = parseInt(client.cage);
                    if (age >= 18 && age <= 25) ageGroups['18-25']++;
                    else if (age >= 26 && age <= 35) ageGroups['26-35']++;
                    else if (age >= 36 && age <= 45) ageGroups['36-45']++;
                    else if (age >= 46 && age <= 60) ageGroups['46-60']++;
                    else if (age > 60) ageGroups['60+']++;
                });

                // Prepare data for Pie chart
                let pieData = {
                    labels: Object.keys(ageGroups),
                    datasets: [{
                        data: Object.values(ageGroups),
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#FF8A00', '#A2FF36'],
                        hoverBackgroundColor: ['#FF2A5D', '#2482B4', '#FFB634', '#FF6600', '#75FF1A']
                    }]
                };

                // Create the pie chart
                createPieChart(pieData);
            }
        });
    }

    function createPieChart(pieData) {
        var ctx = document.getElementById("pieChart").getContext("2d");
        new Chart(ctx, {
            type: 'pie',
            data: pieData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                return tooltipItem.label + ": " + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });
    }

    // Randomized radar chart for design (listofclients)
    var ctx = document.getElementById("listofclients").getContext("2d");
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Flavor A', 'Flavor B', 'Flavor C', 'Flavor D', 'Flavor E'],
            datasets: [{
                label: 'Sample Data',
                data: Array.from({ length: 5 }, () => Math.floor(Math.random() * 100)),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Fetch and create the charts
    fetchProductionData();
    fetchAgeDistribution();
    fetchSalesData();  // Fetch and update sales data in the line chart
  });
</script>

</body>
</html>
