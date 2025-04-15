<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

// Xử lý AJAX request trước khi include các file layout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json');
    $response = array();
    
    if ($_SESSION['level'] != 1) {
        $response = array('status' => 'error', 'message' => 'Bạn không có quyền xóa hợp đồng.');
        echo json_encode($response);
        exit;
    }

    $contract_id = $_POST['contract_id'];
    
    // Delete the contract
    $delete_sql = "DELETE FROM hop_dong_lao_dong WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    
    if (!$delete_stmt) {
        $response = array('status' => 'error', 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error);
        echo json_encode($response);
        exit;
    }
    
    $delete_stmt->bind_param("i", $contract_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            $response = array('status' => 'success', 'message' => 'Xóa hợp đồng thành công.');
        } else {
            $response = array('status' => 'error', 'message' => 'Không tìm thấy hợp đồng để xóa.');
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Không thể xóa hợp đồng: ' . $delete_stmt->error);
    }
    
    $delete_stmt->close();
    echo json_encode($response);
    exit;
}

// Include các file layout sau khi xử lý AJAX
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "Hợp đồng lao động";

// Kiểm tra session
if (!isset($_SESSION['username'], $_SESSION['level'], $_SESSION['idNhanVien'])) {
    die("Bạn chưa đăng nhập.");
}

// Lấy thông tin từ session
$user_quyen = $_SESSION['level'] ?? 0; // Mặc định là 0 nếu chưa đăng nhập
$session_idNhanVien = $_SESSION['idNhanVien'];

// Lọc danh sách nhân viên theo phòng ban
$phongbanFilter = "";
$params = [];
$types = "";

if (!empty($_GET['phong_ban'])) {
    $phongbanFilter = " AND nv.phong_ban_id = ?";
    $params[] = $_GET['phong_ban'];
    $types .= "i";
}

// Nhận ID nhân viên từ URL (nếu có)
$idNhanVien = isset($_GET['id']) ? decryptId($_GET['id']) : $session_idNhanVien;

// Kiểm tra quyền truy cập
if ($user_quyen == 0 && $idNhanVien != $session_idNhanVien) {
    echo "<div style='text-align:center; color:red;padding:50px;'>❌ Bạn không có quyền truy cập thông tin của người khác. </div>";
    exit;
}

// Tạo truy vấn SQL
if ($user_quyen == 1 && !isset($_GET['id'])) {
    // Admin: lấy toàn bộ danh sách hợp đồng
    $sql = "SELECT hd.id, hd.ma_hop_dong, hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.phucap_tnh, 
                   hd.phucap_nha_tro, hd.muc_luong, hd.phucap_nghe, hd.phucap_dac_biet, 
                   hd.nhanvien_id, lhd.ten_hop_dong, nv.ten_nv, hd.trang_thai, nv.ma_nv, nv.phong_ban_id
            FROM hop_dong_lao_dong hd 
            JOIN hop_dong_type lhd ON hd.loai_hop_dong_id = lhd.id             
            JOIN nhanvien nv ON hd.nhanvien_id = nv.id
            JOIN phong_ban pb ON nv.phong_ban_id = pb.id
            WHERE 1 $phongbanFilter
            ORDER BY nv.ngay_vao_lam DESC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

} else {
    // Nhân viên thường hoặc khi có id: chỉ xem hợp đồng của chính họ
    $sql = "SELECT hd.id, hd.ma_hop_dong, hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.phucap_tnh, 
                   hd.phucap_nha_tro, hd.muc_luong, hd.phucap_nghe, hd.phucap_dac_biet, 
                   hd.nhanvien_id, lhd.ten_hop_dong, nv.ten_nv, hd.trang_thai, nv.ma_nv
            FROM hop_dong_lao_dong hd 
            JOIN hop_dong_type lhd ON hd.loai_hop_dong_id = lhd.id 
            JOIN nhanvien nv ON hd.nhanvien_id = nv.id
            WHERE hd.nhanvien_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idNhanVien);
}
// Lấy thông tin từ session
$user_quyen = $_SESSION['level'] ?? 0; // Mặc định là 0 nếu chưa đăng nhập
$session_idNhanVien = $_SESSION['idNhanVien'];

