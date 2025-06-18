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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* General Styling */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
        }

        .hero-section {
            background-image: url('/images/image1.jpeg');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            color: white;
            text-align: center;
        }

        /* Animated Card Hover Effect */
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Fade-in Animation */
        .fade-in {
            opacity: 0;
            transition: opacity 1s;
        }

        .fade-in.show {
            opacity: 1;
        }
    </style>
</head>
<body>
    
   

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
        <h1 class="display-4" style="color: navy;">Welcome to SLPA and the IS Division</h1>
    <p class="lead" style="color: navy;">Empowering the future through innovation and excellence</p>

        </div>
    </div>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4 fade-in">About Us</h2>
            <div class="row">
                <div class="col-lg-6 fade-in">
                    <h3>SLPA</h3>
                    <p>The Sri Lanka Ports Authority (SLPA) is the governing body responsible for the operation, management, and development of all major ports in Sri Lanka. With a mission to provide a world-class port service, SLPA ensures safe, efficient, and sustainable maritime transport to boost the nation's economy.</p>
                </div>
                <div class="col-lg-6 fade-in">
                    <h3>IS Division</h3>
                    <p>The Information Systems (IS) Division of SLPA is vital for driving technological innovation. It supports the SLPA's mission by integrating advanced IT solutions that ensure operational efficiency, enhance data security, and enable smooth communication across all port operations.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Aims and Goals Section -->
    <section id="aims" class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-4 fade-in">Our Aims & Goals</h2>
            <div class="row">
                <div class="col-md-4 fade-in">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Innovation</h5>
                            <p class="card-text">Continuously embracing new technologies to improve port operations and ensure global competitiveness.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 fade-in">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Efficiency</h5>
                            <p class="card-text">Streamlining processes to ensure fast, reliable, and effective service delivery, minimizing delays.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 fade-in">
                    <div class="card mb-3">
                        <div class="card-body">
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
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // JavaScript for fading in elements as they come into view
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            window.addEventListener('scroll', function() {
                elements.forEach((element) => {
                    if (element.getBoundingClientRect().top < window.innerHeight - 50) {
                        element.classList.add('show');
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
include('includes/footer.php');
include('includes/scripts.php');
?>