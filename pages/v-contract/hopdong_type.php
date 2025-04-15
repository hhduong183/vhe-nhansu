<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra đăng nhập
if (!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
    header('Location: dang-nhap.php');
    exit;
}

// Xử lý thêm hợp đồng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $ten_hop_dong = $_POST['ten_hop_dong'];
    $thoi_han = $_POST['thoi_han'] ?: NULL;
    $mo_ta = $_POST['mo_ta'];

    $stmt = $conn->prepare("INSERT INTO hop_dong_type (ten_hop_dong, thoi_han, mo_ta) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("sis", $ten_hop_dong, $thoi_han, $mo_ta);
    $stmt->execute();
    echo '<script>setTimeout("window.location=\'hopdong_type.php?p=system&a=hopdong-type&add=success\'",1000);</script>';
    // header("Location: hopdong_type.php");
    // exit;
}

// Xử lý cập nhật hợp đồng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $ten_hop_dong = $_POST['ten_hop_dong'];
    $thoi_han = $_POST['thoi_han'] ?: NULL;
    $mo_ta = $_POST['mo_ta'];

    $stmt = $conn->prepare("UPDATE hop_dong_type SET ten_hop_dong = ?, thoi_han = ?, mo_ta = ? WHERE id = ?");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("sisi", $ten_hop_dong, $thoi_han, $mo_ta, $id);
    $stmt->execute();    
    echo '<script>setTimeout("window.location=\'hopdong_type.php?p=system&a=hopdong-type&update=success\'",1000);</script>';
    // header("Location: hopdong_type.php");
    // exit;
}

// Xử lý xóa hợp đồng
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM hop_dong_type WHERE id = ?");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo '<script>setTimeout("window.location=\'hopdong_type.php?p=system&a=hopdong-type&del=success\'",1000);</script>';
    // header("Location: hopdong_type.php");
    // exit;
}

// Lấy danh sách hợp đồng
$result = $conn->query("SELECT * FROM hop_dong_type ORDER BY id ASC");
if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
$loai_hop_dongs = $result->fetch_all(MYSQLI_ASSOC);

?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý loại hợp đồng</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Loại hợp đồng</li>
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
                    <h3 class="card-title">Thêm loại hợp đồng mới</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên hợp đồng <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_hop_dong" class="form-control" placeholder="Nhập tên hợp đồng" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Thời hạn (tháng)</label>
                                    <input type="number" name="thoi_han" class="form-control" placeholder="VD: 12">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <input type="text" name="mo_ta" class="form-control" placeholder="Mô tả chi tiết">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" name="add" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus"></i> Thêm mới
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách loại hợp đồng</h3>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th>Tên hợp đồng</th>
                                <th width="15%">Thời hạn</th>
                                <th>Mô tả</th>
                                <th width="10%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loai_hop_dongs as $index => $lhd): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($lhd['ten_hop_dong']) ?></td>
                                <td class="text-center"><?= $lhd['thoi_han'] ? $lhd['thoi_han'] . ' tháng' : 'Không giới hạn' ?></td>
                                <td><?= htmlspecialchars($lhd['mo_ta']) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" onclick="editLoaiHopDong(<?= $lhd['id'] ?>, '<?= addslashes($lhd['ten_hop_dong']) ?>', '<?= $lhd['thoi_han'] ?>', '<?= addslashes($lhd['mo_ta']) ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteLoaiHopDong(<?= $lhd['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Cập nhật loại hợp đồng</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="form-group">
                                    <label>Tên hợp đồng <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_hop_dong" id="edit_ten_hop_dong" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Thời hạn (tháng)</label>
                                    <input type="number" name="thoi_han" id="edit_thoi_han" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <input type="text" name="mo_ta" id="edit_mo_ta" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                <button type="submit" name="update" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Cập nhật
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
$message = '';
if (isset($_GET['add'])) $message = 'Thêm loại hợp đồng thành công';
elseif (isset($_GET['update'])) $message = 'Cập nhật loại hợp đồng thành công';
elseif (isset($_GET['del'])) $message = 'Xóa loại hợp đồng thành công';
?>
<script>
function editLoaiHopDong(id, ten_hop_dong, thoi_han, mo_ta) {
    $('#edit_id').val(id);
    $('#edit_ten_hop_dong').val(ten_hop_dong);
    $('#edit_thoi_han').val(thoi_han || '');
    $('#edit_mo_ta').val(mo_ta);
    $('#editModal').modal('show');
}

function deleteLoaiHopDong(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa loại hợp đồng này?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete=${id}&p=system&a=hopdong-type`;
        }
    });
}

$(document).ready(function() {
    $('#dataTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    });

    // Handle success messages
    <?php if (isset($_GET['add']) || isset($_GET['update']) || isset($_GET['del'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Thành công',
        text: '<?php $message   ?>',
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>
});
</script>

<?php 
include(ROOT_PATH . '/layouts/footer.php')?>
