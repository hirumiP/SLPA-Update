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
    <title>Contact Us - SLPA & IS Division</title>
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
            background: linear-gradient(rgba(0, 0, 50, 0.7), rgba(0, 0, 50, 0.7)), url('/images/contact-hero.jpg');
            background-size: cover;
            background-position: center;
            padding: 120px 0;
            color: white;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
        }

        .hero-section p {
            font-size: 1.5rem;
            margin-top: 10px;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.6);
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .card-title {
            font-size: 1.2rem;
            color: #003399;
            text-align: center;
        }

        .card-text {
            text-align: center;
            color: #555;
        }

        #contact-details .card i {
            font-size: 3rem;
            color: #003399;
            margin-bottom: 15px;
        }

        #social-media a {
            text-decoration: none;
            color: inherit;
        }

        #social-media i {
            color: #003399;
            transition: transform 0.3s ease, color 0.3s ease;
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
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We're here to help! Reach out to SLPA and the IS Division for any assistance</p>
        </div>
    </div>

    <!-- Contact Details Section -->
    <section id="contact-details" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Get in Touch</h2>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="card p-4">
                        <i class="fas fa-map-marker-alt"></i>
                        <h5 class="card-title">Address</h5>
                        <p class="card-text">Sri Lanka Ports Authority Head Office<br>
                        Level 7, HQ Colombo, No. 464, T.B. Jaya Mawatha, Colombo 10</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4">
                        <i class="fas fa-phone"></i>
                        <h5 class="card-title">Phone</h5>
                        <p class="card-text">+94 112 42 12 31 / +94 112 42 12 01</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4">
                        <i class="fas fa-envelope"></i>
                        <h5 class="card-title">Email</h5>
                        <p class="card-text"><a href="mailto:info@slpa.lk">info@slpa.lk</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Media Section -->
    <section id="social-media" class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-4">Follow Us</h2>
            <div class="row justify-content-center text-center">
                <div class="col-md-3">
                    <a href="https://www.facebook.com/SriLankaPortsAuthority" target="_blank">
                        <i class="fab fa-facebook fa-4x"></i>
                        <p>Facebook</p>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="https://twitter.com/SLPA_Official" target="_blank">
                        <i class="fab fa-twitter fa-4x"></i>
                        <p>Twitter</p>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="https://www.linkedin.com/company/sri-lanka-ports-authority/" target="_blank">
                        <i class="fab fa-linkedin fa-4x"></i>
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
