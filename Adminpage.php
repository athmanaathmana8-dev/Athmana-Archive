<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin main page</title>
  <link rel="stylesheet" href="css/Adminpage.css">

  <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>

</head>

<body>

  <div class="sidebar">
    <h2 class="new">Admin</h2>
    <button class="dropdown-btn"><i class="margin fas fa-fighter-jet"></i>Flights
      <i class="fa fa-caret-down"></i></button>
    <div class="dropdown-container">
      <a href="#Flights">ADD</a>
    </div>
    <button class="dropdown-btn"><i class="margin fas fa-male"></i>Employee
      <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-container">
      <a href="#Employee">ADD</a>
      <a href="#removeemployee">REMOVE</a>
    </div>
    <a href="#contact"><i class="margin fa fa-fw fa-envelope"></i> Contact</a>
    <a href="logout.php"><i class="margin fas fa-sign-out-alt"></i> Logout</a>
  </div>



  <section id="home">
    <div class="flights-section">
      <div class="section-header">
        <h1 class="section-title"><i class="fas fa-plane"></i> Flights Management</h1>
        <button class="btn-add" onclick="toggleAddFlightForm()"><i class="fas fa-plus"></i> Add New Flight</button>
      </div>

      <!-- Flight List Container -->
      <div id="flightList" class="flight-list-container">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Flight Number</th>
                <th>Company</th>
                <th>Source</th>
                <th>Destination</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Seats</th>
                <th>Economy</th>
                <th>Business</th>
                <th>First</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="homeFlightTableBody">
              <?php
              $servername = "localhost";
              $username = "root";
              $password = "";
              $dbname = "airport_management_system";

              $conn = mysqli_connect($servername, $username, $password, $dbname);
              $total_records = 0;

              if ($conn) {
                $records = mysqli_query($conn, "SELECT * FROM flights ORDER BY flight_number");
                
                if (mysqli_num_rows($records) > 0) {
                  $total_records = mysqli_num_rows($records);
                  
                  while($data = mysqli_fetch_array($records)) {
                    $flight_number_escaped = htmlspecialchars($data['flight_number'], ENT_QUOTES);
                    
                    echo "<tr data-flight-number=\"" . $flight_number_escaped . "\">";
                    echo "<td><strong>" . $data['flight_number'] . "</strong></td>";
                    echo "<td>" . htmlspecialchars($data['flight_company']) . "</td>";
                    echo "<td>" . htmlspecialchars($data['source']) . "</td>";
                    echo "<td>" . htmlspecialchars($data['destination']) . "</td>";
                    echo "<td>" . date('H:i', strtotime($data['departing_time'])) . "</td>";
                    echo "<td>" . date('H:i', strtotime($data['arrival_time'])) . "</td>";
                    echo "<td>" . $data['no_of_seats'] . "</td>";
                    echo "<td>₹" . number_format($data['price_economy'], 2) . "</td>";
                    echo "<td>₹" . number_format($data['price_business'], 2) . "</td>";
                    echo "<td>₹" . number_format($data['price_first'], 2) . "</td>";
                    echo "<td><button class='btn-remove' onclick='confirmRemove(\"" . $flight_number_escaped . "\")'><i class='fas fa-trash'></i> Remove</button></td>";
                    echo "</tr>";
                  }
                  } else {
                    echo "<tr><td colspan='11' class='no-data'>No flights found. Click 'Add New Flight' to add one.</td></tr>";
                  }
                mysqli_close($conn);
                } else {
                  echo "<tr><td colspan='11' class='error'>Database connection failed</td></tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Controls -->
      <?php if ($total_records > 10) { ?>
      <div class="pagination-container" style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 10px;">
        <button id="prevBtnHome" class="btn-pagination" onclick="changePageHome(-1)" disabled>
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <div class="pagination-info" style="display: flex; align-items: center; gap: 10px; padding: 0 15px;">
          <span id="pageInfoHome" style="color: #2c3e50; font-weight: 600;">Page 1 of 1</span>
        </div>
        <button id="nextBtnHome" class="btn-pagination" onclick="changePageHome(1)">
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      <?php } ?>
      
      <!-- Add Flight Form (Hidden by default) -->
      <div id="addFlightForm" class="add-form-container">
        <div class="form-card">
          <div class="form-header">
            <h2><i class="fas fa-plane"></i> Add New Flight</h2>
            <button class="btn-close" onclick="toggleAddFlightForm()"><i class="fas fa-times"></i></button>
          </div>
          
          <form action="Adminpagephp.php" method="post" class="flight-form">
            <div class="form-row">
              <div class="form-group">
                <label>Flight Number</label>
                <input type="text" id="Flightnumber" name="flight_number" placeholder="Enter flight number.." required>
                <div class="error-message" id="FlightnumberError"></div>
              </div>
              <div class="form-group">
                <label>Flight Company</label>
                <input type="text" id="Flightcompany" name="flight_company" placeholder="Enter flight company.." required>
                <div class="error-message" id="FlightcompanyError"></div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Source</label>
                <input type="text" id="source" name="source" placeholder="Enter source city.." value="Bangalore" required>
                <div class="error-message" id="sourceError"></div>
              </div>
              <div class="form-group">
                <label>Destination</label>
                <input type="text" id="Destination" name="destination" placeholder="Enter destination.." required>
                <div class="error-message" id="DestinationError"></div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Departing Time</label>
                <input type="time" id="DepartingTime" name="departing_time" placeholder="Enter departure time.." required>
                <div class="error-message" id="DepartingTimeError"></div>
              </div>
              <div class="form-group">
                <label>Arrival Time</label>
                <input type="time" id="ArrivalTime" name="arrival_time" placeholder="Enter arrival time.." required>
                <div class="error-message" id="ArrivalTimeError"></div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Number of Seats</label>
                <input type="number" id="NoofSeats" name="no_of_seats" placeholder="Enter number of seats.." required>
                <div class="error-message" id="NoofSeatsError"></div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Economy Class Price (₹)</label>
                <input type="number" step="0.01" id="priceEconomy" name="price_economy" placeholder="Enter economy price.." required>
                <div class="error-message" id="priceEconomyError"></div>
              </div>
              <div class="form-group">
                <label>Business Class Price (₹)</label>
                <input type="number" step="0.01" id="priceBusiness" name="price_business" placeholder="Enter business price.." required>
                <div class="error-message" id="priceBusinessError"></div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>First Class Price (₹)</label>
                <input type="number" step="0.01" id="priceFirst" name="price_first" placeholder="Enter first class price.." required>
                <div class="error-message" id="priceFirstError"></div>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-cancel" onclick="toggleAddFlightForm()">Cancel</button>
              <button type="submit" class="btn-submit" onclick="return addplanefunction()" name="save"><i class="fas fa-check"></i> Add Flight</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
  <section id="full-admin">

    <section id="Employee">
      <div class="employee-section">
        <div class="section-header">
          <h1 class="section-title"><i class="fas fa-users"></i> Employee Management</h1>
          <button class="btn-add" onclick="toggleAddEmployeeForm()"><i class="fas fa-user-plus"></i> Add New Employee</button>
        </div>

        <!-- Employee List Container -->
        <div id="employeeList" class="employee-list-container">
          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Employee ID</th>
                  <th>Name</th>
                  <th>Job Title</th>
                  <th>Salary</th>
                  <th>Flight Number</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="employeeTableBody">
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "airport_management_system";

                $conn2 = mysqli_connect($servername, $username, $password, $dbname);
                $total_emp_records = 0;

                if ($conn2) {
                  $emp_records = mysqli_query($conn2, "SELECT * FROM employee ORDER BY emp_id");
                  
                  if (mysqli_num_rows($emp_records) > 0) {
                    $total_emp_records = mysqli_num_rows($emp_records);
                    
                    while($emp_data = mysqli_fetch_array($emp_records)) {
                      echo "<tr>";
                      echo "<td><strong>" . htmlspecialchars($emp_data['emp_id']) . "</strong></td>";
                      echo "<td>" . htmlspecialchars($emp_data['emp_name']) . "</td>";
                      echo "<td>" . htmlspecialchars($emp_data['job']) . "</td>";
                      echo "<td>₹" . number_format($emp_data['salary'], 2) . "</td>";
                      echo "<td>" . htmlspecialchars($emp_data['flight_number'] ? $emp_data['flight_number'] : 'N/A') . "</td>";
                      echo "<td><button class='btn-remove' onclick='confirmRemoveEmployee(\"" . htmlspecialchars($emp_data['emp_id'], ENT_QUOTES) . "\")'><i class='fas fa-trash'></i> Remove</button></td>";
                      echo "</tr>";
                    }
                  } else {
                    echo "<tr><td colspan='6' class='no-data'>No employees found. Click 'Add New Employee' to add one.</td></tr>";
                  }
                  mysqli_close($conn2);
                } else {
                  echo "<tr><td colspan='6' class='error'>Database connection failed</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_emp_records > 10) { ?>
        <div class="pagination-container" style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 10px;">
          <button id="prevEmpBtn" class="btn-pagination" onclick="changeEmpPage(-1)" disabled>
            <i class="fas fa-chevron-left"></i> Previous
          </button>
          <div class="pagination-info" style="display: flex; align-items: center; gap: 10px; padding: 0 15px;">
            <span id="pageEmpInfo" style="color: #2c3e50; font-weight: 600;">Page 1 of 1</span>
          </div>
          <button id="nextEmpBtn" class="btn-pagination" onclick="changeEmpPage(1)">
            Next <i class="fas fa-chevron-right"></i>
          </button>
        </div>
        <?php } ?>

        <!-- Add Employee Form (Hidden by default) -->
        <div id="addEmployeeForm" class="add-form-container">
          <div class="form-card">
            <div class="form-header">
              <h2><i class="fas fa-user-plus"></i> Add New Employee</h2>
              <button class="btn-close" onclick="toggleAddEmployeeForm()"><i class="fas fa-times"></i></button>
            </div>

            <form action="Adminpagephp1.php" method="post" class="employee-form">
              <div class="form-row">
                <div class="form-group">
                  <label>Employee ID</label>
                  <input type="text" id="EmployeeID" name="emp_id" placeholder="Enter employee ID" required>
                </div>
                <div class="form-group">
                  <label>Employee Name</label>
                  <input type="text" id="Employeename" name="emp_name" placeholder="Enter employee name" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Job Title</label>
                  <input type="text" id="Job" name="job" placeholder="Enter job title" required>
                </div>
                <div class="form-group">
                  <label>Salary</label>
                  <input type="number" id="salary" name="salary" placeholder="Enter salary" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Assigned Flight Number</label>
                  <select id="Fruit" name="Fruit" required>
                    <option disabled selected>---Select Flight Number---</option>
                    <?php
                    $conn4 = mysqli_connect("localhost", "root", "", "airport_management_system");
                    if ($conn4) {
                      $flight_records = mysqli_query($conn4, "SELECT flight_number FROM flights ORDER BY flight_number");
                      while($flight_data = mysqli_fetch_array($flight_records)) {
                        echo "<option value='". htmlspecialchars($flight_data['flight_number']) ."'>" . htmlspecialchars($flight_data['flight_number']) ."</option>";
                      }
                      mysqli_close($conn4);
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="toggleAddEmployeeForm()">Cancel</button>
                <button type="submit" class="btn-submit" onclick="return addemployeefunction()" name="save1">
                  <i class="fas fa-check"></i> Add Employee
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>



    <section id="contact">
      <div>
        <h4 class="contact-section">If any query contact any one of below given numbers </h4>
        <h2>6736873879</h2>
        <h2>3624768833</h2>
        <i class=" icon-1 fab fa-instagram "></i>
        <i class=" icon-2 fab fa-whatsapp "></i>
        <i class="icon-3 fab fa-facebook"></i>
        <i class="icon-4 fab fa-twitter"></i>
      </div>


    </section>

  </section>
  <script src="Adminpage.js"></script>

</body>

</html>
