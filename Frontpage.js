// Weather Widget
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');

// Enhanced Frontpage JavaScript - Load immediately
(function() {
    // Check for booking success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const bookingSuccess = urlParams.get('booking_success');
    const bookingRef = urlParams.get('ref');
    
    // Initialize and load function
    function initializeAndLoad() {
        initializePage();
        setupEventListeners();
        initializeWeatherWidget();
        
        // Load bookings with delay if coming from payment success
        if (bookingSuccess === 'true') {
            // Small delay to ensure database is updated
            setTimeout(() => {
                loadBookings();
                // Scroll to bookings section after a short delay
                setTimeout(() => {
                    scrollToBookingsSection();
                    showBookingSuccessMessage(bookingRef);
                }, 500);
            }, 300);
        } else {
            loadBookings();
        }
    }
    
    // Load bookings immediately (fastest)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAndLoad);
    } else {
        // DOM already loaded
        initializeAndLoad();
    }
})();

function initializePage() {
    // Set minimum date for quick search
    const today = new Date().toISOString().split('T')[0];
    const quickDateInput = document.getElementById('quick_date');
    if (quickDateInput) {
        quickDateInput.value = today;
        quickDateInput.min = today;
    }
    
    // Smooth scrolling for navigation links
    setupSmoothScrolling();
    
    // Initialize animations
    initializeAnimations();
}

function setupEventListeners() {
    // Quick search form
    const quickSearchForm = document.getElementById('quickSearchForm');
    if (quickSearchForm) {
        console.log('✅ Search form found, attaching submit listener');
        quickSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('🔍 Form submitted, starting search...');
            performQuickSearch();
        });
    } else {
        console.error('❌ Quick search form not found!');
    }
    
    // Navbar scroll effect
    window.addEventListener('scroll', handleNavbarScroll);
    
    // Form validation
    setupFormValidation();
}

function setupSmoothScrolling() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

function handleNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.background = 'rgba(0,0,0,0.95)';
        navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.3)';
    } else {
        navbar.style.background = 'rgba(0,0,0,0.9)';
        navbar.style.boxShadow = 'none';
    }
}

function initializeAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all cards and sections
    document.querySelectorAll('.card, .section-title, .section-subtitle').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

function setupFormValidation() {
    // Real-time form validation
    const formInputs = document.querySelectorAll('#quickSearchForm input, #quickSearchForm select');
    formInputs.forEach(input => {
        input.addEventListener('blur', validateInput);
        input.addEventListener('input', clearValidation);
    });
}

function validateInput(e) {
    const input = e.target;
    const value = input.value.trim();
    
    // Remove existing validation classes
    input.classList.remove('is-valid', 'is-invalid');
    
    if (input.hasAttribute('required') && !value) {
        input.classList.add('is-invalid');
        showValidationMessage(input, 'This field is required', 'error');
    } else if (value) {
        input.classList.add('is-valid');
        clearValidationMessage(input);
    }
}

function clearValidation(e) {
    const input = e.target;
    input.classList.remove('is-invalid');
    clearValidationMessage(input);
}

function showValidationMessage(input, message, type) {
    clearValidationMessage(input);
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `invalid-feedback ${type === 'error' ? 'd-block' : ''}`;
    messageDiv.textContent = message;
    
    input.parentNode.appendChild(messageDiv);
}

function clearValidationMessage(input) {
    const existingMessage = input.parentNode.querySelector('.invalid-feedback');
    if (existingMessage) {
        existingMessage.remove();
    }
}

