<?php
// Database connection
$host = 'localhost';
$dbname = 'shuttle_service';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error = '';
$ticketData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingCode = $_POST['bookingCode'];
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                b.booking_code,
                b.departure,
                b.destination,
                b.date,
                u.name,
                u.phone,
                s.seat_number
            FROM Bookings b
            JOIN Users u ON b.user_id = u.id
            JOIN Seats s ON b.seat_id = s.id
            WHERE b.booking_code = ?
        ");
        
        $stmt->execute([$bookingCode]);
        $ticketData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticketData) {
            $error = "No ticket found with this booking code.";
        }
    } catch(PDOException $e) {
        $error = "Error retrieving ticket data.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Print E-Ticket - Arnes Shuttle Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
            body {
                background: white;
            }
            .ticket {
                box-shadow: none !important;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-r from-blue-100 to-purple-100">
    <header class="bg-gradient-to-r from-blue-800 to-purple-800 text-white p-4 no-print">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-bold">Arnes Shuttle Service</h1>
            <nav class="space-x-4">
                <a href="index.php" class="hover:text-gray-300">Home</a>
                <a href="etiket.php" class="hover:text-gray-300">etiket</a>
                <a href="usertracking.php" class="hover:text-gray-300">tracking</a>
            </nav>
        </div>
    </header>

    <main class="container mx-auto mt-10 p-6">
        <!-- Search Form -->
        <div class="max-w-md mx-auto mb-8 bg-white p-6 rounded-lg shadow-md no-print">
            <h2 class="text-2xl font-bold text-center mb-6">Print Your E-Ticket</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label for="bookingCode" class="block text-gray-700">Booking Code:</label>
                    <input type="text" id="bookingCode" name="bookingCode" 
                           class="w-full p-2 border border-gray-300 rounded-md" 
                           required placeholder="Enter your booking code">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
                    Get Ticket
                </button>
            </form>
        </div>

        <!-- Ticket Display -->
        <?php if ($ticketData): ?>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg ticket">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-blue-800">Arnes Shuttle Service</h1>
                    <p class="text-gray-600">E-Ticket</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Booking Code:</p>
                    <p class="font-mono font-bold"><?php echo htmlspecialchars($ticketData['booking_code']); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-sm text-gray-600">Passenger Name</h3>
                    <p class="font-semibold"><?php echo htmlspecialchars($ticketData['name']); ?></p>
                    <p class="text-gray-700"><?php echo htmlspecialchars($ticketData['phone']); ?></p>
                </div>
                <div>
                    <h3 class="text-sm text-gray-600">Seat Number</h3>
                    <p class="font-semibold text-2xl"><?php echo htmlspecialchars($ticketData['seat_number']); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-6 mb-6">
                <div>
                    <h3 class="text-sm text-gray-600">From</h3>
                    <p class="font-semibold"><?php echo htmlspecialchars($ticketData['departure']); ?></p>
                </div>
                <div>
                    <h3 class="text-sm text-gray-600">To</h3>
                    <p class="font-semibold"><?php echo htmlspecialchars($ticketData['destination']); ?></p>
                </div>
                <div>
                    <h3 class="text-sm text-gray-600">Travel Date</h3>
                    <p class="font-semibold"><?php echo htmlspecialchars(date('d M Y', strtotime($ticketData['date']))); ?></p>
                </div>
            </div>

            <div class="border-t border-dashed my-6"></div>

            <div class="text-center text-sm text-gray-600">
                <p class="mb-1">Please arrive 15 minutes before departure</p>
                <p>This ticket is valid only for the date shown</p>
            </div>

            <div class="mt-6 text-center no-print">
                <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i>Print Ticket
                </button>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gradient-to-r from-blue-800 to-purple-800 text-white py-4 mt-10 no-print">
        <div class="container mx-auto text-center">
            <p>&copy; shall Arnes Shuttle Service. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>