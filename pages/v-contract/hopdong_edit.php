<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Include layout files after processing form
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');


// Lấy ID hợp đồng từ URL
$id_url = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Add this after the existing contract query
// Fetch contract types
$sql_contract_types = "SELECT id, ten_hop_dong FROM hop_dong_type";
$result_types = $conn->query($sql_contract_types);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $ma_hop_dong = $_POST['ma_hop_dong'];
    $nhanvien_id = $_POST['nhanvien_id'];
    $ngay_bat_dau = $_POST['ngay_bat_dau'];
    $ngay_ket_thuc = $_POST['ngay_ket_thuc'];
    $loai_hop_dong_id = $_POST['loai_hop_dong_id'];
    $muc_luong = $_POST['muc_luong'];
    $phucap_tnh = $_POST['phucap_tnh'];
    $phucap_nghe = $_POST['phucap_nghe'];
    $phucap_nha_tro = $_POST['phucap_nha_tro'];
    $phucap_dac_biet = $_POST['phucap_dac_biet'];
    $trang_thai = $_POST['trang_thai'];
    $ngay_cap_nhat = date('Y-m-d H:i:s');
    
    // Cập nhật dữ liệu
    $sql = "UPDATE hop_dong_lao_dong 
            SET ma_hop_dong=?, nhanvien_id=?, ngay_bat_dau=?, ngay_ket_thuc=?, loai_hop_dong_id=?, muc_luong=?, 
                phucap_tnh=?, phucap_nghe=?, phucap_nha_tro=?, phucap_dac_biet=?, trang_thai=?, ngay_cap_nhat=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssdddddssi", $ma_hop_dong, $nhanvien_id, $ngay_bat_dau, $ngay_ket_thuc, 
                      $loai_hop_dong_id, $muc_luong, $phucap_tnh, $phucap_nghe, $phucap_nha_tro, 
                      $phucap_dac_biet, $trang_thai, $ngay_cap_nhat, $id_url);
    
    // Process form submission before including any layout files
    if ($stmt->execute()) {
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Hợp đồng đã được cập nhật thành công!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'hopdong_list.php?p=staff&a=contract-list';
                });
            };
        </script>";
    } else {
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Lỗi: " . $conn->error . "'
                });
            }
        </script>";
    }
}

// Lấy dữ liệu hợp đồng
$sql = "SELECT hd.id, hd.ma_hop_dong, hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.phucap_tnh, hd.phucap_nha_tro, 
               hd.muc_luong, hd.phucap_nghe, hd.phucap_dac_biet, lhd.ten_hop_dong, hd.loai_hop_dong_id,
               nv.ten_nv, nv.id AS id_nv, hd.trang_thai, nv.ma_nv, hd.muc_bhxh
        FROM hop_dong_lao_dong hd 
        JOIN hop_dong_type lhd ON hd.loai_hop_dong_id = lhd.id 
        JOIN nhanvien nv ON hd.nhanvien_id = nv.id
        WHERE hd.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_url);
$stmt->execute();
$result = $stmt->get_result();
$contract = $result->fetch_assoc();

if (!$contract) {
    die("Hợp đồng không tồn tại.");
}
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Chỉnh sửa hợp đồng lao động</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Home</a></li>
                        <li class="breadcrumb-item"><a href="hopdong_list.php?p=staff&a=contract-list">Hợp đồng</a></li>
                        <li class="breadcrumb-item active">Chỉnh sửa</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông Tin Hợp Đồng</h3>
                </div>
                <form method="POST" class="form-horizontal">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 col-form-label">Mã hợp đồng:</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="ma_hop_dong" class="form-control" value="<?= htmlspecialchars($contract['ma_hop_dong']) ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Ngày bắt đầu:</label>
                                    <div class="col-sm-8">
                                        <input type="date" name="ngay_bat_dau" class="form-control" value="<?= $contract['ngay_bat_dau'] ?>">
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                    <label class="col-sm-4 control-label">Loại hợp đồng:</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="loai_hop_dong_id" class="form-control" value="<?= $contract['ten_hop_dong'] ?>">
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Loại hợp đồng:</label>
                                    <div class="col-sm-8">
                                        <select name="loai_hop_dong_id" class="form-control">
                                            <?php while($type = $result_types->fetch_assoc()): ?>
                                                <option value="<?= $type['id'] ?>" <?= ($contract['loai_hop_dong_id'] == $type['id']) ? 'selected' : '' ?>>
                                                    <?= $type['ten_hop_dong'] ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Phụ cấp TNH:</label>
                                    <div class="col-sm-8">
                                        <input type="number" name="phucap_tnh" step="0.01" class="form-control" value="<?= $contract['phucap_tnh'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Phụ cấp nhà trọ:</label>
                                    <div class="col-sm-8">
                                        <input type="number" name="phucap_nha_tro" step="0.01" class="form-control" value="<?= $contract['phucap_nha_tro'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Trạng thái:</label>
                                    <div class="col-sm-8">
                                        <select name="trang_thai" class="form-control">
                                            <option value="chon">-- Chọn trạng thái --</option>
                                            <option value="Hiệu lực" <?= $contract['trang_thai'] == 'Hiệu lực' ? 'selected' : '' ?>>Hiệu lực</option>
                                            <option value="Hết hạn" <?= $contract['trang_thai'] == 'Hết hạn' ? 'selected' : '' ?>>Hết hạn</option>
                                            <option value="Chấm dứt" <?= $contract['trang_thai'] == 'Chấm dứt' ? 'selected' : '' ?>>Đã nghỉ việc</option>
                                        </select>
                                        <!-- <input type="text" name="trang_thai" class="form-control" value="<?= $contract['trang_thai'] ?>"> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Nhân viên:</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?= $contract['ten_nv'] ?>" disabled>
                                        <input type="hidden" name="nhanvien_id" value="<?= $contract['id_nv'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Ngày kết thúc:</label>
                                    <div class="col-sm-8">
                                        <input type="date" name="ngay_ket_thuc" class="form-control" value="<?= $contract['ngay_ket_thuc'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Mức lương:</label>
                                    <div class="col-sm-8">
                                        <input type="number" name="muc_luong" step="0.01" class="form-control" value="<?= $contract['muc_luong'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Phụ cấp nghề:</label>
                                    <div class="col-sm-8">
                                        <input type="number" name="phucap_nghe" step="0.01" class="form-control" value="<?= $contract['phucap_nghe'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Phụ cấp đặc biệt:</label>
                                    <div class="col-sm-8">
                                        <input type="number" name="phucap_dac_biet" step="0.01" class="form-control" value="<?= $contract['phucap_dac_biet'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Mức đóng BHXH:</label>
                                    <div class="col-sm-8">
                                        <input type="number" name="muc_bhxh" step="0.01" class="form-control" value="<?= $contract['muc_bhxh'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu
                        </button>
                        <a href="hopdong_list.php?p=staff&a=contract-list" class="btn btn-default">
                            <i class="fas fa-reply"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<!-- Add SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
<?php if (isset($_SESSION['success_message'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Thành công!',
        text: '<?= $_SESSION['success_message'] ?>',
        showConfirmButton: false,
        timer: 1500
    });
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Lỗi!',
        text: '<?= $_SESSION['error_message'] ?>',
    });
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
</script>

<?php include(ROOT_PATH . '/layouts/footer.php'); ?>