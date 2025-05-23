<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking System - Company Registration</title>
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

        .header {
            background-color: #4a90e2;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .login-link a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- <header class="header">
        <div class="logo">SkyConnect</div>
        <nav class="nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="login.php" class="nav-link">User Login</a>
            <a href="company_login.php" class="nav-link">Company Login</a>
            <a href="#about" class="nav-link">About</a>
        </nav>
    </header> -->
    <div class="container">
        <h1>Company Registration</h1>
        <p class="subtitle">Register your airline company to post flights</p>
        
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Database connection
            include 'db_connection.php';
            
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            // Get form data
            $company_name = trim($_POST['company_name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Initialize error array
            $errors = [];
            
            // Validate company name
            if (empty($company_name)) {
                $errors[] = "Company name is required";
            }
            
            // Validate email
            if (empty($email)) {
                $errors[] = "Email is required";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            } else {
                // Check if email exists
                $stmt = $conn->prepare("SELECT email FROM Companies WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $errors[] = "Email already exists";
                }
                $stmt->close();
            }
            
            // Validate password
            if (empty($password)) {
                $errors[] = "Password is required";
            } elseif (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters long";
            }
            
            // Confirm password
            if ($password != $confirm_password) {
                $errors[] = "Passwords do not match";
            }
            
            // If no errors, proceed with registration
            if (empty($errors)) {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Prepare and execute the insert statement
                $stmt = $conn->prepare("INSERT INTO Companies (company_name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $company_name, $email, $hashed_password);
                
                if ($stmt->execute()) {
                    echo "<p style='color: green; text-align: center; margin-bottom: 15px;'>Registration successful! Redirecting to login page...</p>";
                    echo "<script>setTimeout(function(){ window.location.href = 'company_login.php'; }, 3000);</script>";
                } else {
                    echo "<p style='color: red; text-align: center; margin-bottom: 15px;'>Error: " . $stmt->error . "</p>";
                }
                
                $stmt->close();
            } else {
                // Display errors
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
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Business Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Register Company</button>
        </form>
        
        <div class="login-link">
            Already registered? <a href="company_login.php">Login here</a>
        </div>
    </div>
</body>
</html>
