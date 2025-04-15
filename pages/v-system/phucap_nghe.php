<?php
// Kết nối CSDL
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');


// Kiểm tra đăng nhập
if (!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
    header('Location: ' . BASE_URL . '/pages/dang-nhap.php');
    exit;
}

// Xử lý thêm phụ cấp nghề nghiệp
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $ten_phu_cap = $_POST['ten_phu_cap'];
    $so_tien = $_POST['so_tien'] ?: NULL;
    $mo_ta = $_POST['mo_ta'];

    $stmt = $conn->prepare("INSERT INTO luong_pcnghe (ten_phu_cap, so_tien, mo_ta) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("sds", $ten_phu_cap, $so_tien, $mo_ta);
    $stmt->execute();
    header("Location: phucap_nghe.php?p=system&a=phucapnghe&add=success");
    exit;
}

// Xử lý cập nhật phụ cấp nghề nghiệp
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $ten_phu_cap = $_POST['ten_phu_cap'];
    $so_tien = $_POST['so_tien'] ?: NULL;
    $mo_ta = $_POST['mo_ta'];

    $stmt = $conn->prepare("UPDATE luong_pcnghe SET ten_phu_cap = ?, so_tien = ?, mo_ta = ? WHERE id = ?");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("sdsi", $ten_phu_cap, $so_tien, $mo_ta, $id);
    $stmt->execute();
    header("Location: phucap_nghe.php?p=system&a=phucapnghe&update=success");
    exit;
}

// Xử lý xóa phụ cấp nghề nghiệp
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM luong_pcnghe WHERE id = ?");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: phucap_nghe.php?p=system&a=phucapnghe&del=success");
    exit;
}

// Lấy danh sách phụ cấp nghề nghiệp
$result = $conn->query("SELECT * FROM luong_pcnghe ORDER BY id DESC");
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
                    <h1 class="m-0">Quản lý phụ cấp nghề nghiệp</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Phụ cấp nghề nghiệp</li>
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
                    <h3 class="card-title">Thêm phụ cấp mới</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tên phụ cấp <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_phu_cap" class="form-control" placeholder="Nhập tên phụ cấp" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mức phụ cấp</label>
                                    <input type="number" name="so_tien" class="form-control" placeholder="Nhập mức phụ cấp">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <input type="text" name="mo_ta" class="form-control" placeholder="Nhập mô tả">
                                </div>
                            </div>
                            <div class="col-md-1">
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
                                <th>Tên phụ cấp</th>
                                <th width="20%">Mức phụ cấp</th>
                                <th>Mô tả</th>
                                <th width="10%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($phu_caps as $index => $pc): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($pc['ten_phu_cap']) ?></td>
                                <td class="text-right"><?= number_format($pc['so_tien']) ?> VND</td>
                                <td><?= htmlspecialchars($pc['mo_ta']) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" onclick="editPhuCap(<?= $pc['id'] ?>, '<?= addslashes($pc['ten_phu_cap']) ?>', '<?= $pc['so_tien'] ?>', '<?= addslashes($pc['mo_ta']) ?>')">
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
                                <h5 class="modal-title">Cập nhật phụ cấp</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="form-group">
                                    <label>Tên phụ cấp <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_phu_cap" id="edit_ten_phu_cap" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Mức phụ cấp</label>
                                    <input type="number" name="so_tien" id="edit_so_tien" class="form-control">
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
if (isset($_GET['add'])) $message = 'Thêm phụ cấp thành công';
elseif (isset($_GET['update'])) $message = 'Cập nhật phụ cấp thành công';
elseif (isset($_GET['del'])) $message = 'Xóa phụ cấp thành công';
?>

<script>
function editPhuCap(id, ten_phu_cap, so_tien, mo_ta) {
    $('#edit_id').val(id);
    $('#edit_ten_phu_cap').val(ten_phu_cap);
    $('#edit_so_tien').val(so_tien || '');
    $('#edit_mo_ta').val(mo_ta);
    $('#editModal').modal('show');
}

function deletePhuCap(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa phụ cấp này?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete=${id}&p=system&a=phucapnghe`;
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
