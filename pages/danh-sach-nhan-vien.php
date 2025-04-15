<?php
// Bắt đầu session
session_start();
$pageTitle = "Danh sách nhân sự";
// Kết nối cơ sở dữ liệu
include('../config.php');
include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');
include '../plugins/function.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
    header('Location: dang-nhap.php');
    exit();
}

// Xử lý chuyển hướng khi nhấn nút sửa hoặc xem
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit']) || isset($_POST['view'])) {
        $id = $_POST['idStaff'];
        $page = isset($_POST['edit']) ? 'sua-nhan-vien' : 'thong-tin-nhan-vien';
        echo "<script>location.href='$page.php?p=staff&a=list-staff&id=$id'</script>";
    }
}

// Lọc danh sách nhân viên theo phòng ban
$phongbanFilter = "";
$params = [];
$types = "";
if (!empty($_GET['phong_ban'])) {
    $phongbanFilter = " WHERE nhanvien.phong_ban_id = ?";
    $params[] = $_GET['phong_ban'];
    $types .= "i";
}

// Truy vấn danh sách nhân viên
// Modify the filter section
$phongbanFilter = " WHERE nhanvien.trang_thai = 1"; // Default: only active employees
$params = [];
$types = "";

if (!empty($_GET['phong_ban'])) {
    $phongbanFilter .= " AND nhanvien.phong_ban_id = ?";
    $params[] = $_GET['phong_ban'];
    $types .= "i";
}

// Handle tab switching
if (isset($_GET['tab'])) {
    if ($_GET['tab'] == 'inactive') {
        $phongbanFilter = str_replace("nhanvien.trang_thai = 1", "nhanvien.trang_thai = 0", $phongbanFilter);
        if (empty($phongbanFilter)) {
            $phongbanFilter = " WHERE nhanvien.trang_thai = 0";
        }
    }
} elseif (isset($_GET['include_inactive']) && $_GET['include_inactive'] == 1) {
    // Remove trang_thai condition when showing all employees
    $phongbanFilter = str_replace("WHERE nhanvien.trang_thai = 1", "WHERE 1=1", $phongbanFilter);
    if (empty($phongbanFilter)) {
        $phongbanFilter = " WHERE 1=1";
    }
}

$sql = "SELECT nhanvien.id, nhanvien.ma_nv, nhanvien.hinh_anh, nhanvien.ten_nv, nhanvien.gioi_tinh, nhanvien.chuc_vu_id, nhanvien.ngay_vao_lam, nhanvien.to_nhom_id,
              nhanvien.loai_nv_id,nhanvien.ngay_sinh, nhanvien.so_cmnd, nhanvien.trang_thai, phong_ban.ten_phong_ban , chuc_vu.ten_chuc_vu, loai_nv.ten_loai_nv, nhanvien.ma_so_thue, nhanvien.ngay_kt_thuviec
        FROM nhanvien 
        JOIN phong_ban ON nhanvien.phong_ban_id = phong_ban.id
        JOIN chuc_vu ON nhanvien.chuc_vu_id = chuc_vu.id
        JOIN loai_nv ON nhanvien.loai_nv_id = loai_nv.id
        $phongbanFilter ORDER BY nhanvien.ngay_vao_lam DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$arrShow = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Xử lý xóa nhân viên
