<?php
// Kết nối đến MySQL
require '../config.php';


// Tạo CSRF token
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));


// Include layout files
include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');


if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý duyệt đơn nghỉ
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['approve'], $_POST['leave_id'], $_POST['csrf_token'])) {
    if (!isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token không hợp lệ!");
    }
    session_start();
    $id = intval($_POST['leave_id']); // Chuyển về số nguyên để tránh SQL Injection
    $stmt = $conn->prepare("UPDATE nghi_phep SET trang_thai = 'Đã duyệt', ngay_phe_duyet = NOW() WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Đơn nghỉ phép đã được duyệt!'); window.location.href='theodoinghiphep.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi duyệt đơn nghỉ phép!');</script>";
    }
    $stmt->close();
}

// Truy vấn danh sách nhân viên và số ngày nghỉ còn lại
$sql="SELECT 
    nv.id, nv.ma_nv, np.ngay_nghi,np.loai_ngay_nghi,np.ly_do,
    nv.ten_nv AS ten_nv, 
    cv.ten_chuc_vu AS chuc_vu, 
    (12 
 + TIMESTAMPDIFF(MONTH, MAKEDATE(YEAR(CURDATE()), 1), CURDATE()) 
 + FLOOR(TIMESTAMPDIFF(YEAR, nv.ngay_kt_thuviec, CURDATE()) / 5)
 - COALESCE((SELECT SUM(so_ngay) FROM nghi_phep WHERE nhanvien_id = nv.id AND trang_thai = 'Đã duyệt'), 0)
) AS so_ngay,
    np.id AS leave_id, 
    np.trang_thai, 
    np.ngay_phe_duyet
FROM nhanvien nv
JOIN chuc_vu cv ON cv.id = nv.chuc_vu_id
JOIN nghi_phep np ON nv.id = np.nhanvien_id
ORDER BY nv.id ASC";

$result = $conn->query($sql);
if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}



?>

<!-- Content Wrapper. Contains page content -->
<style>
#employeeList {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    background: white;
    z-index: 1000;
}
.dropdown-item {
    padding: 8px;
    cursor: pointer;
}
.dropdown-item:hover {
    background-color: #f1f1f1;
}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Theo dõi nghỉ phép năm</h1>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Bổ sung yêu cầu nghỉ phép</h3>
                        </div>
                        <div class="card-body">
                            <form id="leaveRequestForm" method="post" action="de_xuat_nghi.php">
                                <!-- Form content remains the same, just updated some classes -->
                                <div class="form-group">
                                    <label for="nhanvien">Tên Nhân viên</label>
                                    <input type="text" id="nhanvien" class="form-control" onkeyup="searchEmployee()" placeholder="Nhập tên nhân viên...">
                                    <div id="employeeList" class="list-group shadow" style="display: none; max-height: 250px; overflow-y: auto; position: absolute; width: 100%; z-index: 1000;"></div>
                                    <input type="hidden" id="ma_nv" name="ma_nv">
                                </div>
                                
                                <div class="form-group">
                                    <label for="ngay_nghi">Ngày nghỉ</label>
                                    <input type="date" class="form-control" name="ngay_nghi" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="loai_ngay_nghi">Loại ngày nghỉ</label>
                                    <select class="form-control" name="loai_ngay_nghi" required>
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
                                    <label for="ly_do">Lý do</label>
                                    <textarea class="form-control" name="ly_do" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Gửi đề xuất</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Danh sách ngày nghỉ</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="example1">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Họ và tên</th>
                                        <th>Ngày nghỉ</th>
                                        <th>Loại nghỉ</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['ma_nv']); ?></td>
                                        <td><?= htmlspecialchars($row['ten_nv']); ?></td>
                                        <td><?= htmlspecialchars($row['ngay_nghi']); ?></td>
                                        <td><?= htmlspecialchars($row['loai_ngay_nghi']); ?></td>
                                        <td><?= htmlspecialchars($row['trang_thai'] ?: 'Chưa duyệt'); ?></td>
                                        <td>
                                            <?php if ($row['trang_thai'] !== 'Đã duyệt') { ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="leave_id" value="<?= htmlspecialchars($row['leave_id']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="approve" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                            </form>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Bootstrap -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="successModalLabel">Thông báo</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Đề xuất nghỉ phép đã được gửi thành công!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="form_nghiphep.js"></script>

<script>
function searchData() {
    let keyword = document.getElementById("searchInput").value;

    let xhr = new XMLHttpRequest();
    xhr.open("GET", "nghiphep_tonghop.php?search=" + keyword, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById("tableContainer").innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
</script>


<script>
function searchEmployee() {
    let query = document.getElementById("nhanvien").value.trim();
    let dropdown = document.getElementById("employeeList");

    if (query.length < 2) {
        dropdown.style.display = "none";
        return;
    }

    fetch("search_employee.php?q=" + query)
        .then(response => response.text()) // Lấy dữ liệu dạng HTML
        .then(data => {
            dropdown.innerHTML = data;
            dropdown.style.display = "block";

            document.querySelectorAll(".nhanvien-item").forEach(item => {
                item.addEventListener("click", function () {
                    let info = JSON.parse(this.getAttribute("data-info"));
                    document.getElementById("nhanvien").value = info.ten_nv;
                    document.getElementById("ma_nv").value = info.ma_nv;
                    dropdown.style.display = "none";
                });
            });
        })
        .catch(error => console.error("Lỗi tìm kiếm:", error));
}

// Ẩn dropdown khi click ra ngoài
document.addEventListener("click", function (event) {
    let dropdown = document.getElementById("employeeList");
    let input = document.getElementById("nhanvien");
    if (!input.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = "none";
    }
});

</script>
<?php 

include('../layouts/footer.php'); ?>
