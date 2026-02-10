# 🚀 Quick Start Guide - ESP32 Weather Dashboard

## ⚡ 5-Minute Setup

### Step 1: Database Setup (2 minutes)
```bash
# Open MySQL
mysql -u root -p

# Import database
source database.sql;

# Or copy-paste SQL commands from database.sql
```

### Step 2: Configure & Deploy (1 minute)
```bash
# 1. Edit config.php with your database credentials
# 2. Upload all files to your web server
# 3. Set file permissions
chmod 755 *.php
chmod 644 *.html *.css *.js
```

### Step 3: Test Installation (1 minute)
```bash
# Open in browser
http://your-server/test.php

# Check all green checkmarks ✓
# Then delete test.php for security
```

### Step 4: ESP32 Setup (1 minute)
```cpp
// In ESP32_DHT11_Weather.ino, update:
const char* ssid = "YOUR_WIFI";
const char* password = "YOUR_PASSWORD";
const char* serverUrl = "http://YOUR_SERVER/api_send_data.php";

// Upload to ESP32
```

### Step 5: Open Dashboard
```
http://your-server/index.html
```

---

## 📂 File Structure

```
weather-dashboard/
├── 📄 index.html          # Main dashboard
├── 🎨 style.css           # Styles
├── ⚙️ script.js           # JavaScript
├── 🔧 config.php          # DB config
├── 📡 api_send_data.php   # ESP32 endpoint
├── 📊 api_get_data.php    # Data retrieval
├── 🔔 api_alerts.php      # Alert management
├── 🗄️ database.sql        # Database schema
├── 🤖 ESP32_DHT11_Weather.ino  # Arduino code
├── 📖 README.md           # Full documentation
└── 🧪 test.php            # System test
```

---

## 🔌 ESP32 Wiring

```
ESP32      DHT11
─────      ─────
3.3V   →   VCC
GND    →   GND
GPIO4  →   DATA
```

---

## 🎯 Default Alert Thresholds

| Alert Type       | Threshold | Status  |
|-----------------|-----------|---------|
| High Temp       | 35.0°C    | Enabled |
| Low Temp        | 15.0°C    | Enabled |
| High Humidity   | 80.0%     | Enabled |
| Low Humidity    | 30.0%     | Enabled |

---

## 🔍 Troubleshooting

**No data showing?**
1. Check ESP32 serial monitor for errors
2. Verify WiFi connection
3. Test API: `curl http://your-server/api_get_data.php?type=current`

**Database error?**
1. Check credentials in config.php
2. Ensure database exists
3. Run test.php to diagnose

**ESP32 not connecting?**
1. Check SSID/password
2. Use 2.4GHz WiFi only
3. Check server URL format

---

## 📱 Dashboard Features

✅ Real-time temperature & humidity  
✅ Live updates every 5 seconds  
✅ Interactive historical charts (1H/6H/24H)  
✅ Min/Max/Avg statistics  
✅ Customizable alerts  
✅ Mobile responsive  
✅ Modern dark theme  

---

## 🎓 For Your Report

**Technology Stack:**
- Frontend: HTML5, CSS3, JavaScript, Chart.js
- Backend: PHP 7.4+, MySQL
- Hardware: ESP32, DHT11
- Communication: HTTP REST API, JSON
- Real-time: AJAX polling

**Key Features:**
- Real-time monitoring
- Historical data visualization
- Alert system
- RESTful API
- Responsive design

---

## 📞 Need Help?

1. ✅ Check test.php results
2. 📖 Read full README.md
3. 🔍 Review ESP32 serial output
4. 🌐 Check browser console (F12)

---

**Good luck! 🎉**

Delete test.php after setup for security!
