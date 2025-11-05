var dropdown = document.getElementsByClassName("dropdown-btn");
var i;

for (i = 0; i < dropdown.length; i++) {
  dropdown[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var dropdownContent = this.nextElementSibling;
    if (dropdownContent.style.display === "block") {
      dropdownContent.style.display = "none";
    } else {
      dropdownContent.style.display = "block";
    }
  });
}


// Helper function to show error message
function showFieldError(fieldId, message) {
  var field = document.getElementById(fieldId);
  var errorDiv = document.getElementById(fieldId + "Error");
  if (field) {
    field.classList.add("input-error");
  }
  if (errorDiv) {
    errorDiv.textContent = message;
    errorDiv.classList.add("show");
  }
}

// Helper function to clear error message
function clearFieldError(fieldId) {
  var field = document.getElementById(fieldId);
  var errorDiv = document.getElementById(fieldId + "Error");
  if (field) {
    field.classList.remove("input-error");
  }
  if (errorDiv) {
    errorDiv.textContent = "";
    errorDiv.classList.remove("show");
  }
}

function addplanefunction() {
  var isValid = true;
  var flightnumber = document.getElementById("Flightnumber").value.trim();
  var flightcompany = document.getElementById("Flightcompany").value.trim();
  var departingTime = document.getElementById("DepartingTime").value;
  var arrivalTime = document.getElementById("ArrivalTime").value;
  var source = document.getElementById("source").value.trim();
  var destination = document.getElementById("Destination").value.trim();
  var noofSeats = document.getElementById("NoofSeats").value;

  // Clear all errors first
  clearFieldError("Flightnumber");
  clearFieldError("Flightcompany");
  clearFieldError("source");
  clearFieldError("Destination");
  clearFieldError("DepartingTime");
  clearFieldError("ArrivalTime");
  clearFieldError("NoofSeats");
  clearFieldError("priceEconomy");
  clearFieldError("priceBusiness");
  clearFieldError("priceFirst");

  // Validate Flight Number
  if (flightnumber == "") {
    showFieldError("Flightnumber", "✈️ Flight number is required");
    document.getElementById("Flightnumber").focus();
    isValid = false;
  } else if (flightnumber.length < 2 || flightnumber.length > 10) {
    showFieldError("Flightnumber", "✈️ Flight number must be between 2 and 10 characters");
    document.getElementById("Flightnumber").focus();
    isValid = false;
  }

  // Validate Flight Company
  if (flightcompany == "") {
    showFieldError("Flightcompany", "🏢 Flight company name is required");
    if (isValid) document.getElementById("Flightcompany").focus();
    isValid = false;
  } else if (flightcompany.length < 2) {
    showFieldError("Flightcompany", "🏢 Flight company name must be at least 2 characters long");
    if (isValid) document.getElementById("Flightcompany").focus();
    isValid = false;
  }

  // Validate Source
  if (source == "") {
    showFieldError("source", "📍 Source city is required");
    if (isValid) document.getElementById("source").focus();
    isValid = false;
  } else if (!/^[a-zA-Z\s]+$/.test(source)) {
    showFieldError("source", "📍 Source city should contain only letters");
    if (isValid) document.getElementById("source").focus();
    isValid = false;
  }

  // Validate Destination
  if (destination == "") {
    showFieldError("Destination", "📍 Destination city is required");
    if (isValid) document.getElementById("Destination").focus();
    isValid = false;
  } else if (!/^[a-zA-Z\s]+$/.test(destination)) {
    showFieldError("Destination", "📍 Destination city should contain only letters");
    if (isValid) document.getElementById("Destination").focus();
    isValid = false;
  } else if (source.toLowerCase() === destination.toLowerCase()) {
    showFieldError("Destination", "⚠️ Source and destination cannot be the same");
    if (isValid) document.getElementById("Destination").focus();
    isValid = false;
  }

  // Validate Departing Time
  if (departingTime == "") {
    showFieldError("DepartingTime", "🕐 Departing time is required");
    if (isValid) document.getElementById("DepartingTime").focus();
    isValid = false;
  }

  // Validate Arrival Time
  if (arrivalTime == "") {
    showFieldError("ArrivalTime", "🕐 Arrival time is required");
    if (isValid) document.getElementById("ArrivalTime").focus();
    isValid = false;
  } else if (departingTime && arrivalTime) {
    var depTime = new Date('1970/01/01 ' + departingTime);
    var arrTime = new Date('1970/01/01 ' + arrivalTime);
    
    if (arrTime <= depTime) {
      showFieldError("ArrivalTime", "⏰ Arrival time must be after departing time");
      if (isValid) document.getElementById("ArrivalTime").focus();
      isValid = false;
    }
  }

  // Validate Number of Seats
  if (noofSeats == "") {
    showFieldError("NoofSeats", "💺 Number of seats is required");
    if (isValid) document.getElementById("NoofSeats").focus();
    isValid = false;
  } else if (isNaN(noofSeats) || noofSeats <= 0) {
    showFieldError("NoofSeats", "💺 Number of seats must be a positive number");
    if (isValid) document.getElementById("NoofSeats").focus();
    isValid = false;
  } else if (noofSeats < 1 || noofSeats > 1000) {
    showFieldError("NoofSeats", "💺 Number of seats must be between 1 and 1000");
    if (isValid) document.getElementById("NoofSeats").focus();
    isValid = false;
  }

  // Validate Economy Price
  var priceEconomy = document.getElementById("priceEconomy").value;
  if (priceEconomy == "") {
    showFieldError("priceEconomy", "💰 Economy class price is required");
    if (isValid) document.getElementById("priceEconomy").focus();
    isValid = false;
  } else if (isNaN(priceEconomy) || priceEconomy <= 0) {
    showFieldError("priceEconomy", "💰 Economy price must be a positive number");
    if (isValid) document.getElementById("priceEconomy").focus();
    isValid = false;
  }

  // Validate Business Price
  var priceBusiness = document.getElementById("priceBusiness").value;
  if (priceBusiness == "") {
    showFieldError("priceBusiness", "💰 Business class price is required");
    if (isValid) document.getElementById("priceBusiness").focus();
    isValid = false;
  } else if (isNaN(priceBusiness) || priceBusiness <= 0) {
    showFieldError("priceBusiness", "💰 Business price must be a positive number");
    if (isValid) document.getElementById("priceBusiness").focus();
    isValid = false;
  }

  // Validate First Class Price
  var priceFirst = document.getElementById("priceFirst").value;
  if (priceFirst == "") {
    showFieldError("priceFirst", "💰 First class price is required");
    if (isValid) document.getElementById("priceFirst").focus();
    isValid = false;
  } else if (isNaN(priceFirst) || priceFirst <= 0) {
    showFieldError("priceFirst", "💰 First class price must be a positive number");
    if (isValid) document.getElementById("priceFirst").focus();
    isValid = false;
  }

  // Validate price hierarchy (First > Business > Economy)
  if (priceEconomy && priceBusiness && parseFloat(priceBusiness) <= parseFloat(priceEconomy)) {
    showFieldError("priceBusiness", "⚠️ Business class price must be higher than Economy class price");
    if (isValid) document.getElementById("priceBusiness").focus();
    isValid = false;
  }
  if (priceBusiness && priceFirst && parseFloat(priceFirst) <= parseFloat(priceBusiness)) {
    showFieldError("priceFirst", "⚠️ First class price must be higher than Business class price");
    if (isValid) document.getElementById("priceFirst").focus();
    isValid = false;
  }

  if (!isValid) {
    return false;
  }

  // All validations passed
  console.log('All validations passed');
  return true;
}

