<?php
include 'db.php'; // Memanggil koneksi database

// Ambil semua data pemesanan dari database
$bookings = [];
$result = $conn->query("SELECT b.booking_code, u.name, u.phone, b.departure, b.destination, b.date, s.seat_number 
                        FROM Bookings b
                        JOIN Users u ON b.user_id = u.id
                        JOIN Seats s ON b.seat_id = s.id");
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white p-4">
        <h1 class="text-2xl font-bold">Admin Dashboard - Manage Bookings</h1>
    </header>
    <main class="container mx-auto p-6 bg-white shadow-md mt-6">
        <h2 class="text-xl font-bold mb-4">Booking List</h2>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 p-2">Booking Code</th>
                    <th class="border border-gray-300 p-2">Name</th>
                    <th class="border border-gray-300 p-2">Phone</th>
                    <th class="border border-gray-300 p-2">Departure</th>
                    <th class="border border-gray-300 p-2">Destination</th>
                    <th class="border border-gray-300 p-2">Date</th>
                    <th class="border border-gray-300 p-2">Seat</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($booking['booking_code']); ?></td>
                        <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($booking['name']); ?></td>
                        <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($booking['phone']); ?></td>
                        <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($booking['departure']); ?></td>
                        <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($booking['destination']); ?></td>
                        <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($booking['date']); ?></td>
                        <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($booking['seat_number']); ?></td>
                        <td class="border border-gray-300 p-2 text-center">
                            <button 
                                onclick="editBooking('<?php echo htmlspecialchars($booking['booking_code']); ?>')"
                                class="bg-yellow-500 text-white px-2 py-1 rounded-md hover:bg-yellow-600">
                                Edit
                            </button>
                            <button 
                                onclick="deleteBooking('<?php echo htmlspecialchars($booking['booking_code']); ?>')"
                                class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <script>
        function deleteBooking(bookingCode) {
            if (confirm('Are you sure you want to delete this booking?')) {
                axios.post('process_booking_admin.php', {
                    action: 'delete',
                    booking_code: bookingCode
                })
                .then(response => {
                    alert(response.data.message);
                    if (response.data.status === 'success') {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('An error occurred while deleting the booking.');
                });
            }
        }

        function editBooking(bookingCode) {
            const newName = prompt('Enter new name:');
            const newPhone = prompt('Enter new phone:');
            const newDeparture = prompt('Enter new departure:');
            const newDestination = prompt('Enter new destination:');
            const newDate = prompt('Enter new date (YYYY-MM-DD):');
            const newSeat = prompt('Enter new seat number:');

            if (newName && newPhone && newDeparture && newDestination && newDate && newSeat) {
                axios.post('process_booking_admin.php', {
                    action: 'update',
                    booking_code: bookingCode,
                    name: newName,
                    phone: newPhone,
                    departure: newDeparture,
                    destination: newDestination,
                    date: newDate,
                    seat: newSeat
                })
                .then(response => {
                    alert(response.data.message);
                    if (response.data.status === 'success') {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('An error occurred while updating the booking.');
                });
            } else {
                alert('All fields are required for editing.');
            }
        }
    </script>
</body>
</html>