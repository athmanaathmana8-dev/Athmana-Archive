<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }
        .confirmation-card { 
            margin-top: 20px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            background: white;
        }
        .header-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .header-section h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .details-section {
            padding: 15px;
        }
        .info-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .booking-ref-code {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }
        .detail-value {
            color: #666;
            font-size: 13px;
        }
        .amount-value {
            color: #28a745;
            font-weight: bold;
            font-size: 16px;
        }
        .status-badge {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        .action-buttons {
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 0 0 10px 10px;
            border-top: 1px solid #dee2e6;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 20px;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .qr-section {
            text-align: center;
        }
        #qrcode {
            display: inline-block;
            padding: 10px;
            background: white;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="confirmation-card">
            <div class="header-section">
                <h2><i class="fas fa-check-circle"></i> Booking Confirmed!</h2>
                <p class="mb-0">Your flight has been successfully booked</p>
            </div>
            
            <div class="details-section">
                <!-- Booking Reference & QR Code -->
                <div class="info-card">
                    <div class="section-title"><i class="fas fa-ticket-alt text-primary"></i> Booking Reference</div>
                    <div class="booking-ref-code text-center mb-3" id="bookingReference">Loading...</div>
                    <div class="qr-section mb-3">
                        <div id="qrcode" style="display: inline-block; padding: 15px; background: white; border-radius: 10px; border: 2px solid #dee2e6;"></div>
                        <div class="text-muted mt-2" style="font-size: 14px;">
                            <i class="fas fa-qrcode"></i> Scan or click this QR code to view your ticket<br>
                            <small style="color: #6c757d;"><i class="fas fa-info-circle"></i> Show this QR code at the airport</small>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Details -->
                <div id="bookingDetails">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Loading booking details...</p>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons text-center">
                <a href="booking_status.html" class="btn btn-primary mr-3">
                    <i class="fas fa-search"></i> Check Booking Status
                </a>
                <a href="Frontpage.html" class="btn btn-secondary mr-3">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="Ticketbooking.html" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Book Another Flight
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Get booking reference from URL
        const urlParams = new URLSearchParams(window.location.search);
        const bookingRef = urlParams.get('ref');
        const ticketNumber = urlParams.get('ticket');
        let bookingData = null;
        
        if (bookingRef) {
            document.getElementById('bookingReference').textContent = bookingRef;
            loadBookingDetails(bookingRef);
        } else {
            document.getElementById('bookingReference').textContent = 'Not Available';
            document.getElementById('bookingDetails').innerHTML = '<div class="alert alert-warning">No booking reference provided</div>';
        }
        
        function loadBookingDetails(bookingRef) {
            // Fetch booking details using the reference
            fetch('api/check_booking_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    booking_reference: bookingRef,
                    last_name: 'DETAILS' // This will be handled by the API
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bookingData = data;
                    displayBookingDetails(data);
                    generateQRCode(data, bookingRef);
                } else {
                    // If API doesn't work, show basic info from URL params
                    displayBasicDetails();
                    generateBasicQRCode(bookingRef, ticketNumber);
                }
            })
            .catch(error => {
                console.error('Error loading details:', error);
                displayBasicDetails();
                generateBasicQRCode(bookingRef, ticketNumber);
            });
        }
        
        function generateQRCode(data, bookingRef) {
            // Clear existing QR code
            document.getElementById('qrcode').innerHTML = '';
            
            // Create URL link to ticket page with booking reference
            // Ensure booking reference is properly encoded
            const cleanRef = String(bookingRef || '').trim();
            const ticketUrl = window.location.origin + window.location.pathname.replace('booking_confirmation.php', '') + 'view_ticket.php?ref=' + encodeURIComponent(cleanRef);
            console.log('QR Code URL:', ticketUrl);
            
            // Make QR code clickable to open ticket page
            const qrContainer = document.getElementById('qrcode');
            qrContainer.style.cursor = 'pointer';
            qrContainer.title = 'Click to view ticket';
            qrContainer.onclick = function() {
                window.open(ticketUrl, '_blank');
            };
            
            // Generate QR code with URL link
            try {
                new QRCode(qrContainer, {
                    text: ticketUrl,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } catch (error) {
                console.error('QR Code generation error:', error);
                // Fallback: Use QR code API if library fails
                generateQRCodeViaAPI(ticketUrl);
            }
        }
        
        function generateBasicQRCode(bookingRef, ticketNumber) {
            // Clear existing QR code
            const qrContainer = document.getElementById('qrcode');
            qrContainer.innerHTML = '';
            
            // Create URL link to ticket page
            const cleanRef = String(bookingRef || '').trim();
            const ticketUrl = window.location.origin + window.location.pathname.replace('booking_confirmation.php', '') + 'view_ticket.php?ref=' + encodeURIComponent(cleanRef);
            console.log('QR Code URL (basic):', ticketUrl);
            
            // Make QR code clickable
            qrContainer.style.cursor = 'pointer';
            qrContainer.title = 'Click to view ticket';
            qrContainer.onclick = function() {
                window.open(ticketUrl, '_blank');
            };
            
            // Generate QR code with URL link
            try {
                new QRCode(qrContainer, {
                    text: ticketUrl,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } catch (error) {
                console.error('QR Code generation error:', error);
                // Fallback: Use QR code API if library fails
                generateQRCodeViaAPI(ticketUrl);
            }
        }
        
        // Fallback function to generate QR code via API
        function generateQRCodeViaAPI(qrContent) {
            const qrCodeApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrContent)}`;
            const img = document.createElement('img');
            img.src = qrCodeApiUrl;
            img.alt = 'Flight Booking QR Code';
            img.style.width = '200px';
            img.style.height = '200px';
            img.style.borderRadius = '10px';
            img.style.cursor = 'pointer';
            img.onclick = function() {
                if (qrContent.startsWith('http')) {
                    window.open(qrContent, '_blank');
                }
            };
            document.getElementById('qrcode').appendChild(img);
        }
        
        function displayBookingDetails(data) {
            // Check if data has bookings array (round trip) or single booking
            const bookings = data.bookings || [data.booking];
            const booking = data.booking || data;
            const isRoundTrip = bookings.length > 1;
            
            let detailsHtml = `
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-user text-primary"></i> Passenger Details</h5>
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value">${booking.passenger_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${booking.email || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value">${booking.phone || 'N/A'}</span>
                        </div>
                        ${booking.date_of_birth ? `
                        <div class="detail-row">
                            <span class="detail-label">Date of Birth:</span>
                            <span class="detail-value">${booking.date_of_birth}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                <hr>
            `;
            
            // Display each flight (outbound and return if round trip)
            bookings.forEach((flight, index) => {
                const flightType = index === 0 ? 'Outbound' : 'Return';
                const flightIcon = index === 0 ? 'fa-plane-departure' : 'fa-plane-arrival';
                const timeDisplay = flight.departure_time && flight.arrival_time ? 
                    `${flight.departure_time.substring(0,5)} - ${flight.arrival_time.substring(0,5)}` : 
                    'Not available';
                
                // Format date
                let formattedDate = 'N/A';
                if (flight.departure_date) {
                    try {
                        const dateObj = new Date(flight.departure_date);
                        formattedDate = dateObj.toLocaleDateString('en-IN', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    } catch (e) {
                        formattedDate = flight.departure_date;
                    }
                }
                
                detailsHtml += `
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h5><i class="fas ${flightIcon} text-primary"></i> ${flightType} Flight Details</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="detail-row">
                                <span class="detail-label">Flight:</span>
                                <span class="detail-value">${flight.flight_company || ''} ${flight.flight_number || ''}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Route:</span>
                                <span class="detail-value">${flight.from || ''} → ${flight.to || ''}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-row">
                                <span class="detail-label">Date:</span>
                                <span class="detail-value">${formattedDate}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Time:</span>
                                <span class="detail-value">${timeDisplay}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-row">
                                <span class="detail-label">Ticket Number:</span>
                                <span class="detail-value"><code>${flight.ticket_number || 'N/A'}</code></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Seat:</span>
                                <span class="detail-value"><span class="badge badge-secondary">${flight.seat_number || 'N/A'}</span></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Class:</span>
                                <span class="detail-value"><span class="badge badge-info">${flight.class || 'N/A'}</span></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Price:</span>
                                <span class="detail-value amount-value">₹${parseFloat(String(flight.price || 0).replace(/,/g, '')).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                            </div>
                        </div>
                    </div>
                    ${index < bookings.length - 1 ? '<hr>' : ''}
                `;
            });
            
            // Add total amount section if round trip
            if (isRoundTrip && bookings.length === 2) {
                const price1 = parseFloat(String(bookings[0].price || 0).replace(/,/g, ''));
                const price2 = parseFloat(String(bookings[1].price || 0).replace(/,/g, ''));
                const totalPrice = price1 + price2;
                detailsHtml += `
                    <hr>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h5><i class="fas fa-rupee-sign text-primary"></i> Total Amount</h5>
                            <div style="font-size: 28px; font-weight: bold; color: #28a745; padding: 20px;">
                                ₹${totalPrice.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>
                        </div>
                    </div>
                `;
            }
            
            detailsHtml += `
                <hr>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h5><i class="fas fa-info-circle text-primary"></i> Booking Status</h5>
                        <div class="detail-row">
                            <span class="detail-value"><span class="status-badge">${booking.status || 'Paid'}</span></span>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('bookingDetails').innerHTML = detailsHtml;
        }
        
        function displayBasicDetails() {
            const urlParams = new URLSearchParams(window.location.search);
            const detailsHtml = `
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Booking Confirmed</h5>
                    <p>Your booking has been successfully processed. Please keep your booking reference safe for future reference.</p>
                    <p><strong>Booking Reference:</strong> <code>${bookingRef}</code></p>
                    ${ticketNumber ? `<p><strong>Ticket Number:</strong> <code>${ticketNumber}</code></p>` : ''}
                </div>
            `;
            
            document.getElementById('bookingDetails').innerHTML = detailsHtml;
        }
        
        // Copy booking reference to clipboard
        function copyBookingReference() {
            const bookingRef = document.getElementById('bookingReference').textContent;
            navigator.clipboard.writeText(bookingRef).then(() => {
                alert('Booking reference copied to clipboard!');
            });
        }
        
        // Add click to copy functionality
        document.getElementById('bookingReference').style.cursor = 'pointer';
        document.getElementById('bookingReference').title = 'Click to copy';
        document.getElementById('bookingReference').addEventListener('click', copyBookingReference);
    </script>
</body>
</html>

