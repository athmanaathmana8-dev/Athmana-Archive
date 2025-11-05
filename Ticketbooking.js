// Enhanced Ticket Booking System JavaScript

let availableFlights = [];
let allFlights = [];
let selectedFlight = null;
let selectedSeat = null;
let seatPrices = {
  'Economy': 0,
  'Business': 0,
  'First': 0
};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('search_date').value = today;
    document.getElementById('Departing_date').value = today;
    
    // Add event listeners
    document.getElementById('selected_flight').addEventListener('change', onFlightSelect);
    document.getElementById('travel_class').addEventListener('change', onClassSelect);

    // Bind search form submit (if present)
    const flightSearchForm = document.getElementById('flightSearchForm');
    if (flightSearchForm) {
        flightSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            searchFlights();
        });
    }

    // Auto-search when criteria change (supports UI without a button)
    const searchFrom = document.getElementById('search_from');
    const searchTo = document.getElementById('search_to');
    const searchDate = document.getElementById('search_date');
    const debouncedSearch = debounce(() => searchFlights(), 300);
    if (searchFrom) searchFrom.addEventListener('change', debouncedSearch);
    if (searchTo) searchTo.addEventListener('change', debouncedSearch);
    if (searchDate) searchDate.addEventListener('change', debouncedSearch);

    // Prefill dropdown with a combined master list (mock) so users can pick any flight
    allFlights = getMockFlightsAll();
    populateFlightDropdown(allFlights);

    // Ensure the Selected Flight Details panel is visible (shows the dropdown)
    const resultsPanel = document.getElementById('searchResults');
    if (resultsPanel) {
        resultsPanel.style.display = 'block';
    }

    // Initial auto-search (will merge new results into the combined list)
    if (searchFrom && searchTo && searchDate) {
        searchFlights();
    }

    // bind travel class dropdown -> reuse existing handler if present
    const travelSelect = document.getElementById('travel_class');
    if (travelSelect) {
      travelSelect.addEventListener('change', function (e) {
        const selectedClass = e.target.value;

        // if you have onClassSelect(selectedClass) implemented, call it
        if (typeof onClassSelect === 'function') {
          try { onClassSelect(selectedClass); } catch (err) { /* ignore */ }
        } else {
          // fallback: update price label and toggle seat/payment sections
          if (typeof updatePrice === 'function') {
            updatePrice(selectedClass);
          } else {
            const priceEl = document.getElementById('classPrice');
            if (priceEl) priceEl.textContent = selectedClass ? '' : '';
          }

          // show/hide seat selection
          const seatSection = document.getElementById('seatSelection');
          if (seatSection) seatSection.style.display = selectedClass ? 'block' : 'none';

          // enable/disable proceed button
          if (typeof updateProceedEnabled === 'function') {
            updateProceedEnabled();
          } else {
            const proceedBtn = document.getElementById('proceedBtn');
            if (proceedBtn) proceedBtn.disabled = !selectedClass;
          }
        }

        // update small price hint if seatPrices object exists
        const priceHint = document.getElementById('classPrice');
        if (priceHint && typeof seatPrices === 'object') {
          const p = seatPrices[selectedClass] ?? '';
          priceHint.textContent = p ? `Estimated fare: ₹${p}` : '';
        }
      });
    }
});

// Search for available flights
function searchFlights() {
    const from = document.getElementById('search_from').value;
    const to = document.getElementById('search_to').value;
    const date = document.getElementById('search_date').value;
    
    if (!from || !to || !date) {
        return;
    }
    
    if (from === to) {
        return;
    }
    
    // Show loading state on button (if present)
    const btn = document.querySelector('#flightSearchForm button');
    const original = btn ? btn.innerHTML : null;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
    }

    // Call API to fetch flights
    fetchFlights(from, to, date).finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    });
}

// Fetch flights from server
function fetchFlights(from, to, date) {
    // Try relative endpoint first, then fall back to absolute (legacy). If both fail, use mock data.
    const endpoints = [
        'search_flights.php',
        '/airport_management/search_flights.php'
    ];

    const payload = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ from, to, date })
    };

    return tryFetchSequential(endpoints, payload)
        .then(data => {
            if (!data || data.error || !Array.isArray(data.flights)) {
                throw new Error(data && data.error ? data.error : 'Invalid response');
            }
            availableFlights = data.flights;
            // Merge into global combined list and repopulate dropdown
            allFlights = mergeFlights(allFlights, availableFlights);
            displayFlights(availableFlights);
            populateFlightDropdown(allFlights);
        })
        .catch(err => {
            console.warn('Backend not available, using mock flights. Reason:', err && err.message ? err.message : err);
            const mock = getMockFlights(from, to);
            availableFlights = mock;
            allFlights = mergeFlights(allFlights, mock);
            displayFlights(mock);
            populateFlightDropdown(allFlights);
        });
}

// Display search results
function displayFlights(flights) {
    const resultsDiv = document.getElementById('selectedFlightInfo') || document.getElementById('flightResults');
    const searchResults = document.getElementById('searchResults');
    
    if (flights.length === 0) {
        resultsDiv.innerHTML = '<p class="text-center text-muted">No flights found for the selected criteria.</p>';
    } else {
        // For dropdown flow, don't render a card list; keep the details prompt
        if (resultsDiv) {
            resultsDiv.innerHTML = '<p class="text-muted mb-0">Choose a flight from the dropdown to see details here.</p>';
        }
    }
    
    searchResults.style.display = 'block';
}

// Select a flight
function selectFlight(flightNumber) {
    console.log('selectFlight called with:', flightNumber);
    console.log('allFlights:', allFlights);
    
    selectedFlight = allFlights.find(f => f.flight_number === flightNumber);
    console.log('selectedFlight:', selectedFlight);
    
    // Update flight dropdown
    populateFlightDropdown(allFlights, flightNumber);
    
    // Update seat prices
    if (selectedFlight) {
        seatPrices = {
            'Economy': selectedFlight.price_economy,
            'Business': selectedFlight.price_business,
            'First': selectedFlight.price_first
        };
    }
    
    // Update details panel and show seat selection
    renderSelectedFlightDetails();
    showSeatSelection();

    // If a class is already selected, update price
    const travelClassDropdown = document.getElementById('travel_class');
    if (travelClassDropdown && travelClassDropdown.value) {
        updatePrice(travelClassDropdown.value);
        updateClassDescription(travelClassDropdown.value);
    }

    updateProceedEnabled();
    // Update travel class labels to include prices as chips
    updateClassPriceChips();
}

