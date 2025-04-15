<?php
require '../config.php'; // Kết nối đến database

if (isset($_GET['q'])) {
    $query = $_GET['q'];
    $query = "%$query%";
    
    $stmt = $conn->prepare("SELECT ma_nv, id, ten_nv FROM nhanvien WHERE ma_nv LIKE ? OR ten_nv LIKE ? LIMIT 10");
    
    if (!$stmt) {
        die("Lỗi truy vấn: " . $conn->error); // Hiển thị lỗi nếu prepare() thất bại
    }

    $stmt->bind_param("ss", $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($nhanvien = $result->fetch_assoc()) {
            echo "<div class='dropdown-item nhanvien-item' data-info='" . json_encode($nhanvien) . "'>" . 
                 $nhanvien['ma_nv'] . " - " . $nhanvien['ten_nv'] . "</div>";
        }
    } else {
        echo "<div class='dropdown-item'>Không tìm thấy nhân viên</div>";
    }

    $stmt->close();
}
?>