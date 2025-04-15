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

// create employee type code
$typeCode = "LNV" . time();

// Handle Add Employee Type
if(isset($_POST['save'])) {
    $typeName = $_POST['typeName'];
    $description = $_POST['description'];
    $personCreate = $row_acc['ten_nv'];
    $dateCreate = date("Y-m-d H:i:s");
    
    if(!empty($typeName)) {
        $insert = "INSERT INTO loai_nv(ma_loai_nv, ten_loai_nv, ghi_chu, nguoi_tao, ngay_tao, nguoi_sua, ngay_sua) 
                  VALUES(?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "sssssss", $typeCode, $typeName, $description, $personCreate, $dateCreate, $personCreate, $dateCreate);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<script>window.location='loai-nhan-vien.php?p=system&a=employee-type&add=success';</script>";
            exit;
        }
    }
}

// Handle Update Employee Type
if(isset($_POST['update'])) {
    $id = $_POST['id'];
    $typeName = $_POST['typeName'];
    $description = $_POST['description'];
    $personEdit = $row_acc['ten_nv'];
    $dateEdit = date("Y-m-d H:i:s");

    $update = "UPDATE loai_nv SET ten_loai_nv = ?, ghi_chu = ?, nguoi_sua = ?, ngay_sua = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "ssssi", $typeName, $description, $personEdit, $dateEdit, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='loai-nhan-vien.php?p=system&a=employee-type&update=success';</script>";
        exit;
    }
}

// Handle Delete Employee Type
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = "DELETE FROM loai_nv WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='loai-nhan-vien.php?p=system&a=employee-type&del=success';</script>";
        exit;
    }
}

// Fetch Employee Types with employee count
$showData = "SELECT lnv.id, lnv.ma_loai_nv, lnv.ten_loai_nv, lnv.ghi_chu, 
                    lnv.nguoi_tao, lnv.ngay_tao, lnv.nguoi_sua, lnv.ngay_sua,
                    COUNT(nv.id) AS so_nhan_vien
             FROM loai_nv lnv
             LEFT JOIN nhanvien nv ON lnv.id = nv.loai_nv_id
             GROUP BY lnv.id
             ORDER BY lnv.ngay_tao DESC";
$result = mysqli_query($conn, $showData);
$types = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý loại nhân viên</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Tổng quan</a></li>
                        <li class="breadcrumb-item active">Loại nhân viên</li>
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
                    <h3 class="card-title">Thêm loại nhân viên mới</h3>
                </div>
                <div class="card-body">
                    <?php if($_SESSION['level'] == 1): ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mã loại:</label>
                                    <input type="text" class="form-control" name="typeCode" value="<?php echo $typeCode; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên loại: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Nhập tên loại" name="typeName" required>
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
                    <h3 class="card-title">Danh sách loại nhân viên</h3>
                </div>
                <div class="card-body">
                    <table id="dataTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th>Mã loại</th>
                                <th>Tên loại</th>
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
                        <?php foreach ($types as $index => $type): ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($type['ma_loai_nv']); ?></td>
                                <td><?php echo htmlspecialchars($type['ten_loai_nv']); ?></td>
                                <td class="text-center"><?php echo $type['so_nhan_vien']; ?></td>
                                <td><?php echo htmlspecialchars($type['ghi_chu']); ?></td>
                                <td><?php echo htmlspecialchars($type['nguoi_tao']); ?></td>
                                <td><?php echo $type['ngay_tao']; ?></td>
                                <td><?php echo htmlspecialchars($type['nguoi_sua']); ?></td>
                                <td><?php echo $type['ngay_sua']; ?></td>
                                <td class="text-center">
                                    <?php if($_SESSION['level'] == 1): ?>
                                        <button class="btn btn-warning btn-sm" onclick="editType(<?php echo $type['id']; ?>, '<?php echo addslashes($type['ten_loai_nv']); ?>', '<?php echo addslashes($type['ghi_chu']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteType(<?php echo $type['id']; ?>)">
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
                    <h4 class="modal-title">Cập nhật loại nhân viên</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Tên loại <span class="text-danger">*</span></label>
                        <input type="text" name="typeName" id="edit_typeName" class="form-control" required>
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
    $('#dataTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');

    <?php if (isset($_GET['add']) || isset($_GET['update']) || isset($_GET['del'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Thành công',
        text: '<?php 
            if(isset($_GET['add'])) echo 'Thêm loại nhân viên thành công';
            elseif(isset($_GET['update'])) echo 'Cập nhật loại nhân viên thành công';
            elseif(isset($_GET['del'])) echo 'Xóa loại nhân viên thành công';
        ?>',
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>
});

function editType(id, typeName, description) {
    $('#edit_id').val(id);
    $('#edit_typeName').val(typeName);
    $('#edit_description').val(description);
    $('#editModal').modal('show');
}

function deleteType(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa loại nhân viên này?",
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