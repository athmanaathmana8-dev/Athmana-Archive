# Enhanced Airport Management System

A comprehensive web-based airport management system with advanced features for flight booking, seat reservation, payment processing, and administrative controls.

## 🚀 Features

### ✈️ Enhanced Ticket Booking System
- **Real-time Flight Search**: Search for available flights by date, destination, and availability
- **Interactive Seat Selection**: Visual seat map with real-time availability status
- **Seat Reservation**: Temporary seat reservation system (15-minute hold)
- **Multi-class Support**: Economy, Business, and First Class options
- **Advanced Validation**: Comprehensive form validation with real-time feedback
- **Payment Integration**: Multiple payment methods with transaction tracking

### 🛡️ Flight Blocking Management
- **Administrative Control**: Block flights for specific time periods
- **Reason Tracking**: Record reasons for flight blocking
- **Real-time Updates**: Immediate effect on flight availability
- **Block History**: Track all blocking activities

### 💳 Payment & Validation System
- **Multiple Payment Methods**: Credit Card, Debit Card, Net Banking, UPI
- **Transaction Tracking**: Complete payment history and status
- **Secure Processing**: Transaction validation and confirmation
- **Booking References**: Unique booking and ticket numbers

### 🎫 Seat Management
- **Dynamic Seat Maps**: Real-time seat availability visualization
- **Class-based Layout**: Different seat configurations for each class
- **Reservation System**: Temporary seat holds with expiry
- **Conflict Prevention**: Prevents double booking

### 🔍 Advanced Search & Filtering
- **Date-based Search**: Find flights by departure date
- **Route Filtering**: Search by source and destination
- **Availability Check**: Real-time seat availability
- **Price Comparison**: Compare prices across different classes

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 4
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **APIs**: RESTful API endpoints
- **Styling**: Custom CSS with modern design patterns

## 📁 Project Structure

```
MINIPROJECT1/
├── Frontpage.html              # Landing page
├── Ticketbooking.html          # Enhanced booking interface
├── Ticketbooking.php           # Booking processing backend
├── Ticketbooking.js            # Frontend booking logic
├── Flightschedule.php          # Flight schedule display
├── Adminpage.php               # Admin dashboard
├── flight_blocking.html        # Flight blocking interface
├── block_flight.php            # Flight blocking backend
├── unblock_flight.php          # Flight unblocking backend
├── search_flights.php          # Flight search API
├── seat_reservation.php        # Seat management API
├── database_schema.sql         # Complete database schema
├── css/
│   ├── Ticketbooking.css       # Enhanced booking styles
│   ├── Adminpage.css           # Admin interface styles
│   └── Frontpage.css           # Landing page styles
└── README.md                   # This file
```

## 🗄️ Database Schema

The system uses a comprehensive MySQL database with the following key tables:

- **airport**: Airport information
- **flights**: Flight details with pricing and blocking status
- **seats**: Individual seat management
- **passenger**: Passenger information
- **tickets**: Ticket bookings and confirmations
- **payments**: Payment transactions
- **flight_blocks**: Flight blocking records
- **reservations**: Seat reservation tracking
- **employee**: Staff management

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Installation Steps

1. **Clone/Download the project**
   ```bash
   git clone [repository-url]
   cd AIRPORT_MANAGEMENT-SYSTEM/MINIPROJECT1
   ```

2. **Database Setup**
   ```sql
   -- Import the database schema
   mysql -u root -p < database_schema.sql
   ```

3. **Configure Database Connection**
   - Update database credentials in all PHP files:
   ```php
   $servername = "localhost";
   $username = "your_username";
   $password = "your_password";
   $database_name = "airport_management_system";
   ```

4. **Web Server Configuration**
   - Place files in your web server directory
   - Ensure PHP is enabled
   - Configure proper file permissions

5. **Access the Application**
   - Open `Frontpage.html` in your web browser
   - Navigate to different sections using the navigation menu

## 📱 Usage Guide

### For Passengers

1. **Search Flights**
   - Visit the booking page
   - Select source, destination, and date
   - Click "Search Flights"

2. **Book Tickets**
   - Select a flight from search results
   - Fill in passenger details
   - Choose travel class
   - Select a seat from the interactive map
   - Complete payment

3. **Seat Selection**
   - View real-time seat availability
   - Different colors indicate seat status:
     - Green: Available
     - Blue: Selected
     - Red: Occupied
     - Yellow: Reserved

### For Administrators

1. **Flight Management**
   - Access admin panel
   - Add/remove flights
   - Manage employee records

2. **Flight Blocking**
   - Navigate to flight blocking section
   - Select flight to block
   - Set blocking period and reason
   - Monitor blocked flights

## 🔧 API Endpoints

### Flight Search
- **Endpoint**: `search_flights.php`
- **Method**: POST
- **Parameters**: `from`, `to`, `date`
- **Response**: JSON array of available flights

### Seat Management
- **Endpoint**: `seat_reservation.php`
- **Method**: POST
- **Actions**: `get_seats`, `reserve_seat`, `cancel_reservation`

### Flight Blocking
- **Endpoint**: `block_flight.php`
- **Method**: POST
- **Parameters**: `flight_number`, `blocked_by`, `blocked_from`, `blocked_until`, `reason`

## 🎨 Design Features

- **Responsive Design**: Works on desktop, tablet, and mobile
- **Modern UI**: Clean, professional interface
- **Interactive Elements**: Smooth animations and transitions
- **Accessibility**: Keyboard navigation and screen reader support
- **Color Coding**: Intuitive color schemes for different states

## 🔒 Security Features

- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Prevention**: Prepared statements and parameterized queries
- **XSS Protection**: Output escaping and sanitization
- **Transaction Management**: Database transactions for data integrity

## 🚀 Future Enhancements

- [ ] Real payment gateway integration
- [ ] Email notifications
- [ ] Mobile app development
- [ ] Advanced reporting
- [ ] Multi-language support
- [ ] API rate limiting
- [ ] Caching mechanisms

## 📞 Support

For technical support or questions:
- Email: support@airportmanagement.com
- Phone: +91-9876543210
- Documentation: [Link to detailed docs]

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📊 Performance

- **Page Load Time**: < 2 seconds
- **Database Queries**: Optimized with proper indexing
- **Responsive Design**: Mobile-first approach
- **Browser Support**: Chrome, Firefox, Safari, Edge

---

**Note**: This is a demonstration project. For production use, additional security measures, error handling, and performance optimizations should be implemented.






