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
    <a href="Frontpage.html"><i class="margin fa fa-fw fa-home"></i> Home</a>
    <button class="dropdown-btn"><i class="margin fas fa-fighter-jet"></i>Flights
      <i class="fa fa-caret-down"></i></button>
    <div class="dropdown-container">
      <a href="#Flights">ADD</a>
      <a href="#removeflights">REMOVE</a>
    </div>
    <button class="dropdown-btn"><i class="margin fas fa-male"></i>Employee
      <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-container">
      <a href="#Employee">ADD</a>
      <a href="#removeemployee">REMOVE</a>
    </div>
    <a href="#contact"><i class="margin fa fa-fw fa-envelope"></i> Contact</a>
  </div>



  <section id="home">
    <div class="img-of-add">
      <img src="https://s3-prod.crainsdetroit.com/s3fs-public/Grand%20Rapids%20Airport_rendering_i.jpg" height="500px" width="1357px" alt="">

    </div>
  </section>
  <section id="full-admin">
    <section id="Flights">
      <div class="flights">
        <div class="column column1">
          <div class="card">
            <form action="Adminpagephp.php" method="post">

              <h1 class="plane">ADD PLANE</h1>

              <label>Flight Number</label>
              <input type="text" id="Flightnumber" name="flight_number" placeholder="enter flight number..">
              <label>Flight company</label>
              <input type="text" id="Flightcompany" name="flight_company" placeholder="enter Flight Comapny..">

              <label>Departing Time</label>
              <input type="text" id="DepartingTime" name="departing_time" placeholder="enter Departing time.. ">
              <label>Arrival Time</label>
              <input type="text" id="ArrivalTime" name="arrival_time" placeholder="enter Arrival Time..">
              <label>No of Seats</label>
              <input type="text" id="NoofSeats" name="no_of_seats" placeholder="enter No of seats..">
              <label>source</label>
              <input type="text" id="source" name="source" value="bangalore">
              <label>Destination</label>
              <input type="text" id="Destination" name="destination" placeholder="enter Destination..">


              <button class="button" onclick="return addplanefunction()" name="save">Add Plane</button>

            </form>

          </div>
        </div>
      </div>
      <div>
    </section>

    <section id="removeflights">

      <div class="card_card1">
        <form action="Adminpagephp.php" method="post">
          <h1 class="plane">REMOVE PLANE</h1>
                <label>Flight Number</label>
          <input type="text" id="remove_Flightnumber" name="flight_number" placeholder="enter flight number..">
          <button class="button" onclick="return removeplanefunction()" name="save2">Remove Plane</button>
          </form>

      </div>
    </section>
    <section id="Employee">


      <div class="column column2">
        <div style="height:800px;"class="card1">
          <form  action="Adminpagephp1.php" method="post">



            <h1 class="plane">ADD Employee</h1>


            <label>Employee ID</label>
            <input type="text" id="EmployeeID" name="emp_id" placeholder="enter employee id..">
            <label>Employee name</label>
            <input type="text" id="Employeename" name="emp_name" placeholder="enter employee name..">
            <label>Job</label>
            <input type="text" id="Job" name="job" placeholder="enter job..">
            <label>salary</label>
            <input type="text" id="salary" name="salary" placeholder="enter salary..">

              <label>Flight_number</label>
              <select id="Fruit" name="Fruit">
                <option disabled selected>---select flight number---</option>
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "airport_management_system";

                // Create connection
                $conn = mysqli_connect($servername, $username, $password, $dbname);
                // Check connection
                if (!$conn) {
                  die("Connection failed: " . mysqli_connect_error());
                }



                $records = mysqli_query($conn, "SELECT flight_number From flights");  // Use select query here

                while($data = mysqli_fetch_array($records))
                {
                    echo "<option value='". $data['flight_number'] ."'>" .$data['flight_number'] ."</option>";  // displaying data in option menu
                }

                  ?>


              </select>




            <button class="button" onclick="return addemployeefunction()" name="save1">Add Employee</button>


        </form>

        </div>
                </div>
              </div>
    </section>

    <section id="removeemployee">

      <div class="card_card1">
        <form action="Adminpagephp.php" method="post">
          <h1 class="plane">REMOVE EMPLOYEE</h1>
          <label>Employee ID</label>
          <input type="text" id="remove_EmployeeID" name="emp_id" placeholder="enter employee id..">
          <button class="button" onclick="return removeemployeefunction()" name="save3">Remove Employee </button>

            </form>

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
