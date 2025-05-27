<?php
require '../../vendor/autoload.php';
require '../../config.php';

use MongoDB\Client;

// Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

$client = new Client("mongodb://localhost:27017");
$db = $client->ice_cream_inventory;
$flavorCollection = $db->client;

$action = $_POST['action'] ?? '';

// ADD CLIENT
if ($action == 'add') {
    $cfname = trim($_POST['cfname']);
    $clname = trim($_POST['clname']);
    $cage = trim($_POST['cage']);
    $ccontact = trim($_POST['ccontact']);
    $caddress = trim($_POST['caddress']);

    // Check for existing client
    $existingClient = $flavorCollection->findOne([
        'cfname' => new MongoDB\BSON\Regex('^' . preg_quote($cfname) . '$', 'i'),
        'clname' => new MongoDB\BSON\Regex('^' . preg_quote($clname) . '$', 'i'),
        'ccontact' => $ccontact
    ]);

    if ($existingClient) {
        echo json_encode(['status' => false, 'message' => 'Client already exists.']);
    } else {
        $newClient = [
            'cfname' => $cfname,
            'clname' => $clname,
            'cage' => $cage,
            'ccontact' => $ccontact,
            'caddress' => $caddress,
            'date_added' => date("F j, Y g:i A") // Format in Philippine time
        ];

        $insertResult = $flavorCollection->insertOne($newClient);

        echo json_encode([
            'status' => $insertResult->getInsertedCount() > 0,
            'message' => $insertResult->getInsertedCount() > 0 ? 'Client added successfully' : 'Failed to add client'
        ]);
    }
}

// FETCH CLIENTS
if ($action == 'fetch') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = 10;
    $skip = ($page - 1) * $limit;
    $filter = $_POST['filter'] ?? '';

    $criteria = [];
    if (!empty($filter)) {
        $regex = new MongoDB\BSON\Regex($filter, 'i');
        $criteria = [
            '$or' => [
                ['cfname' => $regex],
                ['clname' => $regex],
                ['cage' => $regex],
                ['ccontact' => $regex],
                ['caddress' => $regex]
            ]
        ];
    }

    $clients = $flavorCollection->find($criteria, ['limit' => $limit, 'skip' => $skip]);
    $clientList = iterator_to_array($clients);

    foreach ($clientList as &$client) {
        $client['id'] = (string) $client['_id'];
        $client['date_added'] = isset($client['date_added']) ? $client['date_added'] : "N/A";
    }

    $totalClients = $flavorCollection->countDocuments($criteria);
    $totalPages = ceil($totalClients / $limit);

    $hasPrev = $page > 1;
    $hasNext = $page < $totalPages;

    echo json_encode([
        'clients' => $clientList,
        'hasPrev' => $hasPrev,
        'hasNext' => $hasNext
    ]);
}

// DELETE MULTIPLE CLIENTS
if ($action == 'delete_multiple') {
    $ids = $_POST['ids'] ?? [];

    if (!empty($ids)) {
        $objectIds = array_map(function ($id) {
            return new MongoDB\BSON\ObjectId($id);
        }, $ids);

        $deleteResult = $flavorCollection->deleteMany(['_id' => ['$in' => $objectIds]]);

        echo json_encode([
            'status' => $deleteResult->getDeletedCount() > 0,
            'message' => $deleteResult->getDeletedCount() > 0
                ? 'Selected clients deleted successfully.'
                : 'No clients were deleted.'
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'No clients selected for deletion.']);
    }
}
