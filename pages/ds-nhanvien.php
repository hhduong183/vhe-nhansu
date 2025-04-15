<?php
require_once '../config.php'; // Kết nối đến database
session_start();

// Kết nối cơ sở dữ liệu
include('../config.php');
include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');
// Lấy tháng hiện tại
$thang_hien_tai = date('m');

// Truy vấn danh sách nhân viên có sinh nhật trong tháng
// Update SQL query to include department information
$sql = "SELECT nv.id, nv.ma_nv, nv.ten_nv, nv.ngay_sinh, nv.hinh_anh, pb.ten_phong_ban 
        FROM nhanvien nv 
        LEFT JOIN phong_ban pb ON nv.phong_ban_id = pb.id 
        WHERE MONTH(nv.ngay_sinh) = ? 
        ORDER BY (nv.phong_ban_id) ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $thang_hien_tai);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Danh sách nhân viên sinh nhật</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Home</a></li>
                        <li class="breadcrumb-item active">Sinh nhật CBCNV</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">DANH SÁCH CBCNV CÓ SINH NHẬT TRONG THÁNG <?= date('m/Y'); ?></h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="example1">
                                <thead>
                                    <tr>
                                        <th style="width: 60px">Ảnh</th>
                                        <th>Mã NV</th>
                                        <th>Tên nhân viên</th>
                                        <th>Phòng ban</th>
                                        <th>Ngày sinh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                        <tr>
                                            <td class="text-center">
                                                <img src="../uploads/staffs/<?= $row['hinh_anh'] ?: 'default-avatar.jpg'; ?>" 
                                                     class="img-circle elevation-2" 
                                                     width="50" height="50" 
                                                     alt="User Image">
                                            </td>
                                            <td><?= htmlspecialchars($row['ma_nv']); ?></td>
                                            <td><?= htmlspecialchars($row['ten_nv']); ?></td>
                                            <td><?= htmlspecialchars($row['ten_phong_ban']); ?></td>
                                            <td><?= date('d/m/Y', strtotime($row['ngay_sinh'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include('../layouts/footer.php'); ?>