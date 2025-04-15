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

// create specialization code
$specialCode = "MCM" . time();

// Handle Add Specialization
if(isset($_POST['save'])) {
    $titleSpecial = $_POST['titleSpecial'];
    $description = $_POST['description'];
    $personCreate = $row_acc['ten_nv'];//isset($row_acc['ho']) ? $row_acc['ho'] . ' ' . $row_acc['ten'] : '';
    $dateCreate = date("Y-m-d H:i:s");
    
    if(!empty($titleSpecial)) {
        $insert = "INSERT INTO chuyen_mon(ma_chuyen_mon, ten_chuyen_mon, ghi_chu, nguoi_tao, ngay_tao, nguoi_sua, ngay_sua) 
                  VALUES(?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "sssssss", $specialCode, $titleSpecial, $description, $personCreate, $dateCreate, $personCreate, $dateCreate);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<script>window.location='chuyen-mon.php?p=system&a=specialize&add=success';</script>";
            exit;
        }
    }
}

// Handle Update Specialization
if(isset($_POST['update'])) {
    $id = $_POST['id'];
    $titleSpecial = $_POST['titleSpecial'];
    $description = $_POST['description'];
    $personEdit = $row_acc['ten_nv'];
    $dateEdit = date("Y-m-d H:i:s");

    $update = "UPDATE chuyen_mon SET ten_chuyen_mon = ?, ghi_chu = ?, nguoi_sua = ?, ngay_sua = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "ssssi", $titleSpecial, $description, $personEdit, $dateEdit, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='chuyen-mon.php?p=system&a=specialize&update=success';</script>";
        exit;
    }
}

// Handle Delete Specialization
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = "DELETE FROM chuyen_mon WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>window.location='chuyen-mon.php?p=system&a=specialize&del=success';</script>";
        exit;
    }
}

// Fetch Specializations with employee count
$showData = "SELECT cm.id, cm.ma_chuyen_mon, cm.ten_chuyen_mon, cm.ghi_chu, 
                    cm.nguoi_tao, cm.ngay_tao, cm.nguoi_sua, cm.ngay_sua,
                    COUNT(nv.id) AS so_nhan_vien
             FROM chuyen_mon cm
             LEFT JOIN nhanvien nv ON cm.id = nv.chuyen_mon_id
             GROUP BY cm.id
             ORDER BY cm.ngay_tao DESC";
$result = mysqli_query($conn, $showData);
$specializations = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý chuyên môn</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Tổng quan</a></li>
                        <li class="breadcrumb-item active">Chuyên môn</li>
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
                    <h3 class="card-title">Thêm chuyên môn mới</h3>
                </div>
                <div class="card-body">
                    <?php if($_SESSION['level'] == 1): ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mã chuyên môn:</label>
                                    <input type="text" class="form-control" name="specialCode" value="<?php echo $specialCode; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên chuyên môn: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" placeholder="Nhập tên chuyên môn" name="titleSpecial" required>
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
                    <h3 class="card-title">Danh sách chuyên môn</h3>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th>Mã chuyên môn</th>
                                <th>Tên chuyên môn</th>
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
                        <?php foreach ($specializations as $index => $spec): ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($spec['ma_chuyen_mon']); ?></td>
                                <td><?php echo htmlspecialchars($spec['ten_chuyen_mon']); ?></td>
                                <td class="text-center"><?php echo $spec['so_nhan_vien']; ?></td>
                                <td class="text-center"><?php echo $spec['ghi_chu']; ?></td>
                                <td><?php echo htmlspecialchars($spec['nguoi_tao']); ?></td>
                                <td><?php echo $spec['ngay_tao']; ?></td>
                                <td><?php echo htmlspecialchars($spec['nguoi_sua']); ?></td>
                                <td><?php echo $spec['ngay_sua']; ?></td>
                                <td class="text-center">
                                    <?php if($_SESSION['level'] == 1): ?>
                                        <button type="button" class="btn btn-warning btn-sm" 
                                                onclick="editSpecialization('<?php echo $spec['id']; ?>', 
                                                                         '<?php echo addslashes($spec['ten_chuyen_mon']); ?>', 
                                                                         '<?php echo addslashes($spec['ghi_chu']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="deleteSpecialization(<?php echo $spec['id']; ?>)">
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
                    <h4 class="modal-title">Cập nhật chuyên môn</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Tên chuyên môn <span class="text-danger">*</span></label>
                        <input type="text" name="titleSpecial" id="edit_titleSpecial" class="form-control" required>
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
    const msg= params.has('add') ? 'Thêm chuyên môn thành công' :
            params.has('update') ? 'Cập nhật chuyên môn  thành công' :
            'Xóa chuyên môn thành công';
    Swal.fire({
        icon: 'success',
        title: 'Thành công',
        text: $msg ,
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>
});
</script>

<script>
function editSpecialization(id, titleSpecial, description) {
    $('#edit_id').val(id);
    $('#edit_titleSpecial').val(titleSpecial);
    $('#edit_description').val(description.trim());
    $('#editModal').modal('show');
}

// Make sure this code is added to document.ready:
$(document).ready(function() {
    // Initialize the modal if it's not already initialized
    $('#editModal').modal({
        show: false
    });
});


function deleteSpecialization(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa chuyên môn này?",
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