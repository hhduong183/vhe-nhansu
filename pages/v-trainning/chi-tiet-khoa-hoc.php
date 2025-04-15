<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

if(isset($_SESSION['username']) && isset($_SESSION['level'])) {
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Fetch course details
        $query = "SELECT kh.*, pb.ten_phong_ban, tn.ten_nhom, nv.ten_nv as nguoi_tao_ten
                 FROM khoa_hoc kh
                 LEFT JOIN phong_ban pb ON kh.phong_ban = pb.ma_phong_ban
                 LEFT JOIN to_nhom tn ON kh.bo_phan = tn.id
                 LEFT JOIN nhanvien nv ON kh.nguoi_tao = nv.id
                 WHERE kh.id = $id";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($conn));
        }
        $course = mysqli_fetch_array($result);

        // Fetch course materials
        $query_tl = "SELECT * FROM tai_lieu_khoa_hoc WHERE ma_khoa_hoc = '$id' ORDER BY ngay_tao DESC";
        $result_tl = mysqli_query($conn, $query_tl);
        if (!$result_tl) {
            die("Query failed: " . mysqli_error($conn));
        }

        // Fetch course progress
        $query_td = "SELECT 
                    COUNT(*) as total_employees,
                    SUM(CASE WHEN trang_thai = 'Hoàn thành' THEN 1 ELSE 0 END) as completed,
                    COALESCE(AVG(NULLIF(tien_do, 0)), 0) as avg_progress
                    FROM khoa_hoc_nhan_vien
                    -- WHERE ma_khoa_hoc = '$id'";
        $result_td = mysqli_query($conn, $query_td);
        if (!$result_td) {
            die("Query failed: " . mysqli_error($conn));
        }
        $progress = mysqli_fetch_array($result_td);
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Chi tiết khóa học</h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
            <li><a href="ds-khoa-hoc.php">Khóa học</a></li>
            <li class="active">Chi tiết khóa học</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <!-- Course Details -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Thông tin khóa học</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px">Mã khóa học</th>
                                <td><?php echo $course['ma_khoa_hoc']; ?></td>
                            </tr>
                            <tr>
                                <th>Tên khóa học</th>
                                <td><?php echo $course['ten_khoa_hoc']; ?></td>
                            </tr>
                            <tr>
                                <th>Phòng ban</th>
                                <td><?php echo $course['ten_phong_ban']; ?></td>
                            </tr>
                            <tr>
                                <th>Bộ phận</th>
                                <td><?php echo $course['ten_nhom']; ?></td>
                            </tr>
                            <tr>
                                <th>Trạng thái</th>
                                <td>
                                    <?php 
                                    if($course['trang_thai'] == 'Active')
                                        echo '<span class="label label-success">Đang hoạt động</span>';
                                    else
                                        echo '<span class="label label-danger">Không hoạt động</span>';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Mô tả</th>
                                <td><?php echo $course['mo_ta']; ?></td>
                            </tr>
                            <tr>
                                <th>Người tạo</th>
                                <td><?php echo $course['nguoi_tao_ten']; ?></td>
                            </tr>
                            <tr>
                                <th>Ngày tạo</th>
                                <td><?php echo date('d-m-Y H:i:s', strtotime($course['ngay_tao'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Course Materials -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tài liệu khóa học</h3>
                        <?php 
                        if($_SESSION['level'] == 1) {
                            echo '<div class="box-tools pull-right">
                                    <a href="them-tai-lieu.php?id='.$id.'" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> Thêm tài liệu
                                    </a>
                                </div>';
                        }
                        ?>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tên tài liệu</th>
                                    <th>Mô tả</th>
                                    <th>Loại</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while($row_tl = mysqli_fetch_array($result_tl)) {
                                    echo '<tr>';
                                    echo '<td>'.$row_tl['ten_tai_lieu'].'</td>';
                                    echo '<td>'.$row_tl['mo_ta'].'</td>';
                                    echo '<td>'.$row_tl['loai_tai_lieu'].'</td>';
                                    echo '<td>'.date('d-m-Y', strtotime($row_tl['ngay_tao'])).'</td>';
                                    echo '<td>
                                            <a href="'.ROOT_PATH.'/uploads/training/'.$row_tl['file_path'].'" class="btn btn-info btn-sm" target="_blank">
                                                <i class="fa fa-download"></i> Tải về
                                            </a>
                                          </td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Course Progress -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tiến độ khóa học</h3>
                    </div>
                    <div class="box-body">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tổng số học viên</span>
                                <span class="info-box-number"><?php echo $progress['total_employees']; ?></span>
                            </div>
                        </div>
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Đã hoàn thành</span>
                                <span class="info-box-number"><?php echo $progress['completed']; ?></span>
                            </div>
                        </div>
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tiến độ trung bình</span>
                                <span class="info-box-number"><?php echo round($progress['avg_progress'], 2); ?>%</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $progress['avg_progress']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Thao tác nhanh</h3>
                    </div>
                    <div class="box-body">
                        <?php if($_SESSION['level'] == 1) { ?>
                        <a href="sua-khoa-hoc.php?id=<?php echo $id; ?>" class="btn btn-warning btn-block">
                            <i class="fa fa-edit"></i> Chỉnh sửa khóa học
                        </a>
                        <a href="phan-cong-khoa-hoc.php" class="btn btn-info btn-block">
                            <i class="fa fa-user-plus"></i> Gán học viên
                        </a>
                        <?php } ?>
                        <a href="ds-hoc-vien.php?id=<?php echo $id; ?>" class="btn btn-success btn-block">
                            <i class="fa fa-users"></i> Xem danh sách học viên
                        </a>
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