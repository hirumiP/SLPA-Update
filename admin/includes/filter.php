<?php
include('includes/dbc.php');
?>

<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc; /* Light background for professionalism */
        }

        .filter-container {
            background: #fff; /* White background for clean contrast */
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .filter-header {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .filter-row {
            display: flex;
            gap: 10px;
            overflow-x: auto; /* Enable horizontal scrolling */
            padding: 10px 0;
            white-space: nowrap; /* Prevent wrapping of buttons */
        }

        .filter-row::-webkit-scrollbar {
            height: 8px;
        }

        .filter-row::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        .filter-row::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .btn {
            min-width: 150px; /* Reduce button width */
            font-size: 0.9rem; /* Slightly smaller text size */
            padding: 8px 15px; /* Adjust padding for compact size */
        }

        .print-btn {
            background-color: #212529; /* Bootstrap dark color */
            color: #fff;
            border-radius: 20px;
        }

        .print-btn:hover {
            background-color: #1a1e23;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        
            <!-- Navigation Buttons -->
            <div class="filter-row">
                <a href="dashbord.php" class="btn btn-primary">Dashboard</a>
                <a href="itemreq_view.php" class="btn btn-primary">Item Request</a>
                <!-- <a href="eq_view.php" class="btn btn-primary">EQ Plan</a> -->
                <a href="item_view.php" class="btn btn-primary">Item</a>
                <a href="category_view.php" class="btn btn-primary">Category</a>
                <a href="division_view.php" class="btn btn-primary">Division</a>
                <a href="receiveditem_view.php" class="btn btn-primary">Item Received</a>
                <a href="issueditem_view.php" class="btn btn-primary">Item Issued</a>
                <a href="condemnitem_view.php" class="btn btn-primary">Condemn Items</a>
            </div>
        </div>
        <br>
        
    </div>

        
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>