// Validation function for Home section form
function addplanefunctionHome() {
  var isValid = true;
  var flightnumber = document.getElementById("FlightnumberHome").value.trim();
  var flightcompany = document.getElementById("FlightcompanyHome").value.trim();
  var departingTime = document.getElementById("DepartingTimeHome").value;
  var arrivalTime = document.getElementById("ArrivalTimeHome").value;
  var source = document.getElementById("sourceHome").value.trim();
  var destination = document.getElementById("DestinationHome").value.trim();
  var noofSeats = document.getElementById("NoofSeatsHome").value;

  // Clear all errors first
  clearFieldError("FlightnumberHome");
  clearFieldError("FlightcompanyHome");
  clearFieldError("sourceHome");
  clearFieldError("DestinationHome");
  clearFieldError("DepartingTimeHome");
  clearFieldError("ArrivalTimeHome");
  clearFieldError("NoofSeatsHome");
  clearFieldError("priceEconomyHome");
  clearFieldError("priceBusinessHome");
  clearFieldError("priceFirstHome");

  // Validate Flight Number
  if (flightnumber == "") {
    showFieldError("FlightnumberHome", "✈️ Flight number is required");
    document.getElementById("FlightnumberHome").focus();
    isValid = false;
  } else if (flightnumber.length < 2 || flightnumber.length > 10) {
    showFieldError("FlightnumberHome", "✈️ Flight number must be between 2 and 10 characters");
    document.getElementById("FlightnumberHome").focus();
    isValid = false;
  }

  // Validate Flight Company
  if (flightcompany == "") {
    showFieldError("FlightcompanyHome", "🏢 Flight company name is required");
    if (isValid) document.getElementById("FlightcompanyHome").focus();
    isValid = false;
  } else if (flightcompany.length < 2) {
    showFieldError("FlightcompanyHome", "🏢 Flight company name must be at least 2 characters long");
    if (isValid) document.getElementById("FlightcompanyHome").focus();
    isValid = false;
  }

  // Validate Source
  if (source == "") {
    showFieldError("sourceHome", "📍 Source city is required");
    if (isValid) document.getElementById("sourceHome").focus();
    isValid = false;
  } else if (!/^[a-zA-Z\s]+$/.test(source)) {
    showFieldError("sourceHome", "📍 Source city should contain only letters");
    if (isValid) document.getElementById("sourceHome").focus();
    isValid = false;
  }

  // Validate Destination
  if (destination == "") {
    showFieldError("DestinationHome", "📍 Destination city is required");
    if (isValid) document.getElementById("DestinationHome").focus();
    isValid = false;
  } else if (!/^[a-zA-Z\s]+$/.test(destination)) {
    showFieldError("DestinationHome", "📍 Destination city should contain only letters");
    if (isValid) document.getElementById("DestinationHome").focus();
    isValid = false;
  } else if (source.toLowerCase() === destination.toLowerCase()) {
    showFieldError("DestinationHome", "⚠️ Source and destination cannot be the same");
    if (isValid) document.getElementById("DestinationHome").focus();
    isValid = false;
  }

  // Validate Departing Time
  if (departingTime == "") {
    showFieldError("DepartingTimeHome", "🕐 Departing time is required");
    if (isValid) document.getElementById("DepartingTimeHome").focus();
    isValid = false;
  }

  // Validate Arrival Time
  if (arrivalTime == "") {
    showFieldError("ArrivalTimeHome", "🕐 Arrival time is required");
    if (isValid) document.getElementById("ArrivalTimeHome").focus();
    isValid = false;
  } else if (departingTime && arrivalTime) {
    var depTime = new Date('1970/01/01 ' + departingTime);
    var arrTime = new Date('1970/01/01 ' + arrivalTime);
    
    if (arrTime <= depTime) {
      showFieldError("ArrivalTimeHome", "⏰ Arrival time must be after departing time");
      if (isValid) document.getElementById("ArrivalTimeHome").focus();
      isValid = false;
    }
  }

  // Validate Number of Seats
  if (noofSeats == "") {
    showFieldError("NoofSeatsHome", "💺 Number of seats is required");
    if (isValid) document.getElementById("NoofSeatsHome").focus();
    isValid = false;
  } else if (isNaN(noofSeats) || noofSeats <= 0) {
    showFieldError("NoofSeatsHome", "💺 Number of seats must be a positive number");
    if (isValid) document.getElementById("NoofSeatsHome").focus();
    isValid = false;
  } else if (noofSeats < 1 || noofSeats > 1000) {
    showFieldError("NoofSeatsHome", "💺 Number of seats must be between 1 and 1000");
    if (isValid) document.getElementById("NoofSeatsHome").focus();
    isValid = false;
  }

  // Validate Economy Price
  var priceEconomy = document.getElementById("priceEconomyHome").value;
  if (priceEconomy == "") {
    showFieldError("priceEconomyHome", "💰 Economy class price is required");
    if (isValid) document.getElementById("priceEconomyHome").focus();
    isValid = false;
  } else if (isNaN(priceEconomy) || priceEconomy <= 0) {
    showFieldError("priceEconomyHome", "💰 Economy price must be a positive number");
    if (isValid) document.getElementById("priceEconomyHome").focus();
    isValid = false;
  }

  // Validate Business Price
  var priceBusiness = document.getElementById("priceBusinessHome").value;
  if (priceBusiness == "") {
    showFieldError("priceBusinessHome", "💰 Business class price is required");
    if (isValid) document.getElementById("priceBusinessHome").focus();
    isValid = false;
  } else if (isNaN(priceBusiness) || priceBusiness <= 0) {
    showFieldError("priceBusinessHome", "💰 Business price must be a positive number");
    if (isValid) document.getElementById("priceBusinessHome").focus();
    isValid = false;
  }

  // Validate First Class Price
  var priceFirst = document.getElementById("priceFirstHome").value;
  if (priceFirst == "") {
    showFieldError("priceFirstHome", "💰 First class price is required");
    if (isValid) document.getElementById("priceFirstHome").focus();
    isValid = false;
  } else if (isNaN(priceFirst) || priceFirst <= 0) {
    showFieldError("priceFirstHome", "💰 First class price must be a positive number");
    if (isValid) document.getElementById("priceFirstHome").focus();
    isValid = false;
  }

  // Validate price hierarchy (First > Business > Economy)
  if (priceEconomy && priceBusiness && parseFloat(priceBusiness) <= parseFloat(priceEconomy)) {
    showFieldError("priceBusinessHome", "⚠️ Business class price must be higher than Economy class price");
    if (isValid) document.getElementById("priceBusinessHome").focus();
    isValid = false;
  }
  if (priceBusiness && priceFirst && parseFloat(priceFirst) <= parseFloat(priceBusiness)) {
    showFieldError("priceFirstHome", "⚠️ First class price must be higher than Business class price");
    if (isValid) document.getElementById("priceFirstHome").focus();
    isValid = false;
  }

  if (!isValid) {
    return false;
  }

  // All validations passed
  console.log('All validations passed for home form');
  return true;
}


