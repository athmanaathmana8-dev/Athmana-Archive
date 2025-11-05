<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flight Schedule</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="css/flightschedule.css">
    <style>
      /* Dark theme */
      body { background:#0f172a; color:#e2e8f0; }
      .page-header { padding: 30px 0 10px; background:#0b1320; border-bottom:1px solid #1f2937; }
      .page-header h3 { color:#1e40af; } /* dark blue */
      .page-header small { color:#2563eb; } /* dark blue accent */
      .card { background:#121826; box-shadow: 0 10px 30px rgba(0,0,0,.4); border: 1px solid #1f2937; }
      .table { color:#e2e8f0; }
      .table thead th { background:#1f2937; color:#e2e8f0; border-color:#334155; white-space:nowrap; }
      .table tbody td { border-color:#243043; }
      .table-striped tbody tr:nth-of-type(odd) { background:#0f172a; }
      .table-striped tbody tr:nth-of-type(even) { background:#111827; }
      .table-hover tbody tr:hover { background:#1b2436; color: white}
      a, .text-muted { color:#94a3b8 !important; }
      .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6) !important; }
    </style>
  </head>
  <body>
    <div class="container-fluid page-header bg-white mb-3 border-bottom">
      <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
          <h3 class="mb-0"><i class="fas fa-plane-departure mr-2"></i>Schedule of Flights</h3>
          <small class="text-muted">Live list of all flights in the system</small>
        </div>
        <a href="Frontpage.html" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 10px 25px; border-radius: 25px; color: white !important; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s;">
          <i class="fas fa-home mr-2"></i>Home
        </a>
      </div>
    </div>

    <div class="container mb-5">
      <div class="card">
        <div class="card-body p-0">
          <?php
$servername    = "localhost";
$username    = "root";
$password    = "";
$db_name = "airport_management_system";

//create connection
$conn = mysqli_connect($servername, $username, $password, $db_name);

//test if connection failed
if(!$conn){
  die("connection failed :" . mysqli_connect_error());
}

//get results from database
// fetch data
$result = mysqli_query($conn,"SELECT * FROM flights ORDER BY departing_time ASC");

if (!$result) {
    echo '<div class="p-4 text-danger">Failed to load flights.</div>';
} else {
    echo '<div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead class="thead-light">';
    // headers - exclude blocked columns
    echo '<tr>';
    $all_property = [];
    $excludedColumns = ['is_blocked', 'blocked_from', 'blocked_until'];
    while ($property = mysqli_fetch_field($result)) {
        // Skip excluded columns
        if (in_array($property->name, $excludedColumns)) {
            continue;
        }
        $displayName = ucwords(str_replace('_', ' ', $property->name));
        echo '<th scope="col">' . htmlspecialchars($displayName) . '</th>';
        $all_property[] = $property->name;
    }
    echo '</tr></thead><tbody>';

    // rows - exclude blocked columns
    mysqli_data_seek($result, 0);
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        foreach ($all_property as $item) {
            // Skip excluded columns
            if (in_array($item, $excludedColumns)) {
                continue;
            }
            $value = isset($row[$item]) ? htmlspecialchars((string)$row[$item]) : '';
            echo '<td>' . $value . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}

?>
        </div>
      </div>
    </div>

    <script src="https://kit.fontawesome.com/a2e0e9f6a3.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>



?>
