<?php
include 'db_connect.php';

$id = $_GET['id'];

$r = $conn->query("SELECT * FROM production_requirements WHERE Req_ID=$id")->fetch_assoc();

$conn->query("UPDATE FINISHED_PRODUCTS SET Quantity = Quantity + {$r['Required_Qty']} WHERE Product_ID={$r['Product_ID']}");

$conn->query("UPDATE production_requirements SET Status='Completed' WHERE Req_ID=$id");

header("Location: production_requirements.php");
