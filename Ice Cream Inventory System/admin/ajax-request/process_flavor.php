<?php
require '../../vendor/autoload.php';
require '../../config.php';
use MongoDB\Client;

$client = new Client("mongodb://localhost:27017");
$db = $client->ice_cream_inventory;
$flavorCollection = $db->flavor;

$action = $_POST['action'] ?? '';

// ===== Add Flavor =====
if ($action === 'addFlavor') {
    $flavorName = trim($_POST['flavor_name']);

    $existingFlavor = $flavorCollection->findOne([
        'name' => ['$regex' => '^' . preg_quote($flavorName) . '$', '$options' => 'i']
    ]);

    if ($existingFlavor) {
        echo json_encode(['status' => 'duplicate']);
    } else {
        $flavorCollection->insertOne(['name' => $flavorName]);
        echo json_encode(['status' => 'success']);
    }
}



// ===== Get Flavors =====
if ($action === 'getFlavors') {
    $flavors = $flavorCollection->find([], ['projection' => ['_id' => 1, 'name' => 1, 'price' => 1]]);
    $result = [];

    foreach ($flavors as $flavor) {
        $result[] = [
            '_id' => (string) $flavor->_id,
            'name' => $flavor->name,
            'price' => $flavor->price ?? null
        ];
    }

    echo json_encode($result);
}

// ===== Update Flavor =====
if ($action === 'updateFlavor') {
    $flavorId = new MongoDB\BSON\ObjectId($_POST['flavor_id']);
    $newFlavorName = trim($_POST['flavor_name']);
    $newFlavorPrice = floatval($_POST['flavor_price']);

    $flavorCollection->updateOne(
        ['_id' => $flavorId],
        ['$set' => ['name' => $newFlavorName, 'price' => $newFlavorPrice]]
    );

    echo "Flavor updated successfully!";
}

// ===== Delete Flavor =====
if ($action === 'deleteFlavor') {
    $flavorId = new MongoDB\BSON\ObjectId($_POST['flavor_id']);
    $flavorCollection->deleteOne(['_id' => $flavorId]);
    echo "Flavor deleted successfully!";
}
?>
