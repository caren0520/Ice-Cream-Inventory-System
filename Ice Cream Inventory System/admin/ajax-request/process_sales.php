<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../vendor/autoload.php';
require '../../config.php';

use MongoDB\Client;

$client = new Client("mongodb://localhost:27017");
$db = $client->ice_cream_inventory;

$clientCollection = $db->client;
$flavorCollection = $db->flavor;
$productionCollection = $db->production;
$salesCollection = $db->sales;

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
$action = $_POST['action'] ?? ($data['action'] ?? '');

if ($action === 'getDropdownData') {
    $clientsCursor = $clientCollection->find([], ['projection' => ['_id' => 1, 'cfname' => 1, 'clname' => 1]]);
    $flavorsCursor = $flavorCollection->find([], ['projection' => ['_id' => 1, 'name' => 1, 'price' => 1]]);

    $clients = [];
    foreach ($clientsCursor as $c) {
        $clients[] = [
            '_id' => (string) $c->_id,
            'name' => $c->cfname . ' ' . $c->clname
        ];
    }

    $flavors = [];
    foreach ($flavorsCursor as $f) {
        $flavors[] = [
            '_id' => (string) $f->_id,
            'name' => $f->name ?? 'Unnamed Flavor',
            'price' => (float) $f->price
        ];
    }

    echo json_encode(['clients' => $clients, 'flavors' => $flavors]);
    exit;
}

if ($action === 'validateStock') {
    $flavorSales = $_POST['flavors'] ?? [];

    foreach ($flavorSales as $item) {
        $flavorId = $item['id'];
        $qtyToSell = (int) $item['qty'];

        $production = $productionCollection->aggregate([
            ['$match' => ['flavor_id' => new MongoDB\BSON\ObjectId($flavorId)]],
            ['$group' => ['_id' => '$flavor_id', 'totalMade' => ['$sum' => '$quantity_made']]]
        ])->toArray();

        $totalMade = $production[0]['totalMade'] ?? 0;

        $sold = $salesCollection->aggregate([
            ['$match' => ['flavor_id' => new MongoDB\BSON\ObjectId($flavorId)]],
            ['$group' => ['_id' => '$flavor_id', 'totalSold' => ['$sum' => '$quantity']]]
        ])->toArray();

        $totalSold = $sold[0]['totalSold'] ?? 0;

        if (($totalSold + $qtyToSell) > $totalMade) {
            echo json_encode(['valid' => false, 'message' => "Not enough stock for flavor ID $flavorId"]);
            exit;
        }
    }

    echo json_encode(['valid' => true]);
    exit;
}

if ($action === 'saveSales') {
    try {
        $sales = $data['sales'] ?? [];

        foreach ($sales as $sale) {
            $clientName = $sale['clientName'];
            $flavorPairs = $sale['flavorPairs'];
        
            $client = $clientCollection->findOne([
                '$where' => "this.cfname + ' ' + this.clname === '$clientName'"
            ]);
            if (!$client) continue;
            $clientId = $client->_id;
        
            foreach ($flavorPairs as $pair) {
                $flavorName = $pair['name'];
                $qty = $pair['qty'];
        
                $flavor = $flavorCollection->findOne(['name' => $flavorName]);
                if (!$flavor) continue;
        
                $flavorId = $flavor->_id;
                $unitPrice = (float) $flavor->price;
                $subtotal = $unitPrice * $qty;
        
                $salesCollection->insertOne([
                    'client_id' => $clientId,
                    'flavor_id' => $flavorId,
                    'quantity' => $qty,
                    'total_price' => $subtotal,
                    'timestamp' => new MongoDB\BSON\UTCDateTime()
                ]);
        
                // Update production only if needed
                $productionCollection->updateOne(
                    ['flavor_id' => $flavorId],
                    ['$inc' => ['quantity_made' => -$qty]]
                );
            }
        }
        

        echo json_encode(['success' => true, 'message' => 'Sales saved and production updated.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }

    exit;
}
?>
