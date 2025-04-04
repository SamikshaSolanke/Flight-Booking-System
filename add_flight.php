<?php
// Start the session
session_start();

// Check if the user is logged in as a company
if (!isset($_SESSION['company_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
// $db_host = getenv('DB_HOST');
// $db_user = getenv('DB_USER');
// $db_pass = getenv('DB_PASS');
// $db_name = getenv('DB_NAME');

// Create connection
$conn = new mysqli("localhost", "root", "Samruddhi@09", "DBMS_PROJECT");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$departure_date = $seats_no = $from_airport = $to_airport = $price = "";
$error = "";
$success = "";

// Fetch all airports for dropdown selection
$airports = [];
$airports_query = "SELECT airport_code, City, State FROM Airports ORDER BY City";
$stmt = $conn->prepare($airports_query);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $airports[] = $row;
    }
    $stmt->close();
} else {
    $error = "Error fetching airports: " . $conn->error;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $departure_date = trim($_POST["departure_date"]);
    $seats_no = trim($_POST["seats_no"]);
    $from_airport = trim($_POST["from_airport"]);
    $to_airport = trim($_POST["to_airport"]);
    $price = trim($_POST["price"]);
    
    // Basic validation
    if (empty($departure_date) || empty($seats_no) || empty($from_airport) || empty($to_airport) || empty($price)) {
        $error = "All fields are required";
    } elseif ($from_airport == $to_airport) {
        $error = "Departure and arrival airports cannot be the same";
    } elseif (!is_numeric($seats_no) || $seats_no <= 0) {
        $error = "Number of seats must be a positive number";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number";
    } elseif (strtotime($departure_date) < strtotime(date('Y-m-d'))) {
        $error = "Departure date cannot be in the past";
    } else {
        // Insert the flight into the database
        $company_id = $_SESSION['company_id'];
        
        $stmt = $conn->prepare("INSERT INTO Flights (company_id, departure_date, seats_no, from_airport_code, to_airport_code, price) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isissd", $company_id, $departure_date, $seats_no, $from_airport, $to_airport, $price);
        
        if ($stmt->execute()) {
            $success = "Flight added successfully!";
            // Clear form fields after successful submission
            $departure_date = $seats_no = $price = "";
            $from_airport = $to_airport = "";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Flight</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #ABD2FA;
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
            width: 80%;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .error {
            color: #ff0000;
            background-color: #ffe6e6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .success {
            color: #008000;
            background-color: #e6ffe6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        form {
            display: flex;
            flex-direction: column;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .nav-buttons a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }
        
        .nav-buttons a:hover {
            text-decoration: underline;
        }
        
        .input-error {
            border: 1px solid #ff0000 !important;
        }
        
        .field-error {
            color: #ff0000;
            font-size: 0.8rem;
            margin-top: 0.3rem;
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
    <div class="container">
        <h1>Add New Flight</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="departure_date">Departure Date:</label>
                <input type="date" id="departure_date" name="departure_date" value="<?php echo htmlspecialchars($departure_date); ?>" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="seats_no">Number of Available Seats:</label>
                <input type="number" id="seats_no" name="seats_no" value="<?php echo htmlspecialchars($seats_no); ?>" min="1" required>
            </div>

            <div class="form-group">
                <label for="price">Ticket Price ($):</label>
                <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" min="0.01" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="from_airport">Departure Airport:</label>
                <select id="from_airport" name="from_airport" required>
                    <option value="">-- Select Departure Airport --</option>
                    <?php foreach ($airports as $airport): ?>
                    <option value="<?php echo htmlspecialchars($airport['airport_code']); ?>" <?php echo ($from_airport == $airport['airport_code']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($airport['airport_code'] . ' - ' . $airport['City'] . ', ' . $airport['State']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="to_airport">Arrival Airport:</label>
                <select id="to_airport" name="to_airport" required>
                    <option value="">-- Select Arrival Airport --</option>
                    <?php foreach ($airports as $airport): ?>
                    <option value="<?php echo htmlspecialchars($airport['airport_code']); ?>" <?php echo ($to_airport == $airport['airport_code']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($airport['airport_code'] . ' - ' . $airport['City'] . ', ' . $airport['State']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit">Add Flight</button>
        </form>
        
        <div class="nav-buttons">
            <a href="company_dashboard.php">Back to Dashboard</a>
            <!-- <a href="view_flights.php">View All Flights</a> -->
        </div>
    </div>
    
    <script>
        // Simple client-side validation
        document.querySelector('form').addEventListener('submit', function(event) {
            let hasErrors = false;
            const departureDate = document.getElementById('departure_date');
            const seatsNo = document.getElementById('seats_no');
            const price = document.getElementById('price');
            const fromAirport = document.getElementById('from_airport');
            const toAirport = document.getElementById('to_airport');
            
            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
            
            // Check if departure date is in the past
            if (new Date(departureDate.value) < new Date(new Date().setHours(0,0,0,0))) {
                addError(departureDate, 'Departure date cannot be in the past');
                hasErrors = true;
            }
            
            // Check if seat number is valid
            if (seatsNo.value <= 0) {
                addError(seatsNo, 'Number of seats must be a positive number');
                hasErrors = true;
            }
            
            // Check if price is valid
            if (price.value <= 0) {
                addError(price, 'Price must be a positive number');
                hasErrors = true;
            }
            
            // Check if airports are selected and not the same
            if (fromAirport.value === toAirport.value && fromAirport.value !== '') {
                addError(toAirport, 'Departure and arrival airports cannot be the same');
                hasErrors = true;
            }
            
            if (hasErrors) {
                event.preventDefault();
            }
        });
        
        function addError(element, message) {
            element.classList.add('input-error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            element.parentNode.appendChild(errorDiv);
        }
    </script>
</body>
</html>