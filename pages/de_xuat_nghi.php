<?php
// Kết nối đến MySQL
require '../config.php';
session_start(); // Đảm bảo bắt đầu session nếu chưa làm vậy ở đầu file

if (isset($_SESSION['level'])) {
    $trang_thai = ($_SESSION['level'] == 0) ? 'Chờ duyệt' : 'Đã duyệt';
} else {
    // Nếu session không tồn tại, bạn có thể xử lý lỗi hoặc mặc định một giá trị
    $trang_thai = 'Chưa xác định';
}
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý đề xuất nghỉ phép
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_nv = $_POST['ma_nv'];
    $ngaynghi = $_POST['ngay_nghi'];
    $loai_ngay_nghi = $_POST['loai_ngay_nghi'];
    $ly_do = $_POST['ly_do'];
    // $trang_thai = ($_SESSION['user_quyen'] == 0) ? 'Chờ duyệt' : 'Đã duyệt';
    
    // Kiểm tra nhân viên có tồn tại không
    $check_sql = "SELECT id FROM nhanvien WHERE ma_nv = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $ma_nv);  // "i" cho integer
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Lấy id từ kết quả
        $row = $check_result->fetch_assoc();
        $nhanvien_id = $row['id'];  // Gán giá trị id vào biến $nhanvien_id
        // Thêm yêu cầu vào bảng nghi_phep
        $insert_sql = "INSERT INTO nghi_phep (nhanvien_id, ngay_nghi, loai_ngay_nghi, ly_do, trang_thai) 
                       VALUES (?, ?, ?, ?, ?)";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("issss", $nhanvien_id, $ngaynghi, $loai_ngay_nghi, $ly_do, $trang_thai);
        // echo "$nhanvien_id, $ngaynghi, $loai_ngay_nghi, $ly_do, $trang_thai";
        if ($insert_stmt->execute()) {
            echo "Đề xuất nghỉ phép đã được gửi thành công!";
        } else {
            echo "Lỗi: " . $insert_stmt->error;
        }
    } else {
        echo "Nhân viên không tồn tại!";
    }
}

// Đóng kết nối
$conn->close();
?>