function removeplanefunction() {
  var flightnumber = document.getElementById("remove_Flightnumber").value;
  if (flightnumber == "") {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Flight number required", 'warning');
    } else {
      alert("Flight number required");
    }
    return false
  }
}

// Toggle Add Flight Form
function toggleAddForm() {
  // Determine which section is currently visible
  var homeSection = document.getElementById('home');
  var flightsSection = document.getElementById('Flights');
  
  var formContainer = null;
  var listContainer = document.getElementById('flightList');
  
  // Check which section is visible and use the corresponding form
  if (homeSection && homeSection.style.display !== 'none') {
    formContainer = document.getElementById('addFlightFormHome');
  } else if (flightsSection && flightsSection.style.display !== 'none') {
    formContainer = document.getElementById('addFlightForm');
  } else {
    // Fallback: try to find any available form
    formContainer = document.getElementById('addFlightForm') || document.getElementById('addFlightFormHome');
  }
  
  if (!formContainer) {
    console.error('Form container not found');
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar('Error: Form container not found. Please refresh the page.', 'error');
    } else {
      alert('Error: Form container not found. Please refresh the page.');
    }
    return;
  }
  
  // Toggle the show class for smooth animation
  if (formContainer.classList.contains('show')) {
    formContainer.classList.remove('show');
    // Scroll to list view
    setTimeout(function() {
      if (listContainer) {
        listContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }, 200);
  } else {
    formContainer.classList.add('show');
    // Scroll to form smoothly
    setTimeout(function() {
      formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 200);
  }
}

// Confirm Remove Flight
function confirmRemove(flightNumber) {
  if (confirm('Are you sure you want to remove this flight?')) {
    console.log('Attempting to delete flight:', flightNumber);
    
    // Send AJAX request to delete the flight
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'Adminpagephp.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        console.log('Response status:', xhr.status);
        console.log('Response text:', xhr.responseText);
        
        if (xhr.status === 200) {
          var response = xhr.responseText.trim();
          
          // Check if deletion was successful
          if (response === 'SUCCESS' || response.indexOf('SUCCESS') !== -1) {
            console.log('Deletion successful, removing row');
            // Remove the row from the table
            var rows = document.querySelectorAll('#flightTableBody tr');
            for (var i = 0; i < rows.length; i++) {
              var firstCell = rows[i].querySelector('td strong');
              if (firstCell && firstCell.textContent === flightNumber) {
                rows[i].style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(function(row) {
                  row.remove();
                  // Check if table is empty
                  var remainingRows = document.querySelectorAll('#flightTableBody tr');
                  if (remainingRows.length === 0) {
                    document.getElementById('flightTableBody').innerHTML = 
                      '<tr><td colspan="8" class="no-data">No flights found. Click \'Add New Flight\' to add one.</td></tr>';
                  }
                }, 500, rows[i]);
                break;
              }
            }
            if (typeof showSnackbar !== 'undefined') {
              showSnackbar('Flight removed successfully!', 'success');
            } else {
              alert('Flight removed successfully!');
            }
          } else {
            console.error('Delete failed. Response:', response);
            if (typeof showSnackbar !== 'undefined') {
              showSnackbar('Error: Could not remove flight. Please try again. Details: ' + response, 'error', 6000);
            } else {
              alert('Error: Could not remove flight. Please try again.\n\nDetails: ' + response);
            }
          }
        } else {
          if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Error: Server request failed. Status: ' + xhr.status, 'error');
          } else {
            alert('Error: Server request failed. Status: ' + xhr.status);
          }
        }
      }
    };
    
    xhr.send('flight_number=' + encodeURIComponent(flightNumber) + '&save2=Remove');
  }
}

