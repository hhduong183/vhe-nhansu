<?php
require "../config.php"; // Kết nối database

if (isset($_GET['query'])) {
    $query = $_GET['query'];
    $query = "%$query%"; // Dùng wildcard cho LIKE

    $stmt = $conn->prepare("SELECT * FROM nhanvien WHERE ma_nv LIKE ? OR ten_nv LIKE ?");
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
