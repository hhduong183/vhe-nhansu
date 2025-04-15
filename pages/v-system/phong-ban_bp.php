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

// create code room
$roomCode = "MBP" . time();

// Handle Add Department
if(isset($_POST['save'])) {
    $roomName = $_POST['roomName'];
    $description = $_POST['description'];
    $personCreate = isset($row_acc['ho']) ? $row_acc['ho'] . ' ' . $row_acc['ten'] : '';
    $dateCreate = date("Y-m-d H:i:s");
    
    if(!empty($roomName)) {
        $insert = "INSERT INTO phong_ban(ma_phong_ban, ten_phong_ban, ghi_chu, nguoi_tao, ngay_tao, nguoi_sua, ngay_sua) 
                  VALUES(?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "sssssss", $roomCode, $roomName, $description, $personCreate, $dateCreate, $personCreate, $dateCreate);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<script>window.location='phong-ban_bp.php?p=system&a=tonhom&add=success';</script>";
            exit;
        }
    }
}

// Add update handler
if(isset($_POST['update'])) {
    $id = $_POST['id'];
    $roomName = $_POST['roomName'];
    $description = $_POST['description'];
    $personEdit = isset($row_acc['ho']) ? $row_acc['ho'] . ' ' . $row_acc['ten'] : '';
    $dateEdit = date("Y-m-d H:i:s");

    $update = "UPDATE phong_ban SET ten_phong_ban = ?, ghi_chu = ?, nguoi_sua = ?, ngay_sua = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "ssssi", $roomName, $description, $personEdit, $dateEdit, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='phong-ban_bp.php?p=system&a=tonhom&update=success';</script>";
        exit;
    }
}

// Add delete handler
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = "DELETE FROM phong_ban WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='phong-ban_bp.php?p=system&a=tonhom&del=success';</script>";
        exit;
    }
}

// Fetch Departments with employee count
$showData = "SELECT pb.id, pb.ma_phong_ban, pb.ten_phong_ban, pb.ghi_chu, 
                    pb.nguoi_tao, pb.ngay_tao, pb.nguoi_sua, pb.ngay_sua, 
                    COUNT(nv.id) AS so_nhan_vien  
             FROM phong_ban pb
             LEFT JOIN nhanvien nv ON pb.id = nv.phong_ban_id 
             GROUP BY pb.id
             ORDER BY pb.ngay_tao DESC";
$result = mysqli_query($conn, $showData);
$departments = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý phòng ban</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Tổng quan</a></li>
                        <li class="breadcrumb-item active">Phòng ban</li>
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
                    <h3 class="card-title">Thêm phòng ban mới</h3>
                </div>
                <div class="card-body">
                    <?php if($_SESSION['level'] == 1): ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mã phòng ban:</label>
                                    <input type="text" class="form-control" name="roomCode" value="<?php echo $roomCode; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên phòng ban: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Nhập tên phòng ban" name="roomName" required>
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
                    <h3 class="card-title">Danh sách phòng ban</h3>
                </div>
                <div class="card-body">
                    <table id="dataTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th>Mã phòng</th>
                                <th>Tên phòng</th>
                                <th>Số nhân viên</th>
                                <th>Người tạo</th>
                                <th>Ngày tạo</th>
                                <th>Người sửa</th>
                                <th>Ngày sửa</th>
                                <th width="10%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($departments as $index => $dept): ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($dept['ma_phong_ban']); ?></td>
                                <td><?php echo htmlspecialchars($dept['ten_phong_ban']); ?></td>
                                <td class="text-center"><?php echo $dept['so_nhan_vien']; ?></td>
                                <td><?php echo htmlspecialchars($dept['nguoi_tao']); ?></td>
                                <td><?php echo $dept['ngay_tao']; ?></td>
                                <td><?php echo htmlspecialchars($dept['nguoi_sua']); ?></td>
                                <td><?php echo $dept['ngay_sua']; ?></td>
                                <td class="text-center">
                                    <?php if($_SESSION['level'] == 1): ?>
                                        <button class="btn btn-warning btn-sm" onclick="editDepartment(<?php echo $dept['id']; ?>, '<?php echo addslashes($dept['ten_phong_ban']); ?>', '<?php echo addslashes($dept['ghi_chu']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteDepartment(<?php echo $dept['id']; ?>)">
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
                    <h4 class="modal-title">Cập nhật phòng ban</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Tên phòng ban <span class="text-danger">*</span></label>
                        <input type="text" name="roomName" id="edit_roomName" class="form-control" required>
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
    Swal.fire({
        icon: 'success',
        title: 'Thành công',
        text: '<?php 
            if(isset($_GET['add'])) echo 'Thêm phòng ban thành công';
            elseif(isset($_GET['update'])) echo 'Cập nhật phòng ban thành công';
            elseif(isset($_GET['del'])) echo 'Xóa phòng ban thành công';
        ?>',
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>
});

function editDepartment(id, roomName, description) {
    $('#edit_id').val(id);
    $('#edit_roomName').val(roomName);
    $('#edit_description').val(description);
    $('#editModal').modal('show');
}

function deleteDepartment(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa phòng ban này?",
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