// Update class description and pricing
function updateClassDescription(selectedClass) {
    const dropdown = document.getElementById('travel_class');
    const descriptionEl = document.getElementById('classDescription');
    
    if (!dropdown || !descriptionEl) return;
    
    if (selectedClass && selectedFlight) {
        const price = seatPrices[selectedClass] || 0;
        const option = dropdown.querySelector(`option[value="${selectedClass}"]`);
        const description = option ? option.getAttribute('data-description') : '';
        
        // Update the option text with actual price
        if (option) {
            option.textContent = `${selectedClass} Class - ₹${price.toLocaleString('en-IN')} (${description})`;
        }
        
        // Update description
        descriptionEl.textContent = `${description} - Price: ₹${price.toLocaleString('en-IN')}`;
        descriptionEl.className = 'form-text text-success';
    } else {
        descriptionEl.textContent = 'Select a travel class to see pricing and details';
        descriptionEl.className = 'form-text text-muted';
    }
}

// Update price when class is selected
function updatePrice(selectedClass) {
    if (!selectedFlight) return;
    
    const priceMap = {
        'Economy': selectedFlight.price_economy || 0,
        'Business': selectedFlight.price_business || 0,
        'First': selectedFlight.price_first || 0
    };
    
    const price = priceMap[selectedClass] || 0;
    const totalAmountEl = document.getElementById('total_amount');
    if (totalAmountEl) {
        totalAmountEl.value = `₹${price.toLocaleString('en-IN')}`;
    }
}

// Update class price chips in dropdown
function updateClassPriceChips() {
    if (!selectedFlight) return;
    const dropdown = document.getElementById('travel_class');
    if (!dropdown) return;
    
    Array.from(dropdown.options).forEach(option => {
        if (option.value) {
            const priceMap = {
                'Economy': selectedFlight.price_economy || 0,
                'Business': selectedFlight.price_business || 0,
                'First': selectedFlight.price_first || 0
            };
            const price = priceMap[option.value] || 0;
            if (price > 0) {
                const desc = option.getAttribute('data-description') || '';
                option.textContent = `${option.value} Class - ₹${price.toLocaleString('en-IN')}${desc ? ' (' + desc + ')' : ''}`;
            }
        }
    });
}

// Show seat selection
function showSeatSelection() {
    const seatSection = document.getElementById('seatSelection');
    if (seatSection) seatSection.style.display = 'block';
    const travelClassSection = document.getElementById('travelClassSection');
    if (travelClassSection) travelClassSection.style.display = 'block';
    generateSeatMap();
}

// Generate seat map
function generateSeatMap() {
    if (!selectedFlight) {
        return;
    }
    
    const seatMap = document.getElementById('seatMap');
    seatMap.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading seat map...</div>';
    
    // Fetch seat data from server
    fetch('seat_reservation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_seats',
            flight_number: selectedFlight.flight_number
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            // Fallback to mock seat map
            const mockSeats = buildMockSeats();
            displaySeatMap(mockSeats);
            return;
        }
        
        displaySeatMap(data.seats);
    })
    .catch(error => {
        // Fallback to mock seat map
        const mockSeats = buildMockSeats();
        displaySeatMap(mockSeats);
    });
}

// Display seat map with real data
function displaySeatMap(seats) {
    const seatMap = document.getElementById('seatMap');
    
    let html = '<div class="seat-map-container">';
    html += '<div class="seat-legend mb-3">';
    html += '<span class="seat-available">Available</span> ';
    html += '<span class="seat-selected">Selected</span> ';
    html += '<span class="seat-occupied">Occupied</span> ';
    html += '<span class="seat-reserved">Reserved</span>';
    html += '</div>';
    
    // Group seats by class
    const economySeats = seats.filter(seat => seat.seat_class === 'Economy');
    const businessSeats = seats.filter(seat => seat.seat_class === 'Business');
    const firstSeats = seats.filter(seat => seat.seat_class === 'First');
    
    // Display First Class seats
    if (firstSeats.length > 0) {
        html += '<div class="seat-class-section mb-4">';
        html += '<h6 class="text-center text-primary">First Class</h6>';
        html += generateSeatGrid(firstSeats);
        html += '</div>';
    }
    
    // Display Business Class seats
    if (businessSeats.length > 0) {
        html += '<div class="seat-class-section mb-4">';
        html += '<h6 class="text-center text-success">Business Class</h6>';
        html += generateSeatGrid(businessSeats);
        html += '</div>';
    }
    
    // Display Economy Class seats
    if (economySeats.length > 0) {
        html += '<div class="seat-class-section mb-4">';
        html += '<h6 class="text-center text-info">Economy Class</h6>';
        html += generateSeatGrid(economySeats);
        html += '</div>';
    }
    
    html += '</div>';
    seatMap.innerHTML = html;
}

