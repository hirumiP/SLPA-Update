<?php
// Database configuration
$host = 'localhost';
$dbname = 'apidata'; // Replace with your database name
$username = 'root'; // Default username for phpMyAdmin
$password = ''; // Default password for phpMyAdmin

// API endpoint
$apiUrl = "http://10.104.4.22:8833/api/Employee/GetAllDivisions";

try {
    // Connect to MySQL database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch data from API
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true); // Convert JSON response to array

    // Debugging: Output the API response to confirm structure
    echo '<pre>';
    print_r($data);
    echo '</pre>';

    if (!empty($data)) {
        // Prepare SQL statement
        $stmt = $pdo->prepare("INSERT INTO divisions (division_id, division_code, division_name) VALUES (:division_id, :division_code, :division_name)");

        // Loop through data and execute insert
        foreach ($data as $row) {
            $stmt->execute([
                ':division_id' => $row['DIVID'],        // Map DIVID to division_id
                ':division_code' => $row['DIVCDE'],    // Map DIVCDE to division_code
                ':division_name' => $row['DIVNME'],    // Map DIVNME to division_name
            ]);
        }

        echo "Data inserted successfully!";
    } else {
        echo "No data found in API response.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
