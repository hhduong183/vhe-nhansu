<?php
session_start();
require_once('../config.php');
require '../vendor/autoload.php'; // Load PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Kiểm tra user đã đăng nhập chưa
if (!isset($_SESSION['idNhanVien'])) {
    die("Bạn chưa đăng nhập!");
}
$id_user = $_SESSION['idNhanVien'];

$countSuccess = 0;
$countFail = 0;
$failedUsers = [];

if (isset($_POST['import'])) {
    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    
    for ($row = 2; $row <= $highestRow; $row++) {

        // Lấy mã nhân viên từ file Excel
        $ma_nv = trim($sheet->getCell("A$row")->getValue());

        // 🔍 Tìm ID nhân viên từ bảng nhanvien
        $sqlFind = "SELECT id FROM nhanvien WHERE ma_nv = ?";
        $stmtFind = $conn->prepare($sqlFind);
        $stmtFind->bind_param("s", $ma_nv);
        $stmtFind->execute();
        $result = $stmtFind->get_result();

        if ($result->num_rows == 0) {
            $countFail++;
            $failedUsers[] = "$ma_nv (Không tìm thấy)";
            continue; // Bỏ qua nếu không tìm thấy nhân viên
        }

        $rowNhanVien = $result->fetch_assoc();
        $nhanvien_id = $rowNhanVien['id']; // ID thực tế trong DB
        $ma_luong = $sheet->getCell("B$row")->getValue();
        $he_luong = $sheet->getCell("C$row")->getValue();
        $ngaycong = $sheet->getCell("D$row")->getValue();
        $hr_rangoai = ($sheet->getCell("E$row")->getValue() ?: 0);
        $hr_ovt = ($sheet->getCell("F$row")->getValue() ?: 0);
        $hr_dem = ($sheet->getCell("G$row")->getValue() ?: 0);
        $hr_ovtCN = ($sheet->getCell("H$row")->getValue() ?: 0);
        $hr_demCN = ($sheet->getCell("I$row")->getValue() ?: 0);
        $hr_ovtLe = ($sheet->getCell("J$row")->getValue() ?: 0);
        $hs_apq = ($sheet->getCell("K$row")->getValue() ?: 0);
        $hs_sanluong = ($sheet->getCell("L$row")->getValue() ?: 0);
        $ml_chinh = ($sheet->getCell("M$row")->getValue() ?: 0);
        $pc_trachnhiem = ($sheet->getCell("N$row")->getValue() ?: 0);
        $pc_nhatro = ($sheet->getCell("O$row")->getValue() ?: 0);
        $pc_nghe = ($sheet->getCell("P$row")->getValue() ?: 0);
        $pc_dem = ($sheet->getCell("R$row")->getValue() ?: 0);
        $pc_congtruong = ($sheet->getCell("U$row")->getValue() ?: 0);
        $ovt_thue = ($sheet->getCell("W$row")->getValue() ?: 0);
        $ovt_kothue = ($sheet->getCell("X$row")->getValue() ?: 0);
        $kt_bhxh = ($sheet->getCell("AC$row")->getValue() ?: 0);
        $kt_congdoan = ($sheet->getCell("AD$row")->getValue() ?: 0);
        $kt_tncn = ($sheet->getCell("AJ$row")->getValue() ?: 0);
        $thuc_linh_net = ($sheet->getCell("AK$row")->getValue() ?: 0);
        $ml_khac = ($sheet->getCell("AL$row")->getValue() ?: 0);
        $kt_khac = ($sheet->getCell("AM$row")->getValue() ?: 0);
        $sal_month = ($sheet->getCell("AN$row")->getValue() ?: 0);
        $sal_year = ($sheet->getCell("AO$row")->getValue() ?: 0);

        // $hr_rangoai = $sheet->getCell("E$row")->getValue();
        // $hr_ovt = $sheet->getCell("F$row")->getValue();
        // $hr_dem = $sheet->getCell("G$row")->getValue();
        // $hr_ovtCN = $sheet->getCell("H$row")->getValue();
        // $hr_demCN = $sheet->getCell("I$row")->getValue();
        // $hr_ovtLe = $sheet->getCell("J$row")->getValue();
        // $hs_apq = $sheet->getCell("K$row")->getValue();
        // $hs_sanluong = $sheet->getCell("L$row")->getValue();
        // $ml_chinh = $sheet->getCell("M$row")->getValue();
        // $pc_trachnhiem = $sheet->getCell("N$row")->getValue();
        // $pc_nhatro = $sheet->getCell("O$row")->getValue();
        // $pc_nghe = $sheet->getCell("P$row")->getValue();
        // $pc_dem = $sheet->getCell("R$row")->getValue();
        // $pc_congtruong = $sheet->getCell("U$row")->getValue();
        // $ovt_thue = $sheet->getCell("W$row")->getValue();
        // $ovt_kothue = $sheet->getCell("X$row")->getValue();
        // $kt_bhxh = $sheet->getCell("AC$row")->getValue();
        // $kt_congdoan = $sheet->getCell("AD$row")->getValue();
        // $kt_tncn = $sheet->getCell("AJ$row")->getValue();
        // $thuc_linh_net = $sheet->getCell("AK$row")->getValue();
        // $ml_khac = $sheet->getCell("AL$row")->getValue();
        // $kt_khac = $sheet->getCell("AM$row")->getValue();

       $sql = "INSERT INTO luong_chamcong (
    nhanvien_id, ma_luong, he_luong, ngaycong, hr_rangoai, hr_ovt, hr_dem, hr_ovtCN, hr_demCN, hr_ovtLe,
    hs_apq, hs_sanluong, ml_chinh, pc_trachnhiem, pc_nhatro, pc_nghe, pc_dem, pc_congtruong,
    ovt_thue, ovt_kothue, kt_bhxh, kt_congdoan, kt_tncn, ml_khac, kt_khac,sal_month,sal_year
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)"; // Thêm 1 dấu '?' cho kt_khac

        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
    die("Lỗi SQL: " . $conn->error); // Hiển thị lỗi SQL cụ thể
}
        $stmt->bind_param(
    "issssssssssssssssssssssssss", // Kiểu dữ liệu cần khớp
    $nhanvien_id, $ma_luong, $he_luong, $ngaycong, $hr_rangoai, $hr_ovt, $hr_dem, $hr_ovtCN, $hr_demCN, $hr_ovtLe,
    $hs_apq, $hs_sanluong, $ml_chinh, $pc_trachnhiem, $pc_nhatro, $pc_nghe, $pc_dem, $pc_congtruong,
    $ovt_thue, $ovt_kothue, $kt_bhxh, $kt_congdoan, $kt_tncn, $ml_khac, $kt_khac,$sal_month,$sal_year
);

        
        if ($stmt->execute()) {
            $countSuccess++;
        } else {
            $countFail++;
            $failedUsers[] = "$ma_nv - $ma_luong - $nhanvien_id, $ma_luong, $he_luong, $ngaycong, $hr_rangoai, $hr_ovt, $hr_dem, $hr_ovtCN, $hr_demCN, $hr_ovtLe,
            $hs_apq, $hs_sanluong, $ml_chinh, $pc_trachnhiem, $pc_nhatro, $pc_nghe, $pc_dem, $pc_congtruong,
            $ovt_thue, $ovt_kothue, $kt_bhxh, $kt_congdoan, $kt_tncn, $ml_khac,$kt_khac";
        }
    }
}
?>

<!-- Modal Bootstrap -->
<div class="modal-dialog modal-dialog-scrollable">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kết quả nhập dữ liệu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Số phiếu lương nhập thành công:</strong> <?php echo $countSuccess; ?></p>
        <p><strong>Số phiếu lương nhập thất bại:</strong> <?php echo $countFail; ?></p>
        <?php if ($countFail > 0) : ?>
          <p><strong>Danh sách phiếu lương bị lỗi:</strong></p>
          <ul>
            <?php foreach ($failedUsers as $user) : ?>
              <li><?php echo $user; ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>