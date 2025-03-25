<?php
/**
 * NetSuite Data Query Interface
 * 
 * This script provides a simple web interface to query the NetSuite transaction data
 * that has been imported into the SQLite database.
 */

// Configuration
$dbFilePath = __DIR__ . '/netsuite_data.sqlite';

// Create database connection
try {
    $pdo = new PDO("sqlite:{$dbFilePath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if database file exists
    if (!file_exists($dbFilePath)) {
        $error = "Database file not found. Please run import-netsuite-data.php first.";
    }
    
    // Process query if submitted
    $results = [];
    $query = '';
    $error = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
        $query = $_POST['query'];
        
        try {
            // Very basic security - only allow SELECT queries
            if (stripos(trim($query), 'select') !== 0) {
                throw new Exception("Only SELECT queries are allowed for security reasons.");
            }
            
            $stmt = $pdo->query($query);
            $results = $stmt->fetchAll();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Predefined queries for common analysis tasks
    $predefinedQueries = [
        "Transaction Counts" => "SELECT type, COUNT(*) as count FROM transactions GROUP BY type ORDER BY count DESC",
        "Total by Type" => "SELECT type, SUM(amount) as total FROM transactions GROUP BY type ORDER BY total DESC",
        "Net Sales Summary" => "SELECT 
                               SUM(CASE WHEN type = 'Invoice' THEN amount ELSE 0 END) as invoice_total,
                               SUM(CASE WHEN type = 'Credit Memo' THEN amount ELSE 0 END) as credit_total,
                               SUM(amount) as net_total
                             FROM transactions",
        "Top Products" => "SELECT description, COUNT(*) as transaction_count, SUM(amount) as total_amount 
                          FROM transactions 
                          WHERE description != 'Cost of Sales' AND description != '' 
                          GROUP BY description 
                          ORDER BY transaction_count DESC 
                          LIMIT 20",
        "Top Customers" => "SELECT customer_name, COUNT(*) as transaction_count, SUM(amount) as total_amount 
                           FROM transactions 
                           GROUP BY customer_name 
                           ORDER BY total_amount DESC 
                           LIMIT 20",
        "Document Count" => "SELECT COUNT(DISTINCT document_number) as count FROM transactions",
        "Sample Credit Memo" => "SELECT * FROM transactions WHERE type = 'Credit Memo' LIMIT 10",
        "Credit Memo Summary" => "SELECT 
                                document_number, 
                                customer_name, 
                                SUM(amount) as total_amount,
                                COUNT(*) as line_items
                                FROM transactions 
                                WHERE type = 'Credit Memo' 
                                GROUP BY document_number
                                ORDER BY total_amount
                                LIMIT 10",
        "Invoice Summary" => "SELECT 
                            document_number, 
                            customer_name, 
                            SUM(amount) as total_amount,
                            COUNT(*) as line_items
                            FROM transactions 
                            WHERE type = 'Invoice' 
                            GROUP BY document_number
                            ORDER BY total_amount DESC
                            LIMIT 10"
    ];
    
} catch (Exception $e) {
    $error = "Database connection error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetSuite Data Query Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .predefined-query { cursor: pointer; }
        pre { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">NetSuite Data Query Tool</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">Predefined Queries</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($predefinedQueries as $name => $sql): ?>
                                <li class="list-group-item predefined-query" data-query="<?php echo htmlspecialchars($sql); ?>">
                                    <?php echo htmlspecialchars($name); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">Custom SQL Query</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <textarea name="query" id="query" class="form-control" rows="5" placeholder="Enter your SQL query here..."><?php echo htmlspecialchars($query); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Run Query</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="card">
                <div class="card-header">Query Results (<?php echo count($results); ?> rows)</div>
                <div class="card-body">
                    <?php if (count($results) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <?php foreach (array_keys($results[0]) as $column): ?>
                                            <th><?php echo htmlspecialchars($column); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $value): ?>
                                                <td><?php echo htmlspecialchars($value); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No results found.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <h3>Table Structure</h3>
            <pre>
CREATE TABLE transactions (
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
            </pre>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const predefinedQueries = document.querySelectorAll('.predefined-query');
            const queryTextarea = document.getElementById('query');
            
            predefinedQueries.forEach(function(item) {
                item.addEventListener('click', function() {
                    queryTextarea.value = this.dataset.query;
                });
            });
        });
    </script>
</body>
</html>