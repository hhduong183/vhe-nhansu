<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Kết nối database
require_once('../config.php');
include('Classes/PHPExcel.php');

// Kiểm tra user đã đăng nhập chưa
if (!isset($_SESSION['idNhanVien'])) {
    die("Bạn chưa đăng nhập!"); // Hoặc chuyển hướng về trang login
}
$id_user = $_SESSION['idNhanVien']; // Lấy ID của user đang đăng nhập

$countSuccess = 0; // Đếm số nhân viên nhập thành công
$countFail = 0; // Đếm số nhân viên nhập thất bại
$failedUsers = []; // Lưu danh sách nhân viên bị lỗi

if (isset($_POST['import'])) {
    $file = $_FILES['file']['tmp_name'];
    $objPHPExcel = PHPExcel_IOFactory::load($file);
    $sheet = $objPHPExcel->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    
    for ($row = 2; $row <= $highestRow; $row++) {
        
        $hinhAnh = "demo-3x4.jpg";
        
        // Lấy dữ liệu từ file Ex cel
        $maNhanVien = $sheet->getCell('A' . $row)->getValue();
        $tenNhanVien = $sheet->getCell('B' . $row)->getValue();
        $sodienthoai = $sheet->getCell('E' . $row)->getValue();
        $gioiTinh = strtolower($sheet->getCell('G' . $row)->getValue()) == 'nam' ? 1 : 0;
        $honNhan = $sheet->getCell('I' . $row)->getValue();
        $CMND = $sheet->getCell('J' . $row)->getValue();
        $noiCap = $sheet->getCell('K' . $row)->getValue();
        $quocTich = $sheet->getCell('N' . $row)->getValue();
        $tonGiao = $sheet->getCell('O' . $row)->getValue();
        $danToc = $sheet->getCell('P' . $row)->getValue();
        $bangCap = $sheet->getCell('U' . $row)->getValue();
        $phongBan = $sheet->getCell('V' . $row)->getValue();
        $chucVu = $sheet->getCell('W' . $row)->getValue();
        $trinhDo = $sheet->getCell('S' . $row)->getValue();
        $chuyenMon = $sheet->getCell('T' . $row)->getValue();
        $loaiNhanVien = $sheet->getCell('R' . $row)->getValue();
        
        
        // Chuyển đổi ngày tháng từ Excel sang PHP Date
        $ngaySinh = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('H' . $row)->getValue()));
        $ngayCap = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('L' . $row)->getValue()));
        $ngayVaolam = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('AC' . $row)->getValue()));
        
        $nguyenQuan = $sheet->getCell('M' . $row)->getValue();
        $hoKhau = $sheet->getCell('Q' . $row)->getValue();
        $trangThai = strtolower($sheet->getCell('X' . $row)->getValue()) == 'Đang làm việc' ? 1 : 0;
        
        // Kiểm tra xem mã nhân viên đã tồn tại hay chưa
        $checkQuery = "SELECT COUNT(*) AS count FROM nhanvien WHERE ma_nv = '$maNhanVien'";
        $checkResult = mysqli_query($conn, $checkQuery);
        $rowCheck = mysqli_fetch_assoc($checkResult);
    
        if ($rowCheck['count'] > 0) {
        // Nếu mã nhân viên đã tồn tại, bỏ qua và tiếp tục vòng lặp
            $countFail++;
            $failedUsers[] = "Dòng $row: [$maNhanVien] $tenNhanVien - Mã NV đã tồn tại";
            continue;
        }


        // Tạo tài khoản đăng nhập
        $tendangnhap = $maNhanVien;
        $matkhau = password_hash($sodienthoai, PASSWORD_BCRYPT); // Mã hóa mật khẩu an toàn

        $ngayTao = date("Y-m-d H:i:s");

        // Thực thi truy vấn SQL
        $sql = "INSERT INTO nhanvien 
                (ma_nv, user_name, mat_khau, so_dth, hinh_anh, ten_nv, gioi_tinh, ngay_sinh, 
                 hon_nhan_id, so_cmnd, noi_cap_cmnd, ngay_cap_cmnd, nguyen_quan, loai_nv_id, quoc_tich_id, 
                 ton_giao_id, dan_toc_id, ho_khau, trinh_do_id, chuyen_mon_id, bang_cap_id, 
                 phong_ban_id, chuc_vu_id, trang_thai, nguoi_tao_id, ngay_tao, nguoi_sua_id, ngay_sua, ngay_vao_lam) 
                VALUES 
                ('$maNhanVien', '$tendangnhap', '$matkhau', '$sodienthoai', '$hinhAnh', '$tenNhanVien', '$gioiTinh', '$ngaySinh',
                 (SELECT id FROM tinh_trang_hon_nhan WHERE ten_tinh_trang='$honNhan'), '$CMND', '$noiCap', '$ngayCap', '$nguyenQuan',
                 (SELECT id FROM loai_nv WHERE ten_loai_nv='$loaiNhanVien'),
                 (SELECT id FROM quoc_tich WHERE ten_quoc_tich='$quocTich'),
                 (SELECT id FROM ton_giao WHERE ten_ton_giao='$tonGiao'),
                 (SELECT id FROM dan_toc WHERE ten_dan_toc='$danToc'),
                 '$hoKhau',
                 (SELECT id FROM trinh_do WHERE ten_trinh_do='$trinhDo'),
                 (SELECT id FROM chuyen_mon WHERE ten_chuyen_mon='$chuyenMon'),
                 (SELECT id FROM bang_cap WHERE ten_bang_cap='$bangCap'),
                 (SELECT id FROM phong_ban WHERE ten_phong_ban='$phongBan'),
                 (SELECT id FROM chuc_vu WHERE ten_chuc_vu='$chucVu'),
                 '$trangThai', '$id_user', '$ngayTao', '$id_user', '$ngayTao','$ngayVaolam'
                )";

    if (mysqli_query($conn, $sql)) {
        $countSuccess++;
    } else {
        $countFail++;
        $failedUsers[] = $tenNhanVien;
    }
}
}
// Hiển thị modal với kết quả
echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var myModal = new bootstrap.Modal(document.getElementById("resultModal"));
        myModal.show();
    });
</script>';

?>
<!-- Modal Bootstrap -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resultModalLabel">Kết quả nhập dữ liệu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Số nhân viên nhập thành công:</strong> <?php echo $countSuccess; ?></p>
        <p><strong>Số nhân viên nhập thất bại:</strong> <?php echo $countFail; ?></p>
        <?php if ($countFail > 0) : ?>
          <p><strong>Danh sách nhân viên bị lỗi:</strong></p>
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