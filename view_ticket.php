<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Ticket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
            }
            .ticket-container {
                box-shadow: none !important;
                border: none !important;
            }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }
        
        .ticket-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .ticket-header {
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .ticket-header h2 {
            color: #1565c0;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1565c0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title i {
            font-size: 20px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .detail-value {
            color: #666;
            font-size: 14px;
            text-align: right;
        }
        
        .highlight-value {
            background: #fff3f3;
            border: 1px solid #ffb3b3;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #d32f2f;
            display: inline-block;
        }
        
        .badge-custom {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            color: white;
        }
        
        .badge-seat {
            background: #424242;
        }
        
        .badge-class {
            background: #00897b;
        }
        
        .badge-status {
            background: #2e7d32;
        }
        
        .price-value {
            color: #2e7d32;
            font-weight: 700;
            font-size: 18px;
        }
        
        .flight-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .action-buttons {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-action {
            padding: 12px 30px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }
        
        .btn-download {
            background: #1976d2;
            color: white;
            box-shadow: 0 4px 15px rgba(25, 118, 210, 0.4);
        }
        
        .btn-download:hover {
            background: #1565c0;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.6);
            color: white;
        }
        
        .info-text {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .flight-details-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container" id="ticketContent">
        <div class="ticket-header">
            <h2><i class="fas fa-plane"></i> Flight Ticket</h2>
        </div>
        
        <div id="ticketDetails">
            <div class="text-center" style="padding: 40px;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-3">Loading ticket details...</p>
            </div>
        </div>
        
        <div class="action-buttons no-print">
            <button class="btn-action btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Ticket
            </button>
            <button class="btn-action btn-download" onclick="downloadTicket()">
                <i class="fas fa-download"></i> Download Ticket
            </button>
            <a href="Frontpage.html" class="btn-action btn-download">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        // Get booking reference from URL
        const urlParams = new URLSearchParams(window.location.search);
        let bookingRef = urlParams.get('ref');
        
        // Decode and clean the booking reference
        if (bookingRef) {
            bookingRef = decodeURIComponent(bookingRef).trim();
        }
        
        if (!bookingRef) {
            document.getElementById('ticketDetails').innerHTML = `
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> Invalid Ticket</h5>
                    <p>No booking reference provided. Please check your ticket link.</p>
                </div>
            `;
        } else {
            console.log('Loading ticket for booking reference:', bookingRef);
            loadTicketDetails(bookingRef);
        }
        
        function loadTicketDetails(bookingRef) {
            fetch('api/check_booking_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    booking_reference: bookingRef
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Ticket API response:', data);
                if (data.success) {
                    displayTicket(data);
                } else {
                    let errorMsg = data.error || 'Unable to find ticket';
                    if (data.debug_info && data.debug_info.similar_references && data.debug_info.similar_references.length > 0) {
                        errorMsg += '<br><small>Similar references found: ' + data.debug_info.similar_references.map(r => r.booking_reference).join(', ') + '</small>';
                    }
                    document.getElementById('ticketDetails').innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-times-circle"></i> Ticket Not Found</h5>
                            <p>Unable to find ticket with booking reference: <strong>${bookingRef}</strong></p>
                            <p>${errorMsg}</p>
                            <p>Please verify your booking reference and try again.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading ticket:', error);
                document.getElementById('ticketDetails').innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                        <p>An error occurred while loading your ticket: ${error.message}</p>
                        <p>Please try again later or contact support.</p>
                    </div>
                `;
            });
        }
        
        function displayTicket(data) {
            const bookings = data.bookings || [data.booking];
            const booking = data.booking || data;
            const isRoundTrip = bookings.length > 1;
            
            // Format date
            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                try {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-IN', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                } catch (e) {
                    return dateString;
                }
            }
            
            // Format time
            function formatTime(timeString) {
                if (!timeString) return 'N/A';
                return timeString.substring(0, 5);
            }
            
            let html = '';
            
            // Passenger Details Section
            html += `
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-user"></i> Passenger Details
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${booking.passenger_name || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${booking.email || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${booking.phone || 'N/A'}</span>
                    </div>
                    ${booking.date_of_birth ? `
                    <div class="detail-item">
                        <span class="detail-label">Date of Birth:</span>
                        <span class="detail-value">${booking.date_of_birth}</span>
                    </div>
                    ` : ''}
                    <div class="detail-item">
                        <span class="detail-label">Booking Reference:</span>
                        <span class="detail-value">
                            <span class="highlight-value">${booking.booking_reference || bookingRef}</span>
                        </span>
                    </div>
                </div>
            `;
            
            // Flight Details Section
            bookings.forEach((flight, index) => {
                const flightType = index === 0 ? 'Outbound' : 'Return';
                const flightIcon = index === 0 ? 'fa-plane-departure' : 'fa-plane-arrival';
                
                html += `
                    <div class="section">
                        <div class="section-title">
                            <i class="fas ${flightIcon}"></i> ${flightType} Flight Details
                        </div>
                        <div class="flight-details-grid">
                            <div>
                                <div class="detail-item">
                                    <span class="detail-label">Flight:</span>
                                    <span class="detail-value">${flight.flight_company || ''} ${flight.flight_number || ''}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Route:</span>
                                    <span class="detail-value">${flight.from || ''} → ${flight.to || ''}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date:</span>
                                    <span class="detail-value">${formatDate(flight.departure_date)}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Time:</span>
                                    <span class="detail-value">${formatTime(flight.departure_time)} - ${formatTime(flight.arrival_time)}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Ticket Number:</span>
                                    <span class="detail-value">
                                        <span class="highlight-value">${flight.ticket_number || 'N/A'}</span>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="detail-item">
                                    <span class="detail-label">Seat:</span>
                                    <span class="detail-value">
                                        <span class="badge-custom badge-seat">${flight.seat_number || 'N/A'}</span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Class:</span>
                                    <span class="detail-value">
                                        <span class="badge-custom badge-class">${flight.class || 'N/A'}</span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Price:</span>
                                    <span class="detail-value price-value">₹${parseFloat(String(flight.price || 0).replace(/,/g, '')).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">
                                        <span class="badge-custom badge-status">${flight.status || booking.payment_status || booking.status || 'Paid'}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            // Total Amount (if round trip)
            if (isRoundTrip && bookings.length === 2) {
                const price1 = parseFloat(String(bookings[0].price || 0).replace(/,/g, ''));
                const price2 = parseFloat(String(bookings[1].price || 0).replace(/,/g, ''));
                const totalPrice = price1 + price2;
                html += `
                    <div class="section">
                        <div class="section-title">
                            <i class="fas fa-rupee-sign"></i> Total Amount
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total Paid:</span>
                            <span class="detail-value price-value" style="font-size: 22px;">₹${totalPrice.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                        </div>
                    </div>
                `;
            }
            
            // Additional Info Section
            html += `
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Additional Info
                    </div>
                    <p class="info-text">This booking is confirmed and ready for travel.</p>
                    ${booking.p_id ? `
                    <div class="detail-item">
                        <span class="detail-label">Passenger ID:</span>
                        <span class="detail-value">${booking.p_id}</span>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('ticketDetails').innerHTML = html;
        }
        
        function downloadTicket() {
            const element = document.getElementById('ticketContent');
            const opt = {
                margin: 10,
                filename: 'flight_ticket_' + (bookingRef || 'ticket') + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>