// Generate seat grid for a specific class
function generateSeatGrid(seats) {
    const seatsPerRow = 6;
    let html = '<div class="seat-grid">';

    // Group seats by row
    const seatsByRow = {};
    seats.forEach(seat => {
        const row = seat.seat_number.match(/^\d+/)[0];
        if (!seatsByRow[row]) {
            seatsByRow[row] = [];
        }
        seatsByRow[row].push(seat);
    });

    // Sort rows and display
    Object.keys(seatsByRow)
        .sort((a, b) => parseInt(a) - parseInt(b))
        .forEach(row => {
            const rowSeats = seatsByRow[row].sort((a, b) => a.seat_number.localeCompare(b.seat_number));
            html += '<div class="seat-row">';

            rowSeats.forEach((seat, idx) => {
                // Insert an aisle gap after 3rd seat
                if (idx === 3) {
                    html += '<div class="aisle-gap"></div>';
                }

                let seatClass = 'available';
                if (!seat.is_available) {
                    seatClass = 'occupied';
                } else if (seat.is_reserved) {
                    seatClass = 'reserved';
                }

                html += `<div class="seat ${seatClass}"
                             data-seat-id="${seat.seat_id}"
                             data-seat-number="${seat.seat_number}"
                             data-seat-class="${seat.seat_class}"
                             title="${seat.seat_number} (${seat.seat_class})"
                             onclick="selectSeat('${seat.seat_number}', ${seat.seat_id})">
                             ${seat.seat_number}
                         </div>`;
            });

            html += '</div>'; // end seat-row
        });

    html += '</div>'; // end seat-grid
    return html;
}

// Reserve seat function (placeholder - should call backend)
function reserveSeat(seatId) {
    // This would typically make an API call to reserve the seat
    console.log('Reserving seat:', seatId);
    // You can implement actual reservation logic here if needed
}

// Select a seat
function selectSeat(seatNumber, seatId) {
    // Remove previous selection
    document.querySelectorAll('.seat.selected').forEach(seat => {
        seat.classList.remove('selected');
        if (seat.classList.contains('available')) {
            seat.classList.add('available');
        }
    });
    
    // Add selection to clicked seat
    const seatElement = document.querySelector(`[data-seat-id="${seatId}"]`);
    if (seatElement && !seatElement.classList.contains('occupied') && !seatElement.classList.contains('reserved')) {
        seatElement.classList.add('selected');
        seatElement.classList.remove('available');
        selectedSeat = seatNumber;
        const selectedSeatInput = document.getElementById('selected_seat');
        if (selectedSeatInput) selectedSeatInput.value = seatNumber;
        
        // Reserve the seat temporarily
        reserveSeat(seatId);
        
        updateProceedEnabled();
        
        // Show proceed section
        const proceedSection = document.getElementById('proceedSection');
        if (proceedSection) proceedSection.style.display = 'block';
    } else if (seatElement && seatElement.classList.contains('reserved')) {
        // Seat is reserved
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('This seat is already reserved. Please select another seat.', 'warning');
        } else {
            alert('This seat is already reserved. Please select another seat.');
        }
    } else if (seatElement && seatElement.classList.contains('occupied')) {
        // Seat is occupied
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('This seat is already occupied. Please select another seat.', 'warning');
        } else {
            alert('This seat is already occupied. Please select another seat.');
        }
    }
}

// Expose functions globally for inline onclick handlers
window.searchFlights = searchFlights;
window.selectFlight = selectFlight;
window.proceedToPayment = proceedToPayment;
window.updatePrice = updatePrice;
window.reserveSeat = reserveSeat;
window.updateClassPriceChips = updateClassPriceChips;

// Simple debounce helper
function debounce(fn, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn.apply(this, args), wait);
    };
}

// Populate the flight <select> with options
function populateFlightDropdown(flights, selectedFlightNumber = '') {
    const flightSelect = document.getElementById('selected_flight');
    if (!flightSelect) return;
    // Sort by source, destination, then departing time
    const sorted = [...flights].sort((a,b) => (a.source+a.destination+a.departing_time).localeCompare(b.source+b.destination+b.departing_time));
    flightSelect.innerHTML = '<option value="">Select a flight</option>';
    sorted.forEach(flight => {
        const option = document.createElement('option');
        option.value = flight.flight_number;
        const route = `${flight.source} → ${flight.destination}`;
        const times = `Dep ${flight.departing_time}, Arr ${flight.arrival_time}`;
        const econ = `Economy ₹${Number(flight.price_economy).toLocaleString('en-IN')}`;
        const seats = `${flight.available_seats} seats`;
        option.textContent = `${flight.flight_number} · ${flight.flight_company} | ${route} `;
        option.title = `${flight.flight_company} ${flight.flight_number} | ${route} | ${times} | Economy: ₹${flight.price_economy} | Business: ₹${flight.price_business} | First: ₹${flight.price_first} | Available: ${flight.available_seats}`;
        if (selectedFlightNumber && flight.flight_number === selectedFlightNumber) {
            option.selected = true;
        }
        flightSelect.appendChild(option);
    });
}

// Render currently selected flight details in the details panel
function renderSelectedFlightDetails() {
    const panel = document.getElementById('selectedFlightInfo');
    if (!panel || !selectedFlight) return;
    const f = selectedFlight;
    panel.classList.remove('text-muted');
    panel.innerHTML = `
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="airline">${f.flight_company}</div>
                <div class="meta">${f.flight_number}</div>
                <div class="meta">${f.source} → ${f.destination}</div>
            </div>
            <div class="col-md-3">
                <strong>${f.departing_time}</strong>
                <div class="meta">Departure</div>
            </div>
            <div class="col-md-3">
                <strong>${f.arrival_time}</strong>
                <div class="meta">Arrival</div>
            </div>
            <div class="col-md-3">
                <div class="price-info">
                    <small>Economy: ₹${f.price_economy}</small>
                    <small>Business: ₹${f.price_business}</small>
                    <small>First: ₹${f.price_first}</small>
                </div>
                <span class="badge badge-success">${f.available_seats} seats</span>
            </div>
        </div>
    `;
}

// Try multiple endpoints sequentially until one succeeds
function tryFetchSequential(urls, payload) {
    let index = 0;
    return new Promise((resolve, reject) => {
        const next = () => {
            if (index >= urls.length) {
                return reject(new Error('All endpoints failed'));
            }
            const url = urls[index++];
            fetch(url, payload)
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(resolve)
                .catch(next);
        };
        next();
    });
}

