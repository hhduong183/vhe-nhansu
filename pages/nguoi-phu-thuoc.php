<?php
ob_start();
session_start();
include('../config.php');
include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');

// Bảo vệ truy cập
if (!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
    header("Location: dang-nhap.php");
    exit();
}

// Xử lý thêm, cập nhật, xóa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $ho_ten = $_POST['ho_ten'];
        $ngay_sinh = $_POST['ngay_sinh'];
        $quan_he = $_POST['quan_he'];
        $so_giay_to = $_POST['so_giay_to'];
        $loai_giay_to=$_POST['loai_giay_to'];
        $nhan_vien_id = $_POST['nhanvien_id'];//$_SESSION['idNhanVien'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $mst_nguoi_phu_thuoc = $_POST['mst_nguoi_phu_thuoc'];

        $stmt = $conn->prepare("INSERT INTO nguoi_phu_thuoc (loai_giay_to, ho_ten, ngay_sinh, quan_he, so_giay_to, nhan_vien_id, mst_nguoi_phu_thuoc, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssisss", $loai_giay_to, $ho_ten, $ngay_sinh, $quan_he, $so_giay_to, $nhan_vien_id, $mst_nguoi_phu_thuoc, $start_date, $end_date);
        if ($stmt->execute()) {
            $_SESSION['alert_success'] = '✅ Thêm người phụ thuộc thành công!';
        } else {
            $_SESSION['alert_error'] = '❌ Lỗi khi thêm!'. $stmt->error;
        }
        header("Location: nguoi-phu-thuoc.php?p=staff&a=giam-tru&add-sucess");
        exit();
    }

    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $ho_ten = $_POST['ho_ten'];
        $ngay_sinh = $_POST['ngay_sinh'];
        $quan_he = $_POST['quan_he'];
        $so_giay_to = $_POST['so_giay_to'];
        $loai_giay_to=$_POST['loai_giay_to'];

        $stmt = $conn->prepare("UPDATE nguoi_phu_thuoc SET loai_giay_to = ?, ho_ten = ?, ngay_sinh = ?, quan_he = ?, so_giay_to = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $loai_giay_to, $ho_ten, $ngay_sinh, $quan_he, $so_giay_to, $id);
        if ($stmt->execute()) {
            $_SESSION['alert_success'] = '✅ Cập nhật thành công!';
        } else {
            $_SESSION['alert_error'] = '❌ Lỗi khi cập nhật!';
        }
        header("Location: nguoi-phu-thuoc.php?p=staff&a=giam-tru&update=sucess");
        exit();
    }

    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM nguoi_phu_thuoc WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['alert_success'] = '✅ Xóa thành công!';
        } else {
            $_SESSION['alert_error'] = '❌ Lỗi khi xóa!';
        }
        header("Location: nguoi-phu-thuoc.php?p=staff&a=giam-tru&delete=sucess");
        exit();
    }
}

