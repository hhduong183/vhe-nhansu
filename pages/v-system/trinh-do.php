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

// create level code
$levelCode = "MTD" . time();

// Handle Add Level
if(isset($_POST['save'])) {
    $titleLevel = $_POST['titleLevel'];
    $description = $_POST['description'];
    $personCreate = $row_acc['ten_nv'];//isset($row_acc['ho']) ? $row_acc['ho'] . ' ' . $row_acc['ten'] : '';
    $dateCreate = date("Y-m-d H:i:s");
    
    if(!empty($titleLevel)) {
        $insert = "INSERT INTO trinh_do(ma_trinh_do, ten_trinh_do, ghi_chu, nguoi_tao, ngay_tao, nguoi_sua, ngay_sua) 
                  VALUES(?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "sssssss", $levelCode, $titleLevel, $description, $personCreate, $dateCreate, $personCreate, $dateCreate);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<script>window.location='trinh-do.php?p=system&a=level&add=success';</script>";
            exit;
        }
    }
}

// Handle Update Level
if(isset($_POST['update'])) {
    $id = $_POST['id'];
    $titleLevel = $_POST['titleLevel'];
    $description = $_POST['description'];
    $personEdit = $row_acc['ten_nv'];//isset($row_acc['ho']) ? $row_acc['ho'] . ' ' . $row_acc['ten'] : '';
    $dateEdit = date("Y-m-d H:i:s");

    $update = "UPDATE trinh_do SET ten_trinh_do = ?, ghi_chu = ?, nguoi_sua = ?, ngay_sua = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "ssssi", $titleLevel, $description, $personEdit, $dateEdit, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='trinh-do.php?p=system&a=level&update=success';</script>";
        exit;
    }
}

// Handle Delete Level
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = "DELETE FROM trinh_do WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='trinh-do.php?p=system&a=level&del=success';</script>";
        exit;
    }
}

// Fetch Levels with employee count
$showData = "SELECT td.id, td.ma_trinh_do, td.ten_trinh_do, td.ghi_chu, 
                    td.nguoi_tao, td.ngay_tao, td.nguoi_sua, td.ngay_sua,
                    COUNT(nv.id) AS so_nhan_vien
             FROM trinh_do td
             LEFT JOIN nhanvien nv ON td.id = nv.trinh_do_id
             GROUP BY td.id
             ORDER BY td.ngay_tao DESC";
$result = mysqli_query($conn, $showData);
$levels = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý trình độ</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Tổng quan</a></li>
                        <li class="breadcrumb-item active">Trình độ</li>
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
                    <h3 class="card-title">Thêm trình độ mới</h3>
                </div>
                <div class="card-body">
                    <?php if($_SESSION['level'] == 1): ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mã trình độ:</label>
                                    <input type="text" class="form-control" name="levelCode" value="<?php echo $levelCode; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên trình độ: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Nhập tên trình độ" name="titleLevel" required>
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
                    <h3 class="card-title">Danh sách trình độ</h3>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th>Mã trình độ</th>
                                <th>Tên trình độ</th>
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
                        <?php foreach ($levels as $index => $level): ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($level['ma_trinh_do']); ?></td>
                                <td><?php echo htmlspecialchars($level['ten_trinh_do']); ?></td>
                                <td class="text-center"><?php echo $level['so_nhan_vien']; ?></td>
                                <td class="text-center"><?php echo $level['ghi_chu']; ?></td>
                                <td><?php echo htmlspecialchars($level['nguoi_tao']); ?></td>
                                <td><?php echo $level['ngay_tao']; ?></td>
                                <td><?php echo htmlspecialchars($level['nguoi_sua']); ?></td>
                                <td><?php echo $level['ngay_sua']; ?></td>
                                <td class="text-center">
                                    <?php if($_SESSION['level'] == 1): ?>
                                        <button class="btn btn-warning btn-sm" onclick="editLevel(<?php echo $level['id']; ?>, '<?php echo addslashes($level['ten_trinh_do']); ?>', '<?php echo addslashes($level['ghi_chu']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteLevel(<?php echo $level['id']; ?>)">
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
                    <h4 class="modal-title">Cập nhật trình độ</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Tên trình độ <span class="text-danger">*</span></label>
                        <input type="text" name="titleLevel" id="edit_titleLevel" class="form-control" required>
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
            if(isset($_GET['add'])) echo 'Thêm trình độ thành công';
            elseif(isset($_GET['update'])) echo 'Cập nhật trình độ thành công';
            elseif(isset($_GET['del'])) echo 'Xóa trình độ thành công';
        ?>',
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>
});

function editLevel(id, titleLevel, description) {
    $('#edit_id').val(id);
    $('#edit_titleLevel').val(titleLevel);
    $('#edit_description').val(description);
    $('#editModal').modal('show');
}

function deleteLevel(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa trình độ này?",
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