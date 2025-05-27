<?php
require '../../vendor/autoload.php';
require '../../fpdf/fpdf.php'; // Update path as needed for FPDF
use MongoDB\Client;

// MongoDB Connection
$client = new Client("mongodb://localhost:27017");
$db = $client->ice_cream_inventory;
$flavorCollection = $db->client;

$clients = $flavorCollection->find();
$clientList = iterator_to_array($clients);

// Create PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Client List', 0, 1, 'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 10, 'First Name', 1);
$pdf->Cell(40, 10, 'Last Name', 1);
$pdf->Cell(20, 10, 'Age', 1);
$pdf->Cell(40, 10, 'Contact', 1);
$pdf->Cell(80, 10, 'Address', 1);
$pdf->Cell(40, 10, 'Date Added', 1);
$pdf->Ln();

// Table body
$pdf->SetFont('Arial', '', 10);
foreach ($clientList as $client) {
    $pdf->Cell(40, 10, $client['cfname'], 1);
    $pdf->Cell(40, 10, $client['clname'], 1);
    $pdf->Cell(20, 10, $client['cage'], 1);
    $pdf->Cell(40, 10, $client['ccontact'], 1);
    $pdf->Cell(80, 10, $client['caddress'], 1);
    $pdf->Cell(40, 10, isset($client['date_added']) ? $client['date_added'] : 'N/A', 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('D', 'client_list.pdf');
