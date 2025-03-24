<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyConnect - Flight Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
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
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .nav {
            display: flex;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            padding: 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/api/placeholder/1200/600');
            background-size: cover;
            background-position: center;
            height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0 1rem;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin-bottom: 2rem;
        }
        
        .cta-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
        }
        
        .btn {
            display: inline-block;
            background-color: #4a90e2;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #357ae8;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid white;
        }
        
        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .options-section {
            padding: 4rem 2rem;
            text-align: center;
        }
        
        .options-section h2 {
            font-size: 2rem;
            margin-bottom: 3rem;
        }
        
        .options-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .option-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .option-card:hover {
            transform: translateY(-5px);
        }
        
        .option-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #4a90e2;
        }
        
        .option-card p {
            margin-bottom: 1.5rem;
            color: #666;
        }
        
        .card-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .btn-primary {
            background-color: #4a90e2;
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .footer p {
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .option-card {
                max-width: 100%;
            }
            
            .card-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">SkyConnect</div>
        <nav class="nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="login.php" class="nav-link">User Login</a>
            <a href="company_login.php" class="nav-link">Company Login</a>
            <a href="#about" class="nav-link">About</a>
        </nav>
    </header>
    
    <main>
        <section class="hero">
            <h1>Welcome to SkyConnect</h1>
            <p>Your one-stop solution for booking flights at the best prices. Connect with airlines directly and manage your travel with ease.</p>
            <div class="cta-buttons">
                <a href="#options" class="btn">Get Started</a>
                <a href="#about" class="btn btn-outline">Learn More</a>
            </div>
        </section>
        
        <section id="options" class="options-section">
            <h2>Choose Your Path</h2>
            <div class="options-container">
                <div class="option-card">
                    <h3>For Travelers</h3>
                    <p>Looking to book flights? Create an account to browse available flights, book tickets, and manage your travel plans all in one place.</p>
                    <div class="card-buttons">
                        <a href="login.php" class="btn btn-primary">Login</a>
                        <a href="signup.php" class="btn btn-secondary">Sign Up</a>
                    </div>
                </div>
                
                <div class="option-card">
                    <h3>For Airlines</h3>
                    <p>Are you an airline company? Register with us to list your flights, manage bookings, and connect with more travelers.</p>
                    <div class="card-buttons">
                        <a href="company_login.php" class="btn btn-primary">Company Login</a>
                        <a href="company_signup.php" class="btn btn-secondary">Register Airline</a>
                    </div>
                </div>
            </div>
        </section>
        
        <section id="about" class="options-section" style="background-color: #f9f9f9;">
            <h2>Why Choose SkyConnect?</h2>
            <div class="options-container">
                <div class="option-card">
                    <h3>For Travelers</h3>
                    <ul style="text-align: left; list-style-position: inside; margin-bottom: 1.5rem;">
                        <li>Search and compare flights from multiple airlines</li>
                        <li>Easy booking process with secure payments</li>
                        <li>Manage all your bookings in one place</li>
                        <li>Get notified about flight updates and offers</li>
                        <li>24/7 customer support for your queries</li>
                    </ul>
                    <a href="signup.php" class="btn btn-primary">Create Account</a>
                </div>
                
                <div class="option-card">
                    <h3>For Airlines</h3>
                    <ul style="text-align: left; list-style-position: inside; margin-bottom: 1.5rem;">
                        <li>List your flights on a trusted platform</li>
                        <li>Manage bookings and flight schedules</li>
                        <li>Get insights on booking patterns</li>
                        <li>Direct communication with travelers</li>
                        <li>Expand your customer reach</li>
                    </ul>
                    <a href="company_signup.php" class="btn btn-primary">Join as Airline</a>
                </div>
            </div>
        </section>
    </main>
    
    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> SkyConnect Flight Booking System. All rights reserved.</p>
        <p>Contact us: support@skyconnect.example.com</p>
    </footer>
    
    <?php
    // Check if user is already logged in and redirect appropriately
    session_start();
    
    if (isset($_SESSION['user_id'])) {
        echo "<script>
            // Add a notification that user is already logged in
            document.addEventListener('DOMContentLoaded', function() {
                const userCard = document.querySelector('.option-card:first-child');
                const cardButtons = userCard.querySelector('.card-buttons');
                cardButtons.innerHTML = '<a href=\"dashboard.php\" class=\"btn btn-primary\">Go to Dashboard</a>';
                
                // Add logged in notification
                const notification = document.createElement('p');
                notification.style.color = 'green';
                notification.textContent = 'You are logged in as " . htmlspecialchars($_SESSION['name']) . "';
                userCard.insertBefore(notification, cardButtons);
            });
        </script>";
    } else if (isset($_SESSION['company_id'])) {
        echo "<script>
            // Add a notification that company is already logged in
            document.addEventListener('DOMContentLoaded', function() {
                const companyCard = document.querySelector('.option-card:nth-child(2)');
                const cardButtons = companyCard.querySelector('.card-buttons');
                cardButtons.innerHTML = '<a href=\"company_dashboard.php\" class=\"btn btn-primary\">Go to Dashboard</a>';
                
                // Add logged in notification
                const notification = document.createElement('p');
                notification.style.color = 'green';
                notification.textContent = 'You are logged in as " . htmlspecialchars($_SESSION['company_name']) . "';
                companyCard.insertBefore(notification, cardButtons);
            });
        </script>";
    }
    ?>
</body>
</html>