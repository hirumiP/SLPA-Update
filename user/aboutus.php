<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['employee_ID'])) {
    header("Location: ../login.php");
    exit();
}
include('includes/header.php');
include(__DIR__ . '/../user/includes/dbc.php'); // Include the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - SLPA & IS Division</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fc;
            color: #222;
        }
        .hero-section {
            background: linear-gradient(rgba(13,41,87,0.7), rgba(0,80,158,0.7)), url('/images/image1.jpeg') center/cover no-repeat;
            padding: 100px 0 80px 0;
            color: #fff;
            text-align: center;
            position: relative;
        }
        .hero-section h1 {
            font-size: 2.8rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .hero-section p {
            font-size: 1.3rem;
            font-weight: 400;
            margin-top: 15px;
        }
        .section-title {
            font-size: 2rem;
            font-weight: 600;
            color: #003366;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 8px 24px rgba(0,0,0,0.13);
        }
        .card-title {
            color: #00509e;
            font-weight: 600;
            font-size: 1.2rem;
        }
        .fade-in {
            opacity: 0;
            transition: opacity 1s;
        }
        .fade-in.show {
            opacity: 1;
        }
        .icon-circle {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e3eafc;
            color: #00509e;
            font-size: 1.7rem;
            margin-bottom: 15px;
        }
        @media (max-width: 767px) {
            .hero-section {
                padding: 60px 0 40px 0;
            }
            .hero-section h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1><i class="fas fa-ship me-2"></i>Welcome to SLPA </h1>
            <p class="lead">Empowering the future through <span style="color:#ffd700;">innovation</span> and <span style="color:#ffd700;">excellence</span></p>
        </div>
    </div>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <h2 class="section-title text-center fade-in">About Us</h2>
            <div class="row">
                <div class="col-lg-12 mb-4 fade-in">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="icon-circle mb-2"><i class="fas fa-landmark"></i></div>
                            <h3 class="card-title">SLPA</h3>
                            <p class="card-text">The Sri Lanka Ports Authority (SLPA) is the governing body responsible for the operation, management, and development of all major ports in Sri Lanka. With a mission to provide a world-class port service, SLPA ensures safe, efficient, and sustainable maritime transport to boost the nation's economy.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Aims and Goals Section -->
    <section id="aims" class="bg-light py-5">
        <div class="container">
            <h2 class="section-title text-center fade-in">Our Aims & Goals</h2>
            <div class="row">
                <div class="col-md-4 mb-4 fade-in">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="icon-circle"><i class="fas fa-lightbulb"></i></div>
                            <h5 class="card-title">Innovation</h5>
                            <p class="card-text">Continuously embracing new technologies to improve port operations and ensure global competitiveness.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4 fade-in">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="icon-circle"><i class="fas fa-bolt"></i></div>
                            <h5 class="card-title">Efficiency</h5>
                            <p class="card-text">Streamlining processes to ensure fast, reliable, and effective service delivery, minimizing delays.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4 fade-in">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="icon-circle"><i class="fas fa-leaf"></i></div>
                            <h5 class="card-title">Sustainability</h5>
                            <p class="card-text">Committing to eco-friendly practices and adhering to international environmental standards for sustainable development.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <script>
        // JavaScript for fading in elements as they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            function fadeInOnScroll() {
                elements.forEach((element) => {
                    if (element.getBoundingClientRect().top < window.innerHeight - 50) {
                        element.classList.add('show');
                    }
                });
            }
            fadeInOnScroll();
            window.addEventListener('scroll', fadeInOnScroll);
        });
    </script>
</body>
</html>
<?php
include('includes/footer.php');
include('includes/scripts.php');
?>