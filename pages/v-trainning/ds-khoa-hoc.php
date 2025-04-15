<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');

if(isset($_SESSION['username']) && isset($_SESSION['level'])) {
    $query = "SELECT k.*, COUNT(kn.id) as so_hoc_vien 
              FROM khoa_hoc k 
              LEFT JOIN khoa_hoc_nhan_vien kn ON k.id = kn.id_khoa_hoc 
              GROUP BY k.id";
    $result = mysqli_query($conn, $query);
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Quản lý khóa học</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Khóa học</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Danh sách khóa học</h3>
                            <div class="card-tools">
                                <a href="them-khoa-hoc.php" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus"></i> Thêm mới
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã khóa học</th>
                                        <th>Tên khóa học</th>
                                        <th>Bộ phận</th>
                                        <th>Số học viên</th>
                                        <th>Trạng thái</th>
                                        <th>Tùy chọn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                if(mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                ?>
                                    <tr>
                                        <td><?php echo $row['ma_khoa_hoc']; ?></td>
                                        <td><?php echo $row['ten_khoa_hoc']; ?></td>
                                        <td><?php echo $row['bo_phan']; ?></td>
                                        <td><?php echo $row['so_hoc_vien']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['trang_thai'] == 'Active' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $row['trang_thai']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="sua-khoa-hoc.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-xs">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="chi-tiet-khoa-hoc.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-xs">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
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
        </div>
    </section>
</div>

<?php
    include(ROOT_PATH . '/layouts/footer.php');
}
else {
    header('Location: ' . BASE_URL . '/pages/dang-nhap.php');
}
?>