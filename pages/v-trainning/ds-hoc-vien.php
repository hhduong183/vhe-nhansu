<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

if(isset($_SESSION['username']) && isset($_SESSION['level'])) {
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Get course info
        $query_kh = "SELECT * FROM khoa_hoc WHERE id = '$id'";
        $result_kh = mysqli_query($conn, $query_kh);
        $course = mysqli_fetch_array($result_kh);

        // Get students list
        $query = "SELECT khnv.*, nv.ma_nv, nv.ten_nv, nv.chuc_vu
                 FROM khoa_hoc_nhan_vien khnv
                 LEFT JOIN nhanvien nv ON khnv.id_nhan_vien = nv.id
                 WHERE khnv.id_khoa_hoc = '$id'
                 ORDER BY khnv.ngay_bat_dau DESC";
        $result = mysqli_query($conn, $query);
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Danh sách học viên</h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
            <li><a href="ds-khoa-hoc.php">Khóa học</a></li>
            <li><a href="chi-tiet-khoa-hoc.php?id=<?php echo $id; ?>">Chi tiết khóa học</a></li>
            <li class="active">Danh sách học viên</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Danh sách học viên - <?php echo $course['ten_khoa_hoc']; ?></h3>
                    </div>
                    <div class="box-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Mã NV</th>
                                    <th>Họ tên</th>
                                    <th>Chức vụ</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Tiến độ</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if(mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                ?>
                                    <tr>
                                        <td><?php echo $row['ma_nv']; ?></td>
                                        <td><?php echo $row['ten_nv']; ?></td>
                                        <td><?php echo $row['chuc_vu']; ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($row['ngay_bat_dau'])); ?></td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar progress-bar-primary" style="width: <?php echo $row['tien_do']; ?>%"></div>
                                            </div>
                                            <span class="badge bg-light"><?php echo $row['tien_do']; ?>%</span>
                                        </td>
                                        <td>
                                            <?php if($row['trang_thai'] == 'Hoàn thành'): ?>
                                                <span class="label label-success">Hoàn thành</span>
                                            <?php elseif($row['trang_thai'] == 'Đang học'): ?>
                                                <span class="label label-primary">Đang học</span>
                                            <?php else: ?>
                                                <span class="label label-default">Chưa bắt đầu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($_SESSION['level'] == 1): ?>
                                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-update-<?php echo $row['id']; ?>">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Update Progress Modal -->
                                    <div class="modal fade" id="modal-update-<?php echo $row['id']; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                    <h4 class="modal-title">Cập nhật tiến độ</h4>
                                                </div>
                                                <form action="cap-nhat-tien-do.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <div class="form-group">
                                                            <label>Tiến độ (%)</label>
                                                            <input type="number" class="form-control" name="tienDo" value="<?php echo $row['tien_do']; ?>" min="0" max="100">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Trạng thái</label>
                                                            <select class="form-control" name="trangThai">
                                                                <option value="Chưa bắt đầu" <?php if($row['trang_thai'] == 'Chưa bắt đầu') echo 'selected'; ?>>Chưa bắt đầu</option>
                                                                <option value="Đang học" <?php if($row['trang_thai'] == 'Đang học') echo 'selected'; ?>>Đang học</option>
                                                                <option value="Hoàn thành" <?php if($row['trang_thai'] == 'Hoàn thành') echo 'selected'; ?>>Hoàn thành</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-primary" name="save">Lưu thay đổi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
    }
    include(ROOT_PATH . '/layouts/footer.php');
}
else {
    header('Location: ' . BASE_URL . '/pages/dang-nhap.php');
}
?>