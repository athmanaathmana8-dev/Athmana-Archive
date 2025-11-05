<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Plane</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <style>
    body { background:#0f75b3; }
    .wrap { max-width: 960px; margin: 30px auto; }
    .card { border: none; box-shadow: 0 15px 35px rgba(0,0,0,.25); }
    .card-header { background:#0b3f66; color:#fff; text-align:center; font-weight:700; font-size:1.75rem; }
    label { font-weight:600; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="card-header">ADD PLANE</div>
      <div class="card-body" style="background: url('https://images.unsplash.com/photo-1526779259212-939e64788e3c?q=80&w=1200&auto=format&fit=crop') center/cover no-repeat;">
        <form id="flightForm" class="p-3" onsubmit="event.preventDefault(); submitFlight();">
          <div class="form-group">
            <label>Flight Number</label>
            <input type="text" class="form-control form-control-lg" id="flight_number" required placeholder="e.g., AI101">
          </div>
          <div class="form-group">
            <label>Flight company</label>
            <input type="text" class="form-control form-control-lg" id="flight_company" required placeholder="e.g., Air India">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Departing Time</label>
              <input type="time" class="form-control form-control-lg" id="departing_time" required>
            </div>
            <div class="form-group col-md-6">
              <label>Arrival Time</label>
              <input type="time" class="form-control form-control-lg" id="arrival_time" required>
            </div>
          </div>
          <div class="form-group">
            <label>No of Seats</label>
            <input type="number" class="form-control form-control-lg" id="no_of_seats" min="1" max="400" required placeholder="e.g., 180">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Source</label>
              <input type="text" class="form-control form-control-lg" id="source" required placeholder="e.g., Bangalore">
            </div>
            <div class="form-group col-md-6">
              <label>Destination</label>
              <input type="text" class="form-control form-control-lg" id="destination" required placeholder="e.g., Delhi">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-4">
              <label>Price Economy</label>
              <input type="number" class="form-control form-control-lg" id="price_economy" min="0" step="0.01" required>
            </div>
            <div class="form-group col-md-4">
              <label>Price Business</label>
              <input type="number" class="form-control form-control-lg" id="price_business" min="0" step="0.01" required>
            </div>
            <div class="form-group col-md-4">
              <label>Price First</label>
              <input type="number" class="form-control form-control-lg" id="price_first" min="0" step="0.01" required>
            </div>
          </div>
          <div class="text-right">
            <button class="btn btn-primary btn-lg" type="submit"><i class="fas fa-plus-circle"></i> Add Flight</button>
          </div>
        </form>
      </div>
    </div>
    <div id="result" class="mt-3"></div>
  </div>

  <script src="https://kit.fontawesome.com/a2e0e9f6a3.js" crossorigin="anonymous"></script>
  <script>
    function collect() {
      return {
        flight_number: document.getElementById('flight_number').value.trim(),
        flight_company: document.getElementById('flight_company').value.trim(),
        departing_time: document.getElementById('departing_time').value,
        arrival_time: document.getElementById('arrival_time').value,
        no_of_seats: parseInt(document.getElementById('no_of_seats').value, 10),
        source: document.getElementById('source').value.trim(),
        destination: document.getElementById('destination').value.trim(),
        price_economy: parseFloat(document.getElementById('price_economy').value || 0),
        price_business: parseFloat(document.getElementById('price_business').value || 0),
        price_first: parseFloat(document.getElementById('price_first').value || 0)
      };
    }
    function submitFlight() {
      const data = collect();
      const btn = document.querySelector('button[type="submit"]');
      const old = btn.innerHTML; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...'; btn.disabled = true;

      fetch('api/add_flight.php', {
        method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)
      }).then(r => r.json()).then(resp => {
        const res = document.getElementById('result');
        if (resp.success) {
          res.innerHTML = `<div class="alert alert-success">Flight added successfully. Booking Reference not applicable. <strong>${data.flight_number}</strong></div>`;
          document.getElementById('flightForm').reset();
        } else {
          res.innerHTML = `<div class="alert alert-danger">${resp.error || 'Failed to add flight'}</div>`;
        }
      }).catch(e => {
        document.getElementById('result').innerHTML = `<div class="alert alert-danger">${e.message}</div>`;
      }).finally(() => { btn.innerHTML = old; btn.disabled = false; });
    }
  </script>
</body>
</html>

























