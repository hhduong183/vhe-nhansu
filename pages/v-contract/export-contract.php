<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
session_start();

use PhpOffice\PhpWord\TemplateProcessor;

// Validate required fields
$required_fields = ['ma_hop_dong', 'ngay_bat_dau', 'loai_hop_dong', 'BacLuong', 'nhanvien_id'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        die("Vui lòng điền đầy đủ thông tin bắt buộc!");
    }
}

// Initialize variables
$maHopDong = $_POST['ma_hop_dong'];
$id = $_POST['nhanvien_id'];
$loai_hop_dong = $_POST['loai_hop_dong'];
$contractSave = isset($_POST['contract-save']) && $_POST['contract-save'] === 'save';

// Check if contract code exists
$stmt = $conn->prepare("SELECT COUNT(*) FROM hop_dong_lao_dong WHERE ma_hop_dong = ?");
$stmt->bind_param("s", $maHopDong);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    die("Mã hợp đồng đã tồn tại!");
}

// Save contract if requested
if ($contractSave) {
    try {
        $sql_mucluong = $_POST['BacLuong'];
        $nhom_PCNNghe = (int) ($_POST['PCNNghe'] ?? 0);
        $sql_nhatro = (int) ($_POST['nha_tro'] ?? 0);
        $sql_dacbiet = (int) ($_POST['dac_biet'] ?? 0);
        $nhomTN = $_POST['nhomTN'] ?? 0;
        $muc_bhxh = $_POST['muc_bhxh'];

        $insertQuery = "INSERT INTO hop_dong_lao_dong (
            loai_hop_dong_id, ma_hop_dong, nhanvien_id, 
            ngay_bat_dau, ngay_ket_thuc, muc_luong, 
            phucap_tnh, phucap_nghe, phucap_nha_tro, 
            phucap_dac_biet, muc_bhxh
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param(
            "isissssiiii",
            $loai_hop_dong,
            $maHopDong,
            $id,
            $_POST['ngay_bat_dau'],
            $_POST['ngay_ket_thuc'],
            $sql_mucluong,
            $nhomTN,
            $nhom_PCNNghe,
            $sql_nhatro,
            $sql_dacbiet,
            $muc_bhxh
        );

        if (!$stmt->execute()) {
            die("Lỗi khi lưu hợp đồng: " . $stmt->error);
        }
        
        $_SESSION['success_message'] = "Hợp đồng đã được lưu thành công!";
    } catch (Exception $e) {
        die("Lỗi: " . $e->getMessage());
    }
}

// Get contract type name
$tenHopDong = "N/A";
$stmt = $conn->prepare("SELECT ten_hop_dong FROM hop_dong_type WHERE id = ?");
$stmt->bind_param("i", $loai_hop_dong);
$stmt->execute();
$stmt->bind_result($tenHopDong);
$stmt->fetch();
$stmt->close();

// Get employee data
$stmt = $conn->prepare("SELECT 
    nv.id, ma_nv, ten_nv, ngay_sinh, so_cmnd, 
    noi_cap_cmnd, nguyen_quan, ten_quoc_tich, 
    ten_ton_giao, ho_khau, ten_phong_ban, 
    ten_chuc_vu, ngay_cap_cmnd
    FROM nhanvien nv
    JOIN quoc_tich qt ON nv.quoc_tich_id = qt.id
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

// Generate Word document
try {
    $templatePath = ($loai_hop_dong == 3) ? ROOT_PATH .'/uploads/temp/hopdong_vth.docx' : ROOT_PATH .'/uploads/temp/hopdong_xd.docx';
    if (!file_exists($templatePath)) {
        die("Lỗi: Template không tồn tại.");
    }

    $templateProcessor = new TemplateProcessor($templatePath);

    // Set template values
    $templateProcessor->setValue('ten_nv', $row['ten_nv'] ?? 'N/A');
    $templateProcessor->setValue('ho_khau', $row['ho_khau'] ?? 'N/A');
    $templateProcessor->setValue('ngay_sinh', date('d-m-Y', strtotime($row['ngay_sinh'])) ?? 'N/A');
    $templateProcessor->setValue('so_cmnd', $row['so_cmnd'] ?? 'N/A');
    $templateProcessor->setValue('noi_cap', $row['noi_cap_cmnd'] ?? 'N/A');
    $templateProcessor->setValue('ten_quoc_tich', $row['ten_quoc_tich'] ?? 'N/A');
    $templateProcessor->setValue('nguyen_quan', $row['nguyen_quan'] ?? 'N/A');
    $templateProcessor->setValue('ten_ton_giao', $row['ten_ton_giao'] ?? 'N/A');
    $templateProcessor->setValue('ten_phong_ban', $row['ten_phong_ban'] ?? 'N/A');
    $templateProcessor->setValue('chuc_vu', $row['ten_chuc_vu'] ?? 'N/A');
    $templateProcessor->setValue('ngay_start', date('d-m-Y', strtotime($_POST['ngay_bat_dau'])) ?? 'N/A');
    $templateProcessor->setValue('ngay_cap_cmnd', date('d-m-Y', strtotime($row['ngay_cap_cmnd'])) ?? 'N/A');
    $templateProcessor->setValue('ma_hop_dong', $maHopDong ?? 'N/A');
    $templateProcessor->setValue('loai_hop_dong', $tenHopDong ?? 'N/A');
    $templateProcessor->setValue('muc_luong', $_POST['MucLuong'] ?? 'N/A');
    $templateProcessor->setValue('phucap_tnh', $_POST['Trachnhiem'] ?? 'N/A');
    $templateProcessor->setValue('phucap_nhatro', isset($_POST['nha_tro']) ? number_format($_POST['nha_tro'], 0, ',', '.') . " VND" : 'N/A');
    $templateProcessor->setValue('phucap_nghe', $_POST['PhucapNghe'] ?? 'N/A');
    $templateProcessor->setValue('phucap_khac', isset($_POST['dac_biet']) ? number_format($_POST['dac_biet'], 0, ',', '.') . " VND" : 'N/A');
    $templateProcessor->setValue('ngay_end', !empty($_POST['ngay_ket_thuc']) ? date('d-m-Y', strtotime($_POST['ngay_ket_thuc'])) : 'N/A');
    $templateProcessor->setValue('nghenghiep', $row['ten_chuc_vu'] ?? 'N/A');

    // Save and download file
    $outputDir = '../uploads/output/';
    if (!file_exists($outputDir) && !mkdir($outputDir, 0777, true)) {
        die("Lỗi: Không thể tạo thư mục output.");
    }

    $outputFile = $outputDir . 'hopdong_' . $id . '_' . $row['ten_nv'] . '.docx';
    $templateProcessor->saveAs($outputFile);

    if (!file_exists($outputFile) || filesize($outputFile) == 0) {
        die("Lỗi: File không được tạo hoặc bị rỗng.");
    }

    // Send file to user
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="'.basename($outputFile).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($outputFile));
    readfile($outputFile);
    unlink($outputFile);

    if ($contractSave) {
        header("Location: hopdong_list.php?p=staff&a=contract-list");
    }
    exit;

} catch (Exception $e) {
    die("Lỗi khi tạo file hợp đồng: " . $e->getMessage());
}
?>
