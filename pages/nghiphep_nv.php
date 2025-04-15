<?php
// Kết nối cơ sở dữ liệu
require '../config.php';
session_start();
// Include layout files
include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');
include('../plugins/function.php');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Giả sử nhân viên có ID cố định, bạn có thể thay đổi để lấy từ session
// $employee_id = 41;
if (isset($_SESSION['username']) && isset($_SESSION['level']) && isset($_SESSION['idNhanVien'])) {

    // Kiểm tra nếu có ID trong URL
    if (isset($_GET['id'])) {
        $employee_id = decryptId($_GET['id']); // Ép kiểu ID thành số nguyên
        $employee_id = (int) $employee_id;
        $idNhanVien = (int) $_SESSION['idNhanVien'];
        // Kiểm tra ID trong URL có khớp với ID của user hiện tại không
        if ($employee_id !== $idNhanVien && $_SESSION['level'] == 0) {
            echo "<div style='text-align:center; color:red;padding:50px;'>❌ Bạn không có quyền truy cập thông tin của người khác. </div>";
            exit;
            // Hoặc chuyển hướng: header("Location: error.php"); exit;
        }



        // Lấy thông tin ngày phép
        $sql_leave = "SELECT 
    nv.id, 
    nv.ten_nv AS ten_nv, nv.ngay_vao_lam, nv.ngay_kt_thuviec, nv.phong_ban_id,
    cv.ten_chuc_vu AS chuc_vu, 
   
    GREATEST(
            TIMESTAMPDIFF(MONTH, 
                GREATEST(nv.ngay_kt_thuviec, MAKEDATE(YEAR(CURDATE()), 1)), 
                CURDATE()
            ) 
            + CASE 
                WHEN MONTH(CURDATE()) = 12 
                     AND FLOOR(TIMESTAMPDIFF(YEAR, nv.ngay_kt_thuviec, CURDATE()) / 5) > 0 
                THEN FLOOR(TIMESTAMPDIFF(YEAR, nv.ngay_kt_thuviec, CURDATE()) / 5) 
                ELSE 0 
            END,
            0
        ) AS so_ngay_nam_hien_tai,
        
        (CASE  
            WHEN YEAR(nv.ngay_kt_thuviec) <= YEAR(CURDATE()) - 2 THEN 12  
            ELSE LEAST(12, GREATEST(0, TIMESTAMPDIFF(MONTH, nv.ngay_kt_thuviec, MAKEDATE(YEAR(CURDATE()), 12)) ))
        END  
        + FLOOR(TIMESTAMPDIFF(YEAR, nv.ngay_kt_thuviec, CURDATE()) / 5)) AS so_ngay_nam_cu,
        
    COALESCE((SELECT SUM(so_ngay) FROM nghi_phep WHERE nhanvien_id = nv.id AND trang_thai = 'Đã duyệt'), 0) AS so_ngay_da_nghi
    FROM nhanvien nv
    JOIN chuc_vu cv ON cv.id = nv.chuc_vu_id
    WHERE nv.id = $employee_id";
        $result_leave = $conn->query($sql_leave);
        if (!$result_leave) {
            die("Lỗi truy vấn: " . $conn->error);
        }
        $leave_info = $result_leave->fetch_assoc() ?? ['so_ngay' => 0];
        $so_ngay_con_lai = $leave_info['so_ngay_nam_cu'] + $leave_info['so_ngay_nam_hien_tai'] - $leave_info['so_ngay_da_nghi'];


        // Lấy danh sách ngày phép đã nghỉ
        $sql_days_off = "SELECT so_ngay, trang_thai,ngay_nghi,ngay_de_xuat,loai_ngay_nghi, ly_do FROM nghi_phep WHERE nhanvien_id = $employee_id ORDER BY ngay_nghi DESC";
        $result_days_off = $conn->query($sql_days_off);
        if (!$result_days_off) {
            die("Lỗi truy vấn: " . $conn->error);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $date = $_POST['date'];
            $reason = $_POST['reason'];
            $status = 'Chờ duyệt'; // Mặc định khi gửi yêu cầu

            $sql_insert = "INSERT INTO nghi_phep (nhanvien_id, ngay_nghi, trang_thai, ly_do) VALUES ($employee_id, '$date', '$status', '$reason')";
            if ($conn->query($sql_insert) === TRUE) {
                echo "<script>alert('Đã gửi yêu cầu nghỉ phép thành công!'); window.location.href='';</script>";
            } else {
                echo "Lỗi: " . $conn->error;
            }
        }
        $namnay = date('Y');
        $namtruoc = date('Y') - 1;
    } else {
        echo "<div style='text-align:center; padding:50px;'>❌ Không có ID trong URL! </div>";
        exit;
    }
} else {
    echo "<div style='text-align:center; padding:50px;'>❌ Bạn chưa đăng nhập!</div>";
    exit;
}
?>


<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <!-- Leave Information Box -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-calendar"></i> Thông tin ngày phép</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="far fa-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Số ngày phép <?= $namtruoc ?></span>
                                            <span class="info-box-number"><?php echo htmlspecialchars($leave_info['so_ngay_nam_cu']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Số ngày phép <?= $namnay ?></span>
                                            <span class="info-box-number"><?php echo htmlspecialchars($leave_info['so_ngay_nam_hien_tai']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-calendar-minus"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Số ngày đã nghỉ</span>
                                            <span class="info-box-number"><?php echo htmlspecialchars($leave_info['so_ngay_da_nghi']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="info-box bg-danger">
                                        <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng số ngày phép còn</span>
                                            <span class="info-box-number"><?php echo htmlspecialchars($so_ngay_con_lai); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave History Box -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Danh sách ngày phép đã nghỉ</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Ngày nghỉ</th>
                                        <th>Loại nghỉ</th>
                                        <th>Lý do</th>
                                        <th>Ngày gửi</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result_days_off->fetch_assoc()) { ?>
                                        <tr class="<?php echo ($row['trang_thai'] !== 'Đã duyệt') ? 'text-red' : ''; ?>">
                                            <td><?php echo htmlspecialchars($row['ngay_nghi']); ?></td>
                                            <td><?php echo htmlspecialchars($row['loai_ngay_nghi']); ?></td>
                                            <td><?php echo htmlspecialchars($row['ly_do']); ?></td>
                                            <td><?php echo htmlspecialchars($row['ngay_de_xuat']); ?></td>
                                            <td><?php echo htmlspecialchars($row['trang_thai']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Leave Request Box -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-pencil-alt"></i> Đề xuất xin nghỉ</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="leaveRequestForm">
                                <div class="form-group">
                                    <label for="date">Ngày nghỉ</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="date" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="type">Loại ngày nghỉ</label>
                                    <select class="form-control" name="type" required>
                                        <option value="">-- Chọn loại ngày nghỉ --</option>
                                        <option value="Nghỉ phép năm">Nghỉ phép năm</option>
                                        <option value="Nghỉ không lương">Nghỉ không lương</option>
                                        <option value="Nghỉ ốm">Nghỉ ốm</option>
                                        <option value="Nghỉ thai sản">Nghỉ thai sản</option>
                                        <option value="Nghỉ cưới hỏi">Nghỉ cưới hỏi</option>
                                        <option value="Nghỉ tang">Nghỉ tang</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="reason">Lý do</label>
                                    <textarea class="form-control" name="reason" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-block" disabled>
                                    <i class="fa fa-send"></i> Gửi đề xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php $conn->close(); ?>
<script src="./vjs/form_nghiphep.js"></script>
<?php include('../layouts/footer.php'); ?>