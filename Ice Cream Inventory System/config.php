<?php
    require 'vendor/autoload.php'; // MongoDB PHP Library

    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    $db = $mongoClient->ice_cream_inventory; // Your database name
    $usersCollection = $db->login; // Users collection
?>