if (isset($_POST['delete']) && isset($_POST['idStaff'])) {
    $showMess = true;
    $id = decryptId($_POST['idStaff']);
    $id = intval($id);

    if (!$conn) {
        die("Kết nối đến database thất bại!");
    }

    // Lấy thông tin ảnh của nhân viên
    $imageQuery = "SELECT hinh_anh FROM nhanvien WHERE id = ?";
    $stmt = mysqli_prepare($conn, $imageQuery);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $imageResult = mysqli_stmt_get_result($stmt);
    $rowImage = mysqli_fetch_assoc($imageResult);
    mysqli_stmt_close($stmt);

    if (!$rowImage) {
        $error['error'] = "Nhân viên không tồn tại.";
    } else {
        // Xóa nhân viên
        $deleteQuery = "DELETE FROM nhanvien WHERE id = ?";
        $stmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "i", $id);
        $deleteSuccess = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($deleteSuccess) {
            // Xóa ảnh nếu không phải ảnh mặc định
            if (!empty($rowImage['hinh_anh']) && $rowImage['hinh_anh'] !== "demo-3x4.jpg") {
                $imagePath = "../uploads/staffs/" . $rowImage['hinh_anh'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $_SESSION['success'] = "Xóa nhân viên thành công.";
        } else {
            $_SESSION['error'] = "Lỗi khi xóa nhân viên.";
        }
    }

    echo "<script>window.location='danh-sach-nhan-vien.php?p=staff&a=list-staff';</script>";
}
?>

<!-- Modal Xóa -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="idStaff" id="delete-staff-id">
                    <p>Bạn có chắc chắn muốn xóa nhân viên này?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger" name="delete">Xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Danh sách nhân viên</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Danh sách nhân viên</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Thông báo -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?= !isset($_GET['tab']) || $_GET['tab'] == 'active' ? 'active' : '' ?>" 
                               href="?p=staff&a=list-staff&tab=active<?= !empty($_GET['phong_ban']) ? '&phong_ban='.$_GET['phong_ban'] : '' ?>">
                                Đang làm việc
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] == 'inactive' ? 'active' : '' ?>" 
                               href="?p=staff&a=list-staff&tab=inactive<?= !empty($_GET['phong_ban']) ? '&phong_ban='.$_GET['phong_ban'] : '' ?>">
                                Đã nghỉ việc
                            </a>
                        </li>
                    </ul>
                    <div class="row mt-3">
                        <div class="col-md-5">
                            <form method="GET" action="" class="form-inline">
                                <input type="hidden" name="p" value="staff">
                                <input type="hidden" name="a" value="list-staff">
                                <input type="hidden" name="tab" value="<?= $_GET['tab'] ?? 'active' ?>">
                                <div class="input-group w-100">
                                    <select name="phong_ban" class="form-control select2" style="width: 75%;" onchange="location.href='danh-sach-nhan-vien.php?p=staff&a=list-staff&phong_ban=' + this.value">
                                        <option value="">-- Chọn phòng ban --</option>
                                        <?php
                                        $sql_pb = "SELECT * FROM phong_ban";
                                        $result_pb = mysqli_query($conn, $sql_pb);
                                        while ($row_pb = mysqli_fetch_assoc($result_pb)) {
                                            $selected = (!empty($_GET['phong_ban']) && $_GET['phong_ban'] == $row_pb['id']) ? 'selected' : '';
                                            echo "<option value='{$row_pb['id']}' $selected>{$row_pb['ten_phong_ban']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Tìm kiếm
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-7">
                            <?php if ($row_acc['user_quyen'] == 1): ?>
                                <div class="float-right" style="display: inline-flex;">
                                    <form method="post" enctype="multipart/form-data" action="import_nhanvien.php" class="d-inline-block mr-2">
                                        <div class="input-group input-group-sm">
                                            <input type="file" name="file" accept=".xlsx,.xls" class="form-control select2" required>
                                            <div class="input-group-append">
                                                <button type="submit" name="import" class="btn btn-warning">
                                                    <i class="fas fa-file-import"></i> Nhập dữ liệu
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <a href="them-nhan-vien.php?p=staff&a=list-staff&d=new" class="btn btn-primary btn-sm mr-2">
                                        <i class="fas fa-plus"></i> Thêm nhân viên
                                    </a>
                                    <!-- <a href="export-nhan-vien.php?phong_ban=<?= urlencode($_GET['phong_ban'] ?? '') ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-excel"></i> Xuất Excel
                                    </a> -->
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="3%">STT</th>
                                <th width="8%">Số C.ty</th>
                                <th width="15%">Họ và tên</th>
                                <th width="8%">Giới tính</th>
                                <th width="10%">Ngày sinh</th>
                                <th width="12%">Số CMND</th>
                                <th width="12%">Mã số thuế</th>
                                <th width="15%">Phòng ban</th>
                                <th width="10%">Ngày vào làm</th>
                                <th width="10%">Ngày KT.TV</th>
                                <th width="10%">Trạng thái</th>
                                <th width="10%">...</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($arrShow as $index => $staff): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($staff['ma_nv']) ?></td>
                                    <td><?= htmlspecialchars($staff['ten_nv']) ?></td>
                                    <td><?= $staff['gioi_tinh'] == 1 ? 'Nam' : 'Nữ' ?></td>
                                    <td><?= date('d-m-Y', strtotime($staff['ngay_sinh'])) ?></td>
                                    <td><?= htmlspecialchars($staff['so_cmnd']) ?></td>
                                    <td><?= htmlspecialchars($staff['ma_so_thue']) ?></td>
                                    <td><?= htmlspecialchars($staff['ten_phong_ban']) ?></td>
                                    <td><?= date('d-m-Y', strtotime($staff['ngay_vao_lam'])) ?></td>
                                    <td><?= !empty($staff['ngay_kt_thuviec']) && ($staff['ngay_kt_thuviec'] !== '0000-00-00') ? date('d-m-Y', strtotime($staff['ngay_kt_thuviec'])) : " " ?></td>
                                    <td><span class="badge <?php echo $staff['trang_thai'] == 1 ? 'badge-success' : 'badge-danger'; ?>"><?= $staff['trang_thai']==1 ? 'Active' : 'Inactive'  ?></span></td>
                                    <td>
                                        <div class="btn-group">
                                            <form method="POST" class="mr-1">
                                                <input type="hidden" name="idStaff" value="<?= encryptId($staff['id']) ?>">
                                                <button type="submit" class="btn btn-info btn-sm" name="view" title="Xem">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </form>
                                            <?php if ($row_acc['user_quyen'] == 1): ?>
                                                <form method="POST" class="mr-1">
                                                    <input type="hidden" name="idStaff" value="<?= encryptId($staff['id']) ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm" name="edit" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm delete-btn" 
                                                        data-toggle="modal" 
                                                        data-target="#deleteModal" 
                                                        data-id="<?= encryptId($staff['id']) ?>" 
                                                        title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<?php include('../layouts/footer.php'); ?>

<script>
$(document).ready(function() {
 
    // Khởi tạo Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Xử lý sự kiện click nút xóa
    $('.delete-btn').click(function() {
        var staffId = $(this).data('id');
        $('#delete-staff-id').val(staffId);
    });

    // Tự động ẩn thông báo sau 5 giây
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
});
</script>