// Close dropdown after clicking link
function closeDropdown() {
  var dropdowns = document.getElementsByClassName("dropdown-container");
  for (var i = 0; i < dropdowns.length; i++) {
    dropdowns[i].style.display = "none";
  }
  
  var buttons = document.getElementsByClassName("dropdown-btn");
  for (var i = 0; i < buttons.length; i++) {
    buttons[i].classList.remove("active");
  }
}

// Show specific section and hide others
function showSection(sectionId) {
  // Hide all sections
  var homeSection = document.getElementById('home');
  var flightsSection = document.getElementById('Flights');
  var employeeSection = document.getElementById('Employee');
  
  if (homeSection) homeSection.style.display = 'none';
  if (flightsSection) flightsSection.style.display = 'none';
  if (employeeSection) employeeSection.style.display = 'none';
  
  // Show the selected section
  if (sectionId === 'home' && homeSection) {
    homeSection.style.display = 'block';
  } else if (sectionId === 'Flights' && flightsSection) {
    flightsSection.style.display = 'block';
  } else if (sectionId === 'Employee' && employeeSection) {
    employeeSection.style.display = 'block';
  }
  
  // Update active navigation state
  updateActiveNavigation(sectionId);
}

// Update active navigation highlight
function updateActiveNavigation(activeSection) {
  // Remove active class from all navigation links
  var navLinks = document.querySelectorAll('.sidebar a');
  navLinks.forEach(function(link) {
    link.classList.remove('active');
  });
  
  // Add active class to the current section's link
  if (activeSection === 'home') {
    var homeLink = document.querySelector('.sidebar a[href="#home"]');
    if (homeLink) homeLink.classList.add('active');
  } else if (activeSection === 'Flights') {
    var flightsLink = document.querySelector('.sidebar a[href="#Flights"]');
    if (flightsLink) flightsLink.classList.add('active');
  } else if (activeSection === 'Employee') {
    var employeeLink = document.querySelector('.sidebar a[href="#Employee"]');
    if (employeeLink) employeeLink.classList.add('active');
  }
}

