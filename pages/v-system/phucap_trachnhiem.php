<?php
// Kết nối CSDL
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');


// Kiểm tra đăng nhập
if (!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
    header('Location: ' . BASE_URL . '/pages/dang-nhap.php');
    exit;
}

// Xử lý thêm nhóm phụ cấp
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    // $nhom = $_POST['nhom'];
    $ten_nhom = $_POST['ten_nhom'];
    $so_tien = $_POST['so_tien'] ?: NULL; // Nếu rỗng thì NULL

    $stmt = $conn->prepare("INSERT INTO luong_pctnh (nhom, ten_nhom, so_tien) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("ssi", $nhom, $ten_nhom, $so_tien);
    $stmt->execute();
    header('Location: phucap_trachnhiem.php?p=system&a=phucaptn&add=success');
    exit;
}

// Xử lý cập nhật nhóm phụ cấp
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    // $nhom = $_POST['nhom'];
    $ten_nhom = $_POST['ten_nhom'];
    $so_tien = $_POST['so_tien'] ?: NULL;

    $stmt = $conn->prepare("UPDATE luong_pctnh SET nhom = ?, ten_nhom = ?, so_tien = ? WHERE id = ?");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("ssii", $nhom, $ten_nhom, $so_tien, $id);
    $stmt->execute();
    header('Location: phucap_trachnhiem.php?p=system&a=phucaptn&update=success');
    exit;
}

// Xử lý xóa nhóm phụ cấp
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM luong_pctnh WHERE id = ?");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: phucap_trachnhiem.php?p=system&a=phucaptn&del=success');
    exit;
}

// Lấy danh sách nhóm phụ cấp
$result = $conn->query("SELECT * FROM luong_pctnh ORDER BY nhom");
if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
$phu_caps = $result->fetch_all(MYSQLI_ASSOC);

// Giao diện trang quản lý
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý phụ cấp trách nhiệm</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Phụ cấp trách nhiệm</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Add Form Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thêm nhóm phụ cấp mới</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên nhóm phụ cấp <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_nhom" class="form-control" placeholder="Nhập tên nhóm" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mức phụ cấp</label>
                                    <input type="number" name="so_tien" class="form-control" placeholder="Nhập mức phụ cấp">
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
                    <h3 class="card-title">Danh sách phụ cấp</h3>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">STT</th>
                                <th>Tên nhóm</th>
                                <th width="20%">Mức phụ cấp</th>
                                <th width="10%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($phu_caps as $index => $pc): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($pc['ten_nhom']) ?></td>
                                <td class="text-right"><?= number_format($pc['so_tien']) ?> VND</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" onclick="editPhuCap(<?= $pc['id'] ?>, '<?= addslashes($pc['ten_nhom']) ?>', '<?= $pc['so_tien'] ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deletePhuCap(<?= $pc['id'] ?>)">
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
                                <h5 class="modal-title">Cập nhật nhóm phụ cấp</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="form-group">
                                    <label>Tên nhóm phụ cấp <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_nhom" id="edit_ten_nhom" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Mức phụ cấp</label>
                                    <input type="number" name="so_tien" id="edit_so_tien" class="form-control">
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
if (isset($_GET['add'])) $message = 'Thêm nhóm phụ cấp thành công';
elseif (isset($_GET['update'])) $message = 'Cập nhật nhóm phụ cấp thành công';
elseif (isset($_GET['del'])) $message = 'Xóa nhóm phụ cấp thành công';
?>

<script>
function editPhuCap(id, ten_nhom, so_tien) {
    $('#edit_id').val(id);
    $('#edit_ten_nhom').val(ten_nhom);
    $('#edit_so_tien').val(so_tien || '');
    $('#editModal').modal('show');
}

function deletePhuCap(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa nhóm phụ cấp này?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete=${id}&p=system&a=phucaptn`;
        }
    });
}

$(document).ready(function() {
    <?php if (isset($_GET['add']) || isset($_GET['update']) || isset($_GET['del'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Thành công',
        text: '<?= $message ?>',
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>
});
</script>

<?php include(ROOT_PATH . '/layouts/footer.php'); ?>
