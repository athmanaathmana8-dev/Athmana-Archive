<?php
/**
 * Airport Management System Setup Script
 * This script helps set up the database and initial configuration
 */

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database_name = "airport_management_system";

echo "<h1>Airport Management System Setup</h1>";
echo "<p>Setting up database and initial configuration...</p>";

// Create connection
$conn = mysqli_connect($servername, $username, $password);

if (!$conn) {
    die("<p style='color: red;'>Connection failed: " . mysqli_connect_error() . "</p>");
}

echo "<p style='color: green;'>✓ Connected to MySQL server</p>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database_name";
if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green;'>✓ Database '$database_name' created successfully</p>";
} else {
    echo "<p style='color: red;'>Error creating database: " . mysqli_error($conn) . "</p>";
}

// Select database
mysqli_select_db($conn, $database_name);

// Read and execute schema file
$schema_file = 'database_schema.sql';
if (file_exists($schema_file)) {
    $schema = file_get_contents($schema_file);
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if (mysqli_query($conn, $statement)) {
                echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Warning: " . mysqli_error($conn) . "</p>";
            }
        }
    }
} else {
    echo "<p style='color: red;'>Schema file not found: $schema_file</p>";
}

// Insert sample data
echo "<h2>Inserting Sample Data</h2>";

// Sample airports
$airports = [
    ['Kempegowda International Airport', 'Bangalore', 'India'],
    ['Indira Gandhi International Airport', 'Delhi', 'India'],
    ['Chhatrapati Shivaji Maharaj International Airport', 'Mumbai', 'India'],
    ['Chennai International Airport', 'Chennai', 'India'],
    ['Rajiv Gandhi International Airport', 'Hyderabad', 'India'],
    ['Netaji Subhas Chandra Bose International Airport', 'Kolkata', 'India'],
    ['Pune Airport', 'Pune', 'India'],
    ['Sardar Vallabhbhai Patel International Airport', 'Ahmedabad', 'India'],
    ['Cochin International Airport', 'Kochi', 'India'],
    ['Dabolim Airport', 'Goa', 'India'],
    ['Jaipur International Airport', 'Jaipur', 'India'],
    ['Chaudhary Charan Singh International Airport', 'Lucknow', 'India'],
    ['Chandigarh Airport', 'Chandigarh', 'India'],
    ['Devi Ahilya Bai Holkar Airport', 'Indore', 'India'],
    ['Biju Patnaik International Airport', 'Bhubaneswar', 'India'],
    ['Coimbatore International Airport', 'Coimbatore', 'India'],
    ['Mangalore International Airport', 'Mangalore', 'India'],
    ['Trivandrum International Airport', 'Trivandrum', 'India'],
    ['Visakhapatnam Airport', 'Vizag', 'India']
];

foreach ($airports as $airport) {
    $sql = "INSERT IGNORE INTO airport (airport_name, city, country) VALUES ('{$airport[0]}', '{$airport[1]}', '{$airport[2]}')";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✓ Added airport: {$airport[0]}</p>";
    }
}

// Sample flights
$flights = [
    ['AI101', 'Air India', '08:00:00', '10:30:00', 150, 'Bangalore', 'Delhi', 15000.00, 25000.00, 35000.00],
    ['SG202', 'SpiceJet', '14:30:00', '17:00:00', 120, 'Bangalore', 'Mumbai', 12000.00, 20000.00, 30000.00],
    ['6E303', 'IndiGo', '19:45:00', '22:15:00', 180, 'Bangalore', 'Chennai', 8000.00, 15000.00, 25000.00],
    ['AI404', 'Air India', '06:30:00', '09:00:00', 160, 'Delhi', 'Bangalore', 18000.00, 28000.00, 38000.00],
    ['SG505', 'SpiceJet', '12:00:00', '14:30:00', 140, 'Mumbai', 'Delhi', 13000.00, 22000.00, 32000.00],
    ['6E606', 'IndiGo', '16:15:00', '18:45:00', 200, 'Chennai', 'Mumbai', 9000.00, 16000.00, 26000.00],
    ['AI707', 'Air India', '10:00:00', '11:30:00', 120, 'Bangalore', 'Hyderabad', 7000.00, 12000.00, 20000.00],
    ['SG808', 'SpiceJet', '15:45:00', '18:15:00', 150, 'Delhi', 'Kolkata', 11000.00, 18000.00, 28000.00],
    ['6E909', 'IndiGo', '09:30:00', '11:00:00', 130, 'Mumbai', 'Pune', 5000.00, 8000.00, 15000.00],
    ['AI010', 'Air India', '13:15:00', '15:45:00', 140, 'Chennai', 'Kochi', 6000.00, 10000.00, 18000.00],
    ['SG111', 'SpiceJet', '07:00:00', '08:30:00', 110, 'Bangalore', 'Goa', 8000.00, 13000.00, 22000.00],
    ['6E212', 'IndiGo', '17:30:00', '19:00:00', 160, 'Delhi', 'Jaipur', 4000.00, 7000.00, 12000.00],
    ['AI313', 'Air India', '11:45:00', '13:15:00', 125, 'Mumbai', 'Ahmedabad', 5500.00, 9000.00, 16000.00],
    ['SG414', 'SpiceJet', '14:00:00', '16:30:00', 135, 'Chennai', 'Trivandrum', 4500.00, 7500.00, 14000.00],
    ['6E515', 'IndiGo', '08:30:00', '10:00:00', 145, 'Hyderabad', 'Bangalore', 6500.00, 11000.00, 19000.00],
    ['AI616', 'Air India', '16:45:00', '19:15:00', 155, 'Kolkata', 'Delhi', 12000.00, 20000.00, 30000.00],
    ['SG717', 'SpiceJet', '12:30:00', '14:00:00', 120, 'Pune', 'Mumbai', 4000.00, 6500.00, 11000.00],
    ['6E818', 'IndiGo', '10:15:00', '12:45:00', 140, 'Kochi', 'Chennai', 5500.00, 9000.00, 16000.00],
    ['AI919', 'Air India', '15:00:00', '16:30:00', 130, 'Goa', 'Bangalore', 7500.00, 12000.00, 21000.00],
    ['SG020', 'SpiceJet', '09:00:00', '10:30:00', 125, 'Jaipur', 'Delhi', 3500.00, 6000.00, 10000.00]
];

