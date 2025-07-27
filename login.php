<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        /* Your same CSS styling (no change needed) */
        /* (keeping your nice design) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            color: #333;
        }

        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.75);
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .form-container h2 {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container input {
            padding: 14px;
            margin: 12px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s ease;
            background-color: #f9f9f9;
        }

        .form-container input:focus {
            border-color: #2575fc;
            outline: none;
            box-shadow: 0 0 8px rgba(37, 117, 252, 0.5);
        }

        .form-container button {
            padding: 14px;
            background: #2575fc;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s ease;
        }

        .form-container button:hover {
            background: #6a11cb;
            box-shadow: 0 4px 12px rgba(106, 17, 203, 0.3);
        }

        .form-container .error {
            color: #d9534f;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }

        .form-container p {
            text-align: center;
            font-size: 14px;
            margin-top: 15px;
            color: #666;
        }

        .form-container p a {
            color: #2575fc;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .form-container p a:hover {
            color: #6a11cb;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Background Video -->
    <video autoplay muted loop class="video-background">
        <source src="./uploads/video.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <!-- Login Form -->
    <div class="form-container">
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="employee_ID" placeholder="Employee ID" required />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>

        <?php
        // Database connection
        include('admin/includes/dbc.php');
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employee_ID = trim($_POST['employee_ID']);
            $password = trim($_POST['password']);

            // Updated query to also select division
            $sql = "SELECT employee_ID, username, pwd, role, status, division FROM users WHERE employee_ID = ?";
            $stmt = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($stmt, "s", $employee_ID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                if ($user['status'] == 0) {
                    echo '<p class="error">Your account is deactivated. Please contact the administrator.</p>';
                } else {
                    if (password_verify($password, $user['pwd'])) {
                        // Login success: save all important session data
                        $_SESSION['employee_ID'] = $user['employee_ID'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['division'] = $user['division'];

                        // Redirect by role
                        if ($user['role'] === 'admin') {
                            header("Location: admin/dashbord.php");
                        } elseif ($user['role'] === 'sub_admin') {
                            header("Location: sub_admin/dashbord_user.php");
                        } else {
                            header("Location: user/dashbord_user.php");
                        }
                        exit();
                    } else {
                        echo '<p class="error">Invalid Employee ID or Password.</p>';
                    }
                }
            } else {
                echo '<p class="error">Invalid Employee ID or Password.</p>';
            }
        }
        ?>
        <p>Forgot your password? <a href="reset_password.php">Reset here</a></p>
    </div>
</body>
</html>
