<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking System - Company Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
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
            margin-bottom: 15px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 16px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Company Login</h1>
        <p class="subtitle">Access your airline company dashboard</p>
        
        <?php
        // $db_host = getenv('DB_HOST');
        // $db_user = getenv('DB_USER');
        // $db_pass = getenv('DB_PASS');
        // $db_name = getenv('DB_NAME');        
        // Start session
        session_start();
        
        // Check if company is already logged in
        if (isset($_SESSION['company_id'])) {
            // Redirect to company dashboard
            header("Location: company_dashboard.php");
            exit();
        }
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Database connection
            $conn = new mysqli("localhost", "root", "Samruddhi@09", "DBMS_PROJECT");
            
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
            
            // If no validation errors, attempt to login
            if (empty($errors)) {
                // Prepare SQL statement
                $stmt = $conn->prepare("SELECT company_id, company_name, email, password FROM Companies WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 1) {
                    $company = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $company['password'])) {
                        // Password is correct, start a new session
                        
                        // Store data in session variables
                        $_SESSION['company_id'] = $company['company_id'];
                        $_SESSION['company_name'] = $company['company_name'];
                        $_SESSION['company_email'] = $company['email'];
                        $_SESSION['user_type'] = 'company'; // Identify user type as company
                        
                        // If remember me is checked, set cookies
                        if ($remember) {
                            // Set cookies for 30 days
                            setcookie("company_login", $email, time() + (30 * 24 * 60 * 60), "/");
                            setcookie("company_id", $company['company_id'], time() + (30 * 24 * 60 * 60), "/");
                        }
                        
                        // Redirect to company dashboard
                        header("Location: company_dashboard.php");
                        exit();
                    } else {
                        $errors[] = "Invalid email or password";
                    }
                } else {
                    $errors[] = "Invalid email or password";
                }
                
                $stmt->close();
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
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Business Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : (isset($_COOKIE['company_login']) ? $_COOKIE['company_login'] : ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" <?php echo isset($_COOKIE['company_login']) ? 'checked' : ''; ?>>
                <label for="remember" style="display: inline; font-weight: normal;">Remember me</label>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="signup-link">
            Need to register your company? <a href="company_signup.php">Sign up here</a>
        </div>
    </div>
</body>
</html>