// Remove dropdown functionality since we're using direct links now
// The dropdown code can stay but won't be used

// Pagination system
var currentPage = 1;
var itemsPerPage = 10;

// Initialize pagination on page load
function initPagination() {
  var tableBody = document.getElementById('flightTableBody');
  if (!tableBody) return;
  
  var allRows = tableBody.getElementsByTagName('tr');
  var totalRows = allRows.length;
  
  // Calculate total pages
  var totalPages = Math.ceil(totalRows / itemsPerPage);
  
  // Store total pages globally
  window.totalPages = totalPages;
  
  // Update page info
  updatePaginationInfo();
  
  // Show first page
  showPage(1);
}

// Show specific page
function showPage(page) {
  var tableBody = document.getElementById('flightTableBody');
  if (!tableBody) return;
  
  var allRows = tableBody.getElementsByTagName('tr');
  var startIndex = (page - 1) * itemsPerPage;
  var endIndex = startIndex + itemsPerPage;
  
  // Hide all rows first
  for (var i = 0; i < allRows.length; i++) {
    allRows[i].style.display = 'none';
  }
  
  // Show rows for current page (but respect user-hidden rows)
  for (var i = startIndex; i < endIndex && i < allRows.length; i++) {
    // Only show if not hidden by user
    if (allRows[i].dataset.userHidden !== 'true') {
      allRows[i].style.display = '';
      allRows[i].style.animation = 'fadeIn 0.3s ease-in';
    }
  }
  
  // Update current page
  currentPage = page;
  updatePaginationInfo();
  
  // Enable/disable navigation buttons
  var prevBtn = document.getElementById('prevBtn');
  var nextBtn = document.getElementById('nextBtn');
  
  if (prevBtn) {
    prevBtn.disabled = (currentPage === 1);
  }
  if (nextBtn) {
    nextBtn.disabled = (currentPage >= window.totalPages);
  }
}

// Change page (direction: -1 for previous, 1 for next)
function changePage(direction) {
  var newPage = currentPage + direction;
  
  if (newPage < 1) {
    newPage = 1;
  } else if (newPage > window.totalPages) {
    newPage = window.totalPages;
  }
  
  if (newPage !== currentPage) {
    showPage(newPage);
  }
}

// Update pagination info display
function updatePaginationInfo() {
  var pageInfo = document.getElementById('pageInfo');
  if (pageInfo && window.totalPages) {
    pageInfo.textContent = 'Page ' + currentPage + ' of ' + window.totalPages;
  }
}

// Pagination system for Home section
var currentPageHome = 1;
var itemsPerPageHome = 10;

// Initialize pagination for home section
function initPaginationHome() {
  var tableBody = document.getElementById('flightTableBodyHome');
  if (!tableBody) return;
  
  var allRows = tableBody.getElementsByTagName('tr');
  var totalRows = allRows.length;
  
  // Calculate total pages
  var totalPages = Math.ceil(totalRows / itemsPerPageHome);
  
  // Store total pages globally
  window.totalPagesHome = totalPages;
  
  // Update page info
  updatePaginationInfoHome();
  
  // Show first page
  showPageHome(1);
}

// Show specific page for home section
function showPageHome(page) {
  var tableBody = document.getElementById('flightTableBodyHome');
  if (!tableBody) return;
  
  var allRows = tableBody.getElementsByTagName('tr');
  var startIndex = (page - 1) * itemsPerPageHome;
  var endIndex = startIndex + itemsPerPageHome;
  
  // Hide all rows first
  for (var i = 0; i < allRows.length; i++) {
    allRows[i].style.display = 'none';
  }
  
  // Show rows for current page
  for (var i = startIndex; i < endIndex && i < allRows.length; i++) {
    if (allRows[i].dataset.userHidden !== 'true') {
      allRows[i].style.display = '';
      allRows[i].style.animation = 'fadeIn 0.3s ease-in';
    }
  }
  
  // Update current page
  currentPageHome = page;
  updatePaginationInfoHome();
  
  // Enable/disable navigation buttons
  var prevBtn = document.getElementById('prevBtnHome');
  var nextBtn = document.getElementById('nextBtnHome');
  
  if (prevBtn) {
    prevBtn.disabled = (currentPageHome === 1);
  }
  if (nextBtn) {
    nextBtn.disabled = (currentPageHome >= window.totalPagesHome);
  }
}

// Change page for home section (direction: -1 for previous, 1 for next)
function changePageHome(direction) {
  var newPage = currentPageHome + direction;
  
  if (newPage < 1) {
    newPage = 1;
  } else if (newPage > window.totalPagesHome) {
    newPage = window.totalPagesHome;
  }
  
  if (newPage !== currentPageHome) {
    showPageHome(newPage);
  }
}

// Update pagination info display for home section
function updatePaginationInfoHome() {
  var pageInfo = document.getElementById('pageInfoHome');
  if (pageInfo && window.totalPagesHome) {
    pageInfo.textContent = 'Page ' + currentPageHome + ' of ' + window.totalPagesHome;
  }
}



