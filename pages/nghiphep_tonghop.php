<?php
require '../config.php';
session_start();

// Kiểm tra đăng nhập và quyền truy cập
if (!isset($_SESSION['username'], $_SESSION['level'], $_SESSION['idNhanVien'])) {
    header("Location: ../login.php");
    exit();
}

// Chỉ cho phép admin và manager truy cập
if ($_SESSION['level'] ==0) {
    header("Location: ../403.php");
    exit();
}

include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');

// ... rest of the existing code ...

// Lọc danh sách nhân viên theo phòng ban
$phongbanFilter = "";
$params = [];
$types = "";
if (!empty($_GET['phong_ban'])) {
    $phongbanFilter = " AND nv.phong_ban_id = ?";
    $params[] = $_GET['phong_ban'];
    $types .= "i";
}

// Truy vấn lấy thông tin phép năm cho tất cả nhân viên
$sql_leave_all = "
    SELECT 
        nv.id, nv.ngay_vao_lam, nv.ngay_kt_thuviec, nv.phong_ban_id,
        nv.ten_nv AS ten_nv, 
        cv.ten_chuc_vu AS chuc_vu, 
        pb.ten_phong_ban AS phong_ban,
        GREATEST(
            TIMESTAMPDIFF(MONTH, 
                GREATEST(nv.ngay_kt_thuviec, MAKEDATE(YEAR(CURDATE()), 1)), 
                CURDATE()
            )
            +   CASE 
                WHEN MONTH(CURDATE()) = 12 
                    AND FLOOR(TIMESTAMPDIFF(YEAR, nv.ngay_kt_thuviec, STR_TO_DATE(CONCAT(YEAR(CURDATE()) - 1, '-12-31'), '%Y-%m-%d')) / 5) > 0 
                THEN FLOOR(TIMESTAMPDIFF(YEAR, nv.ngay_kt_thuviec, STR_TO_DATE(CONCAT(YEAR(CURDATE()) - 1, '-12-31'), '%Y-%m-%d')) / 5) 
                ELSE 0 
            END,
            0
        ) AS so_ngay_nam_hien_tai,

        (CASE  
            WHEN YEAR(nv.ngay_kt_thuviec) <= YEAR(CURDATE()) - 2 THEN 12  
            ELSE LEAST(12, GREATEST(0, TIMESTAMPDIFF(MONTH, nv.ngay_kt_thuviec, MAKEDATE(YEAR(CURDATE()), 12)) ))
        END  
        +  FLOOR(TIMESTAMPDIFF(YEAR, nv.ngay_kt_thuviec, STR_TO_DATE(CONCAT(YEAR(CURDATE()) - 1, '-12-31'), '%Y-%m-%d')) / 5) 
                        
        ) AS so_ngay_nam_cu,


        COALESCE((SELECT SUM(so_ngay) FROM nghi_phep WHERE nhanvien_id = nv.id AND trang_thai = 'Đã duyệt'), 0) AS so_ngay_da_nghi,

        (GREATEST(
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
        )
            + (CASE
            WHEN YEAR(nv.ngay_kt_thuviec) <= YEAR(CURDATE()) - 2 THEN 12 ELSE LEAST(12, GREATEST(0, TIMESTAMPDIFF(MONTH,
                nv.ngay_kt_thuviec, MAKEDATE(YEAR(CURDATE()), 12)))) END + FLOOR(TIMESTAMPDIFF(YEAR, nv.ngay_kt_thuviec, CURDATE())
                / 5)) - COALESCE((SELECT SUM(so_ngay) FROM nghi_phep WHERE nhanvien_id=nv.id AND trang_thai='Đã duyệt' ), 0)) AS so_ngay_con_lai
    FROM nhanvien nv
    JOIN chuc_vu cv ON cv.id = nv.chuc_vu_id
    JOIN phong_ban pb ON pb.id = nv.phong_ban_id
    WHERE cv.ten_chuc_vu NOT IN ('Trưởng phòng')
    AND cv.ten_chuc_vu NOT LIKE '%Giám đốc%'
    AND cv.ten_chuc_vu NOT LIKE '%Chủ tịch%'
    $phongbanFilter
    ORDER BY pb.ten_phong_ban, nv.ten_nv
";

// Chuẩn bị statement
$stmt = $conn->prepare($sql_leave_all);
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}

// Gán giá trị cho tham số
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Thực thi truy vấn
$stmt->execute();
$result_all = $stmt->get_result();

$namcu = date('Y') - 1;
$namnay = date('Y');