// Mock flights fallback (aligned with sample data in setup.php)
function getMockFlights(from, to) {
    const flights = [
        { flight_number: 'AI101', flight_company: 'Air India', departing_time: '08:00:00', arrival_time: '10:30:00', price_economy: 15000, price_business: 25000, price_first: 35000, available_seats: 45, source: 'Bangalore', destination: 'Delhi' },
        { flight_number: 'SG202', flight_company: 'SpiceJet', departing_time: '14:30:00', arrival_time: '17:00:00', price_economy: 12000, price_business: 20000, price_first: 30000, available_seats: 32, source: 'Bangalore', destination: 'Mumbai' },
        { flight_number: '6E303', flight_company: 'IndiGo', departing_time: '19:45:00', arrival_time: '22:15:00', price_economy: 8000, price_business: 15000, price_first: 25000, available_seats: 28, source: 'Bangalore', destination: 'Chennai' },
        { flight_number: 'AI404', flight_company: 'Air India', departing_time: '06:30:00', arrival_time: '09:00:00', price_economy: 18000, price_business: 28000, price_first: 38000, available_seats: 15, source: 'Delhi', destination: 'Bangalore' },
        { flight_number: 'SG505', flight_company: 'SpiceJet', departing_time: '12:00:00', arrival_time: '14:30:00', price_economy: 13000, price_business: 22000, price_first: 32000, available_seats: 22, source: 'Mumbai', destination: 'Delhi' },
        { flight_number: '6E606', flight_company: 'IndiGo', departing_time: '16:15:00', arrival_time: '18:45:00', price_economy: 9000, price_business: 16000, price_first: 26000, available_seats: 38, source: 'Chennai', destination: 'Mumbai' },
        // Additional flights
        { flight_number: 'AI707', flight_company: 'Air India', departing_time: '07:15:00', arrival_time: '09:45:00', price_economy: 14500, price_business: 24000, price_first: 34000, available_seats: 40, source: 'Kolkata', destination: 'Delhi' },
        { flight_number: 'SG808', flight_company: 'SpiceJet', departing_time: '13:45:00', arrival_time: '16:10:00', price_economy: 11000, price_business: 19000, price_first: 29000, available_seats: 30, source: 'Hyderabad', destination: 'Bangalore' },
        { flight_number: '6E909', flight_company: 'IndiGo', departing_time: '18:00:00', arrival_time: '20:30:00', price_economy: 9500, price_business: 17000, price_first: 27000, available_seats: 35, source: 'Pune', destination: 'Mumbai' },
        { flight_number: 'VB010', flight_company: 'Vistara', departing_time: '09:00:00', arrival_time: '11:30:00', price_economy: 16000, price_business: 26000, price_first: 36000, available_seats: 27, source: 'Bangalore', destination: 'Kochi' },
        { flight_number: 'AI111', flight_company: 'Air India', departing_time: '21:00:00', arrival_time: '23:30:00', price_economy: 15500, price_business: 25500, price_first: 35500, available_seats: 20, source: 'Delhi', destination: 'Mumbai' },
        { flight_number: 'SG121', flight_company: 'SpiceJet', departing_time: '05:30:00', arrival_time: '07:55:00', price_economy: 8500, price_business: 14500, price_first: 24500, available_seats: 18, source: 'Chennai', destination: 'Bangalore' }
    ];
    return flights.filter(f => (!from || f.source === from) && (!to || f.destination === to));
}

// Master mock list used to prefill the combined dropdown
function getMockFlightsAll() {
    return [
        { flight_number: 'AI101', flight_company: 'Air India', departing_time: '08:00:00', arrival_time: '10:30:00', price_economy: 15000, price_business: 25000, price_first: 35000, available_seats: 45, source: 'Bangalore', destination: 'Delhi' },
        { flight_number: 'SG202', flight_company: 'SpiceJet', departing_time: '14:30:00', arrival_time: '17:00:00', price_economy: 12000, price_business: 20000, price_first: 30000, available_seats: 32, source: 'Bangalore', destination: 'Mumbai' },
        { flight_number: '6E303', flight_company: 'IndiGo', departing_time: '19:45:00', arrival_time: '22:15:00', price_economy: 8000, price_business: 15000, price_first: 25000, available_seats: 28, source: 'Bangalore', destination: 'Chennai' },
        { flight_number: 'AI404', flight_company: 'Air India', departing_time: '06:30:00', arrival_time: '09:00:00', price_economy: 18000, price_business: 28000, price_first: 38000, available_seats: 15, source: 'Delhi', destination: 'Bangalore' },
        { flight_number: 'SG505', flight_company: 'SpiceJet', departing_time: '12:00:00', arrival_time: '14:30:00', price_economy: 13000, price_business: 22000, price_first: 32000, available_seats: 22, source: 'Mumbai', destination: 'Delhi' },
        { flight_number: '6E606', flight_company: 'IndiGo', departing_time: '16:15:00', arrival_time: '18:45:00', price_economy: 9000, price_business: 16000, price_first: 26000, available_seats: 38, source: 'Chennai', destination: 'Mumbai' },
        { flight_number: 'AI707', flight_company: 'Air India', departing_time: '07:15:00', arrival_time: '09:45:00', price_economy: 14500, price_business: 24000, price_first: 34000, available_seats: 40, source: 'Kolkata', destination: 'Delhi' },
        { flight_number: 'SG808', flight_company: 'SpiceJet', departing_time: '13:45:00', arrival_time: '16:10:00', price_economy: 11000, price_business: 19000, price_first: 29000, available_seats: 30, source: 'Hyderabad', destination: 'Bangalore' },
        { flight_number: '6E909', flight_company: 'IndiGo', departing_time: '18:00:00', arrival_time: '20:30:00', price_economy: 9500, price_business: 17000, price_first: 27000, available_seats: 35, source: 'Pune', destination: 'Mumbai' },
        { flight_number: 'VB010', flight_company: 'Vistara', departing_time: '09:00:00', arrival_time: '11:30:00', price_economy: 16000, price_business: 26000, price_first: 36000, available_seats: 27, source: 'Bangalore', destination: 'Kochi' },
        { flight_number: 'AI111', flight_company: 'Air India', departing_time: '21:00:00', arrival_time: '23:30:00', price_economy: 15500, price_business: 25500, price_first: 35500, available_seats: 20, source: 'Delhi', destination: 'Mumbai' },
        { flight_number: 'SG121', flight_company: 'SpiceJet', departing_time: '05:30:00', arrival_time: '07:55:00', price_economy: 8500, price_business: 14500, price_first: 24500, available_seats: 18, source: 'Chennai', destination: 'Bangalore' }
    ];
}

