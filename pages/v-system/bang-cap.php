<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');


if(!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
    header('Location: ' . BASE_URL . '/pages/dang-nhap.php');
    exit;
}

include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

// create certificate code
$certificateCode = "MBC" . time();

// Handle Add Certificate
if(isset($_POST['save'])) {
    $certificateName = $_POST['certificateName'];
    $description = $_POST['description'];
    $personCreate = $row_acc['ten_nv'];
    $dateCreate = date("Y-m-d H:i:s");
    
    if(!empty($certificateName)) {
        $insert = "INSERT INTO bang_cap(ma_bang_cap, ten_bang_cap, ghi_chu, nguoi_tao, ngay_tao, nguoi_sua, ngay_sua) 
                  VALUES(?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "sssssss", $certificateCode, $certificateName, $description, $personCreate, $dateCreate, $personCreate, $dateCreate);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<script>window.location='bang-cap.php?p=system&a=certificate&add=success';</script>";
            exit;
        }
    }
}

// Handle Update Certificate
if(isset($_POST['update'])) {
    $id = $_POST['id'];
    $certificateName = $_POST['certificateName'];
    $description = $_POST['description'];
    $personEdit = $row_acc['ten_nv'];
    $dateEdit = date("Y-m-d H:i:s");

    $update = "UPDATE bang_cap SET ten_bang_cap = ?, ghi_chu = ?, nguoi_sua = ?, ngay_sua = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "ssssi", $certificateName, $description, $personEdit, $dateEdit, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='bang-cap.php?p=system&a=certificate&update=success';</script>";
        exit;
    }
}

// Handle Delete Certificate
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = "DELETE FROM bang_cap WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='bang-cap.php?p=system&a=certificate&del=success';</script>";
        exit;
    }
}

// Fetch Certificates with employee count
$showData = "SELECT bc.id, bc.ma_bang_cap, bc.ten_bang_cap, bc.ghi_chu, 
                    bc.nguoi_tao, bc.ngay_tao, bc.nguoi_sua, bc.ngay_sua,
                    COUNT(nv.id) AS so_nhan_vien
             FROM bang_cap bc
             LEFT JOIN nhanvien nv ON bc.id = nv.bang_cap_id
             GROUP BY bc.id
             ORDER BY bc.ngay_tao DESC";
$result = mysqli_query($conn, $showData);
$certificates = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý bằng cấp</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Tổng quan</a></li>
                        <li class="breadcrumb-item active">Bằng cấp</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Add Form Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thêm bằng cấp mới</h3>
                </div>
                <div class="card-body">
                    <?php if($_SESSION['level'] == 1): ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mã bằng cấp:</label>
                                    <input type="text" class="form-control" name="certificateCode" value="<?php echo $certificateCode; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên bằng cấp: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Nhập tên bằng cấp" name="certificateName" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mô tả:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Nhập mô tả" name="description">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary" name="save">
                                                <i class="fas fa-plus"></i> Thêm mới
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- List Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách bằng cấp</h3>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th>Mã bằng cấp</th>
                                <th>Tên bằng cấp</th>
                                <th>Số nhân viên</th>
                                <th>Mô tả</th>
                                <th>Người tạo</th>
                                <th>Ngày tạo</th>
                                <th>Người sửa</th>
                                <th>Ngày sửa</th>
                                <th width="10%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($certificates as $index => $cert): ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($cert['ma_bang_cap']); ?></td>
                                <td><?php echo htmlspecialchars($cert['ten_bang_cap']); ?></td>
                                <td class="text-center"><?php echo $cert['so_nhan_vien']; ?></td>
                                <td><?php echo htmlspecialchars($cert['ghi_chu']); ?></td>
                                <td><?php echo htmlspecialchars($cert['nguoi_tao']); ?></td>
                                <td><?php echo $cert['ngay_tao']; ?></td>
                                <td><?php echo htmlspecialchars($cert['nguoi_sua']); ?></td>
                                <td><?php echo $cert['ngay_sua']; ?></td>
                                <td class="text-center">
                                    <?php if($_SESSION['level'] == 1): ?>
                                        <button class="btn btn-warning btn-sm" onclick="editCertificate(<?php echo $cert['id']; ?>, '<?php echo addslashes($cert['ten_bang_cap']); ?>', '<?php echo addslashes($cert['ghi_chu']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteCertificate(<?php echo $cert['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h4 class="modal-title">Cập nhật bằng cấp</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Tên bằng cấp <span class="text-danger">*</span></label>
                        <input type="text" name="certificateName" id="edit_certificateName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    <button type="submit" name="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    <?php if (isset($_GET['add']) || isset($_GET['update']) || isset($_GET['del'])): ?>
    const msg= params.has('add') ? 'Thêm bằng cấp thành công' :
              params.has('update') ? 'Cập nhật bằng cấp  thành công' :
              'Xóa bằng cấp thành công';
    Swal.fire({
        icon: 'success',
        title: 'Thành công',
        text: $msg,
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>
});

function editCertificate(id, certificateName, description) {
    $('#edit_id').val(id);
    $('#edit_certificateName').val(certificateName);
    $('#edit_description').val(description);
    $('#editModal').modal('show');
}

function deleteCertificate(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa bằng cấp này?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete=${id}`;
        }
    });
}
</script>

<?php 
include(ROOT_PATH . '/layouts/footer.php');
?>