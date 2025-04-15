<?php 
//session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require(ROOT_PATH . '/plugins/function.php');



$idNhanVien = isset($_SESSION['idNhanVien']) ? $_SESSION['idNhanVien'] : 0;
$level = isset($_SESSION['level']) ? $_SESSION['level'] : 0;

// Kiểm tra nếu có ID nhân viên hợp lệ
if ($idNhanVien > 0) {
    // Chuẩn bị truy vấn
    $stmt = $conn->prepare("SELECT id, hinh_anh, ten_nv, ma_nv, user_quyen FROM nhanvien WHERE id = ?");
    $stmt->bind_param("i", $idNhanVien);
    $stmt->execute();
    $result = $stmt->get_result();
    $row_acc = $result->fetch_assoc();
} else {
    $row_acc = null; // Không có dữ liệu
}
?>
<body class="hold-transition sidebar-mini layout-fixed ">
    <!-- <header> -->
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light fixed-top">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?= BASE_URL ?>index.php?p=index&a=statistic" class="nav-link">
                        <img src="<?= BASE_URL ?>uploads/VHE_Logo_border_small.png" alt="VHE Logo" style="height: 40px; object-fit: contain;">
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- User Menu Dropdown -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= BASE_URL ?>uploads/staffs/<?php echo $row_acc['hinh_anh']; ?>" 
                             class="user-image img-circle elevation-2" 
                             alt="User Image">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header bg-primary">
                            <img src="<?= BASE_URL ?>uploads/staffs/<?php echo $row_acc['hinh_anh']; ?>" 
                                 class="img-circle elevation-2" 
                                 alt="User Image">
                            <p>
                                <?php echo $row_acc['ten_nv']; ?> - <?php echo $row_acc['ma_nv']; ?>
                                <small>
                                    <?php
                                    if ($row_acc['user_quyen'] == 1) {
                                        echo "Quản trị viên";
                                    } else {
                                        echo "Nhân viên";
                                    }
                                    ?>
                                </small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="thong-tin-nhan-vien.php?p=profile&a=view&id=<?php echo encryptId($idNhanVien); ?>" 
                               class="btn btn-default btn-flat">Thông tin TK</a>
                            <a href="dang-xuat.php" 
                               class="btn btn-default btn-flat float-right">Đăng xuất</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->
    <!-- </header> -->
    <div class="wrapper">
    <!-- </div> -->