// Kiểm tra nếu có dữ liệu
if ($result_all->num_rows > 0) {
    // Tạo bảng tổng hợp
    $max_ngay_nghi = 0;
    $data_all = [];
    while ($row = $result_all->fetch_assoc()) {
        $employee_id = $row['id'];

        // Lấy ngày nghỉ đã duyệt của từng nhân viên
        $sql_ngay_nghi = "SELECT GROUP_CONCAT(np.ngay_nghi ORDER BY np.ngay_nghi) AS ngay_nghi
                          FROM nghi_phep np
                          WHERE np.nhanvien_id = $employee_id AND np.trang_thai = 'Đã duyệt'";

        $result_ngay_nghi = $conn->query($sql_ngay_nghi);
        $ngay_nghi_data = $result_ngay_nghi->fetch_assoc();
        $ngay_nghi_arr = explode(",", $ngay_nghi_data['ngay_nghi']);
        $max_ngay_nghi = max($max_ngay_nghi, count($ngay_nghi_arr));

        // Lưu dữ liệu nhân viên vào mảng
        $row['ngay_nghi'] = $ngay_nghi_arr;
        $data_all[] = $row;
    }
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Theo dõi nghỉ phép năm</h1>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <!-- Form lọc theo phòng ban -->
                            <form method="GET" action="" class="d-flex gap-2">
                                <select name="phong_ban_annual" class="form-control" style="width: 350px;" onchange="location.href='nghiphep_tonghop.php?p=staff&a=anual-sum&phong_ban=' + this.value">
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
                                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Họ và tên</th>
                                    <th>Ngày vào/kt thử việc</th>
                                    <th>Phép năm <?=$namcu?></th>
                                    <th>Phép năm <?=$namnay?></th>
                                    <th>Ngày phép còn</th>
                                    <?php for ($i = 1; $i <= $max_ngay_nghi; $i++) { echo "<th>Ngày $i</th>"; } ?>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="nhanvien_filter">
                                <?php foreach ($data_all as $row) { ?>
                                    <tr data-id="<?php echo $row['id']; ?>">
                                        <td><?php echo htmlspecialchars($row['ten_nv']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ngay_vao_lam']." / ".$row['ngay_kt_thuviec']); ?></td>
                                        <td data-field="so_ngay_nam_cu"><?php echo $row['so_ngay_nam_cu']; ?></td>
                                        <td data-field="so_ngay_nam_hien_tai"><?php echo $row['so_ngay_nam_hien_tai']; ?></td>
                                        <td data-field="so_ngay_con_lai"><?php echo $row['so_ngay_nam_cu']+ $row['so_ngay_nam_hien_tai']- $row['so_ngay_da_nghi']; ?></td>
                                        <?php for ($i = 0; $i < $max_ngay_nghi; $i++) { ?>
                                            <td class="editable" data-field="ngay_nghi_<?=$i?>"><?php echo isset($row['ngay_nghi'][$i]) ? $row['ngay_nghi'][$i] : ''; ?></td>
                                        <?php } ?>
                                        <td>
                                            <button class="btn btn-primary btn-sm edit-row">Sửa</button>
                                            <button class="btn btn-success btn-sm save-row" style="display:none;">Lưu</button>
                                            <button class="btn btn-danger btn-sm cancel-row" style="display:none;">Hủy</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php    
} else {
    echo "Không có dữ liệu.";
}

// Đóng kết nối
$conn->close();
?>
    <?php include('../layouts/footer.php'); ?>
    <script>
$(document).ready(function () {

    // Use event delegation for dynamic elements
    $('#example1').on('click', '.edit-row', function() {
        var row = $(this).closest('tr');
        row.find('.editable').each(function() {
            var content = $(this).text().trim();
            $(this).data('original', content);
            if($(this).data('field').includes('ngay_nghi_')) {
                $(this).html('<input type="date" class="form-control" value="' + content + '">');
            } else {
                $(this).html('<input type="number" class="form-control" value="' + content + '">');
            }
        });
        row.find('.edit-row').hide();
        row.find('.save-row, .cancel-row').show();
    });

    $('#example1').on('click', '.save-row', function() {
        var row = $(this).closest('tr');
        var id = row.data('id');
        var ngayNghi = [];
        var data = {
            nhanvien_id: id,
            so_ngay_nam_cu: row.find('[data-field="so_ngay_nam_cu"] input').val(),
            so_ngay_nam_hien_tai: row.find('[data-field="so_ngay_nam_hien_tai"] input').val(),
            so_ngay_con_lai: row.find('[data-field="so_ngay_con_lai"] input').val()
        };

        // Collect leave dates
        row.find('[data-field^="ngay_nghi_"]').each(function() {
            var value = $(this).find('input').val();
            if(value) {
                ngayNghi.push(value);
            }
        });
        data.ngay_nghi = ngayNghi;

        // Send AJAX request
        $.ajax({
            url: 'update_nghiphep.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    row.find('.editable').each(function() {
                        var value = $(this).find('input').val();
                        $(this).html(value || '');
                    });
                    row.find('.save-row, .cancel-row').hide();
                    row.find('.edit-row').show();
                    alert('Cập nhật thành công!');
                    location.reload(); // Reload to update calculated fields
                } else {
                    alert('Lỗi: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Đã xảy ra lỗi: ' + error);
            }
        });
    });

    $('#example1').on('click', '.cancel-row', function() {
        var row = $(this).closest('tr');
        row.find('.editable').each(function() {
            var originalContent = $(this).data('original');
            $(this).html(originalContent);
        });
        row.find('.save-row, .cancel-row').hide();
        row.find('.edit-row').show();
    });
});
</script>
<script>
        function filterTable() {
            let timkiem = document.getElementById("timkiem").value.toLowerCase();
            let mprFilter = document.getElementById("mprFilter").value.toLowerCase();
            let table = document.getElementById("dataTable");
            let rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

            for (let row of rows) {
                let pjtText = row.cells[1].innerText.toLowerCase();
                let mprText = row.cells[3].innerText.toLowerCase();
                row.style.display = (pjtText.includes(pjtFilter) && mprText.includes(mprFilter)) ? "" : "none";
            }
        }

        function exportToExcel() {
            let table = document.getElementById("dataTable");
            let wb = XLSX.utils.book_new();
            let ws = XLSX.utils.table_to_sheet(table);

            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
            XLSX.writeFile(wb, "Material_Table.xlsx");
        }
</script>