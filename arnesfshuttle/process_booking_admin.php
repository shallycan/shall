<?php
include 'db.php'; // Memanggil koneksi database

// Memastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data JSON dari request
    $input = json_decode(file_get_contents('php://input'), true);

    // Periksa apakah parameter 'action' ada
    if (!isset($input['action'])) {
        echo json_encode(['status' => 'error', 'message' => 'Action is required.']);
        exit;
    }

    $action = $input['action'];

    if ($action === 'delete') {
        // Hapus data berdasarkan booking_code
        $bookingCode = $input['booking_code'] ?? null;

        if (!$bookingCode) {
            echo json_encode(['status' => 'error', 'message' => 'Booking code is required.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM Bookings WHERE booking_code = ?");
        $stmt->bind_param('s', $bookingCode);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Booking successfully deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete booking.']);
        }
        $stmt->close();

    } elseif ($action === 'update') {
        // Perbarui data berdasarkan booking_code
        $bookingCode = $input['booking_code'] ?? null;
        $name = $input['name'] ?? null;
        $phone = $input['phone'] ?? null;
        $departure = $input['departure'] ?? null;
        $destination = $input['destination'] ?? null;
        $date = $input['date'] ?? null;
        $seat = $input['seat'] ?? null;

        if (!$bookingCode || !$name || !$phone || !$departure || !$destination || !$date || !$seat) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE Bookings b
            JOIN Users u ON b.user_id = u.id
            JOIN Seats s ON b.seat_id = s.id
            SET u.name = ?, u.phone = ?, b.departure = ?, b.destination = ?, b.date = ?, s.seat_number = ?
            WHERE b.booking_code = ?
        ");
        $stmt->bind_param('sssssss', $name, $phone, $departure, $destination, $date, $seat, $bookingCode);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Booking successfully updated.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update booking.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
