<?php
// Database configuration
$host = 'localhost';
$dbname = 'bm_slpa'; // Replace with your database name
$username = 'root'; // Default username for phpMyAdmin
$password = ''; // Default password for phpMyAdmin

// API endpoint
$apiUrl = "http://10.104.4.22:8833/api/Employee/GetEmpDiv?employeeAll=employeeAll";

// Increase script execution time and memory
set_time_limit(0); // Remove time limit
ini_set('memory_limit', '512M'); // Increase memory limit

try {
    // Connect to MySQL database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch data from API
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true); // Convert JSON response to array

    if (!empty($data)) {
        // Prepare SQL statement
        $stmt = $pdo->prepare("INSERT INTO employee_details (employee_no, name, division)
                               VALUES (:employee_no, :name, :division)");

        // Process records in batches
        $batchSize = 1000; // Number of records per batch
        $batch = [];

        foreach ($data as $index => $row) {
            // Combine INITIALS and SURNAME to create the name
            $name = $row['INITIALS'] . ' ' . $row['SURNAME'];

            // Add data to batch
            $batch[] = [
                'employee_no' => $row['EMPNO'],   // Map EMPNO to employee_no
                'name' => $name,                 // Combine INITIALS and SURNAME as name
                'division' => $row['DIVNME'],   // Map DIVNAME to division
            ];

            // Execute batch when it reaches the batch size
            if (count($batch) === $batchSize || $index === array_key_last($data)) {
                foreach ($batch as $record) {
                    $stmt->execute([
                        ':employee_no' => $record['employee_no'],
                        ':name' => $record['name'],
                        ':division' => $record['division'],
                    ]);
                }
                $batch = []; // Clear batch
            }
        }

        echo "Employee data inserted successfully!";
    } else {
        echo "No data found in API response.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
