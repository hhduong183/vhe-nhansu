<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

if(isset($_SESSION['username']) && isset($_SESSION['level'])) {
    // Fetch departments
    $query_pb = "SELECT * FROM phong_ban ORDER BY ten_phong_ban ASC";
    $result_pb = mysqli_query($conn, $query_pb);

    // Fetch teams
    $query_tn = "SELECT * FROM to_nhom ORDER BY ten_nhom ASC";
    $result_tn = mysqli_query($conn, $query_tn);

    if(isset($_POST['save'])) {
        $error = array();
        $success = array();
        $showMess = false;

        $maKhoaHoc = $_POST['maKhoaHoc'];
        $tenKhoaHoc = $_POST['tenKhoaHoc'];
        $phongBan = $_POST['phongBan'];
        $boPhan = $_POST['boPhan'];
        $moTa = $_POST['moTa'];
        $nguoiTao = $_POST['nguoiTao'];
        $ngayTao = date("Y-m-d H:i:s");

        // validate
        if(empty($maKhoaHoc))
            $error['maKhoaHoc'] = 'Vui lòng nhập <b> mã khóa học </b>';
        if(empty($tenKhoaHoc))
            $error['tenKhoaHoc'] = 'Vui lòng nhập <b> tên khóa học </b>';
        if(empty($phongBan))
            $error['phongBan'] = 'Vui lòng chọn <b> phòng ban </b>';
        if(empty($boPhan))
            $error['boPhan'] = 'Vui lòng chọn <b> bộ phận </b>';

        if(!$error) {
            $showMess = true;
            $insert = "INSERT INTO khoa_hoc(ma_khoa_hoc, ten_khoa_hoc, mo_ta, phong_ban, bo_phan, nguoi_tao, ngay_tao) 
                      VALUES('$maKhoaHoc', '$tenKhoaHoc', '$moTa', '$phongBan', '$boPhan', '$nguoiTao', '$ngayTao')";
            // echo $insert; exit;
            mysqli_query($conn, $insert);
            $success['success'] = 'Thêm khóa học thành công';
            echo '<script>setTimeout("window.location=\'ds-khoa-hoc.php?p=training&a=course\'",1000);</script>';
        }
    }
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Thêm khóa học mới</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Tổng quan</a></li>
                        <li class="breadcrumb-item"><a href="ds-khoa-hoc.php">Khóa học</a></li>
                        <li class="breadcrumb-item active">Thêm khóa học</li>
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
                            <h3 class="card-title">Thêm khóa học</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php
                            if(isset($error)) {
                                if($showMess == false) {
                                    echo "<div class='alert alert-danger alert-dismissible'>";
                                    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
                                    echo "<h5><i class='icon fas fa-ban'></i> Lỗi!</h5>";
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
                                    echo "<h5><i class='icon fas fa-check'></i> Thành công!</h5>";
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
                                            <label for="maKhoaHoc">Mã khóa học:</label>
                                            <input type="text" class="form-control" id="maKhoaHoc" name="maKhoaHoc" placeholder="Nhập mã khóa học">
                                        </div>
                                        <div class="form-group">
                                            <label for="tenKhoaHoc">Tên khóa học:</label>
                                            <input type="text" class="form-control" id="tenKhoaHoc" name="tenKhoaHoc" placeholder="Nhập tên khóa học">
                                        </div>
                                        <div class="form-group">
                                            <label for="phongBan">Phòng ban:</label>
                                            <select class="form-control" name="phongBan" id="phongBan" style="width: 100%;">
                                                <option value="">--- Chọn phòng ban ---</option>
                                                <?php 
                                                while($row_pb = mysqli_fetch_assoc($result_pb)) {
                                                    echo '<option value="'.$row_pb['ma_phong_ban'].'">'.$row_pb['ten_phong_ban'].'</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="boPhan">Bộ phận:</label>
                                            <select class="form-control" name="boPhan" id="boPhan" style="width: 100%;">
                                                <option value="">--- Chọn bộ phận ---</option>
                                                <?php 
                                                while($row_tn = mysqli_fetch_assoc($result_tn)) {
                                                    echo '<option value="'.$row_tn['ten_nhom'].'">'.$row_tn['ten_nhom'].'</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="editor1">Mô tả:</label>
                                            <textarea id="editor1" name="moTa" class="form-control" rows="10"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="nguoiTao">Người tạo:</label>
                                            <input type="text" class="form-control" id="nguoiTao" name="nguoiTao" value="<?php echo $row_acc['ten_nv']; ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ngayTao">Ngày tạo:</label>
                                            <input type="text" class="form-control" id="ngayTao" name="ngayTao" value="<?php echo date('d-m-Y H:i:s'); ?>" readonly>
                                        </div>
                                        <?php 
                                        if($_SESSION['level'] == 1)
                                            echo "<button type='submit' class='btn btn-primary' name='save'><i class='fas fa-plus'></i> Thêm khóa học</button>";
                                        ?>
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
    include(ROOT_PATH . '/layouts/footer.php');
}
else {
    header('Location: ' . BASE_URL . '/pages/dang-nhap.php');
}
?>