<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');// Kết nối database

// Include layout files
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "Bảng lương";

// Add delete handling
if (isset($_POST['delete_salary']) && isset($_POST['salary_id'])) {
    $salary_id = $_POST['salary_id'];
    
    // Prepare delete statement
    $delete_sql = "DELETE FROM luong_chamcong WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $salary_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Xóa lương thành công";
    } else {
        $_SESSION['error'] = "Lỗi khi xóa lương: " . $conn->error;
    }
    
    $delete_stmt->close();
    header("Location: " . $_SERVER['PHP_SELF'].'?p=staff&a=salary-list',1000);
    exit();
}


$conditions = [];
$params = [];

if (!empty($_GET['month'])) {
    $conditions[] = "blg.sal_month = ?";
    $params[] = $_GET['month'];
}

if (!empty($_GET['year'])) {
    $conditions[] = "blg.sal_year = ?";
    $params[] = $_GET['year'];
}

$sql = "SELECT blg.ma_luong, blg.ngaycong, blg.hs_sanluong, blg.hs_apq, blg.ml_chinh, 
               blg.ml_khac, blg.kt_congdoan, blg.kt_bhxh, blg.kt_tncn, blg.kt_khac, 
               blg.sal_month, blg.sal_year, blg.id,
               nv.ma_nv, nv.ten_nv 
        FROM luong_chamcong blg 
        JOIN nhanvien nv ON blg.nhanvien_id = nv.id";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param(str_repeat("i", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$arrShow = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<style>
    table {
        white-space: nowrap;
        width: 100%;
    }
    td, th {
        text-align: center;
    }
    .modal {
        display: flex;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Danh sách lương</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-4">
                            <form method="GET" action="" class="form-inline">
                                <div class="form-group mr-2">
                                    <input type="hidden" name="p" value="staff">
                                    <input type="hidden" name="a" value="salary-list">
                                    <label for="month" class="mr-2">Tháng:</label>
                                    <select name="month" id="month" class="form-control form-control-sm">
                                        <option value="">Tất cả</option>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?= $m ?>" <?= (isset($_GET['month']) && $_GET['month'] == $m) ? 'selected' : '' ?>>
                                                <?= sprintf('%02d', $m) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group mr-2">
                                    <label for="year" class="mr-2">Năm:</label>
                                    <select name="year" id="year" class="form-control form-control-sm">
                                        <option value="">Tất cả</option>
                                        <?php
                                        $currentYear = date('Y');
                                        for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
                                            <option value="<?= $y ?>" <?= (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '' ?>>
                                                <?= $y ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                            </form>
                        </div>
                        <div class="col-md-8 text-right">
                            <?php if ($row_acc['user_quyen'] == 1): ?>
                                <form method="post" enctype="multipart/form-data" action="import_luong.php" class="d-inline-block">
                                    <div class="input-group input-group-sm">
                                        <input type="file" name="file" accept=".xlsx,.xls" class="form-control" required>
                                        <div class="input-group-append">
                                            <button type="submit" name="import" class="btn btn-warning">Nhập dữ liệu</button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped table-hover" id="example1">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Mã lương</th>
                                <th>Số C.ty</th>
                                <th>Họ và tên</th>
                                <th>Số công</th>
                                <th>HS N.S</th>
                                <th>HS APQ</th>
                                <th>Mức lương</th>
                                <th>Thu nhập khác</th>
                                <th>Công đoàn</th>
                                <th>BHXH</th>
                                <th>TNCN</th>
                                <th>Khấu trừ khác</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($arrShow as $index => $salary): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($salary['ma_luong']) ?></td>
                                    <td><?= htmlspecialchars($salary['ma_nv']) ?></td>
                                    <td class="text-left"><?= htmlspecialchars($salary['ten_nv']) ?></td>
                                    <td><?= $salary['ngaycong'] ?></td>
                                    <td class="text-right"><?= number_format($salary['hs_sanluong'],2,".",",") ?></td>
                                    <td class="text-right"><?= number_format($salary['hs_apq'],1,".",",") ?></td>
                                    <td class="text-right"><?= number_format($salary['ml_chinh'],0,".",",") ?></td>
                                    <td class="text-right"><?= number_format($salary['ml_khac'],0,".",",") ?></td>
                                    <td class="text-right"><?= number_format($salary['kt_congdoan'],0,".",",") ?></td>
                                    <td class="text-right"><?= number_format($salary['kt_bhxh'],0,".",",") ?></td>
                                    <td class="text-right"><?= number_format($salary['kt_tncn'],0,".",",") ?></td>
                                    <td class="text-right"><?= number_format($salary['kt_khac'],0,".",",") ?></td>
                                    <td>
                                        <?php if($row_acc['user_quyen'] == 1): ?>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="bangluong_xem.php?id=<?= $salary['id']?>">
                                                        <i class="fas fa-eye mr-2"></i> Xem chi tiết
                                                    </a>
                                                    <a class="dropdown-item" href="bangluong_phieusua.php?id=<?= $salary['id'] ?>">
                                                        <i class="fas fa-edit mr-2"></i> Sửa
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="salary_id" value="<?= $salary['id'] ?>">
                                                        <button type="submit" name="delete_salary" 
                                                                class="dropdown-item text-danger"
                                                                onclick="return confirm('Xác nhận xóa mục lương này?');">
                                                            <i class="fas fa-trash mr-2"></i> Xóa lương
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
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

<?php include(ROOT_PATH . '/layouts/footer.php'); ?>
