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

// create position code
$positionCode = "MCV" . time();

// Handle Add Position
if(isset($_POST['save'])) {
    $titlePosition = $_POST['titlePosition'];
    $description = $_POST['description'];
    $personCreate = $row_acc['ten_nv'];
    $dateCreate = date("Y-m-d H:i:s");
    
    if(!empty($titlePosition)) {
        $insert = "INSERT INTO chuc_vu(ma_chuc_vu, ten_chuc_vu, ghi_chu, nguoi_tao, ngay_tao, nguoi_sua, ngay_sua) 
                  VALUES(?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "sssssss", $positionCode, $titlePosition, $description, $personCreate, $dateCreate, $personCreate, $dateCreate);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<script>window.location='chuc-vu.php?p=staff&a=position&add=success';</script>";
            exit;
        }
    }
}

// Handle Update Position
if(isset($_POST['update'])) {
    $id = $_POST['id'];
    $titlePosition = $_POST['titlePosition'];
    $description = $_POST['description'];
    $personEdit = $row_acc['ten_nv']; //isset($row_acc['ho']) ? $row_acc['ho'] . ' ' . $row_acc['ten'] : '';
    $dateEdit = date("Y-m-d H:i:s");

    $update = "UPDATE chuc_vu SET ten_chuc_vu = ?, ghi_chu = ?, nguoi_sua = ?, ngay_sua = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "ssssi", $titlePosition, $description, $personEdit, $dateEdit, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='chuc-vu.php?p=staff&a=position&update=success';</script>";
        exit;
    }
}

// Handle Delete Position
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = "DELETE FROM chuc_vu WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='chuc-vu.php?p=staff&a=position&del=success';</script>";
        exit;
    }
}

// Fetch Positions with employee count
$showData = "SELECT cv.id, cv.ma_chuc_vu, cv.ten_chuc_vu, cv.ghi_chu, 
                    cv.nguoi_tao, cv.ngay_tao, cv.nguoi_sua, cv.ngay_sua,
                    COUNT(nv.id) AS so_nhan_vien
             FROM chuc_vu cv
             LEFT JOIN nhanvien nv ON cv.id = nv.chuc_vu_id
             GROUP BY cv.id
             ORDER BY cv.ngay_tao DESC";
$result = mysqli_query($conn, $showData);
$positions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý chức vụ</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Tổng quan</a></li>
                        <li class="breadcrumb-item active">Chức vụ</li>
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
                    <h3 class="card-title">Thêm chức vụ mới</h3>
                </div>
                <div class="card-body">
                    <?php if($_SESSION['level'] == 1): ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mã chức vụ:</label>
                                    <input type="text" class="form-control" name="positionCode" value="<?php echo $positionCode; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên chức vụ: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Nhập tên chức vụ" name="titlePosition" required>
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
                    <h3 class="card-title">Danh sách chức vụ</h3>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th>Mã chức vụ</th>
                                <th>Tên chức vụ</th>
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
                        <?php foreach ($positions as $index => $pos): ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($pos['ma_chuc_vu']); ?></td>
                                <td><?php echo htmlspecialchars($pos['ten_chuc_vu']); ?></td>
                                <td class="text-center"><?php echo $pos['so_nhan_vien']; ?></td>
                                <td class="text-left"><?php echo $pos['ghi_chu']; ?></td>
                                <td><?php echo htmlspecialchars($pos['nguoi_tao']); ?></td>
                                <td><?php echo $pos['ngay_tao']; ?></td>
                                <td><?php echo htmlspecialchars($pos['nguoi_sua']); ?></td>
                                <td><?php echo $pos['ngay_sua']; ?></td>
                                <td class="text-center">
                                    <?php if($_SESSION['level'] == 1): ?>
                                        <button class="btn btn-warning btn-sm" onclick="editPosition(<?php echo $pos['id']; ?>, '<?php echo addslashes($pos['ten_chuc_vu']); ?>', '<?php echo addslashes($pos['ghi_chu']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deletePosition(<?php echo $pos['id']; ?>)">
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
                    <h4 class="modal-title">Cập nhật chức vụ</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Tên chức vụ <span class="text-danger">*</span></label>
                        <input type="text" name="titlePosition" id="edit_titlePosition" class="form-control" required>
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
    const msg= params.has('add') ? 'Thêm chức vụ thành công' :
            params.has('update') ? 'Cập nhật chức vụ  thành công' :
            'Xóa chức vụ thành công';
    Swal.fire({
        icon: 'success',
        title: 'Thành công',
        text: $msg ,
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>
});

function editPosition(id, titlePosition, description) {
    $('#edit_id').val(id);
    $('#edit_titlePosition').val(titlePosition);
    $('#edit_description').val(description);
    $('#editModal').modal('show');
}

function deletePosition(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa chức vụ này?",
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