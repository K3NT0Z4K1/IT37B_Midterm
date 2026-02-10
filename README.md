# ESP32 Weather Monitoring Dashboard

A real-time IoT weather monitoring system using ESP32 and DHT11 sensor with a modern web dashboard.

## 📋 Features

- **Real-time Monitoring**: Live temperature and humidity readings
- **Historical Data**: Interactive charts showing trends over time
- **Alert System**: Customizable alerts for temperature and humidity thresholds
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional interface with smooth animations
- **Statistics**: Min/Max/Average calculations for 24-hour periods

## 🛠️ Hardware Requirements

- ESP32 Development Board
- DHT11 Temperature & Humidity Sensor
- Jumper wires
- USB cable for programming
- Power supply (USB or external)

## 🔌 Wiring Diagram

```
ESP32          DHT11
─────          ─────
3.3V    →      VCC
GND     →      GND
GPIO4   →      DATA
```

## 💻 Software Requirements

### Server Side
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser (Chrome, Firefox, Safari, Edge)

### ESP32 Side
- Arduino IDE or PlatformIO
- ESP32 Board Support
- Required Libraries:
  - DHT sensor library by Adafruit
  - Adafruit Unified Sensor
  - WiFi (built-in)
  - HTTPClient (built-in)

## 📦 Installation

### 1. Database Setup

```bash
# Login to MySQL
mysql -u root -p

# Import the database
mysql -u root -p < database.sql

# Or manually create the database
# Then copy and paste the SQL commands from database.sql
```

### 2. Configure PHP Files

Edit `config.php` and update database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'weather_monitoring');
```

### 3. Deploy Web Files

Upload all files to your web server:
```
/var/www/html/weather/  (or your web root)
├── index.html
├── style.css
├── script.js
├── config.php
├── api_send_data.php
├── api_get_data.php
└── api_alerts.php
```

Set proper permissions:
```bash
chmod 755 *.php
chmod 644 *.html *.css *.js
```

### 4. Configure ESP32

1. Open `ESP32_DHT11_Weather.ino` in Arduino IDE
2. Update WiFi credentials:
   ```cpp
   const char* ssid = "YOUR_WIFI_SSID";
   const char* password = "YOUR_WIFI_PASSWORD";
   ```
3. Update server URL:
   ```cpp
   const char* serverUrl = "http://YOUR_SERVER_IP/api_send_data.php";
   ```
4. Install required libraries (Tools → Manage Libraries)
5. Select ESP32 board (Tools → Board → ESP32 Dev Module)
6. Upload to ESP32

## 🚀 Usage

### Accessing the Dashboard

1. Open your web browser
2. Navigate to: `http://your-server-ip/index.html`
3. Dashboard will automatically start receiving data from ESP32

### Dashboard Features

**Current Readings**
- Large display of current temperature and humidity
- Real-time updates every 5 seconds
- 24-hour min/max/average statistics

**Historical Charts**
- Toggle between 1H, 6H, and 24H views
- Interactive tooltips on hover
- Dual Y-axis for temperature and humidity

**Alerts**
- Automatic alerts when thresholds are exceeded
- Alert history with timestamps
- Dismiss functionality

### API Endpoints

**Send Data (ESP32)**
```
POST /api_send_data.php
Content-Type: application/json

{
  "temperature": 25.5,
  "humidity": 65.0
}
```

**Get Current Data**
```
GET /api_get_data.php?type=current
```

**Get Historical Data**
```
GET /api_get_data.php?type=history&hours=24
```

**Get Statistics**
```
GET /api_get_data.php?type=stats&hours=24
```

**Get Alerts**
```
GET /api_get_data.php?type=alerts
```

## ⚙️ Configuration

### Alert Thresholds

Update alert thresholds in the database:

```sql
UPDATE alert_settings 
SET threshold = 30.0 
WHERE alert_type = 'temp_high';
```

Or use the API:

```
POST /api_alerts.php
Content-Type: application/json

{
  "type": "temp_high",
  "threshold": 30.0,
  "enabled": true
}
```

### Update Interval

Change data send interval in ESP32 code:
```cpp
const unsigned long sendInterval = 5000; // milliseconds
```

Change dashboard refresh rate in `script.js`:
```javascript
const UPDATE_INTERVAL = 5000; // milliseconds
```

## 🐛 Troubleshooting

**ESP32 not connecting to WiFi**
- Check SSID and password
- Ensure 2.4GHz WiFi (ESP32 doesn't support 5GHz)
- Check WiFi signal strength

**Data not appearing in dashboard**
- Verify database connection in `config.php`
- Check browser console for errors (F12)
- Verify ESP32 serial monitor shows successful POST requests
- Check server logs for PHP errors

**Sensor reading NaN**
- Check DHT11 wiring
- Verify sensor is working (try simple test sketch)
- Check power supply (3.3V)
- Add pull-up resistor if needed (4.7kΩ - 10kΩ)

**Charts not loading**
- Ensure Chart.js library is loaded
- Check browser console for JavaScript errors
- Verify historical data exists in database

## 📊 Database Schema

**sensor_data**
- Stores all temperature and humidity readings
- Indexed by timestamp for fast queries

**alert_settings**
- Configurable alert thresholds
- Enable/disable individual alerts

**alert_history**
- Logs all triggered alerts
- Tracks acknowledgment status

## 🎨 Customization

### Colors and Theme

Edit CSS variables in `style.css`:
```css
:root {
    --color-accent-temp: #ff6b6b;
    --color-accent-humidity: #4ecdc4;
    /* ... */
}
```

### Fonts

Change fonts in `index.html` and `style.css`:
```css
--font-display: 'Your Font', sans-serif;
--font-mono: 'Your Mono Font', monospace;
```

## 📝 License

This project is for educational purposes. Feel free to modify and use for your own projects.

## 🤝 Contributing

This is a student project. Suggestions and improvements are welcome!

## 📧 Support

For issues or questions, please check:
1. Serial monitor output from ESP32
2. Browser console for JavaScript errors
3. PHP error logs on server
4. Database connection and data

## 🎓 Project Information

**Course**: IoT Project
**Type**: Individual Midterm Project
**Hardware**: ESP32 + DHT11
**Technology Stack**: PHP, MySQL, HTML, CSS, JavaScript, Arduino

---

**Good luck with your project! 🚀**
