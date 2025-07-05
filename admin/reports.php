<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}

include('includes/header.php');
include('includes/dbc.php');
?>

<div class="container-fluid px-4">
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Budget Management - Report Generation</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        .header-text {
            text-align: center;
            margin: 40px 0;
            font-size: 30px;
            font-weight: 700;
            color: #001f3f; /* Navy blue */
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            width: 320px;
            height: 420px;
            border: none;
            border-radius: 15px;
            display: flex; /* Flexbox for alignment */
            align-items: center; /* Center content horizontally */
            justify-content: center; /* Center content vertically */
            position: relative;
            background: linear-gradient(135deg, #001f3f, #004080); /* Navy blue gradient */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: transform 0.5s, box-shadow 0.5s;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0));
            transform: rotate(45deg);
            transition: opacity 0.5s;
        }

        .card:hover::before {
            opacity: 0.5;
        }

        .card-body {
            text-align: center;
            position: relative;
            z-index: 1;
            display: flex; /* Flexbox for alignment */
            flex-direction: column; /* Stack content vertically */
            align-items: center; /* Center horizontally */
            justify-content: center; /* Center vertically */
            height: 100%; /* Full height for vertical centering */
            color: white;
        }

        .emoji {
            font-size: 50px;
            margin-bottom: 15px;
            animation: glow 1.5s infinite alternate;
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .card-text {
            font-size: 16px;
            margin-bottom: 20px;
            color: #d1d1d1; /* Light gray text */
        }

        @keyframes glow {
            0% {
                text-shadow: 0 0 5px #004080, 0 0 10px #004080, 0 0 20px #001f3f, 0 0 30px #001f3f, 0 0 40px #004080;
            }
            100% {
                text-shadow: 0 0 10px #0066cc, 0 0 20px #004080, 0 0 30px #004080, 0 0 40px #001f3f, 0 0 50px #001f3f;
            }
        }
        </style>
    </head>
    <body>

    <!-- Page Header -->
    <div class="header-text">
        <h2>Generate Budget Reports</h2>
        <p>Select a report type to view details</p>
    </div>

    <!-- Report Cards -->
    <div class="card-container">
        <div class="card" onclick="location.href='genaratePDF.php';">
            <div class="card-body">
                <div class="emoji">📊</div>
                <h5 class="card-title">Final Division Report by Year</h5>
                <p class="card-text">Generate a yearly overview of the budget.</p>
            </div>
        </div>
        <div class="card" onclick="location.href='genarateitemPDF.php';">
            <div class="card-body">
                <div class="emoji">📊</div>
                <h5 class="card-title">Final Item Report by Year</h5>
                <p class="card-text">Generate a detailed report of all items.</p>
            </div>
        </div>
        <div class="card" onclick="location.href='genarateDiviPDF.php';">
            <div class="card-body">
                <div class="emoji">📋</div>
                <h5 class="card-title">Division Report</h5>
                <p class="card-text">Generate a comprehensive report by division.</p>
            </div>
        </div>
        <div class="card" onclick="location.href='genarateCatagoryPDF.php';">
            <div class="card-body">
                <div class="emoji">📋</div>
                <h5 class="card-title">All Divisions Block Allocation</h5>
                <p class="card-text">Generate a detailed report of all item requests</p>
            </div>
        </div>
        <!-- <div class="card" onclick="location.href='generateComparisonForm.php';">
            <div class="card-body">
                <div class="emoji">📋</div>
                <h5 class="card-title">Get the Comparison of the budgets</h5>
                <p class="card-text">Generate a coparison report of two budgets</p>
            </div>
        </div> -->
        <div class="card" onclick="location.href='genarateComparison.php';">
            <div class="card-body">
                <div class="emoji">📊</div>
                <h5 class="card-title">Comparison Report by budget and year</h5>
                <p class="card-text">Generate a comparison report of two budgets.</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