// Lọc danh sách nhân viên theo phòng ban
$phongbanFilter = "";
$params = [];
$types = "";

if (!empty($_GET['phong_ban'])) {
    $phongbanFilter = " AND nv.phong_ban_id = ?";
    $params[] = $_GET['phong_ban'];
    $types .= "i";
}

// Nhận ID nhân viên từ URL (nếu có)
$idNhanVien = isset($_GET['id']) ? decryptId($_GET['id']) : $session_idNhanVien;

// Kiểm tra quyền truy cập
if ($user_quyen == 0 && $idNhanVien != $session_idNhanVien) {
    echo "<div style='text-align:center; color:red;padding:50px;'>❌ Bạn không có quyền truy cập thông tin của người khác. </div>";
    exit;
}

// Tạo truy vấn SQL
if ($user_quyen == 1 && !isset($_GET['id'])) {
    // Admin: lấy toàn bộ danh sách hợp đồng
    $sql = "SELECT hd.id, hd.ma_hop_dong, hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.phucap_tnh, 
                   hd.phucap_nha_tro, hd.muc_luong, hd.phucap_nghe, hd.phucap_dac_biet, 
                   hd.nhanvien_id, lhd.ten_hop_dong, nv.ten_nv, hd.trang_thai, nv.ma_nv, nv.phong_ban_id
            FROM hop_dong_lao_dong hd 
            JOIN hop_dong_type lhd ON hd.loai_hop_dong_id = lhd.id             
            JOIN nhanvien nv ON hd.nhanvien_id = nv.id
            JOIN phong_ban pb ON nv.phong_ban_id = pb.id
            WHERE 1 $phongbanFilter
            ORDER BY nv.ngay_vao_lam DESC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

} else {
    // Nhân viên thường hoặc khi có id: chỉ xem hợp đồng của chính họ
    $sql = "SELECT hd.id, hd.ma_hop_dong, hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.phucap_tnh, 
                   hd.phucap_nha_tro, hd.muc_luong, hd.phucap_nghe, hd.phucap_dac_biet, 
                   hd.nhanvien_id, lhd.ten_hop_dong, nv.ten_nv, hd.trang_thai, nv.ma_nv
            FROM hop_dong_lao_dong hd 
            JOIN hop_dong_type lhd ON hd.loai_hop_dong_id = lhd.id 
            JOIN nhanvien nv ON hd.nhanvien_id = nv.id
            WHERE hd.nhanvien_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idNhanVien);
}

// Add error checking after prepare
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}
// Thực thi truy vấn
$stmt->execute();

// Lấy kết quả
$result = $stmt->get_result();
if (!$result) {
    die("Lỗi lấy dữ liệu: " . $stmt->error);
}

// Lấy dữ liệu hợp đồng vào mảng
$arrShow = $result->fetch_all(MYSQLI_ASSOC);
// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json'); // Add this line to ensure proper JSON response
    $response = array();
    
    if ($user_quyen != 1) {
        $response = array('status' => 'error', 'message' => 'Bạn không có quyền xóa hợp đồng.');
        echo json_encode($response);
        exit;
    }

    $contract_id = $_POST['contract_id'];
    
    // Delete the contract
    $delete_sql = "DELETE FROM hop_dong_lao_dong WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    
    if (!$delete_stmt) {
        $response = array('status' => 'error', 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error);
        echo json_encode($response);
        exit;
    }
    
    $delete_stmt->bind_param("i", $contract_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            $response = array('status' => 'success', 'message' => 'Xóa hợp đồng thành công.');
        } else {
            $response = array('status' => 'error', 'message' => 'Không tìm thấy hợp đồng để xóa.');
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Không thể xóa hợp đồng: ' . $delete_stmt->error);
    }
    
    $delete_stmt->close();
    echo json_encode($response);
    exit;
}

