<?php
include 'db_connect.php';

$name = $_POST['sheet_name'];
$costs = json_encode($_POST['costs']);
$total = $_POST['total'];

$conn->query("INSERT INTO cost_sheets (sheet_name, data, total)
              VALUES ('$name', '$costs', '$total')");

header("Location: cost_sheet.php");
