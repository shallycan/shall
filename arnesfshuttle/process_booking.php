<?php
include 'db.php';  // Memanggil koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari form
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $departure = $_POST['departure'];
    $destination = $_POST['destination'];
    $date = $_POST['date'];
    $seat_number = $_POST['seat'];

    // Validasi input: Memastikan tidak ada data yang kosong
    if (empty($name) || empty($phone) || empty($departure) || empty($destination) || empty($date) || empty($seat_number)) {
        echo json_encode(["status" => "error", "message" => "Semua field harus diisi."]);
        exit;
    }

    // Mulai transaksi
    $conn->begin_transaction();
    try {
        // Menyimpan data pengguna ke tabel Users
        $stmt = $conn->prepare("INSERT INTO Users (name, phone) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $phone);
        $stmt->execute();
        $user_id = $stmt->insert_id;  // Mendapatkan ID pengguna yang baru saja dimasukkan
        $stmt->close();

        // Periksa apakah kursi tersedia
        $stmt = $conn->prepare("SELECT * FROM Seats WHERE seat_number = ? AND status = 1");
        $stmt->bind_param("s", $seat_number);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Kursi sudah dipesan.");
        }
        $stmt->close();

        // Tandai kursi sebagai tidak tersedia
        $stmt = $conn->prepare("UPDATE Seats SET status = 0 WHERE seat_number = ?");
        $stmt->bind_param("s", $seat_number);
        $stmt->execute();
        $stmt->close();

        // Menyimpan pemesanan ke tabel Bookings
        $booking_code = "BOOK-" . strtoupper(uniqid());
        $stmt = $conn->prepare("INSERT INTO Bookings (user_id, departure, destination, date, seat_id, booking_code) 
                                VALUES (?, ?, ?, ?, (SELECT id FROM Seats WHERE seat_number = ?), ?)");
        $stmt->bind_param("isssss", $user_id, $departure, $destination, $date, $seat_number, $booking_code);
        $stmt->execute();
        $stmt->close();

        // Commit transaksi jika semuanya berhasil
        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Pemesanan berhasil!", "booking_code" => $booking_code]);
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Metode permintaan tidak valid."]);
}
?>