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
    <title>Contact Us - SLPA</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fc;
            color: #222;
        }
        .hero-section {
            background: linear-gradient(rgba(0, 0, 50, 0.7), rgba(0, 0, 50, 0.7)), url('/images/contact-hero.jpg');
            background-size: cover;
            background-position: center;
            padding: 110px 0 80px 0;
            color: white;
            text-align: center;
        }
        .hero-section h1 {
            font-size: 2.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }
        .hero-section p {
            font-size: 1.3rem;
            font-weight: 400;
            margin-top: 15px;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
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
            border-radius: 15px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 8px 24px rgba(0,0,0,0.13);
        }
        .card-title {
            color: #003399;
            font-weight: 600;
            font-size: 1.2rem;
        }
        .card-text {
            color: #555;
        }
        #contact-details .card i {
            font-size: 2.5rem;
            color: #003399;
            margin-bottom: 15px;
        }
        #social-media a {
            text-decoration: none;
            color: inherit;
        }
        #social-media i {
            color: #003399;
            transition: transform 0.3s, color 0.3s;
        }
        #social-media i:hover {
            transform: scale(1.2);
            color: #007bff;
        }
        #social-media p {
            margin-top: 5px;
            font-size: 1rem;
        }
        footer {
            background-color: #003399;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 50px;
            letter-spacing: 0.5px;
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
            <h1><i class="fas fa-envelope-open-text mr-2"></i>Contact Us</h1>
            <p>We're here to help! Reach out to SLPA for any assistance.</p>
        </div>
    </div>

    <!-- Contact Details Section -->
    <section id="contact-details" class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Get in Touch</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card p-4 h-100">
                        <i class="fas fa-map-marker-alt"></i>
                        <h5 class="card-title mt-2">Address</h5>
                        <p class="card-text">Sri Lanka Ports Authority Head Office<br>
                        Level 7, HQ Colombo, No. 464, T.B. Jaya Mawatha, Colombo 10</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card p-4 h-100">
                        <i class="fas fa-phone"></i>
                        <h5 class="card-title mt-2">Phone</h5>
                        <p class="card-text">+94 112 42 12 31<br>+94 112 42 12 01</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card p-4 h-100">
                        <i class="fas fa-envelope"></i>
                        <h5 class="card-title mt-2">Email</h5>
                        <p class="card-text"><a href="mailto:info@slpa.lk">info@slpa.lk</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Media Section -->
    <section id="social-media" class="bg-light py-5">
        <div class="container">
            <h2 class="section-title text-center">Follow Us</h2>
            <div class="row justify-content-center text-center">
                <div class="col-md-3 mb-3">
                    <a href="https://www.facebook.com/SriLankaPortsAuthority" target="_blank">
                        <i class="fab fa-facebook fa-3x"></i>
                        <p>Facebook</p>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="https://twitter.com/SLPA_Official" target="_blank">
                        <i class="fab fa-twitter fa-3x"></i>
                        <p>Twitter</p>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="https://www.linkedin.com/company/sri-lanka-ports-authority/" target="_blank">
                        <i class="fab fa-linkedin fa-3x"></i>
                        <p>LinkedIn</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Sri Lanka Ports Authority | All Rights Reserved</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