foreach ($flights as $flight) {
    $sql = "INSERT IGNORE INTO flights (flight_number, flight_company, departing_time, arrival_time, no_of_seats, source, destination, price_economy, price_business, price_first) 
            VALUES ('{$flight[0]}', '{$flight[1]}', '{$flight[2]}', '{$flight[3]}', {$flight[4]}, '{$flight[5]}', '{$flight[6]}', {$flight[7]}, {$flight[8]}, {$flight[9]})";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✓ Added flight: {$flight[1]} {$flight[0]}</p>";
    }
}

// Create seats for flights
echo "<h2>Creating Seat Maps</h2>";
$flight_result = mysqli_query($conn, "SELECT flight_number, no_of_seats FROM flights");
while ($flight = mysqli_fetch_assoc($flight_result)) {
    createSeatsForFlight($conn, $flight['flight_number'], $flight['no_of_seats']);
    echo "<p style='color: green;'>✓ Created seats for flight {$flight['flight_number']}</p>";
}

function createSeatsForFlight($conn, $flight_number, $total_seats) {
    $seats_per_row = 6;
    $rows = ceil($total_seats / $seats_per_row);
    
    for ($row = 1; $row <= $rows; $row++) {
        for ($col = 1; $col <= $seats_per_row; $col++) {
            if (($row - 1) * $seats_per_row + $col > $total_seats) break;
            
            $seat_letter = chr(64 + $col);
            $seat_number = $row . $seat_letter;
            
            $seat_class = 'Economy';
            if ($row <= 2) {
                $seat_class = 'First';
            } elseif ($row <= 4) {
                $seat_class = 'Business';
            }
            
            $insert_sql = "INSERT IGNORE INTO seats (flight_number, seat_number, seat_class, is_available, is_reserved) 
                          VALUES ('$flight_number', '$seat_number', '$seat_class', 1, 0)";
            mysqli_query($conn, $insert_sql);
        }
    }
}

// Sample employees
echo "<h2>Adding Sample Employees</h2>";
$employees = [
    ['EMP001', 'John Smith', 'Pilot', 150000, 1, 'AI101'],
    ['EMP002', 'Sarah Johnson', 'Flight Attendant', 80000, 1, 'AI101'],
    ['EMP003', 'Mike Davis', 'Ground Staff', 60000, 1, 'SG202'],
    ['EMP004', 'Lisa Wilson', 'Manager', 120000, 1, '6E303']
];

foreach ($employees as $emp) {
    $sql = "INSERT IGNORE INTO employee (emp_id, emp_name, job, salary, airport_id, flight_number) 
            VALUES ('{$emp[0]}', '{$emp[1]}', '{$emp[2]}', {$emp[3]}, {$emp[4]}, '{$emp[5]}')";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✓ Added employee: {$emp[1]}</p>";
    }
}

mysqli_close($conn);

echo "<h2>Setup Complete!</h2>";
echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3 style='color: #155724;'>✓ Database setup completed successfully!</h3>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>Open <a href='Frontpage.html'>Frontpage.html</a> to start using the system</li>";
echo "<li>Use the admin panel to manage flights and employees</li>";
echo "<li>Test the booking system with the sample data</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3 style='color: #856404;'>⚠ Important Notes:</h3>";
echo "<ul>";
echo "<li>This setup script should only be run once</li>";
echo "<li>For production use, change default database credentials</li>";
echo "<li>Ensure proper file permissions are set</li>";
echo "<li>Consider implementing additional security measures</li>";
echo "</ul>";
echo "</div>";
?>





