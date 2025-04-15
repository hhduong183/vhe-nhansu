<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

include(ROOT_PATH .'/plugins/function.php');

// Improve session check
if (!isset($_SESSION['username'], $_SESSION['level'], $_SESSION['idNhanVien'])) {
    die("Bạn chưa đăng nhập<--------------.");
}

// Add user permission check
$user_quyen = $_SESSION['level'] ?? 0;
$session_idNhanVien = $_SESSION['idNhanVien'];

// Get and decrypt contract ID
$id = isset($_GET['id']) ? decryptId($_GET['id']) : 0;

// Improve SQL query to include permission check
$sql = "SELECT hd.*, lhd.ten_hop_dong, nv.ten_nv, nv.ma_nv, hd.nhanvien_id
        FROM hop_dong_lao_dong hd 
        JOIN hop_dong_type lhd ON hd.loai_hop_dong_id = lhd.id 
        JOIN nhanvien nv ON hd.nhanvien_id = nv.id
        WHERE hd.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$contract = $result->fetch_assoc();

// Add permission check after fetching data
if (!$contract) {
    die("<div style='text-align:center; color:red;padding:50px;'>❌ Bạn không có hợp đồng nào được ký </div>");
}

// Check if user has permission to view this contract
if ($user_quyen == 0 && $contract['nhanvien_id'] != $session_idNhanVien) {
    die("<div style='text-align:center; color:red;padding:50px;'>❌ Bạn không có quyền xem hợp đồng của người khác.</div>");
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        Chi tiết Hợp Đồng Lao Động
                        <small class="text-muted">Contract Details</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Tổng quan</a></li>
                        <li class="breadcrumb-item"><a href="hopdong_list.php?p=staff&a=contract-list">Hợp đồng</a></li>
                        <li class="breadcrumb-item active">Thêm hợp đồng</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông Tin Hợp Đồng</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mã hợp đồng:</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($contract['ma_hop_dong']) ?></p>
                            </div>
                            <div class="form-group">
                                <label>Nhân viên:</label>
                                <p class="form-control-static"><?= htmlspecialchars($contract['ten_nv']) ?> (<?= htmlspecialchars($contract['ma_nv']) ?>)</p>
                            </div>
                            <div class="form-group">
                                <label>Loại hợp đồng:</label>
                                <p class="form-control-static"><?= htmlspecialchars($contract['ten_hop_dong']) ?></p>
                            </div>
                            <div class="form-group">
                                <label>Ngày bắt đầu:</label>
                                <p class="form-control-static"><?= date('d-m-Y', strtotime($contract['ngay_bat_dau'])) ?></p>
                            </div>
                            <div class="form-group">
                                <label>Ngày kết thúc:</label>
                                <p class="form-control-static">
                                    <?= (!empty($contract['ngay_ket_thuc']) && $contract['ngay_ket_thuc'] != '0000-00-00') 
                                        ? date('d-m-Y', strtotime($contract['ngay_ket_thuc'])) 
                                        : 'Không xác định' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mức lương:</label>
                                <p class="form-control-static"><?= number_format($contract['muc_luong'], 0, ".", ",") ?> VNĐ</p>
                            </div>
                            <div class="form-group">
                                <label>Phụ cấp trách nhiệm:</label>
                                <p class="form-control-static"><?= number_format($contract['phucap_tnh'], 0, ".", ",") ?> VNĐ</p>
                            </div>
                            <div class="form-group">
                                <label>Phụ cấp nghề:</label>
                                <p class="form-control-static"><?= number_format($contract['phucap_nghe'], 0, ".", ",") ?> VNĐ</p>
                            </div>
                            <div class="form-group">
                                <label>Phụ cấp nhà trọ:</label>
                                <p class="form-control-static"><?= number_format($contract['phucap_nha_tro'], 0, ".", ",") ?> VNĐ</p>
                            </div>
                            <div class="form-group">
                                <label>Phụ cấp đặc biệt:</label>
                                <p class="form-control-static"><?= number_format($contract['phucap_dac_biet'], 0, ".", ",") ?> VNĐ</p>
                            </div>
                        </div>
                </div>
                <div class="card-footer">
                    <a href="hopdong_list.php" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include(ROOT_PATH . '/layouts/footer.php'); ?>