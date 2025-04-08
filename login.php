<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking System - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #ABD2FA;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 30px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        input:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        .btn {
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #357ae8;
        }
        
        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .signup-link a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me input {
            width: auto;
            margin-right: 10px;
        }
        
        .success-message {
            color: #2ecc71;
            background-color: #d5f5e3;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }

        .dashboard-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .dashboard-links a {
            flex: 1;
            margin: 0 10px;
            text-align: center;
            text-decoration: none;
            background-color: #4a90e2;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .dashboard-links a:hover {
            background-color: #357ae8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login to Your Account</h1>
        
        <?php
        // Start session
        session_start();
        
        // Variable to track authentication status
        $authenticated = false;
        
        // Check if user is already logged in - instead of redirecting, we'll just show a message
        if (isset($_SESSION['user_id'])) {
            $authenticated = true;
            echo "<div class='success-message'>You are logged in as " . htmlspecialchars($_SESSION['name']) . ".</div>";
        }
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Database connection
            include 'db_connection.php';
            
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            // Get form data
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']) ? true : false;
            
            // Validate input
            $errors = [];
            
            if (empty($email)) {
                $errors[] = "Email is required";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            }
            
            // If no validation errors, attempt to login using stored procedure
            // If no validation errors, attempt to login using stored procedure
        if (empty($errors)) {
            // Call the stored procedure for user authentication
            $stmt = $conn->prepare("CALL authenticate_user(?, ?, 'user')");
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Free the result set
                $result->free();
                $stmt->close();
                
                // Get the hashed password separately
                $passwordStmt = $conn->prepare("SELECT password FROM Users WHERE email = ?");
                $passwordStmt->bind_param("s", $email);
                $passwordStmt->execute();
                $passwordResult = $passwordStmt->get_result();
                $passwordRow = $passwordResult->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $passwordRow['password'])) {
                    // Password is correct, start a new session
                    
                    // Store data in session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_type'] = 'user'; // Identify user type
                    
                    // If remember me is checked, set cookies
                    if ($remember) {
                        // Set cookies for 30 days
                        setcookie("user_login", $email, time() + (30 * 24 * 60 * 60), "/");
                        setcookie("user_id", $user['user_id'], time() + (30 * 24 * 60 * 60), "/");
                    }
                    
                    // Set authentication flag
                    $authenticated = true;
                    
                    // Display success message instead of redirecting
                    echo "<div class='success-message'>Login successful! Welcome, " . htmlspecialchars($user['name']) . ".</div>";
                    
                } else {
                    $errors[] = "Invalid email or password";
                }
                
                $passwordStmt->close(); // Close only passwordStmt here
            } else {
                $errors[] = "Invalid email or password";
                $stmt->close(); // Close stmt here only if the result was empty
            }
            
            // Remove the duplicate $stmt->close() at the end
        }
            
            // Display errors if any
            if (!empty($errors)) {
                echo "<div style='color: #e74c3c; background-color: #fadbd8; padding: 10px; border-radius: 4px; margin-bottom: 15px;'>";
                echo "<ul style='margin-left: 20px;'>";
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
                echo "</ul>";
                echo "</div>";
            }
            
            $conn->close();
        }
        ?>
        
        <?php if (!$authenticated): ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : (isset($_COOKIE['user_login']) ? $_COOKIE['user_login'] : ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" <?php echo isset($_COOKIE['user_login']) ? 'checked' : ''; ?>>
                <label for="remember" style="display: inline; font-weight: normal;">Remember me</label>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </div>
        <?php else: ?>
        <div class="dashboard-links">
            <a href="flight_search.php">Book a Flight</a>
            <a href="my_bookings.php">My Bookings</a>
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">Logout</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
