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

    
        // Truy vấn lấy tất cả thông tin của phiếu lương đang cần xem
        $query = "SELECT * 
        FROM luong_chamcong lc
        JOIN nhanvien nv ON lc.nhanvien_id = nv.id
        WHERE lc.id = ?";
        
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_url);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        Phiếu Lương
                        <small class="text-muted">Chi tiết phiếu lương nhân viên</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="bangluong_list.php?p=staff&a=salary-list">Danh sách Lương</a></li>
                        <li class="breadcrumb-item active">Phiếu Lương</li>
                    </ol>

                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
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
                        <div class="card-body p-0">
                            <table class="table">
                                <tbody>
                                    <tr><th>Số ngày làm việc</th><td><?= $row['ngaycong'] ?></td></tr>
                                    <tr><th>Số giờ ra ngoài</th><td><?= $row['hr_rangoai'] ?></td></tr>
                                    <tr><th>Số giờ OVT</th><td><?= $row['hr_ovt'] ?></td></tr>
                                    <tr><th>Số giờ OVT CN</th><td><?= $row['hr_ovtCN'] ?></td></tr>
                                    <tr><th>Số giờ OVT Lễ</th><td><?= $row['hr_ovtLe'] ?></td></tr>
                                    <tr><th>Hệ số sản lượng (HS1)</th><td><?= $row['hs_sanluong'] ?></td></tr>
                                    <tr><th>Hệ số APQ (HS2)</th><td><?= $row['hs_apq'] ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Lương và Phụ cấp -->
                <div class="col-md-3">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Lương và Phụ cấp</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table">
                                <tbody>
                                    <tr><td>Mức lương chính</td><td class="text-right"><?= number_format($row['ml_chinh']) ?></td></tr>
                                    <tr><td>PC. Trách nhiệm</td><td class="text-right"><?= number_format($row['pc_trachnhiem']) ?></td></tr>
                                    <tr><td>PC. Nghề</td><td class="text-right"><?= number_format($row['pc_nghe']) ?></td></tr>
                                    <tr><td>PC. Nhà trọ</td><td class="text-right"><?= number_format($row['pc_nhatro']) ?></td></tr>
                                    <tr><td>PC. Công trường</td><td class="text-right"><?= number_format($row['pc_congtruong']) ?></td></tr>
                                    <tr><td>OVT Chịu thuế</td><td class="text-right"><?= number_format($row['ovt_thue']) ?></td></tr>
                                    <tr><td>OVT Không thuế</td><td class="text-right"><?= number_format($row['ovt_kothue']) ?></td></tr>
                                    <tr><th>Các khoản thu nhập khác</th><th class="text-right"><?= number_format($row['ml_khac']) ?></th></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tổng hợp lương -->
                <div class="col-md-3">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Tổng hợp lương</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table">
                                <?php
                                // Existing calculation code remains the same
                                $A = 0.7 * ($row['ml_chinh'] + $row['pc_trachnhiem']) + $row['pc_congtruong']+$row['pc_nghe']+$row['pc_nhatro'];
                                if ($row['he_luong']=="mhr") {
                                    $B = 0.3 * ($row['ml_chinh'] + $row['pc_trachnhiem']);
                                }
                                else {
                                    $B = 0.3 * ($row['ml_chinh'] + $row['pc_trachnhiem']) * ($row['hs_sanluong'] + $row['hs_apq']) / 2;
                                }
                                $C = ($row['ovt_thue'] + $row['ovt_kothue']) * $row['hs_sanluong'];
                                $thuNhapChinh = $A + $B + $C;
                                ?>
                                <tbody>
                                    <tr><td>A = 70%(ML+PCTN) + PC khác</td><td class="text-right"><?= number_format($A) ?></td></tr>
                                    <tr><td>B = 30%(ML+PCTN)*(HS1+HS2)/2</td><td class="text-right"><?= number_format($B) ?></td></tr>
                                    <tr><td>C = Tổng lương OVT *HS1</td><td class="text-right"><?= number_format($C) ?></td></tr>
                                    <tr class="bg-primary"><th>Thu nhập chính (A+B+C)</th><td class="text-right"><?= number_format($thuNhapChinh) ?></td></tr>
                                    <tr class="text-danger"><td>Đoàn phí công đoàn</td><td class="text-right"><?= number_format($row['kt_congdoan']) ?></td></tr>
                                    <tr class="text-danger"><td>Bảo hiểm Xã hội (10.5%)</td><td class="text-right"><?= number_format($row['kt_bhxh']) ?></td></tr>
                                    <tr class="text-danger"><td>Thuế TNCN phải nộp</td><td class="text-right"><?= number_format($row['kt_tncn']) ?></td></tr>
                                    <tr class="text-danger"><th>Các khoản khấu trừ khác</th><th class="text-right"><?= number_format($row['kt_khac']) ?></th></tr>
                                    <tr class="bg-success">
                                        <th><h4>Tổng lương thực lĩnh</h4></th>
                                        <th class="text-right"><h4><?= number_format($thuNhapChinh+$row['ml_khac']-$row['kt_bhxh']-$row['kt_tncn']-$row['kt_congdoan']-$row['kt_khac']) ?></h4></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
}
    else
    {
    // go to pages login
    echo '<div style="text-align:center;padding:15px;">>>>>>>> Bạn không có quyền xem thông tin lương của người khác.<<<<<<< </div>';
    }

// include
  include(ROOT_PATH . '/layouts/footer.php');
?>