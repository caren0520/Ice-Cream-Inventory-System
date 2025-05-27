<?php
require '../../vendor/autoload.php'; // MongoDB and FPDF
require '../../fpdf/fpdf.php'; // Update path as needed for FPDF
use MongoDB\Client;

// MongoDB Connection
$client = new Client("mongodb://localhost:27017");
$db = $client->ice_cream_inventory;
$productionCollection = $db->production;
$flavorCollection = $db->flavor; // Assuming you have a flavor collection

// Get selected date from query parameter
if (isset($_GET['date'])) {
    $selectedDate = $_GET['date']; // YYYY-MM-DD
} else {
    die("Date not provided.");
}

// Convert selected date to start and end of day
$start = new MongoDB\BSON\UTCDateTime(strtotime($selectedDate . " 00:00:00") * 1000);
$end = new MongoDB\BSON\UTCDateTime(strtotime($selectedDate . " 23:59:59") * 1000);

// Get production data for the selected date
$productions = $productionCollection->find([
    'timestamp' => ['$gte' => $start, '$lte' => $end]
]);
$productionList = iterator_to_array($productions);

// Create PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Production List for ' . $selectedDate, 0, 1, 'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 10, 'Flavor', 1);
$pdf->Cell(40, 10, 'Quantity Made', 1);
$pdf->Cell(40, 10, 'Timestamp', 1);
$pdf->Ln();

// Table body
$pdf->SetFont('Arial', '', 10);
foreach ($productionList as $production) {
    // Check if the 'flavor_id' field exists in the production document
    if (isset($production['flavor_id'])) {
        // Get flavor name based on flavor_id (make sure flavor_id is ObjectId)
        $flavor = $flavorCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($production['flavor_id'])]);

        // Ensure flavor_name exists and is valid
        $flavorName = isset($flavor['name']) ? $flavor['name'] : 'Unknown';
    } else {
        $flavorName = 'Unknown'; // If no flavor_id, assign 'Unknown'
    }

    // Check if the timestamp is valid and format it
    $timestamp = isset($production['timestamp']) ? $production['timestamp']->toDateTime()->format('Y-m-d H:i:s') : 'N/A';

    // Add data to PDF
    $pdf->Cell(40, 10, $flavorName, 1);
    $pdf->Cell(40, 10, $production['quantity_made'], 1);
    $pdf->Cell(40, 10, $timestamp, 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('D', 'production_list_' . $selectedDate . '.pdf');
?>