// Toggle Add Employee Form
function toggleAddEmployeeForm() {
  var formContainer = document.getElementById('addEmployeeForm');
  var listContainer = document.getElementById('employeeList');
  
  if (!formContainer) {
    console.error('Employee form container not found');
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar('Error: Form container not found. Please refresh the page.', 'error');
    } else {
      alert('Error: Form container not found. Please refresh the page.');
    }
    return;
  }
  
  // Toggle the show class for smooth animation
  if (formContainer.classList.contains('show')) {
    formContainer.classList.remove('show');
    // Scroll to list view
    setTimeout(function() {
      if (listContainer) {
        listContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }, 200);
  } else {
    formContainer.classList.add('show');
    // Scroll to form smoothly
    setTimeout(function() {
      formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 200);
  }
}

// Confirm Remove Employee
function confirmRemoveEmployee(empId) {
  if (confirm('Are you sure you want to remove this employee?')) {
    console.log('Attempting to delete employee:', empId);
    
    // Send AJAX request to delete the employee
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'Adminpagephp.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        console.log('Response status:', xhr.status);
        console.log('Response text:', xhr.responseText);
        
        if (xhr.status === 200) {
          var response = xhr.responseText.trim();
          
          // Check if deletion was successful
          if (response === 'SUCCESS' || response.indexOf('SUCCESS') !== -1) {
            console.log('Deletion successful, removing row');
            // Remove the row from the table
            var rows = document.querySelectorAll('#employeeTableBody tr');
            for (var i = 0; i < rows.length; i++) {
              var firstCell = rows[i].querySelector('td strong');
              if (firstCell && firstCell.textContent === empId) {
                rows[i].style.animation = 'fadeOut 0.5s ease-out';
                setTimeout(function(row) {
                  row.remove();
                  // Check if table is empty
                  var remainingRows = document.querySelectorAll('#employeeTableBody tr');
                  if (remainingRows.length === 0) {
                    document.getElementById('employeeTableBody').innerHTML = 
                      '<tr><td colspan="6" class="no-data">No employees found. Click \'Add New Employee\' to add one.</td></tr>';
                  }
                }, 500, rows[i]);
                break;
              }
            }
            if (typeof showSnackbar !== 'undefined') {
              showSnackbar('Employee removed successfully!', 'success');
            } else {
              alert('Employee removed successfully!');
            }
          } else {
            console.error('Delete failed. Response:', response);
            if (typeof showSnackbar !== 'undefined') {
              showSnackbar('Error: Could not remove employee. Please try again. Details: ' + response, 'error', 6000);
            } else {
              alert('Error: Could not remove employee. Please try again.\n\nDetails: ' + response);
            }
          }
        } else {
          if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Error: Server request failed. Status: ' + xhr.status, 'error');
          } else {
            alert('Error: Server request failed. Status: ' + xhr.status);
          }
        }
      }
    };
    
    xhr.send('emp_id=' + encodeURIComponent(empId) + '&save3=Remove');
  }
}

// Toggle Select All Flights
function toggleSelectAll() {
  var selectAllCheckbox = document.getElementById('selectAllFlights');
  var checkboxes = document.querySelectorAll('.flight-checkbox');
  
  checkboxes.forEach(function(checkbox) {
    checkbox.checked = selectAllCheckbox.checked;
  });
  
  updateSelectedCount();
}

// Update Selected Count and Show/Hide Delete Button
function updateSelectedCount() {
  var checkboxes = document.querySelectorAll('.flight-checkbox:checked');
  var count = checkboxes.length;
  var deleteBtn = document.getElementById('deleteSelectedBtn');
  var selectedCountSpan = document.getElementById('selectedCount');
  
  if (selectedCountSpan) {
    selectedCountSpan.textContent = count;
  }
  
  if (deleteBtn) {
    if (count > 0) {
      deleteBtn.style.display = 'block';
    } else {
      deleteBtn.style.display = 'none';
    }
  }
  
  // Update "Select All" checkbox state
  var selectAllCheckbox = document.getElementById('selectAllFlights');
  var allCheckboxes = document.querySelectorAll('.flight-checkbox');
  
  if (selectAllCheckbox && allCheckboxes.length > 0) {
    var allChecked = Array.from(allCheckboxes).every(function(cb) {
      return cb.checked;
    });
    selectAllCheckbox.checked = allChecked;
  }
}

