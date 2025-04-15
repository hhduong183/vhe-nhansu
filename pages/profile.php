<?php
session_start();
require '../config.php'; // Kết nối database


  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');
  
  
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php"); 
    exit();
}

$user_name = $_SESSION['user_name'];

// Lấy ID nhân viên từ URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Truy vấn thông tin nhân viên
    $query = "SELECT nv.id, quoc_tich_id, ton_giao_id, dan_toc_id, loai_nv_id, bang_cap_id, phong_ban_id, 
                     chuc_vu_id, trinh_do_id, chuyen_mon_id, hon_nhan_id, ma_nv, hinh_anh, ten_nv, biet_danh, 
                     gioi_tinh, nv.ngay_tao, ngay_sinh, noi_sinh, so_cmnd, ngay_cap_cmnd, noi_cap_cmnd, nguyen_quan, 
                     ten_quoc_tich, ten_dan_toc, ten_ton_giao, ho_khau, tam_tru, ten_loai_nv, ten_trinh_do, 
                     ten_chuyen_mon, ten_bang_cap, ten_phong_ban, ten_chuc_vu, ten_tinh_trang, trang_thai 
              FROM nhanvien nv
              LEFT JOIN quoc_tich qt ON nv.quoc_tich_id = qt.id
              LEFT JOIN dan_toc dt ON nv.dan_toc_id = dt.id
              LEFT JOIN ton_giao tg ON nv.ton_giao_id = tg.id
              LEFT JOIN loai_nv lnv ON nv.loai_nv_id = lnv.id
              LEFT JOIN trinh_do td ON nv.trinh_do_id = td.id
              LEFT JOIN chuyen_mon cm ON nv.chuyen_mon_id = cm.id
              LEFT JOIN bang_cap bc ON nv.bang_cap_id = bc.id
              LEFT JOIN phong_ban pb ON nv.phong_ban_id = pb.id
              LEFT JOIN chuc_vu cv ON nv.chuc_vu_id = cv.id
              LEFT JOIN tinh_trang_hon_nhan hn ON nv.hon_nhan_id = hn.id
              WHERE nv.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $nhanvien = $result->fetch_assoc();
} else {
    echo "Không tìm thấy thông tin nhân viên.";
    exit();
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $nhanvien = $result->fetch_assoc();
} else {
    echo "Không tìm thấy thông tin nhân viên.";
    exit();
}
?>

<!--<!DOCTYPE html>-->
<!--<html lang="vi">-->
<!--<head>-->
<!--    <meta charset="UTF-8">-->
<!--    <meta name="viewport" content="width=device-width, initial-scale=1.0">-->
<!--    <title>Thông Tin Cá Nhân</title>-->
<!--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">-->
<!--</head>-->
<!--<body class="bg-light">-->

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center">
            <h3>Thông Tin Cá Nhân</h3>
        </div>
        <div class="card-body">
            <div class="text-center">
                <img src="uploads/<?php echo $nhanvien['hinh_anh']; ?>" class="rounded-circle" width="150" height="150" alt="Ảnh nhân viên">
                <h4 class="mt-2"><?php echo $nhanvien['ten_nv']; ?></h4>
                <p class="text-muted"><?php echo $nhanvien['ten_chucvu']; ?> - <?php echo $nhanvien['ten_phongban']; ?></p>
            </div>

            <table class="table table-bordered">
                <tr><td><strong>Mã NV</strong></td><td><?php echo $nhanvien['ma_nv']; ?></td></tr>
                <tr><td><strong>Họ tên</strong></td><td><?php echo $nhanvien['ten_nv']; ?></td></tr>
                <tr><td><strong>Giới tính</strong></td><td><?php echo ($nhanvien['gioi_tinh'] == 1) ? "Nam" : "Nữ"; ?></td></tr>
                <tr><td><strong>Ngày sinh</strong></td><td><?php echo date("d/m/Y", strtotime($nhanvien['ngay_sinh'])); ?></td></tr>
                <tr><td><strong>Quê quán</strong></td><td><?php echo $nhanvien['nguyen_quan']; ?></td></tr>
                <tr><td><strong>Số CMND</strong></td><td><?php echo $nhanvien['so_cmnd']; ?></td></tr>
                <tr><td><strong>Nơi cấp CMND</strong></td><td><?php echo $nhanvien['noi_cap_cmnd']; ?></td></tr>
                <tr><td><strong>Quốc tịch</strong></td><td><?php echo $nhanvien['ten_quoctich']; ?></td></tr>
                <tr><td><strong>Dân tộc</strong></td><td><?php echo $nhanvien['ten_dantoc']; ?></td></tr>
                <tr><td><strong>Tôn giáo</strong></td><td><?php echo $nhanvien['ten_tongiao']; ?></td></tr>
                <tr><td><strong>Trình độ</strong></td><td><?php echo $nhanvien['trinh_do_id']; ?></td></tr>
                <tr><td><strong>Chuyên môn</strong></td><td><?php echo $nhanvien['chuyen_mon_id']; ?></td></tr>
                <tr><td><strong>Bằng cấp</strong></td><td><?php echo $nhanvien['bang_cap_id']; ?></td></tr>
                <tr><td><strong>Phòng ban</strong></td><td><?php echo $nhanvien['ten_phongban']; ?></td></tr>
                <tr><td><strong>Chức vụ</strong></td><td><?php echo $nhanvien['ten_chucvu']; ?></td></tr>
                <tr><td><strong>Trạng thái</strong></td><td><?php echo ($nhanvien['trang_thai'] == 1) ? "Đang làm việc" : "Nghỉ việc"; ?></td></tr>
            </table>
        </div>
        <div class="card-footer text-center">
            <a href="dashboard.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!--</body>-->
<!--</html>-->
