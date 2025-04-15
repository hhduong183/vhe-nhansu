<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

require '../vendor/autoload.php'; // Gọi thư viện PhpSpreadsheet
require_once('../config.php'); // Kết nối database

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Bảng nhân viên');

// Định dạng cột tự động co giãn
foreach (range('A', 'W') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Định dạng tiêu đề
$sheet->getStyle('A1:W1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('00ffff00');
$sheet->getStyle('A1:W1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Tiêu đề bảng
$headers = ['STT', 'Mã nhân viên', 'Tên nhân viên', 'Giới tính', 'Ngày sinh', 'Nơi sinh', 'Tình trạng hôn nhân',
    'Số CMND', 'Ngày cấp', 'Nơi cấp', 'Nguyên quán', 'Quốc tịch', 'Dân tộc', 'Tôn giáo', 'Hộ khẩu', 'Tạm trú',
    'Loại nhân viên', 'Trình độ', 'Chuyên môn', 'Bằng cấp', 'Phòng ban', 'Chức vụ', 'Trạng thái'];

$sheet->fromArray($headers, null, 'A1');
$rowCount = 2;
$stt = 0;

// Truy vấn dữ liệu từ database
$sql = "SELECT nv.id, ma_nv, ten_nv, gioi_tinh, ngay_sinh, so_cmnd, ten_tinh_trang, 
               ngay_cap_cmnd, noi_cap_cmnd, nguyen_quan, ten_quoc_tich, ten_dan_toc, 
               ten_ton_giao, ho_khau, ten_loai_nv, ten_trinh_do, ten_chuyen_mon, 
               ten_bang_cap, ten_phong_ban, ten_chuc_vu, trang_thai 
        FROM nhanvien nv
        INNER JOIN quoc_tich qt ON nv.quoc_tich_id = qt.id
        INNER JOIN dan_toc dt ON nv.dan_toc_id = dt.id
        INNER JOIN ton_giao tg ON nv.ton_giao_id = tg.id
        INNER JOIN loai_nv lnv ON nv.loai_nv_id = lnv.id
        INNER JOIN trinh_do td ON nv.trinh_do_id = td.id
        INNER JOIN chuyen_mon cm ON nv.chuyen_mon_id = cm.id
        INNER JOIN bang_cap bc ON nv.bang_cap_id = bc.id
        INNER JOIN phong_ban pb ON nv.phong_ban_id = pb.id
        INNER JOIN chuc_vu cv ON nv.chuc_vu_id = cv.id
        INNER JOIN tinh_trang_hon_nhan hn ON nv.hon_nhan_id = hn.id
        WHERE 1 = 1";

if (!empty($_GET['phong_ban'])) {
    $phong_ban = mysqli_real_escape_string($conn, $_GET['phong_ban']);
    $sql .= " AND nv.phong_ban_id = '$phong_ban'";
}
$sql .= " ORDER BY nv.id DESC";

$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $stt++;
    $data = [
        $stt,
        $row['ma_nv'],
        $row['ten_nv'],
        $row['gioi_tinh'] == 1 ? 'Nam' : 'Nữ',
        date_format(date_create($row['ngay_sinh']), 'd/m/Y'),
        '', // Nơi sinh
        $row['ten_tinh_trang'],
        $row['so_cmnd'],
        date_format(date_create($row['ngay_cap_cmnd']), 'd/m/Y'),
        $row['noi_cap_cmnd'],
        '', // Nguyên quán
        $row['ten_quoc_tich'],
        $row['ten_dan_toc'],
        $row['ten_ton_giao'],
        $row['ho_khau'],
        '', // Tạm trú
        $row['ten_loai_nv'],
        $row['ten_trinh_do'],
        $row['ten_chuyen_mon'],
        $row['ten_bang_cap'],
        $row['ten_phong_ban'],
        $row['ten_chuc_vu'],
        $row['trang_thai'] == 1 ? 'Đang làm việc' : 'Đã nghỉ việc'
    ];
    $sheet->fromArray($data, null, 'A' . $rowCount);
    $rowCount++;
}

// Kẻ viền bảng
$sheet->getStyle('A1:W' . ($rowCount - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Xuất file Excel
$filename = 'nhan-vien.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

// Cấu hình header để tải file về
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Length: ' . filesize($filename));
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
readfile($filename);
exit;