// Delete Selected Flights
function deleteSelectedFlights() {
  var checkboxes = document.querySelectorAll('.flight-checkbox:checked');
  
  if (checkboxes.length === 0) {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar('Please select at least one flight to delete.', 'warning');
    } else {
      alert('Please select at least one flight to delete.');
    }
    return;
  }
  
  var flightNumbers = [];
  checkboxes.forEach(function(checkbox) {
    flightNumbers.push(checkbox.value);
  });
  
  var count = flightNumbers.length;
  var confirmMessage = 'Are you sure you want to delete ' + count + ' flight(s)?\n\nThis action cannot be undone.';
  
  if (!confirm(confirmMessage)) {
    return;
  }
  
  // Show loading state
  var deleteBtn = document.getElementById('deleteSelectedBtn');
  if (deleteBtn) {
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
  }
  
  // Send AJAX request to delete flights
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'Adminpagephp.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        var response = xhr.responseText.trim();
        
        if (response === 'SUCCESS' || response.indexOf('SUCCESS') !== -1) {
          // Check for partial success
          if (response.indexOf('PARTIAL_SUCCESS') !== -1) {
            if (typeof showSnackbar !== 'undefined') {
              showSnackbar('Warning: ' + response, 'warning', 6000);
            } else {
              alert('Warning: ' + response);
            }
            // Reload the page to refresh the list
            window.location.reload();
            return;
          }
          
          // Remove all selected rows with animation
          var selectedRows = [];
          checkboxes.forEach(function(checkbox) {
            var row = checkbox.closest('tr');
            if (row) {
              selectedRows.push(row);
            }
          });
          
          selectedRows.forEach(function(row) {
            row.style.animation = 'fadeOut 0.5s ease-out';
            setTimeout(function() {
              row.remove();
            }, 500);
          });
          
          // Update the table after a delay
          setTimeout(function() {
            var remainingRows = document.querySelectorAll('#flightTableBody tr');
            if (remainingRows.length === 0) {
              document.getElementById('flightTableBody').innerHTML = 
                '<tr><td colspan="12" class="no-data">No flights found. Click \'Add New Flight\' to add one.</td></tr>';
            }
            
            // Reset select all checkbox
            var selectAllCheckbox = document.getElementById('selectAllFlights');
            if (selectAllCheckbox) {
              selectAllCheckbox.checked = false;
            }
            
            // Update UI
            updateSelectedCount();
            
            if (typeof showSnackbar !== 'undefined') {
              showSnackbar('Successfully deleted ' + count + ' flight(s)!', 'success');
            } else {
              alert('Successfully deleted ' + count + ' flight(s)!');
            }
            
            // Reload the page to refresh the list
            window.location.reload();
          }, 600);
          
        } else {
          if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Error: Could not delete flights. Details: ' + response, 'error', 6000);
          } else {
            alert('Error: Could not delete flights.\n\nDetails: ' + response);
          }
          
          // Reset button
          if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">' + count + '</span>)';
          }
        }
      } else {
        alert('Error: Server request failed. Status: ' + xhr.status);
        
        // Reset button
        if (deleteBtn) {
          deleteBtn.disabled = false;
          deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">' + count + '</span>)';
        }
      }
    }
  };
  
  // Send flight numbers as comma-separated list
  xhr.send('flight_numbers=' + encodeURIComponent(flightNumbers.join(',')) + '&bulk_delete=1');
}

// Add Employee Validation
function addemployeefunction() {
  var employeeID = document.getElementById("EmployeeID").value.trim();
  var employeename = document.getElementById("Employeename").value.trim();
  var job = document.getElementById("Job").value.trim();
  var salary = document.getElementById("salary").value;
  var flightNumber = document.getElementById("Fruit").value;

  // Validate Employee ID
  if (employeeID == "") {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Employee ID is required", 'warning');
    } else {
      alert("👤 Employee ID is required");
    }
    document.getElementById("EmployeeID").focus();
    return false;
  }
  if (employeeID.length < 2 || employeeID.length > 20) {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Employee ID must be between 2 and 20 characters", 'warning');
    } else {
      alert("👤 Employee ID must be between 2 and 20 characters");
    }
    document.getElementById("EmployeeID").focus();
    return false;
  }

  // Validate Employee Name
  if (employeename == "") {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Employee name is required", 'warning');
    } else {
      alert("👤 Employee name is required");
    }
    document.getElementById("Employeename").focus();
    return false;
  }
  if (employeename.length < 2) {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Employee name must be at least 2 characters long", 'warning');
    } else {
      alert("👤 Employee name must be at least 2 characters long");
    }
    document.getElementById("Employeename").focus();
    return false;
  }
  if (!/^[a-zA-Z\s]+$/.test(employeename)) {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Employee name should contain only letters", 'warning');
    } else {
      alert("👤 Employee name should contain only letters");
    }
    document.getElementById("Employeename").focus();
    return false;
  }

  // Validate Job
  if (job == "") {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Job title is required", 'warning');
    } else {
      alert("💼 Job title is required");
    }
    document.getElementById("Job").focus();
    return false;
  }
  if (job.length < 2) {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Job title must be at least 2 characters long", 'warning');
    } else {
      alert("💼 Job title must be at least 2 characters long");
    }
    document.getElementById("Job").focus();
    return false;
  }

  // Validate Salary
  if (salary == "") {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Salary is required", 'warning');
    } else {
      alert("💰 Salary is required");
    }
    document.getElementById("salary").focus();
    return false;
  }
  if (isNaN(salary) || salary <= 0) {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Salary must be a positive number", 'warning');
    } else {
      alert("💰 Salary must be a positive number");
    }
    document.getElementById("salary").focus();
    return false;
  }
  if (salary < 1000 || salary > 10000000) {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Salary must be between 1,000 and 10,000,000", 'warning');
    } else {
      alert("💰 Salary must be between 1,000 and 10,000,000");
    }
    document.getElementById("salary").focus();
    return false;
  }

  // Validate Flight Number
  if (flightNumber == "" || flightNumber == "---Select Flight Number---") {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar("Please select a flight number", 'warning');
    } else {
      alert("✈️ Please select a flight number");
    }
    document.getElementById("Fruit").focus();
    return false;
  }

  // All validations passed
  console.log('All employee validations passed');
  return true;
}