function performQuickSearch() {
    const from = document.getElementById('quick_from').value;
    const to = document.getElementById('quick_to').value;
    const date = document.getElementById('quick_date').value;
    
    // Validate inputs
    if (!from || !to || !date) {
        showAlert('Please fill in all fields', 'warning');
        return;
    }
    
    if (from === to) {
        showAlert('Source and destination cannot be the same', 'warning');
        return;
    }
    
    // Show loading state
    const searchBtn = document.querySelector('#quickSearchForm button');
    const originalText = searchBtn.innerHTML;
    searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
    searchBtn.disabled = true;
    
    // Call the search API
    fetch('search_flights.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            from: from,
            to: to,
            date: date
        })
    })
    .then(response => {
        console.log('API Response Status:', response.status);
        return response.text().then(text => {
            console.log('API Response Text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON Parse Error:', e);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        console.log('Parsed Flight Data:', data);
        
        // Reset button
        searchBtn.innerHTML = originalText;
        searchBtn.disabled = false;
        
        if (data.error) {
            showAlert('Error: ' + data.error, 'danger');
            // Still show empty results section
            displaySearchResults([], from, to, date);
            const resultsSection = document.getElementById('search-results-section');
            if (resultsSection) {
                resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            return;
        }
        
        // Handle both array and object responses
        const flights = Array.isArray(data) ? data : (data.flights || []);
        console.log('Flights to display:', flights);
        
        // Display search results
        displaySearchResults(flights, from, to, date);
        
        // Scroll to results
        const resultsSection = document.getElementById('search-results-section');
        if (resultsSection) {
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        searchBtn.innerHTML = originalText;
        searchBtn.disabled = false;
        showAlert('Failed to search flights: ' + error.message, 'danger');
        // Show empty results
        displaySearchResults([], from, to, date);
        const resultsSection = document.getElementById('search-results-section');
        if (resultsSection) {
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}

function quickSearch() {
    performQuickSearch();
}

function displaySearchResults(flights, from, to, date) {
    console.log('displaySearchResults called with:', { flights, from, to, date });
    
    const resultsSection = document.getElementById('search-results-section');
    const resultsContainer = document.getElementById('search-results-container');
    const resultsSubtitle = document.getElementById('search-results-subtitle');
    
    console.log('Found elements:', { resultsSection, resultsContainer, resultsSubtitle });
    
    if (!resultsSection || !resultsContainer) {
        console.error('Results section or container not found!');
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Error: Results section not found. Please refresh the page.', 'error');
        } else {
            alert('Error: Results section not found. Please refresh the page.');
        }
        return;
    }
    
    // Show results section
    resultsSection.style.display = 'block';
    console.log('Results section display set to block');
    
    // Clear previous results
    resultsContainer.innerHTML = '';
    
    // Update subtitle
    const formattedDate = new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    resultsSubtitle.textContent = `${flights.length} flight(s) found from ${from} to ${to} on ${formattedDate}`;
    
    // Check if no flights found
    if (flights.length === 0) {
        resultsContainer.innerHTML = `
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>No Flights Found</h4>
                <p>No flights available for the selected route and date. Please try different search criteria.</p>
                <button class="btn btn-primary mt-3" onclick="document.getElementById('quick_from').focus()">
                    <i class="fas fa-search"></i> Try Another Search
                </button>
            </div>
        `;
        return;
    }
    
    // Create table structure
    const tableHTML = `
        <div class="table-responsive">
            <table class="table table-hover table-striped" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                        <th style="padding: 15px; border: none;"><i class="fas fa-plane"></i> Airline</th>
                        <th style="padding: 15px; border: none;"><i class="fas fa-hashtag"></i> Flight No.</th>
                        <th style="padding: 15px; border: none;"><i class="fas fa-clock"></i> Departure</th>
                        <th style="padding: 15px; border: none;"><i class="fas fa-clock"></i> Arrival</th>
                        <th style="padding: 15px; border: none;"><i class="fas fa-route"></i> Route</th>
                        <th style="padding: 15px; border: none;"><i class="fas fa-chair"></i> Seats</th>
                        <th style="padding: 15px; border: none;"><i class="fas fa-info-circle"></i> Details</th>
                    </tr>
                </thead>
                <tbody id="flightTableBody">
                    ${flights.map((flight, index) => {
                        const departureTime = flight.departing_time ? 
                            (flight.departing_time.includes(':') ? flight.departing_time.substring(0, 5) : 
                            new Date(flight.departing_time).toLocaleTimeString('en-IN', {
                                hour: '2-digit',
                                minute: '2-digit'
                            })) : 'N/A';
                        
                        const arrivalTime = flight.arrival_time ? 
                            (flight.arrival_time.includes(':') ? flight.arrival_time.substring(0, 5) :
                            new Date(flight.arrival_time).toLocaleTimeString('en-IN', {
                                hour: '2-digit',
                                minute: '2-digit'
                            })) : 'N/A';
                        
                        return `
                            <tr style="transition: all 0.3s ease; cursor: pointer;" 
                                onmouseover="this.style.backgroundColor='#f8f9ff'" 
                                onmouseout="this.style.backgroundColor='white'">
                                <td style="padding: 15px; vertical-align: middle;">
                                    <strong style="color: #667eea;">${flight.flight_company || 'Airline'}</strong>
                                </td>
                                <td style="padding: 15px; vertical-align: middle;">
                                    <span class="badge badge-primary" style="font-size: 0.9rem; padding: 6px 12px;">
                                        ${flight.flight_number || 'N/A'}
                                    </span>
                                </td>
                                <td style="padding: 15px; vertical-align: middle;">
                                    <strong>${departureTime}</strong><br>
                                    <small class="text-muted">${flight.source || from}</small>
                                </td>
                                <td style="padding: 15px; vertical-align: middle;">
                                    <strong>${arrivalTime}</strong><br>
                                    <small class="text-muted">${flight.destination || to}</small>
                                </td>
                                <td style="padding: 15px; vertical-align: middle;">
                                    <span style="font-size: 0.9rem;">
                                        ${flight.source || from} <i class="fas fa-arrow-right text-primary"></i> ${flight.destination || to}
                                    </span>
                                </td>
                                <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                    <span class="badge badge-success" style="font-size: 0.85rem; padding: 6px 10px;">
                                        ${flight.available_seats || 0}
                                    </span>
                                </td>
                                <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                    <button class="btn btn-sm btn-outline-primary" type="button" 
                                            data-toggle="collapse" data-target="#details-${index}"
                                            style="padding: 5px 15px;">
                                        <i class="fas fa-info-circle"></i> View
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse" id="details-${index}">
                                <td colspan="7" style="padding: 20px; background: #f8f9fa;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-plane-departure text-primary"></i> Departure Details:</strong>
                                            <p class="mb-0 ml-2">${flight.source || from} at ${departureTime}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-plane-arrival text-primary"></i> Arrival Details:</strong>
                                            <p class="mb-0 ml-2">${flight.destination || to} at ${arrivalTime}</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    resultsContainer.innerHTML = tableHTML;
}

function bookFlight(flightNumber, from, to, date) {
    // Redirect to booking page with flight details
    const params = new URLSearchParams({
        flight_number: flightNumber,
        from: from,
        to: to,
        date: date
    });
    window.location.href = `Ticketbooking.html?${params.toString()}`;
}

function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    // Insert at the top of the quick booking section
    const quickBookingSection = document.getElementById('quick-booking');
    if (quickBookingSection) {
        quickBookingSection.insertBefore(alertDiv, quickBookingSection.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

function initializeWeatherWidget() {
    // Weather widget is loaded via the script tag
    // Additional weather widget customization can be added here
}

// Load bookings with optimized performance and error handling
function loadBookings() {
    const loadingDiv = document.getElementById('bookings-loading');
    const bookingsList = document.getElementById('bookings-list');
    const emptyDiv = document.getElementById('bookings-empty');
    
    // Create timeout promise (5 seconds)
    const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Request timeout - server took too long to respond')), 5000);
    });
    
    // Fetch promise
    const fetchPromise = fetch('./api/get_bookings.php', {
        method: 'GET',
        cache: 'no-store'
    }).then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json().catch(err => {
            console.error('JSON parse error:', err);
            throw new Error('Invalid response from server');
        });
    });
    
    // Race between fetch and timeout
    Promise.race([fetchPromise, timeoutPromise])
        .then(data => {
            console.log('Bookings API response:', data);
            
            if (loadingDiv) loadingDiv.style.display = 'none';
            
            // Handle response
            if (data.success === false) {
                // API returned an error
                console.error('API Error:', data.error);
                showBookingError(data.error || 'Failed to load bookings');
                if (emptyDiv) emptyDiv.style.display = 'block';
                return;
            }
            
            if (data.bookings && data.bookings.length > 0) {
                displayBookings(data.bookings);
                
                // Check if we came from a successful booking
                const urlParams = new URLSearchParams(window.location.search);
                const bookingRef = urlParams.get('ref');
                if (urlParams.get('booking_success') === 'true' && bookingRef) {
                    // Highlight the new booking
                    highlightNewBooking(bookingRef);
                }
            } else {
                // No bookings found - show empty state and hide control panel
                if (emptyDiv) emptyDiv.style.display = 'block';
                const controlPanel = document.getElementById('bookings-control-panel');
                const deleteAllBtn = document.getElementById('deleteAllBtn');
                if (controlPanel) controlPanel.style.display = 'none';
                if (deleteAllBtn) deleteAllBtn.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading bookings:', error);
            
            if (loadingDiv) loadingDiv.style.display = 'none';
            
            // Show user-friendly error message
            const errorMsg = error.message || 'Failed to load bookings. Please refresh the page.';
            showBookingError(errorMsg);
            
            if (emptyDiv) emptyDiv.style.display = 'block';
            
            // Hide control panel on error
            const controlPanel = document.getElementById('bookings-control-panel');
            const deleteAllBtn = document.getElementById('deleteAllBtn');
            if (controlPanel) controlPanel.style.display = 'none';
            if (deleteAllBtn) deleteAllBtn.style.display = 'none';
        });
}

// Show booking error message
function showBookingError(message) {
    const bookingsList = document.getElementById('bookings-list');
    if (!bookingsList) return;
    
    // Clear existing error messages
    const existingErrors = bookingsList.querySelectorAll('.alert-warning');
    existingErrors.forEach(err => err.remove());
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-warning alert-dismissible fade show';
    errorDiv.innerHTML = `
        <strong><i class="fas fa-exclamation-triangle"></i> Error:</strong> ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="this.parentElement.remove()">
            <span aria-hidden="true">&times;</span>
        </button>
        <div class="mt-2">
            <button class="btn btn-sm btn-primary" onclick="loadBookings()">
                <i class="fas fa-sync-alt"></i> Retry
            </button>
        </div>
    `;
    bookingsList.innerHTML = '';
    bookingsList.appendChild(errorDiv);
    
    // Auto-remove after 15 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.remove();
        }
    }, 15000);
}

// Display bookings - optimized for speed
function displayBookings(bookings) {
    const bookingsList = document.getElementById('bookings-list');
    if (!bookingsList) return;
    
    // Clear existing content
    bookingsList.innerHTML = '';
    
    // Show/hide control panel
    const controlPanel = document.getElementById('bookings-control-panel');
    const deleteAllBtn = document.getElementById('deleteAllBtn');
    if (controlPanel && deleteAllBtn) {
        if (bookings.length > 0) {
            controlPanel.style.display = 'block';
            deleteAllBtn.style.display = 'inline-block';
        } else {
            controlPanel.style.display = 'none';
            deleteAllBtn.style.display = 'none';
        }
    }
    
    // Store bookings globally for delete all function
    window.allBookings = bookings;
    
    // Group bookings by booking_reference
    const groupedBookings = {};
    bookings.forEach(booking => {
        const ref = booking.booking_reference || 'UNKNOWN';
        if (!groupedBookings[ref]) {
            groupedBookings[ref] = [];
        }
        groupedBookings[ref].push(booking);
    });
    
    const fragment = document.createDocumentFragment();
    const headerDiv = document.createElement('div');
    headerDiv.className = 'row mb-4';
    headerDiv.innerHTML = `<div class="col-12"><h4 class="text-center mb-3"><i class="fas fa-plane text-primary"></i> Your Booked Tickets (${Object.keys(groupedBookings).length})</h4><p class="text-center text-muted">All your confirmed flights with complete booking details</p></div>`;
    fragment.appendChild(headerDiv);
    
    // Render each booking group (round trips will show as one card)
    Object.keys(groupedBookings).forEach((bookingRef, groupIndex) => {
        const bookingGroup = groupedBookings[bookingRef];
        const isRoundTrip = bookingGroup.length > 1;
        
        // Use first booking for shared info (passenger, etc.)
        const firstBooking = bookingGroup[0];
        
        // Fast batch rendering
        bookingGroup.forEach((booking, index) => {
            const formattedDate = booking.date && booking.date !== 'N/A' 
                ? new Date(booking.date).toLocaleDateString('en-IN', {year: 'numeric', month: 'long', day: 'numeric'})
                : 'N/A';
            
            const timeDisplay = booking.departure_time && booking.arrival_time
                ? `${booking.departure_time.substring(0,5)} - ${booking.arrival_time.substring(0,5)}`
                : 'Not available';
            
            const card = document.createElement('div');
            card.className = 'card mb-4 shadow-sm booking-card';
            // Add booking reference and ticket_id as data attributes for easier finding
            card.setAttribute('data-booking-ref', booking.booking_reference || '');
            card.setAttribute('data-ticket-id', booking.ticket_id || '');
            card.innerHTML = `
            <div class="card-header bg-gradient text-white position-relative" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <!-- Checkbox for selection -->
                <div class="position-absolute" style="top: 15px; right: 15px; z-index: 10;">
                    <input type="checkbox" class="booking-checkbox" 
                           value="${booking.ticket_id || ''}" 
                           data-ticket-id="${booking.ticket_id || ''}"
                           onchange="updateSelectedCount()" 
                           style="width: 20px; height: 20px; cursor: pointer; background: white; border-radius: 3px;">
                </div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-plane"></i> ${booking.flight_company || 'Flight'} ${booking.flight_number || 'N/A'}
                        </h5>
                        <p class="mb-0" style="font-size: 0.95em; opacity: 0.9;">
                            <i class="fas fa-map-marker-alt"></i> <strong>From:</strong> ${booking.from || 'N/A'} 
                            <i class="fas fa-arrow-right mx-2"></i> 
                            <strong>To:</strong> ${booking.to || 'N/A'}
                        </p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-light mr-2">
                            <i class="fas fa-check-circle"></i> ${booking.status || 'Confirmed'}
                        </span>
                        <small class="text-light">#${index + 1}</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Booking Reference Highlight -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-success border-left" style="border-left: 4px solid #28a745 !important; background: #d4edda;">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1"><i class="fas fa-key"></i> Booking Reference</h6>
                                    <p class="mb-0 text-muted">Use this reference to check status or make changes</p>
                                </div>
                                <div class="col-md-4 text-md-right">
                                    <h4 class="mb-0" style="font-family: 'Courier New', monospace; color: #28a745; letter-spacing: 1px;">
                                        ${booking.booking_reference || 'N/A'}
                                    </h4>
                                    <button class="btn btn-outline-success btn-sm mt-1" onclick="copyBookingReference('${booking.booking_reference || ''}')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Passenger Information -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="fas fa-user"></i> Passenger Information</h6>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Name:</strong></p>
                        <p class="text-muted">${booking.passenger_name || 'N/A'}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Email:</strong></p>
                        <p class="text-muted">${booking.email || 'N/A'}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-1"><strong>Phone:</strong></p>
                        <p class="text-muted">${booking.phone || 'N/A'}</p>
                    </div>
                </div>
                
                <hr>
                
                <!-- Flight Details -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="fas fa-plane"></i> Flight Details</h6>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <h6 class="text-muted"><i class="fas fa-route"></i> Route</h6>
                        <p class="mb-0">
                            <strong>${booking.from || 'N/A'}</strong> <i class="fas fa-arrow-right text-primary"></i> <strong>${booking.to || 'N/A'}</strong>
                        </p>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <h6 class="text-muted"><i class="fas fa-calendar"></i> Departure Date</h6>
                        <p class="mb-0">${formattedDate}</p>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <h6 class="text-muted"><i class="fas fa-clock"></i> Flight Time</h6>
                        <p class="mb-0">${timeDisplay}</p>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-3">
                        <h6 class="text-muted"><i class="fas fa-rupee-sign"></i> Price</h6>
                        <p class="mb-0"><strong class="text-success" style="font-size: 1.2em;">₹${booking.price || '0.00'}</strong></p>
                    </div>
                </div>
                
                <hr>
                
                <!-- Seat and Class Information -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="fas fa-info-circle"></i> Booking Details</h6>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6 class="text-muted"><i class="fas fa-chair"></i> Seat Number</h6>
                        <p class="mb-0">
                            <span class="badge badge-secondary" style="font-size: 1.1em; padding: 8px 12px;">
                                ${booking.seat_number || 'N/A'}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6 class="text-muted"><i class="fas fa-briefcase"></i> Travel Class</h6>
                        <p class="mb-0">
                            <span class="badge badge-info" style="font-size: 1.1em; padding: 8px 12px;">
                                ${booking.class || 'N/A'}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6 class="text-muted"><i class="fas fa-ticket-alt"></i> Ticket Number</h6>
                        <p class="mb-0">
                            <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">
                                ${booking.ticket_number || 'N/A'}
                            </code>
                        </p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h6 class="text-muted"><i class="fas fa-check-circle"></i> Status</h6>
                        <p class="mb-0">
                            <span class="badge badge-success" style="font-size: 1.1em; padding: 8px 12px;">
                                ${booking.status || 'Confirmed'}
                            </span>
                        </p>
                    </div>
                </div>
                
                <hr>
                
                <!-- Action Buttons -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <a href="booking_status.html" class="btn btn-info btn-sm">
                            <i class="fas fa-search"></i> Check Status
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-danger btn-sm" onclick="deleteSingleBookingConfirm(${booking.ticket_id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
            fragment.appendChild(card);
        });
    });
    
    // Single DOM update for better performance
    bookingsList.appendChild(fragment);
}

// Cancel booking
function cancelBooking(ticketId) {
    // Show confirmation alert
    if (!confirm('Are you sure you want to cancel this booking?')) {
        // User clicked Cancel - do nothing
        return;
    }
    
    // User clicked OK - find and remove the booking card from the list
    const bookingsList = document.getElementById('bookings-list');
    if (!bookingsList) return;
    
    // Find the booking card by ticket_id (we need to store it as data attribute)
    // First, try to find it by looking for the cancel button that was clicked
    const bookingCards = bookingsList.querySelectorAll('.booking-card');
    let bookingCardToRemove = null;
    
    bookingCards.forEach(card => {
        const cancelButton = card.querySelector(`button[onclick*="cancelBooking(${ticketId})"]`);
        if (cancelButton) {
            bookingCardToRemove = card;
        }
    });
    
    // If found, remove it immediately from the UI
    if (bookingCardToRemove) {
        // Add fade-out animation
        bookingCardToRemove.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        bookingCardToRemove.style.opacity = '0';
        bookingCardToRemove.style.transform = 'translateX(-20px)';
        
        // Remove from DOM after animation
        setTimeout(() => {
            bookingCardToRemove.remove();
            
            // Check if there are no more bookings
            const remainingCards = bookingsList.querySelectorAll('.booking-card');
            if (remainingCards.length === 0) {
                // Show empty state
                const emptyDiv = document.getElementById('bookings-empty');
                if (emptyDiv) {
                    emptyDiv.style.display = 'block';
                }
                // Remove header if exists
                const headerDiv = bookingsList.querySelector('.row.mb-4');
                if (headerDiv) {
                    headerDiv.remove();
                }
            } else {
                // Update header count
                const headerH4 = bookingsList.querySelector('h4');
                if (headerH4) {
                    const newCount = remainingCards.length;
                    headerH4.innerHTML = `<i class="fas fa-plane text-primary"></i> Your Booked Tickets (${newCount})`;
                }
            }
        }, 300);
    }
    
    // Also call API to delete from backend
    fetch('./api/cancel_booking.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ticket_id: ticketId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Booking already removed from UI, just show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle"></i> Booking cancelled successfully!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            
            const bookingsSection = document.getElementById('confirmed-flights-section');
            if (bookingsSection) {
                bookingsSection.insertBefore(alertDiv, bookingsSection.querySelector('.container'));
            }
            
            // Auto-remove alert after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        } else {
            // If API call failed, reload to restore the booking
            console.error('Error cancelling booking:', data.error);
            loadBookings();
            if (typeof showSnackbar !== 'undefined') {
                showSnackbar('Error cancelling booking: ' + (data.error || 'Unknown error'), 'error');
            } else {
                alert('Error cancelling booking: ' + (data.error || 'Unknown error'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // If API call failed, reload to restore the booking
        loadBookings();
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('An error occurred while cancelling the booking. Please try again.', 'error');
        } else {
            alert('An error occurred while cancelling the booking. Please try again.');
        }
    });
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Copy booking reference to clipboard
function copyBookingReference(reference) {
    navigator.clipboard.writeText(reference).then(() => {
        // Show success feedback
        const toast = document.createElement('div');
        toast.className = 'alert alert-success position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <i class="fas fa-check-circle"></i> 
            Booking Reference copied: <strong>${reference}</strong>
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;
        document.body.appendChild(toast);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 3000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Booking Reference: ' + reference, 'info', 6000);
        } else {
            alert('Booking Reference: ' + reference);
        }
    });
}

// Scroll to bookings section
function scrollToBookingsSection() {
    const bookingsSection = document.getElementById('confirmed-flights-section');
    if (bookingsSection) {
        bookingsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Show booking success message
function showBookingSuccessMessage(bookingRef) {
    const bookingsList = document.getElementById('bookings-list');
    if (!bookingsList) return;
    
    // Remove any existing success messages
    const existingSuccess = bookingsList.querySelectorAll('.alert-success.booking-success');
    existingSuccess.forEach(msg => msg.remove());
    
    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success alert-dismissible fade show booking-success';
    successDiv.style.cssText = 'margin-bottom: 20px; border-left: 4px solid #28a745;';
    successDiv.innerHTML = `
        <h5><i class="fas fa-check-circle"></i> Booking Confirmed Successfully!</h5>
        <p class="mb-2">Your flight has been booked. Your new booking appears in the list below.</p>
        ${bookingRef ? `<p class="mb-0"><strong>Booking Reference:</strong> <code style="background: #fff; padding: 5px 10px; border-radius: 4px; font-size: 1.1em;">${bookingRef}</code></p>` : ''}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="this.parentElement.remove()">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    // Insert at the top of bookings list
    const headerDiv = bookingsList.querySelector('.row.mb-4');
    if (headerDiv) {
        bookingsList.insertBefore(successDiv, headerDiv.nextSibling);
    } else {
        bookingsList.insertBefore(successDiv, bookingsList.firstChild);
    }
    
    // Clean up URL parameters (remove booking_success and ref to prevent showing again on refresh)
    if (window.history && window.history.replaceState) {
        const url = new URL(window.location);
        url.searchParams.delete('booking_success');
        url.searchParams.delete('ref');
        url.searchParams.delete('ticket');
        window.history.replaceState({}, document.title, url.pathname + (url.search || ''));
    }
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.remove();
        }
    }, 10000);
}

// Highlight new booking after loading
function highlightNewBooking(bookingRef) {
    if (!bookingRef) return;
    
    setTimeout(() => {
        // Find card by data attribute (most reliable)
        const bookingCard = document.querySelector(`.booking-card[data-booking-ref="${bookingRef}"]`);
        
        if (bookingCard) {
            // Highlight the new booking card
            bookingCard.style.border = '3px solid #28a745';
            bookingCard.style.boxShadow = '0 0 20px rgba(40, 167, 69, 0.3)';
            bookingCard.style.transform = 'scale(1.02)';
            bookingCard.style.transition = 'all 0.3s ease';
            bookingCard.classList.add('new-booking-highlight');
            
            // Add animation keyframes if not already added
            if (!document.getElementById('booking-highlight-style')) {
                const style = document.createElement('style');
                style.id = 'booking-highlight-style';
                style.textContent = `
                    @keyframes pulse-green {
                        0%, 100% { box-shadow: 0 0 20px rgba(40, 167, 69, 0.3); }
                        50% { box-shadow: 0 0 30px rgba(40, 167, 69, 0.6); }
                    }
                    .new-booking-highlight {
                        animation: pulse-green 2s ease-in-out infinite;
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Scroll to the highlighted card
            setTimeout(() => {
                bookingCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 200);
            
            // Remove highlight after 5 seconds
            setTimeout(() => {
                bookingCard.style.border = '';
                bookingCard.style.boxShadow = '';
                bookingCard.style.transform = '';
                bookingCard.classList.remove('new-booking-highlight');
            }, 5000);
        } else {
            // Fallback: search by text content
            const bookingCards = document.querySelectorAll('.booking-card');
            bookingCards.forEach((card) => {
                const cardRef = card.getAttribute('data-booking-ref') || '';
                const refElements = card.querySelectorAll('code, h4');
                let found = false;
                
                if (cardRef === bookingRef) {
                    found = true;
                } else {
                    refElements.forEach(element => {
                        const text = element.textContent.trim();
                        if (text === bookingRef || text.includes(bookingRef)) {
                            found = true;
                        }
                    });
                }
                
                if (found) {
                    card.style.border = '3px solid #28a745';
                    card.style.boxShadow = '0 0 20px rgba(40, 167, 69, 0.3)';
                    card.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 200);
                    setTimeout(() => {
                        card.style.border = '';
                        card.style.boxShadow = '';
                        card.style.transform = '';
                    }, 5000);
                }
            });
        }
    }, 1000);
}

// Global variable to store searched flights
let storedFlights = [];
let searchParams = {};

// Search flights function - fetches and stores data
function searchFlights() {
    console.log('=== SEARCH FLIGHTS FUNCTION CALLED ===');
    console.log('🔍 Searching for flights...');
    
    const from = document.getElementById('quick_from').value;
    const to = document.getElementById('quick_to').value;
    const date = document.getElementById('quick_date').value;
    
    console.log('Form values:', { from, to, date });
    
    // Validate inputs
    if (!from || !to || !date) {
        console.warn('❌ Validation failed: Missing fields');
        showAlert('Please fill in all fields (FROM, TO, and DATE)', 'warning');
        return;
    }
    
    console.log('✅ Validation passed');
    
    if (from === to) {
        showAlert('Source and destination cannot be the same', 'warning');
        return;
    }
    
    // Store search parameters
    searchParams = { from, to, date };
    
    // Show loading message
    const resultsSection = document.getElementById('search-results-section');
    const resultsContainer = document.getElementById('search-results-container');
    const resultsSubtitle = document.getElementById('search-results-subtitle');
    
    if (resultsSection) {
        resultsSection.style.display = 'block';
        resultsSubtitle.textContent = 'Searching...';
        resultsContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                <p class="mt-3">Searching for flights from ${from} to ${to}...</p>
            </div>
        `;
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    // Fetch flights from API
    fetch('search_flights.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ from, to, date })
    })
    .then(response => {
        console.log('📡 API Response Status:', response.status);
        return response.text().then(text => {
            console.log('📄 Raw Response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON Parse Error:', e);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        console.log('✅ Search complete! Data received:', data);
        
        const flights = Array.isArray(data) ? data : (data.flights || []);
        
        // STORE the flights data
        storedFlights = flights;
        console.log(`💾 Stored ${storedFlights.length} flights in memory`);
        
        if (data.error) {
            console.warn('Database error, using sample flights:', data.error);
            // Use sample flights as fallback
            const sampleFlights = getSampleFlights(from, to);
            storedFlights = sampleFlights;
            resultsSubtitle.textContent = `Found ${sampleFlights.length} sample flight(s) from ${from} to ${to}`;
            displaySearchResults(sampleFlights, from, to, date);
            showAlert(`Showing ${sampleFlights.length} sample flight(s). Database may be empty.`, 'info');
            return;
        }
        
        if (flights.length === 0) {
            console.log('No flights in database, using sample flights');
            // Use sample flights as fallback
            const sampleFlights = getSampleFlights(from, to);
            storedFlights = sampleFlights;
            resultsSubtitle.textContent = `Found ${sampleFlights.length} sample flight(s) from ${from} to ${to}`;
            displaySearchResults(sampleFlights, from, to, date);
            showAlert(`Showing ${sampleFlights.length} sample flight(s). Database may be empty.`, 'info');
            return;
        }
        
        // Automatically display the search results
        console.log('✅ Search complete! Auto-displaying results...');
        resultsSubtitle.textContent = `Found ${flights.length} flight(s) from ${from} to ${to}`;
        displaySearchResults(flights, from, to, date);
        
        showAlert(`Search successful! Found ${flights.length} flight(s).`, 'success');
    })
    .catch(error => {
        console.error('❌ Error:', error);
        console.log('Using sample flights as fallback due to error');
        // Use sample flights as fallback
        const sampleFlights = getSampleFlights(from, to);
        storedFlights = sampleFlights;
        resultsSubtitle.textContent = `Found ${sampleFlights.length} sample flight(s) from ${from} to ${to}`;
        displaySearchResults(sampleFlights, from, to, date);
        showAlert(`Showing ${sampleFlights.length} sample flight(s). Database connection failed.`, 'warning');
    });
}

// Show stored flights function - displays previously searched data
function showStoredFlights() {
    console.log('=== SHOW STORED FLIGHTS FUNCTION CALLED ===');
    console.log('📋 Showing stored flights...');
    console.log('Current storedFlights:', storedFlights);
    console.log('Number of stored flights:', storedFlights ? storedFlights.length : 0);
    
    const resultsSection = document.getElementById('search-results-section');
    const resultsContainer = document.getElementById('search-results-container');
    const resultsSubtitle = document.getElementById('search-results-subtitle');
    
    if (!resultsSection || !resultsContainer) {
        console.error('❌ Results section not found!');
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Results section not found. Please refresh the page.', 'error');
        } else {
            alert('Results section not found. Please refresh the page.');
        }
        return;
    }
    
    console.log('✅ Results section elements found');
    
    // Check if we have stored flights
    if (!storedFlights || storedFlights.length === 0) {
        console.warn('⚠️ No stored flights available');
        resultsSection.style.display = 'block';
        resultsSubtitle.textContent = 'No search results available';
        resultsContainer.innerHTML = `
            <div class="alert alert-warning text-center">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>No Search Results</h4>
                <p>Please click the <strong>"Search"</strong> button first to find flights.</p>
                <p class="text-muted">Fill in FROM, TO, and DATE, then click Search.</p>
            </div>
        `;
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }
    
    // Show results section
    resultsSection.style.display = 'block';
    resultsSubtitle.textContent = `Showing ${storedFlights.length} flight(s) from ${searchParams.from || 'origin'} to ${searchParams.to || 'destination'}`;
    
    // Display the stored flights
    displaySearchResults(storedFlights, searchParams.from, searchParams.to, searchParams.date);
    
    // Scroll to results
    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Show all available flights function (for backward compatibility)
function showAllAvailableFlights() {
    console.log('📋 Showing all available flights...');
    
    // Show loading state in results section
    const resultsSection = document.getElementById('search-results-section');
    const resultsContainer = document.getElementById('search-results-container');
    const resultsSubtitle = document.getElementById('search-results-subtitle');
    
    if (!resultsSection || !resultsContainer) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Results section not found. Please refresh the page.', 'error');
        } else {
            alert('Results section not found. Please refresh the page.');
        }
        return;
    }
    
    // Show results section with loading message
    resultsSection.style.display = 'block';
    resultsSubtitle.textContent = 'Loading all available flights...';
    resultsContainer.innerHTML = `
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3">Fetching all available flights...</p>
        </div>
    `;
    
    // Scroll to results
    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Fetch all flights from API
    fetch('api/get_flights.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('📡 API Response Status:', response.status);
        return response.text().then(text => {
            console.log('📄 Raw Response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON Parse Error:', e);
                throw new Error('Invalid JSON response from server');
            }
        });
    })
    .then(data => {
        console.log('✈️ All Flights Data:', data);
        
        const flights = Array.isArray(data) ? data : (data.flights || []);
        console.log('Total flights found:', flights.length);
        
        if (data.error) {
            resultsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                    <p>${data.error}</p>
                </div>
            `;
            resultsSubtitle.textContent = 'Error loading flights';
            return;
        }
        
        if (flights.length === 0) {
            resultsContainer.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h4>No Flights Available</h4>
                    <p>There are no flights in the database yet.</p>
                    <a href="add_complete_network.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus-circle"></i> Add Flights to Database
                    </a>
                </div>
            `;
            resultsSubtitle.textContent = 'No flights found';
            return;
        }
        
        // Update subtitle
        resultsSubtitle.textContent = `Showing all ${flights.length} available flight(s)`;
        
        // Group flights by route
        const flightsByRoute = {};
        flights.forEach(flight => {
            const route = `${flight.source} → ${flight.destination}`;
            if (!flightsByRoute[route]) {
                flightsByRoute[route] = [];
            }
            flightsByRoute[route].push(flight);
        });
        
        // Display flights grouped by route
        let html = '';
        Object.keys(flightsByRoute).forEach(route => {
            const routeFlights = flightsByRoute[route];
            html += `
                <div class="route-group mb-4">
                    <h4 class="mb-3" style="color:#667eea; border-bottom:2px solid #667eea; padding-bottom:10px;">
                        <i class="fas fa-route"></i> ${route}
                        <span class="badge badge-info ml-2">${routeFlights.length} flight(s)</span>
                    </h4>
            `;
            
            routeFlights.forEach((flight, index) => {
                const departureTime = flight.departing_time ? 
                    flight.departing_time.substring(0, 5) : 'N/A';
                const arrivalTime = flight.arrival_time ? 
                    flight.arrival_time.substring(0, 5) : 'N/A';
                
                html += `
                    <div class="card mb-3 shadow-sm hover-shadow" style="transition: all 0.3s ease;">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <h5 class="mb-1">
                                        <i class="fas fa-plane text-primary"></i> 
                                        ${flight.flight_company || 'Airline'}
                                    </h5>
                                    <p class="text-muted mb-0">${flight.flight_number || 'N/A'}</p>
                                </div>
                                
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="text-center">
                                            <h4 class="mb-0">${departureTime}</h4>
                                            <small class="text-muted">${flight.source || 'N/A'}</small>
                                        </div>
                                        <div class="mx-3">
                                            <i class="fas fa-arrow-right text-primary"></i>
                                        </div>
                                        <div class="text-center">
                                            <h4 class="mb-0">${arrivalTime}</h4>
                                            <small class="text-muted">${flight.destination || 'N/A'}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="text-center">
                                        <p class="mb-1 text-muted">Starting from</p>
                                        <h4 class="text-success mb-0">
                                            ₹${Math.min(
                                                flight.price_economy || 9999,
                                                flight.price_business || 9999,
                                                flight.price_first || 9999
                                            )}
                                        </h4>
                                        <small class="text-muted">
                                            <i class="fas fa-chair"></i> 
                                            ${flight.no_of_seats || 0} total seats
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 text-center">
                                    <button class="btn btn-success btn-block" onclick="bookFlight('${flight.flight_number}', '${flight.source}', '${flight.destination}', '')">
                                        <i class="fas fa-check-circle"></i> Select Flight
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Additional Details -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#details-all-${index}">
                                        <i class="fas fa-info-circle"></i> View Details
                                    </button>
                                    <div class="collapse mt-2" id="details-all-${index}">
                                        <div class="card card-body bg-light">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong>Economy Class:</strong> ₹${flight.price_economy || 'N/A'}
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Business Class:</strong> ₹${flight.price_business || 'N/A'}
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>First Class:</strong> ₹${flight.price_first || 'N/A'}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
        });
        
        resultsContainer.innerHTML = html;
    })
    .catch(error => {
        console.error('❌ Error:', error);
        resultsContainer.innerHTML = `
            <div class="alert alert-danger">
                <h5><i class="fas fa-times-circle"></i> Error Loading Flights</h5>
                <p>${error.message}</p>
                <p>Make sure XAMPP Apache and MySQL are running.</p>
                <button class="btn btn-primary mt-2" onclick="showAllAvailableFlights()">
                    <i class="fas fa-redo"></i> Try Again
                </button>
            </div>
        `;
        resultsSubtitle.textContent = 'Error occurred';
    });
}

// Sample flights generator (same as ticket booking page)
function getSampleFlights(from, to) {
    // Sample flights matching the ticket booking page
    const allSampleFlights = [
        { flight_number: 'AI101', flight_company: 'Air India', price_economy: 4500, price_business: 9500, price_first: 15000, departing_time: '06:30', arrival_time: '09:05', source: 'Hyderabad', destination: 'Pune', available_seats: 45 },
        { flight_number: '6E202', flight_company: 'IndiGo', price_economy: 4000, price_business: 9000, price_first: 14500, departing_time: '09:15', arrival_time: '11:50', source: 'Hyderabad', destination: 'Pune', available_seats: 40 },
        { flight_number: 'UK303', flight_company: 'Vistara', price_economy: 5000, price_business: 10000, price_first: 15500, departing_time: '12:45', arrival_time: '15:20', source: 'Hyderabad', destination: 'Pune', available_seats: 35 },
        { flight_number: 'SG404', flight_company: 'SpiceJet', price_economy: 3800, price_business: 8800, price_first: 13800, departing_time: '15:00', arrival_time: '17:35', source: 'Hyderabad', destination: 'Pune', available_seats: 50 },
        { flight_number: 'G8505', flight_company: 'GoAir', price_economy: 4200, price_business: 9200, price_first: 14800, departing_time: '18:30', arrival_time: '21:05', source: 'Hyderabad', destination: 'Pune', available_seats: 38 },
        { flight_number: 'AA606', flight_company: 'AirAsia', price_economy: 3900, price_business: 8900, price_first: 14200, departing_time: '21:00', arrival_time: '23:35', source: 'Hyderabad', destination: 'Pune', available_seats: 42 },
        
        // Bangalore to Delhi
        { flight_number: 'AI201', flight_company: 'Air India', price_economy: 5500, price_business: 11000, price_first: 18000, departing_time: '07:00', arrival_time: '09:30', source: 'Bangalore', destination: 'Delhi', available_seats: 50 },
        { flight_number: '6E302', flight_company: 'IndiGo', price_economy: 5000, price_business: 10500, price_first: 17000, departing_time: '10:30', arrival_time: '13:00', source: 'Bangalore', destination: 'Delhi', available_seats: 45 },
        { flight_number: 'UK403', flight_company: 'Vistara', price_economy: 6000, price_business: 12000, price_first: 19000, departing_time: '14:00', arrival_time: '16:30', source: 'Bangalore', destination: 'Delhi', available_seats: 40 },
        
        // Mumbai to Chennai
        { flight_number: 'AI301', flight_company: 'Air India', price_economy: 4800, price_business: 9800, price_first: 16000, departing_time: '08:15', arrival_time: '10:30', source: 'Mumbai', destination: 'Chennai', available_seats: 48 },
        { flight_number: '6E402', flight_company: 'IndiGo', price_economy: 4300, price_business: 9300, price_first: 15500, departing_time: '11:45', arrival_time: '14:00', source: 'Mumbai', destination: 'Chennai', available_seats: 43 },
        
        // Delhi to Mumbai
        { flight_number: 'AI401', flight_company: 'Air India', price_economy: 5200, price_business: 10200, price_first: 17500, departing_time: '09:00', arrival_time: '11:15', source: 'Delhi', destination: 'Mumbai', available_seats: 52 },
        { flight_number: 'SG502', flight_company: 'SpiceJet', price_economy: 4700, price_business: 9700, price_first: 16500, departing_time: '13:30', arrival_time: '15:45', source: 'Delhi', destination: 'Mumbai', available_seats: 47 },
        
        // Kolkata to Bangalore
        { flight_number: 'AI501', flight_company: 'Air India', price_economy: 5800, price_business: 11500, price_first: 18500, departing_time: '06:45', arrival_time: '09:30', source: 'Kolkata', destination: 'Bangalore', available_seats: 44 },
        { flight_number: '6E602', flight_company: 'IndiGo', price_economy: 5300, price_business: 11000, price_first: 18000, departing_time: '12:15', arrival_time: '15:00', source: 'Kolkata', destination: 'Bangalore', available_seats: 41 },
        
        // Add more routes for all 10 cities
        { flight_number: 'AI601', flight_company: 'Air India', price_economy: 4600, price_business: 9600, price_first: 15800, departing_time: '07:30', arrival_time: '10:00', source: 'Chennai', destination: 'Hyderabad', available_seats: 46 },
        { flight_number: 'UK701', flight_company: 'Vistara', price_economy: 5100, price_business: 10100, price_first: 16800, departing_time: '08:45', arrival_time: '11:15', source: 'Pune', destination: 'Kolkata', available_seats: 39 },
        { flight_number: 'SG801', flight_company: 'SpiceJet', price_economy: 4400, price_business: 9400, price_first: 15200, departing_time: '10:00', arrival_time: '12:30', source: 'Kochi', destination: 'Mumbai', available_seats: 51 },
        { flight_number: 'G8901', flight_company: 'GoAir', price_economy: 4900, price_business: 9900, price_first: 16200, departing_time: '14:30', arrival_time: '17:00', source: 'Goa', destination: 'Delhi', available_seats: 37 },
        { flight_number: 'AA1001', flight_company: 'AirAsia', price_economy: 5400, price_business: 10400, price_first: 17200, departing_time: '16:45', arrival_time: '19:15', source: 'Ahmedabad', destination: 'Bangalore', available_seats: 49 }
    ];
    
    // Filter flights by from/to if specified
    let filteredFlights = allSampleFlights;
    
    if (from && to) {
        filteredFlights = allSampleFlights.filter(f => f.source === from && f.destination === to);
        
        // If no exact match, show all flights from the source city
        if (filteredFlights.length === 0 && from) {
            filteredFlights = allSampleFlights.filter(f => f.source === from);
        }
        
        // If still no match, show all flights to the destination city
        if (filteredFlights.length === 0 && to) {
            filteredFlights = allSampleFlights.filter(f => f.destination === to);
        }
        
        // If still no match, show all flights
        if (filteredFlights.length === 0) {
            filteredFlights = allSampleFlights;
        }
    } else if (from) {
        filteredFlights = allSampleFlights.filter(f => f.source === from);
    } else if (to) {
        filteredFlights = allSampleFlights.filter(f => f.destination === to);
    }
    
    return filteredFlights;
}

/* ========================================
   MODERN CAROUSEL FUNCTIONALITY
   ======================================== */

let currentSlide = 0;
let autoSlideInterval;
let slides = [];
let dots = [];
let totalSlides = 0;

// Initialize carousel when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for all elements to be ready
    setTimeout(() => {
        slides = document.querySelectorAll('.carousel-slide');
        dots = document.querySelectorAll('.dot');
        totalSlides = slides.length;
        console.log('Carousel initializing with', totalSlides, 'slides');
        initializeCarousel();
    }, 100);
});

function initializeCarousel() {
    if (totalSlides === 0) return;
    
    // Show first slide
    showSlide(0);
    
    // Start automatic sliding
    startAutoSlide();
    
    // Pause on hover
    const carouselWrapper = document.querySelector('.carousel-wrapper');
    if (carouselWrapper) {
        carouselWrapper.addEventListener('mouseenter', stopAutoSlide);
        carouselWrapper.addEventListener('mouseleave', startAutoSlide);
    }
    
    // Touch/swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    if (carouselWrapper) {
        carouselWrapper.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        carouselWrapper.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
    }
    
    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            // Swipe left - next slide
            changeSlide(1);
        }
        if (touchEndX > touchStartX + 50) {
            // Swipe right - previous slide
            changeSlide(-1);
        }
    }
}

function showSlide(index) {
    // Re-query slides and dots in case they weren't ready before
    if (slides.length === 0) {
        slides = document.querySelectorAll('.carousel-slide');
        dots = document.querySelectorAll('.dot');
        totalSlides = slides.length;
    }
    
    if (totalSlides === 0) {
        console.log('No slides found');
        return;
    }
    
    // Wrap around if necessary
    if (index >= totalSlides) {
        currentSlide = 0;
    } else if (index < 0) {
        currentSlide = totalSlides - 1;
    } else {
        currentSlide = index;
    }
    
    console.log('Showing slide:', currentSlide);
    
    // Update slides
    const slidesContainer = document.getElementById('carouselSlides');
    if (slidesContainer) {
        const offset = -currentSlide * 100;
        slidesContainer.style.transform = `translateX(${offset}%)`;
        
        // Update active class on slides
        slides.forEach((slide, i) => {
            if (i === currentSlide) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });
    }
    
    // Update dots
    dots.forEach((dot, i) => {
        if (i === currentSlide) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function changeSlide(direction) {
    console.log(`Changing slide by ${direction}`);
    stopAutoSlide();
    showSlide(currentSlide + direction);
    startAutoSlide();
}

function goToSlide(index) {
    console.log(`Going to slide ${index}`);
    stopAutoSlide();
    showSlide(index);
    startAutoSlide();
}

function startAutoSlide() {
    stopAutoSlide(); // Clear any existing interval
    autoSlideInterval = setInterval(() => {
        showSlide(currentSlide + 1);
    }, 5000); // Change slide every 5 seconds
}

function stopAutoSlide() {
    if (autoSlideInterval) {
        clearInterval(autoSlideInterval);
    }
}

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
        changeSlide(-1);
    } else if (e.key === 'ArrowRight') {
        changeSlide(1);
    }
});

// Bulk delete functions for confirmed flights
function toggleSelectAllBookings() {
    const selectAllCheckbox = document.getElementById('selectAllBookings');
    const checkboxes = document.querySelectorAll('.booking-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        updateCardVisualState(checkbox);
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.booking-checkbox');
    const checkedBoxes = document.querySelectorAll('.booking-checkbox:checked');
    const count = checkedBoxes.length;
    const total = checkboxes.length;
    
    const selectedCountEl = document.getElementById('selectedCount');
    if (selectedCountEl) {
        selectedCountEl.textContent = count;
    }
    
    // Update "Select All" checkbox state
    const selectAllCheckbox = document.getElementById('selectAllBookings');
    if (selectAllCheckbox && total > 0) {
        selectAllCheckbox.checked = count === total;
        selectAllCheckbox.indeterminate = count > 0 && count < total;
    }
    
    // Update all checkbox visual states
    checkboxes.forEach(checkbox => updateCardVisualState(checkbox));
}

function updateCardVisualState(checkbox) {
    const card = checkbox.closest('.booking-card');
    if (card) {
        if (checkbox.checked) {
            card.style.border = '2px solid #667eea';
            card.style.backgroundColor = '#f0f8ff';
            // For table rows, add selected class
            card.classList.add('table-selected');
        } else {
            card.style.border = '';
            card.style.backgroundColor = '';
            // Remove selected class from table rows
            card.classList.remove('table-selected');
        }
    }
}

function deleteSelectedBookings() {
    const checkboxes = document.querySelectorAll('.booking-checkbox:checked');
    
    if (checkboxes.length === 0) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Please select at least one booking to delete.', 'warning');
        } else {
            alert('Please select at least one booking to delete.');
        }
        return;
    }

    const confirmed = confirm(`Are you sure you want to delete ${checkboxes.length} booking(s)?`);
    if (!confirmed) {
        return;
    }

    const ticketIds = Array.from(checkboxes).map(cb => cb.value);
    
    // Delete bookings one by one
    Promise.all(ticketIds.map(ticketId => deleteSingleBooking(ticketId)))
        .then(results => {
            const successCount = results.filter(r => r.success).length;
            const failCount = results.filter(r => !r.success).length;

            if (failCount === 0) {
                if (typeof showSnackbar !== 'undefined') {
                    showSnackbar(`Successfully deleted ${successCount} booking(s).`, 'success');
                } else {
                    alert(`Successfully deleted ${successCount} booking(s).`);
                }
            } else {
                if (typeof showSnackbar !== 'undefined') {
                    showSnackbar(`Deleted ${successCount} booking(s). ${failCount} failed.`, 'warning');
                } else {
                    alert(`Deleted ${successCount} booking(s). ${failCount} failed.`);
                }
            }

            // Reload bookings
            loadBookings();
        })
        .catch(error => {
            console.error('Error deleting bookings:', error);
            if (typeof showSnackbar !== 'undefined') {
                showSnackbar('An error occurred while deleting bookings.', 'error');
            } else {
                alert('An error occurred while deleting bookings.');
            }
        });
}

function deleteAllBookings() {
    const bookings = window.allBookings || [];
    
    if (bookings.length === 0) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('No bookings to delete.', 'info');
        } else {
            alert('No bookings to delete.');
        }
        return;
    }

    const confirmed = confirm(`Are you absolutely sure you want to delete ALL ${bookings.length} confirmed flight(s)?\n\nThis action cannot be undone!`);
    if (!confirmed) {
        return;
    }

    // Show loading state
    const deleteAllBtn = document.getElementById('deleteAllBtn');
    if (!deleteAllBtn) {
        if (typeof showSnackbar !== 'undefined') {
            showSnackbar('Error: Delete button not found. Please refresh the page.', 'error');
        } else {
            alert('Error: Delete button not found. Please refresh the page.');
        }
        return;
    }
    
    const originalText = deleteAllBtn.innerHTML;
    deleteAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting All...';
    deleteAllBtn.disabled = true;

    // Delete all bookings
    Promise.all(bookings.map(booking => deleteSingleBooking(booking.ticket_id)))
        .then(results => {
            const successCount = results.filter(r => r.success).length;
            const failCount = results.filter(r => !r.success).length;

            if (failCount === 0) {
                if (typeof showSnackbar !== 'undefined') {
                    showSnackbar(`Successfully deleted all ${successCount} booking(s).`, 'success');
                } else {
                    alert(`Successfully deleted all ${successCount} booking(s).`);
                }
            } else {
                if (typeof showSnackbar !== 'undefined') {
                    showSnackbar(`Deleted ${successCount} booking(s). ${failCount} failed.`, 'warning');
                } else {
                    alert(`Deleted ${successCount} booking(s). ${failCount} failed.`);
                }
            }

            // Reset delete button first
            deleteAllBtn.innerHTML = originalText;
            deleteAllBtn.disabled = false;
            
            // Clear the bookings from window
            window.allBookings = [];
            
            // Reload bookings to refresh the interface
            loadBookings();
        })
        .catch(error => {
            console.error('Error deleting all bookings:', error);
            if (typeof showSnackbar !== 'undefined') {
                showSnackbar('An error occurred while deleting bookings.', 'error');
            } else {
                alert('An error occurred while deleting bookings.');
            }
            deleteAllBtn.innerHTML = originalText;
            deleteAllBtn.disabled = false;
        });
}

function deleteSingleBooking(ticketId) {
    return fetch('./api/cancel_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ticket_id: ticketId
        })
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Error deleting booking:', error);
        return { success: false, error: error.message };
    });
}

function deleteSingleBookingConfirm(ticketId) {
    // Find and remove the booking card from the UI immediately
    const bookingsList = document.getElementById('bookings-list');
    if (!bookingsList) return;
    
    const bookingCards = bookingsList.querySelectorAll('.booking-card');
    let bookingCardToRemove = null;
    
    bookingCards.forEach(card => {
        if (card.getAttribute('data-ticket-id') == ticketId) {
            bookingCardToRemove = card;
        }
    });
    
    // If found, remove it immediately from the UI
    if (bookingCardToRemove) {
        bookingCardToRemove.remove();
        
        // Check if there are no more bookings
        const remainingCards = bookingsList.querySelectorAll('.booking-card');
        if (remainingCards.length === 0) {
            // Show empty state
            const emptyDiv = document.getElementById('bookings-empty');
            if (emptyDiv) {
                emptyDiv.style.display = 'block';
            }
            // Hide control panel
            const controlPanel = document.getElementById('bookings-control-panel');
            if (controlPanel) {
                controlPanel.style.display = 'none';
            }
            // Remove header if exists
            const headerDiv = bookingsList.querySelector('.row.mb-4');
            if (headerDiv) {
                headerDiv.remove();
            }
        } else {
            // Update header count
            const headerH4 = bookingsList.querySelector('h4');
            if (headerH4) {
                const newCount = remainingCards.length;
                headerH4.innerHTML = `<i class="fas fa-plane text-primary"></i> Your Booked Tickets (${newCount})`;
            }
        }
    }
    
    // Call API to delete from backend silently (no alerts)
    deleteSingleBooking(ticketId)
        .catch(error => {
            console.error('Error:', error);
        });
}

// Export functions for global access
window.quickSearch = quickSearch;
window.performQuickSearch = performQuickSearch;
window.cancelBooking = cancelBooking;
window.copyBookingReference = copyBookingReference;
window.loadBookings = loadBookings;
window.bookFlight = bookFlight;
window.displaySearchResults = displaySearchResults;
window.showAllAvailableFlights = showAllAvailableFlights;
window.searchFlights = searchFlights;
window.showStoredFlights = showStoredFlights;
window.getSampleFlights = getSampleFlights;
window.changeSlide = changeSlide;
window.goToSlide = goToSlide;
window.toggleSelectAllBookings = toggleSelectAllBookings;
window.updateSelectedCount = updateSelectedCount;
window.deleteSelectedBookings = deleteSelectedBookings;
window.deleteAllBookings = deleteAllBookings;
window.deleteSingleBookingConfirm = deleteSingleBookingConfirm;