$stmt = $conn->prepare("
    SELECT npt.*, nv.ma_nv, nv.ten_nv 
    FROM nguoi_phu_thuoc npt 
    JOIN nhanvien nv ON npt.nhan_vien_id = nv.id 
 
");
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid"><div class="row mb-2"><div class="col-sm-6">
            <h1 class="m-0">Quản lý người phụ thuộc</h1></div></div></div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Form thêm mới -->
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Thêm người phụ thuộc</h3></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nhanvien">Tên Nhân viên</label>
                                    <input type="text" id="nhanvien" class="form-control" onkeyup="searchEmployee()" placeholder="Nhập tên nhân viên..." autocomplete="off">
                                    <div id="employeeList" class="list-group position-absolute w-100" style="display: none; max-height: 250px; overflow-y: auto; background: white; z-index: 1000;"></div>
                                    <input type="hidden" id="ma_nv" name="ma_nv">
                                    <input type="hidden" id="nhanvien_id" name="nhanvien_id">
                                </div>
                                <!-- <input type="text" class="form-control mb-3" name="ten_nv" required> -->
                                <label>Họ tên người phụ thuộc:</label>
                                <input type="text" class="form-control mb-3" name="ho_ten" required>
                                <label>Ngày sinh:</label>
                                <input type="date" class="form-control mb-3" name="ngay_sinh" required>
                                <label>MST Người phụ thuộc:</label>
                                <input type="text" class="form-control mb-3" name="mst_nguoi_phu_thuoc" required>
                                
                            </div>
                            <div class="col-md-6">
                                <label>Quan hệ:</label>
                                <!-- <input type="text" class="form-control mb-3" name="quan_he" required> -->
                                <select class="form-control mb-3" name="quan_he" required>
                                    <option value="">-- Chọn quan hệ --</option>
                                    <option value="Con">Con</option>
                                    <option value="Cha">Cha</option>
                                    <option value="Mẹ">Mẹ</option>
                                    <option value="Vợ">Vợ</option>
                                    <option value="Chồng">Chồng</option>
                                </select>
                                <label>Loại giấy tờ:</label>
                                <input type="text" class="form-control mb-3" name="loai_giay_to" required>
                                <label>Số giấy tờ:</label>
                                <input type="text" class="form-control mb-3" name="so_giay_to" required>
                                <div class="row">
                                <div class="col-md-6">
                                    <label>Ngày bắt đầu:</label>
                                    <input type="date" class="form-control mb-3" name="start_date" required>      
                                </div>
                                <div class="col-md-6">                          
                                    <label>Ngày kết thúc:</label>
                                    <input type="date" class="form-control mb-3" name="end_date" required>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-3 text-right">
                            <button type="submit" name="add" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm mới</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bảng danh sách -->
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover table-sm" id="example1">
                        <thead class="thead-light">
                            <tr>
                                <th>STT</th>
                                <th>Nhân viên</th>
                                <th>Người phụ thuộc</th>
                                <th>Ngày sinh</th>
                                <th>Quan hệ</th>
                                <th>Loại giấy tờ</th>
                                <th>Số giấy tờ</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $stt = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $stt++ ?></td>
                                <td><?= htmlspecialchars($row['ten_nv']) ?></td>
                                <td><?= htmlspecialchars($row['ho_ten']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['ngay_sinh'])) ?></td>
                                <td><?= htmlspecialchars($row['quan_he']) ?></td>
                                <td><?= htmlspecialchars($row['loai_giay_to']) ?></td>
                                <td><?= htmlspecialchars($row['so_giay_to']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['start_time'])) ?></td>
                                <td><?= $row['end_time']!==NULL ? date('d/m/Y', strtotime($row['end_time'])) : " " ?></td>


                                <td>
                                    <button class="btn btn-warning btn-xs" onclick='editDependant(<?= json_encode([
                                        "id" => $row["id"],
                                        "ho_ten" => $row["ho_ten"],
                                        "ngay_sinh" => date("d/m/Y", strtotime($row["ngay_sinh"])),
                                        "quan_he" => $row["quan_he"],
                                        "loai_giay_to" => $row["loai_giay_to"],
                                        "so_giay_to" => $row["so_giay_to"]
                                    ]) ?>)'><i class="fas fa-edit"></i></button>
                                    <form method="POST" style="display:inline-block;" onsubmit="return confirm('Xác nhận xóa?')">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button name="delete" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal chỉnh sửa -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h4 class="modal-title">Cập nhật thông tin</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <label>Họ tên:</label>
                <input type="text" class="form-control" name="ho_ten" id="edit_ho_ten" required>
                <label>Ngày sinh:</label>
                <input type="date" class="form-control" name="ngay_sinh" id="edit_ngay_sinh" required>
                <label>Quan hệ:</label>
                <!-- <input type="text" class="form-control" name="quan_he" id="edit_quan_he" required> -->
                <select class="form-control" name="quan_he" id="edit_quan_he" required>
                    <option value="">-- Chọn quan hệ --</option>
                    <option value="Con">Con</option>
                    <option value="Cha">Cha</option>
                    <option value="Mẹ">Mẹ</option>
                    <option value="Vợ">Vợ</option>
                    <option value="Chồng">Chồng</option>
                </select>
                <label>Loại giấy tờ:</label>
                <input type="text" class="form-control" name="loai_giay_to" id="edit_loai_giay_to" required>

                <label>Số giấy tờ:</label>
                <input type="text" class="form-control" name="so_giay_to" id="edit_so_giay_to" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <button type="submit" name="update" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div></div>
</div>

<?php include('../layouts/footer.php'); ?>
<?php ob_end_flush(); // ✅ Kết thúc buffer ?>
<!-- SweetAlert2 + JS xử lý -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).on("click", ".nhanvien-item", function() {
        let nhanvien = $(this).data("info");
        $("#nhanvien_id").val(nhanvien.id);
        $("#result_nhanvien").hide();
    });
function editDependant(data) {
    let parts = data.ngay_sinh.split('/');
    let formatted = `${parts[2]}-${parts[1]}-${parts[0]}`;
    $('#edit_id').val(data.id);
    $('#edit_ho_ten').val(data.ho_ten);
    $('#edit_ngay_sinh').val(formatted);
    $('#edit_quan_he').val(data.quan_he);
    $('#edit_so_giay_to').val(data.so_giay_to);
    $('#edit_loai_giay_to').val(data.loai_giay_to);

    $('#editModal').modal('show');
}

// Thông báo
<?php if (isset($_SESSION['alert_success'])): ?>
    Swal.fire({ icon: 'success', title: 'Thành công', text: '<?= $_SESSION["alert_success"] ?>' });
    <?php unset($_SESSION['alert_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['alert_error'])): ?>
    Swal.fire({ icon: 'error', title: 'Lỗi', text: '<?= $_SESSION["alert_error"] ?>' });
    <?php unset($_SESSION['alert_error']); ?>
<?php endif; ?>
</script>
<script src="./vjs/search_employee.js"></script>
