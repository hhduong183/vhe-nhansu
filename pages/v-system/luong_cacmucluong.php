<?php
// Kết nối cơ sở dữ liệu
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

  // include file
  include(ROOT_PATH . '/layouts/header.php');
  include(ROOT_PATH . '/layouts/topbar.php');
  include(ROOT_PATH . '/layouts/sidebar.php');


// Truy vấn dữ liệu từ bảng luong_mucluong
$sql = "SELECT bac_luong, muc_luong FROM luong_mucluong ORDER BY bac_luong;";
$result = $conn->query($sql);

// Mảng lưu dữ liệu lương theo bậc lương
$salaries = array();
while ($row = $result->fetch_assoc()) {
    $salaries[$row['bac_luong']] = $row['muc_luong'];
}

// Danh sách bậc lương từ A-01 đến L-30
$columns = range('A', 'L');
$rows = range(1, 30);
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Thang bảng Lương</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="salaryTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Bậc Lương</th>
                                        <?php foreach ($columns as $col) { ?>
                                            <th><?php echo $col; ?></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row) { ?>
                                        <tr>
                                            <td><strong><?php echo str_pad($row, 2, '0', STR_PAD_LEFT); ?></strong></td>
                                            <?php foreach ($columns as $col) { 
                                                $key = "$col-" . str_pad($row, 2, '0', STR_PAD_LEFT);
                                            ?>
                                                <td><?php echo isset($salaries[$key]) ? number_format($salaries[$key]) . " VND" : "-"; ?></td>
                                            <?php } ?>
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

<script>
    $(document).ready(function() {
        $('#salaryTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print"],
            "paging": true,
            "searching": true,
            "scrollX": true
        });
    });
</script>

<?php
  // include
  include(ROOT_PATH . '/layouts/footer.php');

?>