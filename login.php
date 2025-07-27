
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; overflow: hidden; color: #333; }
        .video-background { position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; }
        .form-container { background: rgba(255,255,255,0.85); padding: 30px 25px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.4); width: 100%; max-width: 400px; animation: fadeIn 2s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .form-container h2 { text-align: center; font-size: 26px; font-weight: bold; margin-bottom: 20px; color: #333; }
        .form-container form { display: flex; flex-direction: column; }
        .form-container label { font-size: 15px; margin-bottom: 4px; margin-top: 10px; color: #444; }
        .form-container input { padding: 14px; margin: 8px 0 12px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; transition: 0.3s ease; background-color: #f9f9f9; }
        .form-container input:focus { border-color: #2575fc; outline: none; box-shadow: 0 0 8px rgba(37,117,252,0.5); }
        .form-container .password-toggle { margin-top: -10px; margin-bottom: 10px; font-size: 13px; color: #666; cursor: pointer; user-select: none; }
        .form-container button { padding: 14px; background: #2575fc; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; color: #fff; cursor: pointer; margin-top: 10px; transition: 0.3s ease; }
        .form-container button:hover { background: #6a11cb; box-shadow: 0 4px 12px rgba(106,17,203,0.3); }
        .form-container .error { color: #d9534f; font-size: 14px; text-align: center; margin-top: 10px; }
        .form-container .success { color: #28a745; font-size: 14px; text-align: center; margin-top: 10px; }
        .form-container p { text-align: center; font-size: 14px; margin-top: 15px; color: #666; }
        .form-container p a { color: #2575fc; text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .form-container p a:hover { color: #6a11cb; text-decoration: underline; }
    </style>
</head>
<body>
    <!-- Background Video -->
    <video autoplay muted loop class="video-background" aria-hidden="true">
        <source src="./uploads/video.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <!-- Login Form -->
    <div class="form-container" role="main" aria-label="Login Form">
        <h2>Login</h2>
        <form method="POST" autocomplete="off" id="loginForm">
            <label for="employee_ID">Employee ID</label>
            <input type="text" name="employee_ID" id="employee_ID" placeholder="Employee ID" required autocomplete="username" autofocus />

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Password" required autocomplete="current-password" />
            <span class="password-toggle" onclick="togglePassword()">Show Password</span>

            <button type="submit" id="loginBtn">Login</button>
        </form>

        <?php
        // Database connection
        include('admin/includes/dbc.php');
        session_start();
        date_default_timezone_set('Asia/Colombo'); // Set your timezone

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employee_ID = trim($_POST['employee_ID']);
            $password = trim($_POST['password']);

            $sql = "SELECT employee_ID, username, pwd, role, status, division, access_start, access_end FROM users WHERE employee_ID = ?";
            $stmt = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($stmt, "s", $employee_ID);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                if ($user['status'] == 0) {
                    $error = 'Your account is deactivated. Please contact the administrator.';
                } elseif (!password_verify($password, $user['pwd'])) {
                    $error = 'Invalid Employee ID or Password.';
                } else {
                    // Access control: Only for user and sub_admin
                    if (in_array($user['role'], ['user', 'sub_admin'])) {
                        $now = date('Y-m-d H:i:s');
                        if (empty($user['access_start']) || empty($user['access_end'])) {
                            $error = 'Your access period is not set. Please contact the administrator.';
                        } elseif ($now < $user['access_start'] || $now > $user['access_end']) {
                            $error = 'Your access period is not valid.<br>Allowed: ' . $user['access_start'] . ' to ' . $user['access_end'];
                        }
                    }
                    // If no error, proceed to login
                    if (empty($error)) {
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
                    }
                }
            } else {
                $error = 'Invalid Employee ID or Password.';
            }

            if (!empty($error)) {
                echo '<div class="error" role="alert">' . $error . '</div>';
            }
        }
        ?>
        <p>Forgot your password? <a href="reset_password.php">Reset here</a></p>
    </div>
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const toggle = document.querySelector('.password-toggle');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                toggle.textContent = 'Hide Password';
            } else {
                pwd.type = 'password';
                toggle.textContent = 'Show Password';
            }
        }
    </script>
</body>
</html>
