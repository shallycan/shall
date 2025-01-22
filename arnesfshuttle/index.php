<?php
include 'db.php';

// Inisialisasi pesan error dan sukses
$error = '';
$success = '';
$booking_code = '';

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $departure = $_POST['departure'];
    $destination = $_POST['destination'];
    $date = $_POST['date'];
    $seat = $_POST['seat'];

    // Validasi input
    if (empty($name) || empty($phone) || empty($departure) || empty($destination) || empty($date) || empty($seat)) {
        $error = "Semua field harus diisi!";
    } else {
        // Mulai transaksi
        $conn->begin_transaction();
        try {
            // Cek ketersediaan kursi
            $stmt = $conn->prepare("SELECT status FROM Seats WHERE seat_number = ? AND status = 1");
            $stmt->bind_param("s", $seat);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Kursi sudah dipesan!");
            }
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO Users (name, phone) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $phone);
            $stmt->execute();
            $user_id = $stmt->insert_id;

            // Update status kursi
            $stmt = $conn->prepare("UPDATE Seats SET status = 0 WHERE seat_number = ?");
            $stmt->bind_param("s", $seat);
            $stmt->execute();

            // Generate booking code
            $booking_code = "BOOK-" . strtoupper(uniqid());

            // Insert booking
            $stmt = $conn->prepare("INSERT INTO Bookings (user_id, departure, destination, date, seat_id, booking_code) 
                                  VALUES (?, ?, ?, ?, (SELECT id FROM Seats WHERE seat_number = ?), ?)");
            $stmt->bind_param("isssss", $user_id, $departure, $destination, $date, $seat, $booking_code);
            $stmt->execute();

            $conn->commit();
            $success = "Pemesanan berhasil!";

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Ambil data kursi yang tersedia
$available_seats = [];
$result = $conn->query("SELECT seat_number FROM Seats WHERE status = 1");
while ($row = $result->fetch_assoc()) {
    $available_seats[] = $row['seat_number'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shuttle Service - Booking & Seat Selection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .seat.selected {
            background-color: #4A90E2;
            color: white;
            transform: scale(1.1);
            transition: transform 0.2s, background-color 0.2s;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-blue-100 to-purple-100">
    <header class="bg-gradient-to-r from-blue-800 to-purple-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-bold">Shuttle Service</h1>
            <nav class="space-x-4">
                <a href="index.php" class="hover:text-gray-300">Home</a>
                <a href="etiket.php" class="hover:text-gray-300">etiket</a>
                <a href="usertracking.php" class="hover:text-gray-300">tracking</a>
            </nav>
        </div>
    </header>

    <main class="container mx-auto mt-10 p-6 bg-white shadow-md rounded-lg" id="booking">
        <h2 class="text-3xl font-bold text-center mb-6">Book Your Shuttle</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo $success; ?>
                <div class="font-bold mt-2">Kode Booking Anda: <?php echo $booking_code; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="name" class="block text-gray-700">Name:</label>
                <input type="text" id="name" name="name" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>

            <div>
                <label for="phone" class="block text-gray-700">Phone Number:</label>
                <input type="tel" id="phone" name="phone" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>

            <div>
                <label for="departure" class="block text-gray-700">Departure:</label>
                <select id="departure" name="departure" class="w-full p-2 border border-gray-300 rounded-md" required>
                    <option value="">Select Departure</option>
                    <option value="Bandung">Bandung</option>
                    <option value="Jakarta">Jakarta</option>
                    <option value="Bekasi">Bekasi</option>
                </select>
            </div>

            <div>
                <label for="destination" class="block text-gray-700">Destination:</label>
                <select id="destination" name="destination" class="w-full p-2 border border-gray-300 rounded-md" required>
                    <option value="">Select Destination</option>
                    <option value="Bandung">Bandung</option>
                    <option value="Jakarta">Jakarta</option>
                    <option value="Bekasi">Bekasi</option>
                </select>
            </div>

            <div>
                <label for="date" class="block text-gray-700">Date:</label>
                <input type="date" id="date" name="date" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>

            <section class="container mx-auto py-12" id="seat-selection">
                <h2 class="text-3xl font-bold text-center mb-8 text-blue-800">Select Your Seat</h2>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="grid grid-cols-3 gap-4">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <label class="seat bg-gray-200 p-4 rounded-lg text-center cursor-pointer <?php echo !in_array($i, $available_seats) ? 'opacity-50' : ''; ?>">
                                <input type="radio" name="seat" value="<?php echo $i; ?>" 
                                       <?php echo !in_array($i, $available_seats) ? 'disabled' : ''; ?> 
                                       class="hidden" required>
                                <i class="fas fa-chair text-2xl"></i>
                                <p class="mt-2">Seat <?php echo $i; ?></p>
                                <?php if (!in_array($i, $available_seats)): ?>
                                    <p class="text-red-500 text-sm">(Booked)</p>
                                <?php endif; ?>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
            </section>

            <div class="mt-8 text-center">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
                    Book Now
                </button>
            </div>
        </form>
    </main>

    <footer class="bg-gradient-to-r from-blue-800 to-purple-800 text-white text-center p-4 mt-10">
        <p>©shall Arne's Shuttle Service. All rights reserved.</p>
    </footer>

    <script>
        // Handle seat selection visual effect
        document.querySelectorAll('.seat').forEach(function(seat) {
            const radio = seat.querySelector('input[type="radio"]');
            if (!radio.disabled) {
                seat.addEventListener('click', function() {
                    document.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));
                    this.classList.add('selected');
                });
            }
        });
    </script>
</body>
</html>