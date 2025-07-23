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
            margin: 40px 0 30px 0;
            font-size: 2.2rem;
            font-weight: 700;
            color: #0d2957;
        }
        .sub-header-text {
            text-align: center;
            margin-bottom: 40px;
            font-size: 1.2rem;
            color: #3b4a6b;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-bottom: 40px;
        }
        .card {
            width: 320px;
            height: 420px;
            border: none;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: linear-gradient(135deg, #0d2957 60%, #3b4a6b 100%);
            box-shadow: 0 8px 24px rgba(13,41,87,0.13);
            overflow: hidden;
            transition: transform 0.4s, box-shadow 0.4s;
            cursor: pointer;
        }
        .card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 16px 40px rgba(13,41,87,0.22);
        }
        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.12), rgba(255,255,255,0));
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #fff;
        }
        .emoji {
            font-size: 54px;
            margin-bottom: 18px;
            animation: glow 1.5s infinite alternate;
        }
        .card-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }
        .card-text {
            font-size: 1.05rem;
            margin-bottom: 20px;
            color: #e0e6f1;
        }
        @keyframes glow {
            0% {
                text-shadow: 0 0 8px #3b4a6b, 0 0 16px #0d2957;
            }
            100% {
                text-shadow: 0 0 16px #3b4a6b, 0 0 32px #0d2957;
            }
        }
        @media (max-width: 991px) {
            .card-container {
                gap: 20px;
            }
            .card {
                width: 95vw;
                max-width: 400px;
                height: 360px;
            }
        }
        @media (max-width: 600px) {
            .header-text {
                font-size: 1.5rem;
            }
            .card {
                height: 320px;
            }
        }
    </style>
</head>
<body>
    <div class="header-text">
        Generate Budget Reports
    </div>
    <div class="sub-header-text">
        Select a report type to view or download details
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
                <p class="card-text">Generate a detailed report of all item requests.</p>
            </div>
        </div>
        <div class="card" onclick="location.href='genarateSummaryPDF.php';">
            <div class="card-body">
                <div class="emoji">📋</div>
                <h5 class="card-title">All Divisions Block Allocation - Summary</h5>
                <p class="card-text">Generate a summary report of all item requests.</p>
            </div>
        </div>
        <div class="card" onclick="location.href='genarateComparison.php';">
            <div class="card-body">
                <div class="emoji">📊</div>
                <h5 class="card-title">Comparison Report by Budget and Year</h5>
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
