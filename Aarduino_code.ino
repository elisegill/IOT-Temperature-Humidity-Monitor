include <WiFi.h>
#include <DHT.h>

#define TEMP_THRESHOLD 30.0   // Temperature threshold in Celsius
#define HUMID_THRESHOLD 70.0  // Humidity threshold in %
#define DHTPIN 4
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);

//wifi credentials

const char* ssid = "PLUSNET-F7C262";           
const char* password = "M4EYq9XdXrPNkD"; 
const char* server = "192.168.1.204";   // Replace with your server IP
const int serverPort = 8888;   // MAMP default port          

WiFiClient client;

void setup() {
    Serial.begin(115200);
    dht.begin();                  // Initialize DHT sensor
    connectToWiFi();              // Connect to Wi-Fi
    testServerConnection();       // Test server connectivity
}

void connectToWiFi() {
    Serial.print("Connecting to WiFi: ");
    Serial.println(ssid);
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nConnected to WiFi!");
    Serial.print("Arduino IP Address: ");
    Serial.println(WiFi.localIP());
}

void testServerConnection() {
    Serial.println("Testing server connection...");
    if (client.connect(server, serverPort)) {
        Serial.println("Successfully connected to server!");
        client.stop();
    } else {
        Serial.println("Server is not reachable. Check IP and port.");
    }
}

void sendSensorData(float temperature, float humidity) {
    Serial.println("Sending sensor data...");
    if (client.connect(server, serverPort)) {
        // Create POST data
        String postData = "temperature=" + String(temperature) + "&humidity=" + String(humidity);

        // Send HTTP POST request
        client.println("POST /insert_data.php HTTP/1.1");
        client.println("Host: " + String(server));
        client.println("Content-Type: application/x-www-form-urlencoded");
        client.print("Content-Length: ");
        client.println(postData.length());
        client.println();
        client.print(postData);

        // Wait for server response
        Serial.println("POST request sent. Awaiting server response...");
        while (client.connected()) {
            if (client.available()) {
                String response = client.readString();
                Serial.println("Server response: ");
                Serial.println(response);
                break;
            }
        }
        client.stop();
    } else {
        Serial.println("Failed to connect to server for POST request.");
    }
}

void loop() {
    // Read temperature and humidity from DHT sensor
    Serial.println("Reading DHT sensor...");
    float temperature = dht.readTemperature();
    float humidity = dht.readHumidity();

    // Check if the readings are valid
    if (isnan(temperature) || isnan(humidity)) {
        Serial.println("Failed to read from DHT sensor!");
        return;
    }

    // Print the sensor readings
    Serial.print("Temperature: ");
    Serial.println(temperature);
    Serial.print("Humidity: ");
    Serial.println(humidity);

    // Send the data to the server
    sendSensorData(temperature, humidity);

    // Wait 5 seconds before taking another reading
    delay(5000);
}