// Merge two flight lists by unique flight_number
function mergeFlights(existing, incoming) {
    const map = new Map(existing.map(f => [f.flight_number, f]));
    incoming.forEach(f => map.set(f.flight_number, f));
    return Array.from(map.values());
}

// Build a simple mock seat map if backend is unavailable
function buildMockSeats() {
    const seats = [];
    const seatsPerRow = 6;
    const total = 60;
    for (let i = 1; i <= Math.ceil(total / seatsPerRow); i++) {
        for (let c = 0; c < seatsPerRow; c++) {
            const index = (i - 1) * seatsPerRow + c + 1;
            if (index > total) break;
            const letter = String.fromCharCode(65 + c); // A-F
            const seatNum = `${i}${letter}`;
            const seatClass = i <= 2 ? 'First' : i <= 4 ? 'Business' : 'Economy';
            seats.push({
                seat_id: index,
                seat_number: seatNum,
                seat_class: seatClass,
                is_available: Math.random() > 0.1,
                is_reserved: false
            });
        }
    }
    return seats;
}

// Enable proceed only when flight, class, and seat are selected (dropdown version)
function updateProceedEnabled() {
    const hasFlight = !!selectedFlight;
    const dropdown = document.getElementById('travel_class');
    const hasClass = !!dropdown && !!dropdown.value;
    const hasSeat = !!selectedSeat;
    const btn = document.getElementById('proceedBtn');
    if (btn) btn.disabled = !(hasFlight && hasClass && hasSeat);
}

// Proceed to payment (accepts class from radios or dropdown)
function proceedToPayment() {
    if (!selectedFlight) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a flight first', 'warning');
        } else {
            alert('Please select a flight first');
        }
        return;
    }

    if (!selectedSeat) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a seat first', 'warning');
        } else {
            alert('Please select a seat first');
        }
        return;
    }

    const dropdown = document.getElementById('travel_class');
    const selectedClass = dropdown ? dropdown.value : '';

    if (!selectedClass) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a travel class', 'warning');
        } else {
            alert('Please select a travel class');
        }
        return;
    }

    const paymentSection = document.getElementById('paymentSection');
    const proceedBtn = document.getElementById('proceedBtn');
    const bookBtn = document.getElementById('bookBtn');

    if (!paymentSection) {
        console.error('Missing #paymentSection in DOM. Create a container with id="paymentSection" to show payment UI.');
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Payment section is unavailable. Please contact support or try again later.', 'error');
        } else {
            alert('Payment section is unavailable. Please contact support or try again later.');
        }
        return;
    }

    // Sync form fields
    const searchFrom = document.getElementById('search_from');
    const searchTo = document.getElementById('search_to');
    const searchDate = document.getElementById('search_date');
    
    const flyingFrom = document.getElementById('Flying_from');
    const flyingTo = document.getElementById('Flying_to');
    const departingDate = document.getElementById('Departing_date');
    
    if (searchFrom && flyingFrom) flyingFrom.value = searchFrom.value;
    if (searchTo && flyingTo) flyingTo.value = searchTo.value;
    if (searchDate && departingDate) departingDate.value = searchDate.value;
    
    const hiddenFlight = document.getElementById('selected_flight_hidden');
    const flightSelect = document.getElementById('selected_flight');
    if (hiddenFlight && flightSelect) hiddenFlight.value = flightSelect.value;
    
    const hiddenClass = document.getElementById('travel_class_hidden');
    if (hiddenClass && dropdown) hiddenClass.value = dropdown.value;
    
    const selectedSeatInput = document.getElementById('selected_seat');
    const selectedSeatHidden = document.getElementById('selected_seat_hidden');
    if (selectedSeatInput && selectedSeatHidden) {
        selectedSeatHidden.value = selectedSeatInput.value;
    }

    // Show payment section and buttons if present
    paymentSection.style.display = 'block';
    if (proceedBtn) proceedBtn.style.display = 'none';
    if (bookBtn) bookBtn.style.display = 'inline-block';

    // Scroll to payment section
    paymentSection.scrollIntoView({ behavior: 'smooth' });
}

