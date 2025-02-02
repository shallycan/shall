<?php
include 'db.php'; // Koneksi ke database

$trackingData = null;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_code = $_POST['booking_code'];
    
    // Query untuk mendapatkan data tracking berdasarkan booking code
    $stmt = $conn->prepare("SELECT t.booking_code, u.name, t.current_location, t.destination, 
                           t.last_updated, t.latitude, t.longitude 
                           FROM Tracking t
                           JOIN Users u ON t.user_id = u.id
                           JOIN Bookings b ON t.booking_code = b.booking_code
                           WHERE t.booking_code = ?");
    $stmt->bind_param("s", $booking_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $trackingData = $result->fetch_assoc();
    } else {
        $error = "Booking code not found or journey hasn't started yet.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Track Your Journey</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white p-4">
        <h1 class="text-2xl font-bold">Track Your Journey</h1>
        <nav class="space-x-4">
                <a href="index.php" class="hover:text-gray-300">Home</a>
                <a href="etiket.php" class="hover:text-gray-300">etiket</a>
                <a href="usertracking.php" class="hover:text-gray-300">tracking</a>
            </nav>
    </header>
    
    <main class="container mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Enter Your Booking Code</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-4">
                <div>
                    <input type="text" 
                           name="booking_code" 
                           placeholder="Enter your booking code" 
                           class="w-full p-2 border border-gray-300 rounded-md"
                           required>
                </div>
                <button type="submit" 
                        class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Track Journey
                </button>
            </form>
        </div>

        <?php if ($trackingData): ?>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Journey Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="p-4 bg-gray-50 rounded-md">
                        <p class="text-gray-600">Passenger Name</p>
                        <p class="font-bold"><?php echo htmlspecialchars($trackingData['name']); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-md">
                        <p class="text-gray-600">Current Location</p>
                        <p class="font-bold"><?php echo htmlspecialchars($trackingData['current_location']); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-md">
                        <p class="text-gray-600">Destination</p>
                        <p class="font-bold"><?php echo htmlspecialchars($trackingData['destination']); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-md">
                        <p class="text-gray-600">Last Updated</p>
                        <p class="font-bold"><?php echo htmlspecialchars($trackingData['last_updated']); ?></p>
                    </div>
                </div>
                <div id="map"></div>
            </div>
            
            <script>
                let map;
                
                function initMap() {
                    const lat = <?php echo $trackingData['latitude']; ?>;
                    const lng = <?php echo $trackingData['longitude']; ?>;
                    
                    document.getElementById('map').style.display = 'block';
                    
                    map = L.map('map').setView([lat, lng], 15);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    
                    L.marker([lat, lng]).addTo(map)
                        .bindPopup('Current Location')
                        .openPopup();
                }

                window.onload = initMap;
            </script>
        <?php endif; ?>
    </main>
</body>
</html>