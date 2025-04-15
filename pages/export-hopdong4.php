
<!--require_once '../vendor/autoload.php';-->
<!--require '../config.php';-->

<!--session_start();-->

<!--$mpdf = new \Mpdf\Mpdf();-->

<!--    nv.id as id, ma_nv, hinh_anh, ten_nv, gioi_tinh, nv.ngay_tao as ngay_tao, -->
<!--    ngay_sinh, so_cmnd, ten_tinh_trang, ngay_cap_cmnd, noi_cap_cmnd, nguyen_quan, -->
<!--    ten_quoc_tich, ten_dan_toc, ten_ton_giao, ho_khau, ten_loai_nv, -->
<!--    ten_trinh_do, ten_chuyen_mon, ten_bang_cap, ten_phong_ban, ten_chuc_vu,ngay_vao_lam, so_dth, trang_thai-->
<!--    FROM nhanvien nv-->
<!--    JOIN quoc_tich qt ON nv.quoc_tich_id = qt.id-->
<!--    JOIN dan_toc dt ON nv.dan_toc_id = dt.id-->
<!--    JOIN ton_giao tg ON nv.ton_giao_id = tg.id-->
<!--    JOIN loai_nv lnv ON nv.loai_nv_id = lnv.id-->
<!--    JOIN trinh_do td ON nv.trinh_do_id = td.id-->
<!--    JOIN chuyen_mon cm ON nv.chuyen_mon_id = cm.id-->
<!--    JOIN bang_cap bc ON nv.bang_cap_id = bc.id-->
<!--    JOIN phong_ban pb ON nv.phong_ban_id = pb.id-->
<!--    JOIN chuc_vu cv ON nv.chuc_vu_id = cv.id-->
<!--    JOIN tinh_trang_hon_nhan hn ON nv.hon_nhan_id = hn.id-->
<!--    WHERE nv.id = ?");-->

<!--$stmt->bind_param("i", $id);-->
<!--$stmt->execute();-->
<!--$result = $stmt->get_result();-->
<!--$row = $result->fetch_assoc();-->

<!--if (!$row) {-->
<!--    die("Không tìm thấy nhân viên.");-->
<!--}-->

<!--$usermail = isset($_POST['usermail']) ? $_POST['usermail'] : 'Không có email';-->
<!--$residence = isset($_POST['residence']) ? $_POST['residence'] : 'Không có thông tin';-->

<!--$username = $row['ma_nv'];-->
<!--$userstory = $row['ho_khau'];-->

<!--$infor = '';-->
<!--$infor .= '<h2>Thông tin nhân viên</h2>';-->
<!--$infor .= '<strong>Mã NV: </strong>' . htmlspecialchars($username) . '<br/>';-->
<!--$infor .= '<strong>Email: </strong>' . htmlspecialchars($usermail) . '<br/>';-->
<!--$infor .= '<strong>Địa chỉ: </strong>' . htmlspecialchars($residence) . '<br/>';-->
<!--$infor .= '<strong>Hộ khẩu: </strong>' . htmlspecialchars($userstory) . '<br/>';-->

<!--$mpdf->WriteHTML($infor);-->
<!--$mpdf->Output();-->
<!--?>-->

<?php
require_once '../vendor/autoload.php';
require '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

use PhpOffice\PhpWord\IOFactory;
// use Mpdf\Mpdf;
use TCPDF;
//

// Kiểm tra xem ID có được truyền vào không
if (!isset($_GET['id'])) {
    die("ID nhân viên không hợp lệ.");
}
$id = intval($_GET['id']); // Ép kiểu thành số nguyên để tránh SQL Injection

// Chuẩn bị truy vấn lấy thông tin nhân viên
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

// Lấy nội dung mẫu hợp đồng lao động từ file Word
//$template = file_get_contents("../uploads/temp/Hop-dong-lao-dong-mau.docx");

// Đọc file Word
$phpWord = IOFactory::load("../uploads/temp/hopdong_temp_03.docx");

// Chuyển đổi sang HTML
$htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
ob_start();
$htmlWriter->save('php://output');
$template = ob_get_clean();



// Thay thế các placeholder bằng dữ liệu thực tế
$template = str_replace("{{ten_nv}}", $row['ten_nv'], $template);
$template = str_replace("{{ngay_sinh}}", $row['ngay_sinh'], $template);
$template = str_replace("{{so_cmnd}}", $row['so_cmnd'], $template);
$template = str_replace("{{ho_khau}}", $row['ho_khau'], $template);
$template = str_replace("{{ten_quoc_tich}}", $row['ten_quoc_tich'], $template);
$template = str_replace("{{so_cmnd2}}", $row['ngay_cap_cmnd'], $template);
$template = str_replace("{{noi_cap}}", $row['noi_cap_cmnd'], $template);
// $template = str_replace("{{ten_dan_toc}}", $row['ten_dan_toc'], $template);
// $template = str_replace("{{ten_ton_giao}}", $row['ten_ton_giao'], $template);
$template = str_replace("{{phong_ban}}", $row['ten_phong_ban'], $template);
// $template = str_replace("{{ten_chuc_vu}}", $row['ten_chuc_vu'], $template);
// $template = str_replace("{{ngay_vao_lam}}", $row['ngay_vao_lam'], $template);
// $template = str_replace("{{so_dth}}", $row['so_dth'], $template);

// Xuất hợp đồng ra file PDF
$template = mb_convert_encoding($template, 'UTF-8', 'auto');

// Tạo đối tượng TCPDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12);

// Thêm nội dung vào PDF
$pdf->writeHTML($template, true, false, true, false, '');



if (headers_sent()) {
    die("Lỗi: Headers đã gửi trước khi tạo PDF.");
}

//if (!class_exists('Mpdf\Mpdf')) {
//    die("Lỗi: Thư viện Mpdf chưa được nạp.");
//}

echo $template; // Xem thử nội dung HTML trước khi truyền vào PDF
// $mpdf = new \Mpdf\Mpdf();
// $mpdf = new \Mpdf\Mpdf([
//     'default_font' => 'dejavusans'
// ]);
// $mpdf->WriteHTML($template);
//ob_clean(); // Xóa mọi dữ liệu rác trước đó
flush();    // Xả bộ nhớ đệm để tránh lỗi
// Xuất file PDF (tải về)
$pdf->Output("HopDong_{$row['ten_nv']}.doc", "D");
// $mpdf->Output("HopDong_{$row['ten_nv']}.doc", "D");
// $mpdf->Output("/home/evjmcxxjhosting/public_html/vhe_qlns/uploads/temp/HopDong.pdf", "F");
// echo "File PDF đã được tạo. <a href='/vhe_qlns/uploads/temp/HopDong.pdf'>Tải xuống</a>";
?>