// Enhanced validation function
function bookticket() {
    const name = document.getElementById("Name").value;
    const dateofbirth = document.getElementById("Date_of_birth").value;
    const passportnumber = document.getElementById("Passport_number").value;
    const city = document.getElementById("City").value;
    const email = document.getElementById("Email").value;
    const phone = document.getElementById("Phone").value;
    const flyingfrom = document.getElementById("Flying_from").value;
    const flyingto = document.getElementById("Flying_to").value;
    const departingdate = document.getElementById("Departing_date").value;
    const selectedFlight = document.getElementById("selected_flight").value;
    const selectedClass = document.getElementById('travel_class').value;
    const selectedSeat = document.getElementById("selected_seat").value;
    const paymentMethod = document.getElementById("payment_method").value;

    // Validation
    if (!name.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Full name is required", 'warning');
        } else {
            alert("Full name is required");
        }
        return false;
    }
    
    if (!email.trim() || !isValidEmail(email)) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Valid email address is required", 'warning');
        } else {
            alert("Valid email address is required");
        }
        return false;
    }
    
    if (!phone.trim() || phone.length < 10) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Valid phone number is required", 'warning');
        } else {
            alert("Valid phone number is required");
        }
        return false;
    }
    
    if (!dateofbirth) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Date of birth is required", 'warning');
        } else {
            alert("Date of birth is required");
        }
        return false;
    }
    
    if (!passportnumber.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Passport/ID number is required", 'warning');
        } else {
            alert("Passport/ID number is required");
        }
        return false;
    }
    
    if (!city.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("City is required", 'warning');
        } else {
            alert("City is required");
        }
        return false;
    }
    
    if (!flyingfrom) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Source is required", 'warning');
        } else {
            alert("Source is required");
        }
        return false;
    }
    
    if (!flyingto) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Destination is required", 'warning');
        } else {
            alert("Destination is required");
        }
        return false;
    }
    
    if (flyingfrom === flyingto) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Source and destination cannot be the same", 'warning');
        } else {
            alert("Source and destination cannot be the same");
        }
        return false;
    }
    
    if (!departingdate) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Departure date is required", 'warning');
        } else {
            alert("Departure date is required");
        }
        return false;
    }
    
    if (!selectedFlight) {
        return false;
    }
    
    if (!selectedClass) {
        alert("Please select a travel class");
        return false;
    }
    
    if (!selectedSeat) {
        return false;
    }
    
    if (!paymentMethod) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Please select a payment method", 'warning');
        } else {
            alert("Please select a payment method");
        }
        return false;
    }
    
    // Additional validations
    const today = new Date();
    const departureDate = new Date(departingdate);
    
    if (departureDate < today) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Departure date cannot be in the past", 'warning');
        } else {
            alert("Departure date cannot be in the past");
        }
        return false;
    }
    
    const birthDate = new Date(dateofbirth);
    const age = today.getFullYear() - birthDate.getFullYear();
    
    if (age < 0 || age > 120) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Please enter a valid date of birth", 'warning');
        } else {
            alert("Please enter a valid date of birth");
        }
        return false;
    }
    
    // Show confirmation
    const confirmation = confirm(`Confirm booking:\n\nFlight: ${selectedFlight}\nClass: ${selectedClass.value}\nSeat: ${selectedSeat}\nTotal: ${document.getElementById('total_amount').value}\n\nProceed with payment?`);
    
    if (confirmation) {
        // Simulate payment processing
        showPaymentProcessing();
        return true;
    }
    
    return false;
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show payment processing
function showPaymentProcessing() {
    const bookBtn = document.getElementById('bookBtn');
    const originalText = bookBtn.innerHTML;
    
    bookBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
    bookBtn.disabled = true;
    
    // Simulate payment processing delay
    setTimeout(() => {
        bookBtn.innerHTML = originalText;
        bookBtn.disabled = false;
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Payment processed successfully! Your ticket has been booked.', 'success', 5000);
        } else {
            alert('Payment processed successfully! Your ticket has been booked.');
        }
    }, 3000);
}

// Handle flight selection from dropdown
function onFlightSelect() {
    const flightNumber = document.getElementById('selected_flight').value;
    if (flightNumber) {
        selectFlight(flightNumber);
    }
}

// Handle class selection from dropdown
function onClassSelect(e) {
    const dropdown = document.getElementById('travel_class');
    const value = dropdown ? dropdown.value : null;
    
    if (value && selectedFlight) {
        updatePrice(value);
        updateClassDescription(value);
        showSeatSelection();
    } else {
        // clear price if no class selected
        const totalEl = document.getElementById('total_amount');
        if (totalEl) totalEl.value = '';
        updateClassDescription('');
    }
    
    updateProceedEnabled();
}

// Enable proceed only when flight, class, and seat are selected (dropdown version)
function updateProceedEnabled() {
    const hasFlight = !!selectedFlight;
    const dropdown = document.getElementById('travel_class');
    const hasClass = !!dropdown && !!dropdown.value;
    const hasSeat = !!selectedSeat;
    const btn = document.getElementById('proceedBtn');
    if (btn) btn.disabled = !(hasFlight && hasClass && hasSeat);
}

// Proceed to payment (accepts class from radios or dropdown)
function proceedToPayment() {
    if (!selectedFlight) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a flight first', 'warning');
        } else {
            alert('Please select a flight first');
        }
        return;
    }

    if (!selectedSeat) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a seat first', 'warning');
        } else {
            alert('Please select a seat first');
        }
        return;
    }

    const dropdown = document.getElementById('travel_class');
    const selectedClass = dropdown ? dropdown.value : '';

    if (!selectedClass) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a travel class', 'warning');
        } else {
            alert('Please select a travel class');
        }
        return;
    }

    const paymentSection = document.getElementById('paymentSection');
    const proceedBtn = document.getElementById('proceedBtn');
    const bookBtn = document.getElementById('bookBtn');

    if (!paymentSection) {
        console.error('Missing #paymentSection in DOM. Create a container with id="paymentSection" to show payment UI.');
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Payment section is unavailable. Please contact support or try again later.', 'error');
        } else {
            alert('Payment section is unavailable. Please contact support or try again later.');
        }
        return;
    }

    // Sync form fields
    const searchFrom = document.getElementById('search_from');
    const searchTo = document.getElementById('search_to');
    const searchDate = document.getElementById('search_date');
    
    const flyingFrom = document.getElementById('Flying_from');
    const flyingTo = document.getElementById('Flying_to');
    const departingDate = document.getElementById('Departing_date');
    
    if (searchFrom && flyingFrom) flyingFrom.value = searchFrom.value;
    if (searchTo && flyingTo) flyingTo.value = searchTo.value;
    if (searchDate && departingDate) departingDate.value = searchDate.value;
    
    const hiddenFlight = document.getElementById('selected_flight_hidden');
    const flightSelect = document.getElementById('selected_flight');
    if (hiddenFlight && flightSelect) hiddenFlight.value = flightSelect.value;
    
    const hiddenClass = document.getElementById('travel_class_hidden');
    if (hiddenClass && dropdown) hiddenClass.value = dropdown.value;
    
    const selectedSeatInput = document.getElementById('selected_seat');
    const selectedSeatHidden = document.getElementById('selected_seat_hidden');
    if (selectedSeatInput && selectedSeatHidden) {
        selectedSeatHidden.value = selectedSeatInput.value;
    }

    // Show payment section and buttons if present
    paymentSection.style.display = 'block';
    if (proceedBtn) proceedBtn.style.display = 'none';
    if (bookBtn) bookBtn.style.display = 'inline-block';

    // Scroll to payment section
    paymentSection.scrollIntoView({ behavior: 'smooth' });
}

