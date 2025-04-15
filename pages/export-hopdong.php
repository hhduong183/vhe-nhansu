<?php
require_once '../vendor/autoload.php';
require '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

use PhpOffice\PhpWord\TemplateProcessor;

// Kiểm tra tham số ID
if (!isset($_GET['id'])) {
    die("ID nhân viên không hợp lệ.");
}
$id = intval($_GET['id']); // Chuyển thành số nguyên để tránh SQL Injection

// Truy vấn dữ liệu nhân viên
$stmt = $conn->prepare("SELECT 
    nv.id as id, ma_nv, ten_nv, gioi_tinh, ngay_sinh, so_cmnd, 
    ngay_cap_cmnd, noi_cap_cmnd, ho_khau, ten_quoc_tich, ten_dan_toc, ten_ton_giao, 
    ten_phong_ban, ten_chuc_vu, ngay_vao_lam, so_dth, trang_thai
    FROM nhanvien nv
    JOIN quoc_tich qt ON nv.quoc_tich_id = qt.id
    JOIN dan_toc dt ON nv.dan_toc_id = dt.id
    JOIN ton_giao tg ON nv.ton_giao_id = tg.id
    JOIN phong_ban pb ON nv.phong_ban_id = pb.id
    JOIN chuc_vu cv ON nv.chuc_vu_id = cv.id
    WHERE nv.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("Không tìm thấy nhân viên.");
}

// Định nghĩa thư mục lưu file
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/vhe_qlns/uploads/temp/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Tạo tên file không dấu
$tenFile = "HopDong_" . slugify($row['ma_nv']);
$docxFile = $uploadDir . "$tenFile.docx";

// Xử lý file Word
$templateProcessor = new TemplateProcessor("../uploads/temp/hopdong_temp_03.docx");
$templateProcessor->setValue('{{ten_nv}}', $row['ten_nv']);
$templateProcessor->setValue('{{ho_khau}}', $row['ho_khau']);
$templateProcessor->setValue('{{so_cmnd}}', $row['so_cmnd']);
$templateProcessor->setValue('{{ten_quoc_tich}}', $row['ten_quoc_tich']);
$templateProcessor->setValue('{{ngay_sinh}}', $row['ngay_sinh']);
$templateProcessor->setValue('{{noi_cap}}', $row['gioi_tinh']);
//$templateProcessor->setValue('{{ten_chuc_vu}}', $row['ten_chuc_vu']);
$templateProcessor->saveAs($docxFile);

// Gửi file Word về trình duyệt
if (file_exists($docxFile)) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . basename($docxFile) . '"');
    readfile($docxFile);
    exit;
} else {
    die("Lỗi: Không tìm thấy file Word.");
}

// Hàm chuyển tên có dấu thành không dấu
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'file' : $text;
}
