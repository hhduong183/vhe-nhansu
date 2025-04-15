<?php
require '../config.php';
session_start();

header('Content-Type: application/json');

// Kiểm tra đăng nhập và quyền truy cập
if (!isset($_SESSION['username'], $_SESSION['level'], $_SESSION['idNhanVien'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện chức năng này']);
    exit;
}

// Chỉ cho phép admin và manager cập nhật
if ($_SESSION['level'] == 0 ) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện chức năng này']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['nhanvien_id'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    $conn->begin_transaction();

    // Update existing leave records
    $delete_sql = "DELETE FROM nghi_phep WHERE nhanvien_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $input['nhanvien_id']);
    $stmt->execute();

    // Insert new leave records
    if (!empty($input['ngay_nghi'])) {
        $insert_sql = "INSERT INTO nghi_phep (nhanvien_id, ngay_nghi, trang_thai, so_ngay) VALUES (?, ?, 'Đã duyệt', 1)";
        $stmt = $conn->prepare($insert_sql);
        
        foreach ($input['ngay_nghi'] as $ngay) {
            $stmt->bind_param("is", $input['nhanvien_id'], $ngay);
            $stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

$conn->close();
?>