// Enhanced validation function
function bookticket() {
    const name = document.getElementById("Name").value;
    const dateofbirth = document.getElementById("Date_of_birth").value;
    const passportnumber = document.getElementById("Passport_number").value;
    const city = document.getElementById("City").value;
    const email = document.getElementById("Email").value;
    const phone = document.getElementById("Phone").value;
    const flyingfrom = document.getElementById("Flying_from").value;
    const flyingto = document.getElementById("Flying_to").value;
    const departingdate = document.getElementById("Departing_date").value;
    const selectedFlight = document.getElementById("selected_flight").value;
    const selectedClass = document.getElementById('travel_class').value;
    const selectedSeat = document.getElementById("selected_seat").value;
    const paymentMethod = document.getElementById("payment_method").value;

    // Validation
    if (!name.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Full name is required", 'warning');
        } else {
            alert("Full name is required");
        }
        return false;
    }
    
    if (!email.trim() || !isValidEmail(email)) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Valid email address is required", 'warning');
        } else {
            alert("Valid email address is required");
        }
        return false;
    }
    
    if (!phone.trim() || phone.length < 10) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Valid phone number is required", 'warning');
        } else {
            alert("Valid phone number is required");
        }
        return false;
    }
    
    if (!dateofbirth) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Date of birth is required", 'warning');
        } else {
            alert("Date of birth is required");
        }
        return false;
    }
    
    if (!passportnumber.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Passport/ID number is required", 'warning');
        } else {
            alert("Passport/ID number is required");
        }
        return false;
    }
    
    if (!city.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("City is required", 'warning');
        } else {
            alert("City is required");
        }
        return false;
    }
    
    if (!flyingfrom) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Source is required", 'warning');
        } else {
            alert("Source is required");
        }
        return false;
    }
    
    if (!flyingto) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Destination is required", 'warning');
        } else {
            alert("Destination is required");
        }
        return false;
    }
    
    if (flyingfrom === flyingto) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Source and destination cannot be the same", 'warning');
        } else {
            alert("Source and destination cannot be the same");
        }
        return false;
    }
    
    if (!departingdate) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Departure date is required", 'warning');
        } else {
            alert("Departure date is required");
        }
        return false;
    }
    
    if (!selectedFlight) {
        return false;
    }
    
    if (!selectedClass) {`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              `
        alert("Please select a travel class");
        return false;
    }
    
    if (!selectedSeat) {
        return false;
    }
    
    if (!paymentMethod) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Please select a payment method", 'warning');
        } else {
            alert("Please select a payment method");
        }
        return false;
    }
    
    // Additional validations
    const today = new Date();
    const departureDate = new Date(departingdate);
    
    if (departureDate < today) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Departure date cannot be in the past", 'warning');
        } else {
            alert("Departure date cannot be in the past");
        }
        return false;
    }
    
    const birthDate = new Date(dateofbirth);
    const age = today.getFullYear() - birthDate.getFullYear();
    
    if (age < 0 || age > 120) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Please enter a valid date of birth", 'warning');
        } else {
            alert("Please enter a valid date of birth");
        }
        return false;
    }
    
    // Show confirmation
    const confirmation = confirm(`Confirm booking:\n\nFlight: ${selectedFlight}\nClass: ${selectedClass.value}\nSeat: ${selectedSeat}\nTotal: ${document.getElementById('total_amount').value}\n\nProceed with payment?`);
    
    if (confirmation) {
        // Simulate payment processing
        showPaymentProcessing();
        return true;
    }
    
    return false;
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show payment processing
function showPaymentProcessing() {
    const bookBtn = document.getElementById('bookBtn');
    const originalText = bookBtn.innerHTML;
    
    bookBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
    bookBtn.disabled = true;
    
    // Simulate payment processing delay
    setTimeout(() => {
        bookBtn.innerHTML = originalText;
        bookBtn.disabled = false;
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Payment processed successfully! Your ticket has been booked.', 'success', 5000);
        } else {
            alert('Payment processed successfully! Your ticket has been booked.');
        }
    }, 3000);
}

// Handle flight selection from dropdown
function onFlightSelect() {
    const flightNumber = document.getElementById('selected_flight').value;
    if (flightNumber) {
        selectFlight(flightNumber);
    }
}

// Handle class selection from dropdown
function onClassSelect(e) {
    const dropdown = document.getElementById('travel_class');
    const value = dropdown ? dropdown.value : null;
    
    if (value && selectedFlight) {
        updatePrice(value);
        updateClassDescription(value);
        showSeatSelection();
    } else {
        // clear price if no class selected
        const totalEl = document.getElementById('total_amount');
        if (totalEl) totalEl.value = '';
        updateClassDescription('');
    }
    
    updateProceedEnabled();
}

// Enable proceed only when flight, class, and seat are selected (dropdown version)
function updateProceedEnabled() {
    const hasFlight = !!selectedFlight;
    const dropdown = document.getElementById('travel_class');
    const hasClass = !!dropdown && !!dropdown.value;
    const hasSeat = !!selectedSeat;
    const btn = document.getElementById('proceedBtn');
    if (btn) btn.disabled = !(hasFlight && hasClass && hasSeat);
}

