<?php
require '../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

$client = new Client("mongodb://localhost:27017");
$db = $client->ice_cream_inventory;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'fetch_flavors') {
    $flavors = $db->flavor->find([], ['projection' => ['name' => 1]]);
    $flavorList = [];

    foreach ($flavors as $flavor) {
        $flavorList[] = [
            '_id' => (string) $flavor->_id,
            'name' => $flavor->name
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($flavorList);
    exit;
}

if ($action === 'insert_production') {
    $flavorId = $_POST['flavor_id'] ?? '';
    $quantity = (int) ($_POST['quantity_made'] ?? 0);

    if ($flavorId && $quantity > 0) {
        // Fetch the flavor name using the flavor_id
        $flavor = $db->flavor->findOne(['_id' => new ObjectId($flavorId)]);

        if ($flavor) {
            $insertResult = $db->production->insertOne([
                'flavor_id' => new ObjectId($flavorId),
                'flavor_name' => $flavor->name, // Include flavor name here
                'quantity_made' => $quantity,
                'timestamp' => new UTCDateTime()
            ]);

            if ($insertResult->getInsertedCount() > 0) {
                echo "Production record saved successfully!";
            } else {
                http_response_code(500);
                echo "Failed to save production record.";
            }
        } else {
            http_response_code(400);
            echo "Flavor not found.";
        }
    } else {
        http_response_code(400);
        echo "Invalid input.";
    }
    exit;
}

if ($action === 'fetch_production') {
    $flavorFilter = $_POST['flavor'] ?? '';
    $dateFilter = $_POST['date'] ?? '';

    $query = [];
    if ($flavorFilter) {
        $query['flavor_name'] = $flavorFilter;
    }

    if ($dateFilter) {
        $query['timestamp'] = [
            '$gte' => new UTCDateTime(strtotime($dateFilter . " 00:00:00") * 1000),
            '$lt' => new UTCDateTime(strtotime($dateFilter . " 23:59:59") * 1000)
        ];
    }

    $productions = $db->production->find($query);

    $productionList = [];
    foreach ($productions as $production) {
        $productionList[] = [
            'flavor' => $production['flavor_name'],
            'quantity_made' => $production['quantity_made'],
            'timestamp' => $production['timestamp']->toDateTime()->format('Y-m-d H:i:s')
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($productionList);
    exit;
}
?>
