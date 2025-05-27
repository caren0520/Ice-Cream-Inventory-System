<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../vendor/autoload.php';
use MongoDB\Client;

$client = new Client("mongodb://localhost:27017");
$db = $client->ice_cream_inventory;

$clientCollection = $db->client;
$flavorCollection = $db->flavor;
$salesCollection = $db->sales;

$action = $_POST['action'] ?? '';

if ($action === 'getSalesData') {
    $salesCursor = $salesCollection->find([], [
        'sort' => ['timestamp' => -1]
    ]);

    $groupedSales = [];

    foreach ($salesCursor as $sale) {
        $client = $clientCollection->findOne(['_id' => $sale->client_id]);
        $flavor = $flavorCollection->findOne(['_id' => $sale->flavor_id]);

        $clientName = $client ? $client->cfname . ' ' . $client->clname : 'Unknown';
        $flavorName = $flavor->name ?? 'Unknown Flavor';

        // Create a key for grouping
        $groupKey = $clientName;

        // Initialize group if not exists
        if (!isset($groupedSales[$groupKey])) {
            $groupedSales[$groupKey] = [
                'client_name' => $clientName,
                'flavors' => [],
                'total_price' => 0,
                'timestamp' => $sale->timestamp->toDateTime()->format('Y-m-d H:i:s')
            ];
        }

        // Append flavor and quantity
        $groupedSales[$groupKey]['flavors'][] = $flavorName . '(' . $sale->quantity . ')';

        // Sum total price
        $groupedSales[$groupKey]['total_price'] += $sale->total_price ?? 0;
    }

    // Convert to indexed array
    $salesData = array_values(array_map(function ($entry) {
        $entry['flavor_string'] = implode(' ', $entry['flavors']);
        unset($entry['flavors']);
        return $entry;
    }, $groupedSales));

    echo json_encode(['sales' => $salesData]);
    exit;
}
