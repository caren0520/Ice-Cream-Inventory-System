<?php
require '../../vendor/autoload.php';
require '../../config.php';
use MongoDB\Client;

// Set timezone to Philippine Time (PHT)
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

try {
    // Connect to MongoDB
    $client = new Client("mongodb://localhost:27017");
    $db = $client->ice_cream_inventory;
    $flavorCollection = $db->flavor;
    $productionCollection = $db->production;

    $action = $_POST['action'] ?? '';

    // Fetch Flavors
    if ($action === 'fetch_flavors') {
        $flavors = $flavorCollection->find([], ['projection' => ['_id' => 1, 'name' => 1]]);
        
        $flavorList = [];
        foreach ($flavors as $flavor) {
            $flavorList[] = [
                '_id' => (string) $flavor['_id'],
                'name' => $flavor['name']
            ];
        }

        echo json_encode(['status' => 'success', 'flavors' => $flavorList]);
        exit;
    }

    // Save Production
    if ($action === 'save_production') {
        $ice_cream_id = $_POST['ice_cream_id'] ?? '';
        $quantity_made = (int) ($_POST['quantity_made'] ?? 0);

        if (!$ice_cream_id || $quantity_made <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
            exit;
        }

        $productionCollection->insertOne([
            'ice_cream_id' => new MongoDB\BSON\ObjectId($ice_cream_id),
            'quantity_made' => $quantity_made,
            'date' => new MongoDB\BSON\UTCDateTime()
        ]);

        echo json_encode(['status' => 'success']);
        exit;
    }

    // Fetch Production Records (with pagination)
    if ($action === 'fetch_production_records') {
        $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
        $recordsPerPage = isset($_POST['recordsPerPage']) ? (int) $_POST['recordsPerPage'] : 20;
        $skip = ($page - 1) * $recordsPerPage;
        $totalRecords = $productionCollection->countDocuments();
        $totalPages = ceil($totalRecords / $recordsPerPage);

        $records = $productionCollection->find([], [
            'sort' => ['date' => -1],
            'limit' => $recordsPerPage,
            'skip' => $skip
        ]);

        $recordList = [];
        foreach ($records as $record) {
            $flavor = $flavorCollection->findOne(['_id' => $record['ice_cream_id']], ['projection' => ['name' => 1]]);
            $flavorName = $flavor ? $flavor['name'] : 'Unknown';

            $dateTime = $record['date']->toDateTime();
            $dateTime->setTimezone(new DateTimeZone('Asia/Manila'));
            $formattedDateTime = $dateTime->format('d/m/Y h:i A');

            $recordList[] = [
                'flavor_name' => $flavorName,
                'quantity_made' => $record['quantity_made'],
                'date_time' => $formattedDateTime
            ];
        }

        echo json_encode(['status' => 'success', 'records' => $recordList, 'totalPages' => $totalPages]);
        exit;
    }

    // If no action matches
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
