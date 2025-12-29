<?php
include 'secure_page_template.php'; 
include 'db_connect.php'; 

// Load Dompdf library files - IMPORTANT: Path must match your setup!
// Assuming the folder is named 'dompdf' inside your project root.
require_once 'dompdf/autoload.inc.php'; 

// Reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

// Ensure ID is provided and numeric
$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($invoice_id <= 0) {
    die("Invalid Invoice ID provided.");
}

// 1. Fetch Invoice Header Data
$sql_header = "SELECT 
                    I.Invoice_ID, I.Date, I.TotalAmount, 
                    C.Name AS CustomerName, C.Phone, C.Email, C.Address 
               FROM 
                    INVOICE I
               JOIN 
                    CUSTOMER C ON I.customer_ID = C.customer_ID
               WHERE 
                    I.Invoice_ID = $invoice_id";
                    
$header_result = $conn->query($sql_header);
if (!$header_result || $header_result->num_rows == 0) {
    die("Invoice ID $invoice_id not found.");
}
$invoice_header = $header_result->fetch_assoc();

// 2. Fetch Invoice Details (Line Items)
$sql_details = "SELECT 
                    ID.Quantity, ID.Rate, ID.Subtotal, 
                    P.Name AS ProductName, P.Unit 
                FROM 
                    INVOICE_DETAILS ID
                JOIN 
                    PRODUCTS P ON ID.Product_ID = P.Product_ID
                WHERE 
                    ID.Invoice_ID = $invoice_id";

$details_result = $conn->query($sql_details);

// 3. Start building the HTML content for the PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Invoice #' . $invoice_id . '</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; font-size: 14px; line-height: 24px; color: #555; }
        .header h1 { color: #4f46e5; margin: 0; font-size: 24px; }
        .details table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        .details table td { padding: 5px; vertical-align: top; }
        .details table tr.information table td { padding-bottom: 30px; }
        .invoice-id { font-weight: bold; font-size: 18px; color: #10b981; }
        .item table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .item table thead tr { background: #f1f5f9; font-weight: bold; }
        .item table th, .item table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .total-row td { background: #fef3c7; font-weight: bold; font-size: 16px; padding: 10px 8px; text-align: right; border: none; }
        .total-label { text-align: right; border-right: none; }
        .total-value { text-align: right; background-color: #ffe082; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>SALES INVOICE</h1>
            <div style="text-align: right; margin-top: -30px;">
                <span class="invoice-id">INVOICE #'. $invoice_header['Invoice_ID'] . '</span><br>
                Date: ' . date('d M, Y', strtotime($invoice_header['Date'])) . '
            </div>
        </div>

        <div class="details">
            <table cellpadding="0" cellspacing="0">
                <tr class="information">
                    <td>
                        <table>
                            <tr>
                                <td>
                                    <strong>Billed To:</strong><br>
                                    ' . htmlspecialchars($invoice_header['CustomerName']) . '<br>
                                    Phone: ' . htmlspecialchars($invoice_header['Phone']) . '<br>
                                    Email: ' . htmlspecialchars($invoice_header['Email']) . '
                                </td>
                                <td>
                                    <strong>Issued By:</strong><br>
                                    Inventory & Billing System<br>
                                    [Your Company Name Here]<br>
                                    [Contact Email/Phone]
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="item">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Product Name</th>
                        <th style="width: 10%;">Unit</th>
                        <th style="width: 10%;">Qty</th>
                        <th style="width: 15%;">Rate (₹)</th>
                        <th style="width: 15%;">Subtotal (₹)</th>
                    </tr>
                </thead>
                <tbody>';
                    
                    while($item = $details_result->fetch_assoc()) {
                        $html .= '<tr>
                                    <td>' . htmlspecialchars($item['ProductName']) . '</td>
                                    <td>' . htmlspecialchars($item['Unit']) . '</td>
                                    <td>' . $item['Quantity'] . '</td>
                                    <td>' . number_format($item['Rate'], 2) . '</td>
                                    <td>' . number_format($item['Subtotal'], 2) . '</td>
                                </tr>';
                    }
                    
$html .= '
                </tbody>
            </table>
        </div>
        
        <table style="width: 100%; margin-top: 20px;">
            <tr class="total-row">
                <td class="total-label"></td>
                <td style="width: 15%;" class="total-label"></td>
                <td style="width: 15%;" class="total-label"></td>
                <td class="total-label">GRAND TOTAL:</td>
                <td style="width: 15%;" class="total-value">₹' . number_format($invoice_header['TotalAmount'], 2) . '</td>
            </tr>
        </table>
        
        <div style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 10px;">
            <p style="font-size: 10pt; text-align: center; color: #888;">Thank you for your business!</p>
        </div>
        
    </div>
</body>
</html>
';

// 4. Instantiate Dompdf and render HTML
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF (inline or attachment)
$filename = 'INVOICE_' . $invoice_header['Invoice_ID'] . '_' . date('Ymd') . '.pdf';
$dompdf->stream($filename, ["Attachment" => false]); // Attachment => false displays inline

$conn->close();
exit;
?>