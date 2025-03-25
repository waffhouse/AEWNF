<?php
/**
 * NetSuite JSON Data Importer
 * 
 * This script reads the NetSuite JSON data file and imports it into a SQLite database
 * for easier analysis and querying.
 */

// Configuration
$jsonFilePath = __DIR__ . '/jsondata.txt';
$dbFilePath = __DIR__ . '/netsuite_data.sqlite';

// Create database connection
try {
    // Create or open SQLite database
    $pdo = new PDO("sqlite:{$dbFilePath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create the transactions table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        transaction_date TEXT,
        document_number TEXT,
        quantity TEXT,
        customer_name TEXT,
        description TEXT,
        amount REAL,
        type TEXT,
        status TEXT,
        price_level TEXT,
        account TEXT,
        netsuite_id TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
    ");
    
    echo "Database and table created successfully.\n";
    
    // Read JSON data
    $jsonContent = file_get_contents($jsonFilePath);
    if ($jsonContent === false) {
        throw new Exception("Could not read JSON file: {$jsonFilePath}");
    }
    
    $jsonData = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }
    
    echo "JSON file loaded successfully. Found " . count($jsonData) . " records.\n";
    
    // Clear existing data
    $pdo->exec("DELETE FROM transactions");
    
    // Prepare insert statement
    $stmt = $pdo->prepare("
        INSERT INTO transactions (
            transaction_date, document_number, quantity, customer_name, 
            description, amount, type, status, price_level, account, netsuite_id
        ) VALUES (
            :date, :document_number, :quantity, :customer_name,
            :description, :amount, :type, :status, :price_level, :account, :netsuite_id
        )
    ");
    
    // Insert records
    $pdo->beginTransaction();
    $count = 0;
    
    foreach ($jsonData as $record) {
        // Clean amount value (remove commas, convert empty to 0)
        $amount = $record['Amount'];
        if ($amount === '') {
            $amount = '0.00';
        } else {
            $amount = str_replace(',', '', $amount);
        }
        
        $stmt->execute([
            'date' => $record['Date'],
            'document_number' => $record['Document Number'],
            'quantity' => $record['Quantity'],
            'customer_name' => $record['Name'],
            'description' => $record['Description'],
            'amount' => $amount,
            'type' => $record['Type'],
            'status' => $record['Status'],
            'price_level' => $record['Price Level'],
            'account' => $record['Account'],
            'netsuite_id' => $record['ID']
        ]);
        
        $count++;
        
        // Commit in batches to avoid memory issues
        if ($count % 1000 === 0) {
            $pdo->commit();
            $pdo->beginTransaction();
            echo "Imported {$count} records...\n";
        }
    }
    
    $pdo->commit();
    echo "Import complete. {$count} records imported successfully.\n";
    
    // Run some summary queries
    echo "\nSummary Reports:\n";
    echo "----------------\n";
    
    // Count by transaction type
    $types = $pdo->query("SELECT type, COUNT(*) as count FROM transactions GROUP BY type ORDER BY count DESC")->fetchAll();
    echo "\nTransaction Types:\n";
    foreach ($types as $type) {
        echo "- {$type['type']}: {$type['count']} records\n";
    }
    
    // Sum amounts by transaction type
    $sums = $pdo->query("SELECT type, SUM(amount) as total FROM transactions GROUP BY type ORDER BY total DESC")->fetchAll();
    echo "\nTotal Amounts by Type:\n";
    foreach ($sums as $sum) {
        echo "- {$sum['type']}: $" . number_format($sum['total'], 2) . "\n";
    }
    
    // Get total net (sales - credits)
    $netSales = $pdo->query("
        SELECT 
            SUM(CASE WHEN type = 'Invoice' THEN amount ELSE 0 END) as invoice_total,
            SUM(CASE WHEN type = 'Credit Memo' THEN amount ELSE 0 END) as credit_total,
            SUM(amount) as net_total
        FROM transactions
    ")->fetch();
    
    echo "\nNet Sales Summary:\n";
    echo "- Invoice Total: $" . number_format($netSales['invoice_total'], 2) . "\n";
    echo "- Credit Memo Total: $" . number_format($netSales['credit_total'], 2) . "\n";
    echo "- Net Sales: $" . number_format($netSales['net_total'], 2) . "\n";
    
    // Count unique documents
    $documentCount = $pdo->query("SELECT COUNT(DISTINCT document_number) as count FROM transactions")->fetchColumn();
    echo "\nUnique Document Numbers: {$documentCount}\n";
    
    // Sample invoice
    echo "\nSample Invoice:\n";
    $invoice = $pdo->query("
        SELECT * FROM transactions 
        WHERE type = 'Invoice' AND amount > 0
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    print_r($invoice);
    
    // Sample credit memo
    echo "\nSample Credit Memo:\n";
    $creditMemo = $pdo->query("
        SELECT * FROM transactions 
        WHERE type = 'Credit Memo' 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    print_r($creditMemo);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
}