// Proceed to payment (accepts class from radios or dropdown)
function proceedToPayment() {
    if (!selectedFlight) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a flight first', 'warning');
        } else {
            alert('Please select a flight first');
        }
        return;
    }

    if (!selectedSeat) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a seat first', 'warning');
        } else {
            alert('Please select a seat first');
        }
        return;
    }

    const dropdown = document.getElementById('travel_class');
    const selectedClass = dropdown ? dropdown.value : '';

    if (!selectedClass) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select a travel class', 'warning');
        } else {
            alert('Please select a travel class');
        }
        return;
    }

    const paymentSection = document.getElementById('paymentSection');
    const proceedBtn = document.getElementById('proceedBtn');
    const bookBtn = document.getElementById('bookBtn');

    if (!paymentSection) {
        console.error('Missing #paymentSection in DOM. Create a container with id="paymentSection" to show payment UI.');
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Payment section is unavailable. Please contact support or try again later.', 'error');
        } else {
            alert('Payment section is unavailable. Please contact support or try again later.');
        }
        return;
    }

    // Sync form fields
    const searchFrom = document.getElementById('search_from');
    const searchTo = document.getElementById('search_to');
    const searchDate = document.getElementById('search_date');
    
    const flyingFrom = document.getElementById('Flying_from');
    const flyingTo = document.getElementById('Flying_to');
    const departingDate = document.getElementById('Departing_date');
    
    if (searchFrom && flyingFrom) flyingFrom.value = searchFrom.value;
    if (searchTo && flyingTo) flyingTo.value = searchTo.value;
    if (searchDate && departingDate) departingDate.value = searchDate.value;
    
    const hiddenFlight = document.getElementById('selected_flight_hidden');
    const flightSelect = document.getElementById('selected_flight');
    if (hiddenFlight && flightSelect) hiddenFlight.value = flightSelect.value;
    
    const hiddenClass = document.getElementById('travel_class_hidden');
    if (hiddenClass && dropdown) hiddenClass.value = dropdown.value;
    
    const selectedSeatInput = document.getElementById('selected_seat');
    const selectedSeatHidden = document.getElementById('selected_seat_hidden');
    if (selectedSeatInput && selectedSeatHidden) {
        selectedSeatHidden.value = selectedSeatInput.value;
    }

    // Show payment section and buttons if present
    paymentSection.style.display = 'block';
    if (proceedBtn) proceedBtn.style.display = 'none';
    if (bookBtn) bookBtn.style.display = 'inline-block';

    // Scroll to payment section
    paymentSection.scrollIntoView({ behavior: 'smooth' });
}

// Enhanced validation function
function bookticket() {
    const name = document.getElementById("Name").value;
    const dateofbirth = document.getElementById("Date_of_birth").value;
    const passportnumber = document.getElementById("Passport_number").value;
    const city = document.getElementById("City").value;
    const email = document.getElementById("Email").value;
    const phone = document.getElementById("Phone").value;
    const flyingfrom = document.getElementById("Flying_from").value;
    const flyingto = document.getElementById("Flying_to").value;
    const departingdate = document.getElementById("Departing_date").value;
    const selectedFlight = document.getElementById("selected_flight").value;
    const selectedClass = document.getElementById('travel_class').value;
    const selectedSeat = document.getElementById("selected_seat").value;
    const paymentMethod = document.getElementById("payment_method").value;

    // Validation
    if (!name.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Full name is required", 'warning');
        } else {
            alert("Full name is required");
        }
        return false;
    }
    
    if (!email.trim() || !isValidEmail(email)) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Valid email address is required", 'warning');
        } else {
            alert("Valid email address is required");
        }
        return false;
    }
    
    if (!phone.trim() || phone.length < 10) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Valid phone number is required", 'warning');
        } else {
            alert("Valid phone number is required");
        }
        return false;
    }
    
    if (!dateofbirth) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Date of birth is required", 'warning');
        } else {
            alert("Date of birth is required");
        }
        return false;
    }
    
    if (!passportnumber.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Passport/ID number is required", 'warning');
        } else {
            alert("Passport/ID number is required");
        }
        return false;
    }
    
    if (!city.trim()) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("City is required", 'warning');
        } else {
            alert("City is required");
        }
        return false;
    }
    
    if (!flyingfrom) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Source is required", 'warning');
        } else {
            alert("Source is required");
        }
        return false;
    }
    
    if (!flyingto) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Destination is required", 'warning');
        } else {
            alert("Destination is required");
        }
        return false;
    }
    
    if (flyingfrom === flyingto) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Source and destination cannot be the same", 'warning');
        } else {
            alert("Source and destination cannot be the same");
        }
        return false;
    }
    
    if (!departingdate) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Departure date is required", 'warning');
        } else {
            alert("Departure date is required");
        }
        return false;
    }
    
    if (!selectedFlight) {
        return false;
    }
    
    if (!selectedClass) {
        alert("Please select a travel class");
        return false;
    }
    
    if (!selectedSeat) {
        return false;
    }
    
    if (!paymentMethod) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Please select a payment method", 'warning');
        } else {
            alert("Please select a payment method");
        }
        return false;
    }
    
    // Additional validations
    const today = new Date();
    const departureDate = new Date(departingdate);
    
    if (departureDate < today) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Departure date cannot be in the past", 'warning');
        } else {
            alert("Departure date cannot be in the past");
        }
        return false;
    }
    
    const birthDate = new Date(dateofbirth);
    const age = today.getFullYear() - birthDate.getFullYear();
    
    if (age < 0 || age > 120) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar("Please enter a valid date of birth", 'warning');
        } else {
            alert("Please enter a valid date of birth");
        }
        return false;
    }
    
    // Show confirmation
    const confirmation = confirm(`Confirm booking:\n\nFlight: ${selectedFlight}\nClass: ${selectedClass.value}\nSeat: ${selectedSeat}\nTotal: ${document.getElementById('total_amount').value}\n\nProceed with payment?`);
    
    if (confirmation) {
        // Simulate payment processing
        showPaymentProcessing();
        return true;
    }
    
    return false;
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show payment processing
function showPaymentProcessing() {
    const bookBtn = document.getElementById('bookBtn');
    const originalText = bookBtn.innerHTML;
    
    bookBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
    bookBtn.disabled = true;
    
    // Simulate payment processing delay
    setTimeout(() => {
        bookBtn.innerHTML = originalText;
        bookBtn.disabled = false;
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Payment processed successfully! Your ticket has been booked.', 'success', 5000);
        } else {
            alert('Payment processed successfully! Your ticket has been booked.');
        }
    }, 3000);
}
