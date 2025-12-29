<?php
// Database Connection Details
$servername = "127.0.0.1";
$username = "root";
$password = ""; // ← Leave blank since your root has no password now
$dbname = "inventorybillingdb";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset for proper symbol display (₹ etc.)
$conn->set_charset("utf8");

// --- START: STRUCTURAL TABLE CREATIONS ---

$sql_structural_tables = "
CREATE TABLE IF NOT EXISTS PURCHASE (
    Purchase_ID INT PRIMARY KEY AUTO_INCREMENT,
    Supplier_ID INT,
    Date DATE NOT NULL,
    TotalAmount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (Supplier_ID) REFERENCES SUPPLIER(Supplier_ID) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS PURCHASE_DETAILS (
    PurchaseDetail_ID INT PRIMARY KEY AUTO_INCREMENT,
    Purchase_ID INT,
    Product_ID INT,
    Quantity INT NOT NULL,
    Rate DECIMAL(10, 2) NOT NULL,
    Subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (Purchase_ID) REFERENCES PURCHASE(Purchase_ID) ON DELETE CASCADE,
    FOREIGN KEY (Product_ID) REFERENCES PRODUCTS(Product_ID) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS STOCK_ADJUSTMENT (
    Adjustment_ID INT PRIMARY KEY AUTO_INCREMENT,
    Product_ID INT NOT NULL,
    Date DATE NOT NULL,
    Adjustment_Quantity INT NOT NULL,
    Reason VARCHAR(255) NOT NULL,
    Notes TEXT,
    Recorded_By VARCHAR(100) DEFAULT 'System/Admin',
    FOREIGN KEY (Product_ID) REFERENCES PRODUCTS(Product_ID) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS USERS (
    User_ID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role VARCHAR(20) NOT NULL DEFAULT 'Cashier'
);
";

if ($conn->multi_query($sql_structural_tables)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
}

$check_admin = $conn->query("SELECT User_ID FROM USERS WHERE Username='admin'");
if ($check_admin && $check_admin->num_rows == 0) {
    $default_password = md5('admin123');
    $sql_insert_admin = "INSERT INTO USERS (Username, Password, Role)
                         VALUES ('admin', '$default_password', 'Admin')";
    $conn->query($sql_insert_admin);
}

// --- END: STRUCTURAL TABLE CREATIONS ---

// --- START: DEMAND FORECAST TABLE CREATION ---
$sql_forecast_table = "
CREATE TABLE IF NOT EXISTS DEMAND_FORECAST (
    Forecast_ID INT PRIMARY KEY AUTO_INCREMENT,
    Product_ID INT NOT NULL,
    Forecast_Month DATE NOT NULL,
    Predicted_Demand INT NOT NULL,
    Forecast_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_forecast (Product_ID, Forecast_Month),
    FOREIGN KEY (Product_ID) REFERENCES PRODUCTS(Product_ID) ON DELETE CASCADE
);
";
$conn->query($sql_forecast_table);
// --- END: DEMAND FORECAST TABLE CREATION ---

/* ============================================================
   BOOK COST SYSTEM TABLES  (ONLY ADDITION — nothing else touched)
   ============================================================ */

$conn->query("
CREATE TABLE IF NOT EXISTS BOOK_COST_SHEETS (
    Sheet_ID INT AUTO_INCREMENT PRIMARY KEY,
    Book_Name VARCHAR(150) NOT NULL,
    Grand_Total DECIMAL(10,2) NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
");

$conn->query("
CREATE TABLE IF NOT EXISTS BOOK_COST_ITEMS (
    Item_ID INT AUTO_INCREMENT PRIMARY KEY,
    Sheet_ID INT NOT NULL,
    Category VARCHAR(100),
    Item VARCHAR(150),
    Cost DECIMAL(10,2),
    Qty DECIMAL(10,2),
    Amount DECIMAL(10,2),
    FOREIGN KEY (Sheet_ID) REFERENCES BOOK_COST_SHEETS(Sheet_ID) ON DELETE CASCADE
);
");

/* ===================== END BOOK COST ADDITIONS ===================== */
?>