// Toggle Hide/Show Flight
function toggleHideFlight(flightNumber) {
  var tableBody = document.getElementById('flightTableBody');
  if (!tableBody) return;
  
  var rows = tableBody.getElementsByTagName('tr');
  var targetRow = null;
  
  // Find the row with the matching flight number
  for (var i = 0; i < rows.length; i++) {
    var firstCell = rows[i].querySelector('td strong');
    if (firstCell && firstCell.textContent === flightNumber) {
      targetRow = rows[i];
      break;
    }
  }
  
  if (!targetRow) {
    if (typeof showSnackbar !== 'undefined') {
      showSnackbar('Flight not found', 'warning');
    } else {
      alert('Flight not found');
    }
    return;
  }
  
  // Check if row is currently hidden by user or by pagination
  var isUserHidden = targetRow.dataset.userHidden === 'true';
  
  if (isUserHidden) {
    // Show the row
    targetRow.dataset.userHidden = 'false';
    
    // Only show if it's on current page
    var rowIndex = Array.from(tableBody.getElementsByTagName('tr')).indexOf(targetRow);
    var startIndex = (currentPage - 1) * itemsPerPage;
    var endIndex = startIndex + itemsPerPage;
    
    if (rowIndex >= startIndex && rowIndex < endIndex) {
      targetRow.style.display = '';
      targetRow.style.animation = 'fadeIn 0.5s ease-in';
    }
    
    // Update the button text
    var buttons = targetRow.querySelectorAll('button');
    for (var j = 0; j < buttons.length; j++) {
      if (buttons[j].innerHTML.includes('fa-eye')) {
        buttons[j].innerHTML = '<i class="fas fa-eye-slash"></i> Hide';
      }
    }
  } else {
    // Hide the row
    targetRow.dataset.userHidden = 'true';
    targetRow.style.display = 'none';
    
    // Update the button text
    var buttons = targetRow.querySelectorAll('button');
    for (var j = 0; j < buttons.length; j++) {
      if (buttons[j].innerHTML.includes('fa-eye-slash')) {
        buttons[j].innerHTML = '<i class="fas fa-eye"></i> Show';
      }
    }
  }
}

// Employee Pagination system
var currentEmpPage = 1;
var empItemsPerPage = 10;

// Initialize employee pagination on page load
function initEmpPagination() {
  var tableBody = document.getElementById('employeeTableBody');
  if (!tableBody) return;
  
  var allRows = tableBody.getElementsByTagName('tr');
  var totalRows = allRows.length;
  
  // Calculate total pages
  var totalPages = Math.ceil(totalRows / empItemsPerPage);
  
  // Store total pages globally
  window.totalEmpPages = totalPages;
  
  // Update page info
  updateEmpPaginationInfo();
  
  // Show first page
  showEmpPage(1);
}

// Show specific employee page
function showEmpPage(page) {
  var tableBody = document.getElementById('employeeTableBody');
  if (!tableBody) return;
  
  var allRows = tableBody.getElementsByTagName('tr');
  var startIndex = (page - 1) * empItemsPerPage;
  var endIndex = startIndex + empItemsPerPage;
  
  // Hide all rows first
  for (var i = 0; i < allRows.length; i++) {
    allRows[i].style.display = 'none';
  }
  
  // Show rows for current page
  for (var i = startIndex; i < endIndex && i < allRows.length; i++) {
    allRows[i].style.display = '';
    allRows[i].style.animation = 'fadeIn 0.3s ease-in';
  }
  
  // Update current page
  currentEmpPage = page;
  updateEmpPaginationInfo();
  
  // Enable/disable navigation buttons
  var prevBtn = document.getElementById('prevEmpBtn');
  var nextBtn = document.getElementById('nextEmpBtn');
  
  if (prevBtn) {
    prevBtn.disabled = (currentEmpPage === 1);
  }
  if (nextBtn) {
    nextBtn.disabled = (currentEmpPage >= window.totalEmpPages);
  }
}

// Change employee page (direction: -1 for previous, 1 for next)
function changeEmpPage(direction) {
  var newPage = currentEmpPage + direction;
  
  if (newPage < 1) {
    newPage = 1;
  } else if (newPage > window.totalEmpPages) {
    newPage = window.totalEmpPages;
  }
  
  if (newPage !== currentEmpPage) {
    showEmpPage(newPage);
  }
}

// Update employee pagination info display
function updateEmpPaginationInfo() {
  var pageInfo = document.getElementById('pageEmpInfo');
  if (pageInfo && window.totalEmpPages) {
    pageInfo.textContent = 'Page ' + currentEmpPage + ' of ' + window.totalEmpPages;
  }
}