?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Danh sách Hợp Đồng Lao Động</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                <div class="row mt-3">
                    <div class="col-md-5">
                    <form method="GET" action="" class="d-flex align-items-center" style="display:flex; ">
                        <div class="input-group w-100">
                            <select name="phong_ban" class="form-control" style="width: 75%;" onchange="location.href='hopdong_list.php?p=staff&a=contract-list&phong_ban=' + this.value">
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
                        <div class="float-right">
                            <?php if ($row_acc['user_quyen'] == 1): ?>
                                <div class="btn-group">
                                    <form method="post" enctype="multipart/form-data" action="import_luong.php" class="form-inline">
                                        <div class="input-group input-group-sm">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="file" accept=".xlsx,.xls" required disabled>
                                                <label class="custom-file-label">Chọn file</label>
                                            </div>
                                            <div class="input-group-append">
                                                <button type="submit" name="import" class="btn btn-warning mr-3" disabled>
                                                    <i class="fas fa-upload"></i> Nhập dữ liệu
                                                </button>
                                            </div>
                                            <a href="hopdong_create.php?p=staff&a=contract-list&add-new" class="btn btn-primary btn-sm mr-2">
                                                <i class="fas fa-plus"></i> Thêm hợp đồng
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped table-hover" >
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px">#</th>
                                    <th>Mã Hợp đồng</th>
                                    <th>Số C.ty</th>
                                    <th>Họ và tên</th>
                                    <th>Loại hợp đồng</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Mức lương</th>
                                    <th>PC. Trách nhiệm</th>
                                    <th>PC. Nghề</th>
                                    <th>PC. Nhà trọ</th>
                                    <th>PC. Đặc biệt</th>
                                    <th>Tình trạng</th>
                                    <th style="width: 120px">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($arrShow as $index => $contract): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($contract['ma_hop_dong']) ?></td>
                                        <td><?= htmlspecialchars($contract['ma_nv']) ?></td>
                                        <td><?= htmlspecialchars($contract['ten_nv']) ?></td>
                                        <td><?= htmlspecialchars($contract['ten_hop_dong']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($contract['ngay_bat_dau'])) ?></td>
                                        <td><?= (!empty($contract['ngay_ket_thuc']) && $contract['ngay_ket_thuc'] != 0) ? date('d-m-Y', strtotime($contract['ngay_ket_thuc']))  : "" ?></td>
                                        <td style="text-align:right;"><?= number_format($contract['muc_luong'],0,".",",")?></td>
                                        <td style="text-align:right;"><?= number_format($contract['phucap_tnh'],0,".",",")?></td>
                                        <td style="text-align:right;"><?= number_format($contract['phucap_nghe'],0,".",",")?></td>
                                        <td style="text-align:right;"><?= number_format($contract['phucap_nha_tro'],0,".",",")?></td>
                                        <td style="text-align:right;"><?= number_format($contract['phucap_dac_biet'],0,".",",")?></td>
                                        <td><?= htmlspecialchars($contract['trang_thai']) ?></td>
                                        <td>
                                            <?php if ($row_acc['user_quyen']==1) :?>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="hopdong_view.php?p=staff&a=contract-list&id=<?= encryptId($contract['id']) ?>" class="btn btn-info mr-1" title="Xem">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="hopdong_edit.php?p=staff&a=contract-edit&id=<?= $contract['id'] ?>" class="btn btn-warning mr-1" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" 
                                                    onclick="showDeleteModal(<?= $contract['id'] ?>)" 
                                                    class="btn btn-danger" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            <?php endif ;?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa hợp đồng này không?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thông báo</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="messageContent"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
let contractIdToDelete = null;

function showDeleteModal(contractId) {
    contractIdToDelete = contractId;
    $('#deleteModal').modal('show');
}

$(document).ready(function() {
    $('#confirmDelete').click(function() {
        $.ajax({
            url: window.location.href,
            type: 'POST',
            dataType: 'json', // Add this line
            data: {
                action: 'delete',
                contract_id: contractIdToDelete
            },
            success: function(result) {
                $('#deleteModal').modal('hide');
                $('#messageContent').text(result.message);
                $('#messageModal').modal('show');
                
                if (result.status === 'success') {
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                }
            },
            error: function(xhr, status, error) {
                $('#deleteModal').modal('hide');
                $('#messageContent').text('Có lỗi xảy ra: ' + error);
                $('#messageModal').modal('show');
            }
        });
    });
});
</script>
<?php include(ROOT_PATH . '/layouts/footer.php'); 
// Đóng statement
$stmt->close();
$conn->close();

?>