<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

include(ROOT_PATH .'/plugins/function.php');

if (isset($_SESSION['username']) && isset($_SESSION['level'])) {
    $id_url = $_GET['id'];
    
    // Truy vấn lấy thông tin phiếu lương
    $query = "SELECT * 
    FROM luong_chamcong lc
    JOIN nhanvien nv ON lc.nhanvien_id = nv.id
    WHERE lc.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_url);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Update query will go here
        $update_query = "UPDATE luong_chamcong SET 
            ngaycong = ?, hr_rangoai = ?, hr_ovt = ?, hr_ovtCN = ?, hr_ovtLe = ?,
            hs_sanluong = ?, hs_apq = ?, ml_chinh = ?, pc_trachnhiem = ?,
            pc_nghe = ?, pc_nhatro = ?, pc_congtruong = ?, ovt_thue = ?,
            ovt_kothue = ?, ml_khac = ?, kt_congdoan = ?, kt_bhxh = ?,
            kt_tncn = ?, kt_khac = ?
            WHERE id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param(
            "dddddddddddddddddddi",
            $_POST['ngaycong'], $_POST['hr_rangoai'], $_POST['hr_ovt'],
            $_POST['hr_ovtCN'], $_POST['hr_ovtLe'], $_POST['hs_sanluong'],
            $_POST['hs_apq'], $_POST['ml_chinh'], $_POST['pc_trachnhiem'],
            $_POST['pc_nghe'], $_POST['pc_nhatro'], $_POST['pc_congtruong'],
            $_POST['ovt_thue'], $_POST['ovt_kothue'], $_POST['ml_khac'],
            $_POST['kt_congdoan'], $_POST['kt_bhxh'], $_POST['kt_tncn'],
            $_POST['kt_khac'], $id_url
        );
        
        if ($stmt->execute()) {
            echo "<script>
                alert('Cập nhật thành công!');
                window.location.href = 'bangluong_xem.php?id=" . $id_url . "';
            </script>";
        } else {
            echo "<script>alert('Lỗi cập nhật!');</script>";
        }
    }
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        Sửa Phiếu Lương
                        <small class="text-muted">Chỉnh sửa phiếu lương nhân viên</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#"><i class="fas fa-dashboard"></i> Home</a></li>
                        <li class="breadcrumb-item active">Sửa Phiếu Lương</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="">
                <div class="row">
                    <!-- Thông tin nhân viên -->
                    <div class="col-md-3">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin nhân viên</h3>
                            </div>
                            <div class="card-body">
                                <strong><i class="fas fa-user mr-1"></i> Họ và tên</strong>
                                <p class="text-muted"><?= $row['ten_nv']?></p>
                                <strong><i class="fas fa-id-card mr-1"></i> Số C.ty</strong>
                                <p class="text-muted"><?= $row['ma_nv']?></p>
                                <strong><i class="fas fa-calendar mr-1"></i> Lương tháng</strong>
                                <p class="text-muted"><?= $row['sal_month']."-".$row['sal_year']?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chấm công -->
                    <div class="col-md-3">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin chấm công</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Số ngày làm việc</label>
                                    <input type="number" step="0.5" class="form-control" name="ngaycong" value="<?= $row['ngaycong'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Số giờ ra ngoài</label>
                                    <input type="number" step="0.5" class="form-control" name="hr_rangoai" value="<?= $row['hr_rangoai'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Số giờ OVT</label>
                                    <input type="number" step="0.5" class="form-control" name="hr_ovt" value="<?= $row['hr_ovt'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Số giờ OVT CN</label>
                                    <input type="number" step="0.5" class="form-control" name="hr_ovtCN" value="<?= $row['hr_ovtCN'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Số giờ OVT Lễ</label>
                                    <input type="number" step="0.5" class="form-control" name="hr_ovtLe" value="<?= $row['hr_ovtLe'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Hệ số sản lượng (HS1)</label>
                                    <input type="number" step="0.01" class="form-control" name="hs_sanluong" value="<?= $row['hs_sanluong'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Hệ số APQ (HS2)</label>
                                    <input type="number" step="0.01" class="form-control" name="hs_apq" value="<?= $row['hs_apq'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lương và Phụ cấp -->
                    <div class="col-md-3">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Lương và Phụ cấp</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Mức lương chính</label>
                                    <input type="number" class="form-control" name="ml_chinh" value="<?= $row['ml_chinh'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>PC. Trách nhiệm</label>
                                    <input type="number" class="form-control" name="pc_trachnhiem" value="<?= $row['pc_trachnhiem'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>PC. Nghề</label>
                                    <input type="number" class="form-control" name="pc_nghe" value="<?= $row['pc_nghe'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>PC. Nhà trọ</label>
                                    <input type="number" class="form-control" name="pc_nhatro" value="<?= $row['pc_nhatro'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>PC. Công trường</label>
                                    <input type="number" class="form-control" name="pc_congtruong" value="<?= $row['pc_congtruong'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>OVT Chịu thuế</label>
                                    <input type="number" class="form-control" name="ovt_thue" value="<?= $row['ovt_thue'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>OVT Không thuế</label>
                                    <input type="number" class="form-control" name="ovt_kothue" value="<?= $row['ovt_kothue'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Các khoản thu nhập khác</label>
                                    <input type="number" class="form-control" name="ml_khac" value="<?= $row['ml_khac'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Các khoản khấu trừ -->
                    <div class="col-md-3">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Các khoản khấu trừ</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Đoàn phí công đoàn</label>
                                    <input type="number" class="form-control" name="kt_congdoan" value="<?= $row['kt_congdoan'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Bảo hiểm Xã hội (10.5%)</label>
                                    <input type="number" class="form-control" name="kt_bhxh" value="<?= $row['kt_bhxh'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Thuế TNCN phải nộp</label>
                                    <input type="number" class="form-control" name="kt_tncn" value="<?= $row['kt_tncn'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Các khoản khấu trừ khác</label>
                                    <input type="number" class="form-control" name="kt_khac" value="<?= $row['kt_khac'] ?>">
                                </div>
                                <div class="form-group text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-lg">Cập nhật</button>
                                    <a href="bangluong_xem.php?id=<?= $id_url ?>" class="btn btn-default btn-lg">Quay lại</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php
}
else {
    echo '<div style="text-align:center;padding:15px;">>>>>>>> Bạn không có quyền sửa thông tin lương của người khác.<<<<<<< </div>';
}

include(ROOT_PATH . '/layouts/footer.php');
?>