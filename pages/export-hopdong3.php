<?php
require_once '../vendor/autoload.php';
require '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use TCPDF;

// ✅ Tạo class kế thừa từ TCPDF để vẽ khung trên tất cả các trang
class CustomPDF extends TCPDF {
    // Ghi đè phương thức Header()
    public function Header() {
        // Kẻ khung bao quanh mỗi trang (lề 5mm)
        $this->Rect(5, 5, 200, 287);
    }
}


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
$tenFile = "HopDong_" . slugify($row['ten_nv']);
$docxFile = $uploadDir . "$tenFile.docx";
$htmlFile = $uploadDir . "$tenFile.html";
$pdfFile = $uploadDir . "$tenFile.pdf"; 

// Xử lý file Word
$templateProcessor = new TemplateProcessor("../uploads/temp/hopdong_temp_02.docx");
$templateProcessor->setValue('{{ten_nv}}', $row['ten_nv'] ?? 'N/A');
$templateProcessor->setValue('{{ho_khau}}', $row['ho_khau'] ?? 'N/A');
$templateProcessor->setValue('{{ngay_sinh}}', date_format(date_create($row['ngay_sinh']), 'd-m-Y') ?? 'N/A');
$templateProcessor->setValue('{{so_cmnd}}', $row['so_cmnd']?? 'N/A');
$templateProcessor->setValue('{{noi_cap}}', $row['noi_cap_cmnd']?? 'N/A');
$templateProcessor->setValue('{{ten_quoc_tich}}', $row['ten_quoc_tich']?? 'N/A');
$templateProcessor->setValue('{{so_cmnd2}}', $row['ten_chuc_vu']?? 'N/A');
$templateProcessor->saveAs($docxFile);

// Chuyển Word sang HTML
$phpWord = IOFactory::load($docxFile);
$htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
$htmlWriter->save($htmlFile);
$htmlContent = file_get_contents($htmlFile);

$htmlContent = preg_replace('/\s+/', ' ', $htmlContent); // Xóa khoảng trắng thừa
$htmlContent = preg_replace('/<p>\s*<\/p>/', '', $htmlContent); // Xóa thẻ <p> rỗng
$htmlContent = preg_replace('/<br\s*\/?>/', '', $htmlContent); // Xóa các dòng xuống hàng dư thừa


$htmlContent = '<style>
    body { max-width: 800px; margin: auto; font-size: 10pt; }
    table { width: 100%; border-collapse: collapse; }
    td, th { border: 1px solid black; padding: 5px; }
</style>' . $htmlContent;


// Tạo PDF bằng TCPDF
//$pdf = new TCPDF(P, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(10, 10, 10); // Cài đặt lề trang

// Tắt tự động ngăn trang
//$pdf->SetAutoPageBreak(false, 0);

$pdf->AddPage('P', 'A4');

//$pdf->Rect(5, 5, 200, 287); // (X, Y, Width, Height)

$pdf->SetFont('dejavusans', '', 10);
$pdf->writeHTML($htmlContent, true, false, true, false, '');
$pdf->Output($pdfFile, 'F'); // Lưu PDF vào file

// Gửi PDF về trình duyệt
if (file_exists($pdfFile)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($pdfFile) . '"');
    readfile($pdfFile);
    exit;
} else {
    die("Lỗi: Không tìm thấy file PDF.");
}

// Hàm chuyển tên có dấu thành không dấu
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    //$text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return empty($text) ? 'file' : $text;
}
?>
