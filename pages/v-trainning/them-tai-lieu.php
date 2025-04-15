<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

if(isset($_SESSION['username']) && isset($_SESSION['level'])) {
    if($_SESSION['level'] == 1) {
        if(isset($_GET['id'])) {
            $id = $_GET['id'];
            
            // Get course info
            $query = "SELECT * FROM khoa_hoc WHERE id = '$id'";
            $result = mysqli_query($conn, $query);
            $course = mysqli_fetch_array($result);

            if(isset($_POST['save'])) {
                $error = array();
                $success = array();
                $showMess = false;

                $tenTaiLieu = $_POST['tenTaiLieu'];
                $moTa = $_POST['moTa'];
                $loaiTaiLieu = $_POST['loaiTaiLieu'];
                $nguoiTao = $_POST['nguoiTao'];
                $ngayTao = date("Y-m-d H:i:s");

                // File upload handling
                if(isset($_FILES['fileTaiLieu'])) {
                    $file = $_FILES['fileTaiLieu'];
                    $fileName = $file['name'];
                    $fileTmp = $file['tmp_name'];
                    $fileSize = $file['size'];
                    $fileError = $file['error'];

                    // Get file extension
                    $fileExt = explode('.', $fileName);
                    $fileActualExt = strtolower(end($fileExt));

                    $allowed = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar');

                    if(in_array($fileActualExt, $allowed)) {
                        if($fileError === 0) {
                            if($fileSize < 10000000) { // 10MB max
                                $fileNameNew = uniqid('', true) . "." . $fileActualExt;
                                $fileDestination = ROOT_PATH . '/uploads/training/' . $fileNameNew;

                                if(move_uploaded_file($fileTmp, $fileDestination)) {
                                    $insert = "INSERT INTO tai_lieu_khoa_hoc(ma_khoa_hoc, ten_tai_lieu, mo_ta, loai_tai_lieu, file_path, nguoi_tao, ngay_tao) 
                                              VALUES('$id', '$tenTaiLieu', '$moTa', '$loaiTaiLieu', '$fileNameNew', '$nguoiTao', '$ngayTao')";
                                    mysqli_query($conn, $insert);
                                    
                                    $success['success'] = 'Thêm tài liệu thành công.';
                                    echo '<script>setTimeout("window.location=\'chi-tiet-khoa-hoc.php?id='.$id.'\'",1000);</script>';
                                }
                                else {
                                    $error['error'] = 'Có lỗi xảy ra khi tải file lên.';
                                }
                            }
                            else {
                                $error['error'] = 'File quá lớn. Kích thước tối đa là 10MB.';
                            }
                        }
                        else {
                            $error['error'] = 'Có lỗi xảy ra khi tải file.';
                        }
                    }
                    else {
                        $error['error'] = 'Loại file không được hỗ trợ.';
                    }
                }
            }
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Thêm tài liệu khóa học</h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
            <li><a href="ds-khoa-hoc.php">Khóa học</a></li>
            <li><a href="chi-tiet-khoa-hoc.php?id=<?php echo $id; ?>">Chi tiết khóa học</a></li>
            <li class="active">Thêm tài liệu</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Thêm tài liệu cho khóa học: <?php echo $course['ten_khoa_hoc']; ?></h3>
                    </div>

                    <div class="box-body">
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

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Tên tài liệu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tenTaiLieu" placeholder="Nhập tên tài liệu" required>
                            </div>

                            <div class="form-group">
                                <label>Mô tả</label>
                                <textarea class="form-control" name="moTa" rows="3" placeholder="Nhập mô tả tài liệu"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Loại tài liệu <span class="text-danger">*</span></label>
                                <select class="form-control" name="loaiTaiLieu" required>
                                    <option value="">--- Chọn loại tài liệu ---</option>
                                    <option value="Tài liệu">Tài liệu</option>
                                    <option value="Bài tập">Bài tập</option>
                                    <option value="Bài giảng">Bài giảng</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>File tài liệu <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="fileTaiLieu" required>
                                <p class="help-block">Hỗ trợ: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR (Max: 10MB)</p>
                            </div>

                            <div class="form-group">
                                <label>Người tạo</label>
                                <input type="text" class="form-control" name="nguoiTao" value="<?php echo $row_acc['ten_nv']; ?>" readonly>
                            </div>

                            <button type="submit" class="btn btn-primary" name="save">
                                <i class="fa fa-plus"></i> Thêm tài liệu
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
        }
        include(ROOT_PATH . '/layouts/footer.php');
    }
    else {
        header('Location: index.php');
    }
}
else {
    header('Location: ' . BASE_URL . '/pages/dang-nhap.php');
}
?>