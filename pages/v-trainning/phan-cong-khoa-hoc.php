<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

if(isset($_SESSION['username']) && isset($_SESSION['level'])) {

    if(isset($_POST['assign'])) {
        $error = array();
        $success = array();
        $showMess = false;

        $khoaHocId = $_POST['khoaHoc'];
        $nhanVienIds = $_POST['nhanVien'];
        $ngayBatDau = $_POST['ngayBatDau'];
        $nguoiTao = $_POST['nguoiTao'];
        $ngayTao = date("Y-m-d H:i:s");

        if(empty($khoaHocId))
            $error['khoaHoc'] = 'Vui lòng chọn khóa học';
        if(empty($nhanVienIds))
            $error['nhanVien'] = 'Vui lòng chọn nhân viên';
        if(empty($ngayBatDau))
            $error['ngayBatDau'] = 'Vui lòng chọn ngày bắt đầu';

        if(!$error) {
            $showMess = true;
            foreach($nhanVienIds as $nhanVienId) {
                $insert = "INSERT INTO khoa_hoc_nhan_vien(id_khoa_hoc, id_nhan_vien, ngay_bat_dau, nguoi_tao, ngay_tao) 
                          VALUES('$khoaHocId', '$nhanVienId', '$ngayBatDau', '$nguoiTao', '$ngayTao')";
                mysqli_query($conn, $insert);
            }
            $success['success'] = 'Phân công khóa học thành công';
            echo '<script>setTimeout("window.location=\'chi-tiet-khoa-hoc.php?p=training&a=course&id='.$khoaHocId.'\'",1000);</script>';
        }
    }

    // Get departments list
    $queryPhongBan = "SELECT * FROM phong_ban ORDER BY ten_phong_ban ASC";
    $resultPhongBan = mysqli_query($conn, $queryPhongBan);

    // Get courses list
    $queryKhoaHoc = "SELECT * FROM khoa_hoc WHERE trang_thai = 'Active'";
    $resultKhoaHoc = mysqli_query($conn, $queryKhoaHoc);

    // Initialize employee query
    $queryNhanVien = "SELECT * FROM nhanvien WHERE trang_thai = '1'";
    if(isset($_POST['phongBan']) && !empty($_POST['phongBan'])) {
        $phongBan = $_POST['phongBan'];
        $queryNhanVien .= " AND phong_ban_id = '$phongBan'";
    }
    $queryNhanVien .= " ORDER BY ten_nv ASC";
    $resultNhanVien = mysqli_query($conn, $queryNhanVien);
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Phân công khóa học</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Tổng quan</a></li>
                        <li class="breadcrumb-item"><a href="ds-khoa-hoc.php">Khóa học</a></li>
                        <li class="breadcrumb-item active">Phân công khóa học</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Phân công khóa học cho nhân viên</h3>
                        </div>

                        <div class="card-body">
                            <?php if(isset($error) && $showMess == false): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-ban"></i> Lỗi!</h5>
                                <?php foreach ($error as $err) echo $err . "<br/>"; ?>
                            </div>
                            <?php endif; ?>

                            <?php if(isset($success) && $showMess == true): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-check"></i> Thành công!</h5>
                                <?php foreach ($success as $suc) echo $suc . "<br/>"; ?>
                            </div>
                            <?php endif; ?>

                            <form action="" method="POST">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Chọn khóa học <span class="text-danger">*</span></label>
                                            <select class="form-control select2bs4" name="khoaHoc" style="width: 100%;">
                                                <option value="">--- Chọn khóa học ---</option>
                                                <?php while($khoaHoc = mysqli_fetch_array($resultKhoaHoc)): ?>
                                                <option value="<?= $khoaHoc['id'] ?>" <?= (isset($_POST['khoaHoc']) && $_POST['khoaHoc'] == $khoaHoc['id']) ? 'selected' : '' ?>>
                                                    <?= $khoaHoc['ten_khoa_hoc'] ?> (<?= $khoaHoc['ma_khoa_hoc'] ?>)
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Chọn phòng ban</label>
                                            <select class="form-control select2bs4" name="phongBan" id="phongBan" style="width: 100%;">
                                                <option value="">--- Tất cả phòng ban ---</option>
                                                <?php while($phongBan = mysqli_fetch_array($resultPhongBan)): ?>
                                                <option value="<?= $phongBan['id'] ?>" <?= (isset($_POST['phongBan']) && $_POST['phongBan'] == $phongBan['id']) ? 'selected' : '' ?>>
                                                    <?= $phongBan['ten_phong_ban'] ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Chọn nhân viên <span class="text-danger">*</span></label>
                                            <select class="form-control select2bs4" multiple="multiple" name="nhanVien[]" id="nhanVien" style="width: 100%;">
                                                <?php while($nhanVien = mysqli_fetch_array($resultNhanVien)): ?>
                                                <option value="<?= $nhanVien['ma_nv'] ?>" 
                                                    <?= (isset($_POST['nhanVien']) && in_array($nhanVien['ma_nv'], $_POST['nhanVien'])) ? 'selected' : '' ?>>
                                                    <?= $nhanVien['ma_nv'] ?> - <?= $nhanVien['ten_nv'] ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Ngày bắt đầu <span class="text-danger">*</span></label>
                                            <div class="input-group date" id="ngayBatDau" data-target-input="nearest">
                                                <input type="text" class="form-control datetimepicker-input" 
                                                    name="ngayBatDau" 
                                                    data-target="#ngayBatDau"
                                                    value="<?= isset($_POST['ngayBatDau']) ? $_POST['ngayBatDau'] : '' ?>"/>
                                                <div class="input-group-append" data-target="#ngayBatDau" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Người tạo</label>
                                            <input type="text" class="form-control" name="nguoiTao" value="<?= $row_acc['ten_nv'] ?>" readonly>
                                        </div>

                                        <?php if($_SESSION['level'] == 1): ?>
                                        <button type="submit" class="btn btn-primary" name="assign">
                                            <i class="fas fa-plus"></i> Phân công
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
    include('../../layout/footer.php');
}
else {
    header('Location: dang-nhap.php');
}
?>

<script>
$(function () {
    //Initialize Select2 Elements
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    });

    //Date picker
    $('#ngayBatDau').datetimepicker({
        format: 'L'
    });

    $('#phongBan').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
