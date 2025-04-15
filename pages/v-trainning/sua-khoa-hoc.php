<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

if(isset($_SESSION['username']) && isset($_SESSION['level'])) {


    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $showData = "SELECT id, ma_khoa_hoc, ten_khoa_hoc, mo_ta, bo_phan, trang_thai, nguoi_tao, ngay_tao, nguoi_sua, ngay_sua 
                     FROM khoa_hoc WHERE id = $id";
        $result = mysqli_query($conn, $showData);
        $row = mysqli_fetch_array($result);
    }

    if(isset($_POST['save'])) {
        $error = array();
        $success = array();
        $showMess = false;

        $tenKhoaHoc = $_POST['tenKhoaHoc'];
        $boPhan = $_POST['boPhan'];
        $moTa = $_POST['moTa'];
        $trangThai = $_POST['trangThai'];
        $nguoiSua = $_POST['nguoiSua'];
        $ngaySua = date("Y-m-d H:i:s");

        if(empty($tenKhoaHoc))
            $error['tenKhoaHoc'] = 'Vui lòng nhập <b> tên khóa học </b>';
        if(empty($boPhan))
            $error['boPhan'] = 'Vui lòng chọn <b> bộ phận </b>';

        if(!$error) {
            $showMess = true;
            $update = "UPDATE khoa_hoc SET 
                      ten_khoa_hoc = '$tenKhoaHoc',
                      mo_ta = '$moTa',
                      bo_phan = '$boPhan',
                      trang_thai = '$trangThai',
                      nguoi_sua = '$nguoiSua',
                      ngay_sua = '$ngaySua'
                      WHERE id = $id";
            mysqli_query($conn, $update);
            $success['success'] = 'Cập nhật thành công';
            echo '<script>setTimeout("window.location=\'sua-khoa-hoc.php?p=training&a=course&id='.$id.'\'",1000);</script>';
        }
    }
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Chỉnh sửa khóa học</h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
            <li><a href="ds-khoa-hoc.php">Khóa học</a></li>
            <li class="active">Chỉnh sửa khóa học</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Chỉnh sửa khóa học</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
                        </div>
                    </div>

                    <div class="box-body">
                        <?php
                        if($row_acc['user_quyen'] != 1) {
                            echo "<div class='alert alert-warning alert-dismissible'>";
                            echo "<h4><i class='icon fa fa-ban'></i> Thông báo!</h4>";
                            echo "Bạn <b> không có quyền </b> thực hiện chức năng này.";
                            echo "</div>";
                        }
                        ?>

                        <?php
                        if(isset($error)) {
                            if($showMess == false) {
                                echo "<div class='alert alert-danger alert-dismissible'>";
                                echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
                                echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                                foreach ($error as $err) {
                                    echo $err . "<br/>";
                                }
                                echo "</div>";
                            }
                        }
                        ?>

                        <?php
                        if(isset($success)) {
                            if($showMess == true) {
                                echo "<div class='alert alert-success alert-dismissible'>";
                                echo "<h4><i class='icon fa fa-check'></i> Thành công!</h4>";
                                foreach ($success as $suc) {
                                    echo $suc . "<br/>";
                                }
                                echo "</div>";
                            }
                        }
                        ?>

                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Mã khóa học:</label>
                                        <input type="text" class="form-control" value="<?php echo $row['ma_khoa_hoc']; ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Tên khóa học:</label>
                                        <input type="text" class="form-control" name="tenKhoaHoc" value="<?php echo $row['ten_khoa_hoc']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Bộ phận:</label>
                                        <select class="form-control" name="boPhan">
                                            <option value="">--- Chọn bộ phận ---</option>
                                            <option value="Sales" <?php if($row['bo_phan'] == 'Sales') echo 'selected'; ?>>Sales</option>
                                            <option value="Management" <?php if($row['bo_phan'] == 'Management') echo 'selected'; ?>>Management</option>
                                            <option value="Support" <?php if($row['bo_phan'] == 'Support') echo 'selected'; ?>>Support</option>
                                            <option value="Export" <?php if($row['bo_phan'] == 'Export') echo 'selected'; ?>>Export</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Trạng thái:</label>
                                        <select class="form-control" name="trangThai">
                                            <option value="Active" <?php if($row['trang_thai'] == 'Active') echo 'selected'; ?>>Active</option>
                                            <option value="Inactive" <?php if($row['trang_thai'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Mô tả:</label>
                                        <textarea id="editor1" name="moTa" class="form-control" rows="10" cols="80"><?php echo $row['mo_ta']; ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Người sửa:</label>
                                        <input type="text" class="form-control" name="nguoiSua" value="<?php echo $row_acc['ten_nv']; ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Ngày sửa:</label>
                                        <input type="text" class="form-control" value="<?php echo date('d-m-Y H:i:s'); ?>" readonly>
                                    </div>
                                    <?php 
                                    if($_SESSION['level'] == 1)
                                        echo "<button type='submit' class='btn btn-warning' name='save'><i class='fa fa-save'></i> Lưu lại</button>";
                                    ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
    include(ROOT_PATH . '/layouts/footer.php');
}
else {
    header('Location: dang-nhap.php');
}
?>