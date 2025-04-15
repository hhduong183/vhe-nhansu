<?php 
session_start();
ob_start();

if(isset($_SESSION['username']) && isset($_SESSION['level']))
{
    include('../layouts/header.php');
    include('../layouts/topbar.php');
    include('../layouts/sidebar.php');

    if(isset($_GET['id']))
    {
        $id = $_GET['id'];
        $showData = "SELECT id, ma_phong_ban, ten_phong_ban, ghi_chu, nguoi_sua, ngay_sua FROM phong_ban WHERE id = ?";
        $stmt = mysqli_prepare($conn, $showData);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result);
        $personEdit = $row_acc['ten_nv'];
    }

    if(isset($_POST['save']))
    {
        // create array error
        $error = array();
        $success = array();
        $showMess = false;

        // get id in form
        $roomName = $_POST['roomName'];
        $description = $_POST['description'];
        $dateEdit = date("Y-m-d H:i:s");

        // validate
        if(empty($roomName))
          $error['roomName'] = 'Vui lòng nhập <b> tên phòng ban </b>';

        if(!$error)
        {
            $showMess = true;
            $update = " UPDATE phong_ban SET
                    ten_phong_ban = '$roomName',
                    ghi_chu = '$description',
                    nguoi_sua = '$personEdit',
                    ngay_sua = '$dateEdit'
                    WHERE id = $id";
            mysqli_query($conn, $update);
            $success['success'] = 'Lưu lại thành công.';
            echo '<script>setTimeout("window.location=\'sua-phong-ban.php?p=staff&a=room&id='.$id.'\'",1000);</script>';
        }

    }
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Chỉnh sửa phòng ban</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="phong-ban.php?p=staff&a=room">Phòng ban</a></li>
                        <li class="breadcrumb-item active">Chỉnh sửa phòng ban</li>
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
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin phòng ban</h3>
                        </div>
                        <div class="card-body">
                            <?php 
                            if(isset($error) && $showMess == false) {
                                echo '<div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <h5><i class="icon fas fa-ban"></i> Lỗi!</h5>';
                                foreach ($error as $err) {
                                    echo $err . "<br/>";
                                }
                                echo '</div>';
                            }

                            if(isset($success) && $showMess == true) {
                                echo '<div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <h5><i class="icon fas fa-check"></i> Thành công!</h5>';
                                foreach ($success as $suc) {
                                    echo $suc . "<br/>";
                                }
                                echo '</div>';
                            }
                            ?>

                            <form action="" method="POST">
                                <div class="form-group">
                                    <label>Mã phòng ban</label>
                                    <input type="text" class="form-control" name="roomCode" 
                                           value="<?= htmlspecialchars($row['ma_phong_ban']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Tên phòng ban <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Nhập tên phòng ban" 
                                           name="roomName" value="<?= htmlspecialchars($row['ten_phong_ban']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Mô tả</label> </br>
                                    <textarea id="editor1" rows="10" cols="80" name="description"><?= htmlspecialchars($row['ghi_chu']) ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Người sửa</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($personEdit) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Ngày sửa</label>
                                    <input type="text" class="form-control" 
                                           value="<?= date('d/m/Y H:i:s') ?>" readonly>
                                </div>
                                <button type="submit" class="btn btn-primary" name="save">
                                    <i class="fas fa-save"></i> Lưu lại
                                </button>
                                <a href="phong-ban.php?p=staff&a=room" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
include('../layouts/footer.php');
?>

<script>
$(document).ready(function() {
    $('#summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview']]
        ]
    });
});
</script>

<?php
}
else
{
    header('Location: dang-nhap.php');
    exit;
}
ob_end_flush();
?>