<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

include(ROOT_PATH .'/plugins/function.php');

if (isset($_SESSION['username']) && isset($_SESSION['level']) && isset($_GET['id'])) {
    $id_url = decryptId($_GET['id']);

    if ($id_url == $_SESSION['idNhanVien'] || $_SESSION['level'] == 1) {
        $months = [
            'all' => 'Tất cả các tháng',
            '01' => 'Tháng 1', '02' => 'Tháng 2', '03' => 'Tháng 3', '04' => 'Tháng 4',
            '05' => 'Tháng 5', '06' => 'Tháng 6', '07' => 'Tháng 7', '08' => 'Tháng 8',
            '09' => 'Tháng 9', '10' => 'Tháng 10', '11' => 'Tháng 11', '12' => 'Tháng 12'
        ];
    
        // Truy vấn để lấy tháng gần nhất có phiếu lương của nhân viên
        $queryLatestSalary = "SELECT sal_month, sal_year FROM luong_chamcong 
                              WHERE nhanvien_id = ? 
                              ORDER BY sal_year DESC, sal_month DESC 
                              LIMIT 1";
        $stmt = $conn->prepare($queryLatestSalary);
        $stmt->bind_param("i", $id_url);
        $stmt->execute();
        $result = $stmt->get_result();
        $latestSalary = $result->fetch_assoc();
    
        // Lấy tháng và năm gần nhất có phiếu lương
        $latestMonth = $latestSalary['sal_month'] ?? date('m', strtotime('-1 month'));
        $latestYear = $latestSalary['sal_year'] ?? date('Y', strtotime('-1 month'));
    
        // Nếu có giá trị từ GET thì dùng, nếu không thì dùng tháng gần nhất có lương
        $selectedMonth = $_GET['month'] ?? $latestMonth;
        $selectedYear = $_GET['year'] ?? $latestYear;
    
        $selectedSalaryId = $_GET['salary_id'] ?? null;
    
        // Truy vấn lấy tất cả phiếu lương của nhân viên trong tháng được chọn
        $query = "SELECT * FROM luong_chamcong 
                  WHERE nhanvien_id = ? 
                  AND sal_month = ?
                  AND sal_year = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $id_url, $selectedMonth, $selectedYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $salaryList = $result->fetch_all(MYSQLI_ASSOC);
    
        // Nếu chưa chọn phiếu lương, chọn phiếu đầu tiên mặc định
        if (!$selectedSalaryId && count($salaryList) > 0) {
            $selectedSalaryId = $salaryList[0]['id'];
        }
    

        // Lấy chi tiết phiếu lương đang chọn
        $selectedSalary = null;
        foreach ($salaryList as $salary) {
            if ($salary['id'] == $selectedSalaryId) {
                $selectedSalary = $salary;
                break;
            }
        }
        
        $query = "SELECT sal_month,
                SUM(0.7 * (ml_chinh + pc_trachnhiem) + pc_congtruong + pc_nghe + pc_nhatro) AS AA,
                SUM(
                    CASE 
                        WHEN he_luong = 'mhr' THEN 0.3 * (ml_chinh + pc_trachnhiem)
                        ELSE 0.3 * (ml_chinh + pc_trachnhiem) * (hs_sanluong + hs_apq) / 2
                    END
                ) AS BB,
                SUM((ovt_thue + ovt_kothue) * hs_sanluong) AS CC,
                SUM(kt_congdoan) AS kt_congdoan,
                SUM(kt_bhxh) AS kt_bhxh,
                SUM(kt_tncn) AS kt_tncn,
                SUM(kt_khac) AS kt_khac,
                SUM(ml_khac) AS ml_khac
          FROM luong_chamcong 
          WHERE nhanvien_id = ?
          AND sal_year = ?
          GROUP BY sal_month 
          ORDER BY sal_month ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $id_url,$selectedYear);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $salaryData = [];
        while ($row = $result->fetch_assoc()) {
            $thuNhap_month = $row['AA'] + $row['BB'] + $row['CC'];
            $tongLuongThucLinh = $thuNhap_month + $row['ml_khac'] - $row['kt_bhxh'] - $row['kt_tncn'] - $row['kt_congdoan'] - $row['kt_khac'];
            
            $salaryData[$row['sal_month']] = [
                'AA' => $row['AA'],
                'BB' => $row['BB'],
                'CC' => $row['CC'],
                'thuNhap_month' => $thuNhap_month,
                'kt_congdoan' => $row['kt_congdoan'],
                'kt_bhxh' => $row['kt_bhxh'],
                'kt_tncn' => $row['kt_tncn'],
                'kt_khac' => $row['kt_khac'],
                'tongLuongThucLinh' => $tongLuongThucLinh
            ];
        }

?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">
                        BẢNG LƯƠNG <?= mb_strtoupper($months[$selectedMonth])?> NĂM <?= $selectedYear ?>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Month Selection Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <input type="hidden" name="p" value="salary">
                                <input type="hidden" name="a" value="view">
                                <input type="hidden" name="id" value="<?= htmlspecialchars(encryptId($id_url)) ?>">
                                
                                <div class="form-group">
                                    <label for="month" class="mr-2">Chọn tháng:</label>
                                    <select name="month" id="month" class="form-control" onchange="this.form.submit()">
                                        <?php foreach ($months as $key => $month) : ?>
                                            <option value="<?= $key ?>" <?= ($key == $selectedMonth) ? 'selected' : '' ?>><?= $month ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($selectedMonth == 'all') : ?>
            <!-- All Months View -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Tháng</th>
                                        <th>Lương thực lĩnh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salaryData as $key => $data) : ?>
                                    <?php if ($key !== 'all') : ?>
                                        <tr>
                                            <td><?= $months[$key] ?></td>
                                            <td><?= number_format($data['tongLuongThucLinh']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php else : ?>
            <!-- Single Month View -->
            <div class="row">
                <!-- Salary List -->
                <div class="col-md-2">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Phiếu lương</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($salaryList as $salary) : ?>
                                <a href="?p=salary&a=view&id=<?= encryptId($id_url) ?>&month=<?= $selectedMonth ?>&salary_id=<?= $salary['id'] ?>" 
                                   class="btn btn-block <?= ($salary['id'] == $selectedSalaryId) ? 'btn-primary' : 'btn-default' ?> mb-2">
                                    <?= htmlspecialchars($salary['ma_luong']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php if ($selectedSalaryId) : ?>
                <!-- Attendance Info -->
                <div class="col-md-3">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin chấm công</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table">
                                <tbody>
                                    <tr><th>Số ngày làm việc</th><td><?= $selectedSalary['ngaycong'] ?></td></tr>
                                    <tr><th>Số giờ ra ngoài</th><td><?= $selectedSalary['hr_rangoai'] ?></td></tr>
                                    <tr><th>Số giờ OVT</th><td><?= $selectedSalary['hr_ovt'] ?></td></tr>
                                    <tr><th>Số giờ OVT CN</th><td><?= $selectedSalary['hr_ovtCN'] ?></td></tr>
                                    <tr><th>Số giờ OVT Lễ</th><td><?= $selectedSalary['hr_ovtLe'] ?></td></tr>
                                    <tr><th>Hệ số sản lượng (HS1)</th><td><?= $selectedSalary['hs_sanluong'] ?></td></tr>
                                    <tr><th>Hệ số APQ (HS2)</th><td><?= $selectedSalary['hs_apq'] ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Salary and Allowances -->
                <div class="col-md-3">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Lương và Phụ cấp</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table">
                                <tbody>
                                    <tr><td>Mức lương chính</td><td class="text-right"><?= number_format($selectedSalary['ml_chinh']) ?></td></tr>
                                    <tr><td>PC. Trách nhiệm</td><td class="text-right"><?= number_format($selectedSalary['pc_trachnhiem']) ?></td></tr>
                                    <tr><td>PC. Nghề</td><td class="text-right"><?= number_format($selectedSalary['pc_nghe']) ?></td></tr>
                                    <tr><td>PC. Nhà trọ</td><td class="text-right"><?= number_format($selectedSalary['pc_nhatro']) ?></td></tr>
                                    <tr><td>PC. Công trường</td><td class="text-right"><?= number_format($selectedSalary['pc_congtruong']) ?></td></tr>
                                    <tr><td>OVT Chịu thuế</td><td class="text-right"><?= number_format($selectedSalary['ovt_thue']) ?></td></tr>
                                    <tr><td>OVT Không thuế</td><td class="text-right"><?= number_format($selectedSalary['ovt_kothue']) ?></td></tr>
                                    <tr><th>Các khoản thu nhập khác</th><th class="text-right"><?= number_format($selectedSalary['ml_khac']) ?></th></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Salary Summary -->
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Tổng hợp lương</h3>
                        </div>
                        <div class="card-body p-0">
                            <?php
                            $A = 0.7 * ($selectedSalary['ml_chinh'] + $selectedSalary['pc_trachnhiem']) + $selectedSalary['pc_congtruong']+$selectedSalary['pc_nghe']+$selectedSalary['pc_nhatro'];
                            if ($selectedSalary['he_luong']=="mhr") {
                                $B = 0.3 * ($selectedSalary['ml_chinh'] + $selectedSalary['pc_trachnhiem']);
                            }
                            else {
                                $B = 0.3 * ($selectedSalary['ml_chinh'] + $selectedSalary['pc_trachnhiem']) * ($selectedSalary['hs_sanluong'] + $selectedSalary['hs_apq']) / 2;
                            }
                            $C = ($selectedSalary['ovt_thue'] + $selectedSalary['ovt_kothue']) * $selectedSalary['hs_sanluong'];
                            $thuNhapChinh = $A + $B + $C;
                            ?>
                            <table class="table">
                                <tbody>
                                    <tr><td>A = 70%(ML+PCTN) + PC khác</td><td class="text-right"><?= number_format($A) ?></td></tr>
                                    <tr><td>B = 30%(ML+PCTN)*(HS1+HS2)/2</td><td class="text-right"><?= number_format($B) ?></td></tr>
                                    <tr><td>C = Tổng lương OVT *HS1</td><td class="text-right"><?= number_format($C) ?></td></tr>
                                    <tr class="bg-primary"><th>Thu nhập chính (A+B+C)</th><td class="text-right"><?= number_format($thuNhapChinh) ?></td></tr>
                                    <tr class="text-danger"><td>Đoàn phí công đoàn</td><td class="text-right"><?= number_format($selectedSalary['kt_congdoan']) ?></td></tr>
                                    <tr class="text-danger"><td>Bảo hiểm Xã hội (10.5%)</td><td class="text-right"><?= number_format($selectedSalary['kt_bhxh']) ?></td></tr>
                                    <tr class="text-danger"><td>Thuế TNCN phải nộp</td><td class="text-right"><?= number_format($selectedSalary['kt_tncn']) ?></td></tr>
                                    <tr class="text-danger"><th>Các khoản khấu trừ khác</th><th class="text-right"><?= number_format($selectedSalary['kt_khac']) ?></th></tr>
                                    <tr class="bg-success">
                                        <th><h5 class="m-0">Tổng lương thực lĩnh</h5></th>
                                        <th class="text-right"><h5 class="m-0"><?= number_format($thuNhapChinh+$selectedSalary['ml_khac']-$selectedSalary['kt_bhxh']-$selectedSalary['kt_tncn']-$selectedSalary['kt_congdoan']-$selectedSalary['kt_khac']) ?></h5></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php else : ?>
                <!-- No Data Message -->
                <div class="col-md-10">
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Không có dữ liệu!</h5>
                        <p>Không có dữ liệu lương cho tháng này.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
}
}
// include
  include(ROOT_PATH . '/layouts/footer.php');
?>