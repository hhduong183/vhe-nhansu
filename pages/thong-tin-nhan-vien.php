<?php 

// Tạo session
session_start();
    // Include file cần thiết
    include('../layouts/header.php');
    include('../layouts/topbar.php');
    include('../layouts/sidebar.php');
    include('../plugins/function.php');
    require "../config.php";
    
if (isset($_SESSION['username']) && isset($_SESSION['level']) && isset($_GET['id'])) {
    $id_url = decryptId($_GET['id']);
    $id_url = (int) $id_url;
    $idNhanVien = (int) $_SESSION['idNhanVien'];
        // Kiểm tra ID trong URL có khớp với ID của user hiện tại không
        if ($id_url !== $idNhanVien && $_SESSION['level']==0) {
            echo "<div style='text-align:center; color:red;padding:50px;'>❌ Bạn không có quyền truy cập thông tin của người khác. </div>";
            // var_dump($_SESSION['idNhanVien'], $id_url , $_SESSION['level'] );
            exit;
            // Hoặc chuyển hướng: header("Location: error.php"); exit;
        } else {

        // Chuẩn bị truy vấn thông tin nhân viên
        $stmt = $conn->prepare("SELECT 
            nv.id as id, ma_nv, hinh_anh, ten_nv, gioi_tinh, nv.ngay_tao as ngay_tao, 
            ngay_sinh, so_cmnd, ten_tinh_trang, ngay_cap_cmnd, noi_cap_cmnd, nguyen_quan, 
            ten_quoc_tich, ten_dan_toc, ten_ton_giao, ho_khau, ten_loai_nv, 
            ten_trinh_do, ten_chuyen_mon, ten_bang_cap, ten_phong_ban, ten_chuc_vu, ngay_vao_lam, so_dth, trang_thai, ma_so_thue
        FROM nhanvien nv
        JOIN quoc_tich qt ON nv.quoc_tich_id = qt.id
        JOIN dan_toc dt ON nv.dan_toc_id = dt.id
        JOIN ton_giao tg ON nv.ton_giao_id = tg.id
        JOIN loai_nv lnv ON nv.loai_nv_id = lnv.id
        JOIN trinh_do td ON nv.trinh_do_id = td.id
        JOIN chuyen_mon cm ON nv.chuyen_mon_id = cm.id
        JOIN bang_cap bc ON nv.bang_cap_id = bc.id
        JOIN phong_ban pb ON nv.phong_ban_id = pb.id
        JOIN chuc_vu cv ON nv.chuc_vu_id = cv.id
        JOIN tinh_trang_hon_nhan hn ON nv.hon_nhan_id = hn.id
        WHERE nv.id = ?");
        
        $stmt->bind_param("i", $id_url);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Truy vấn lịch sử khen thưởng - kỷ luật
        $ktkl = $conn->prepare("SELECT ma_kt, so_qd, ngay_qd, ten_khen_thuong, hinh_thuc, so_tien, flag, ghi_chu
            FROM khen_thuong_ky_luat 
            WHERE nhanvien_id = ?
            ORDER BY ngay_qd DESC");

        $ktkl->bind_param("i", $id_url);
        $ktkl->execute();
        $result2 = $ktkl->get_result();

        // Chia thành 2 danh sách riêng: Khen thưởng (flag = 1), Kỷ luật (flag = 0)
        $khenThuong = [];
        $kyLuat = [];

        while ($row2 = $result2->fetch_assoc()) {
            if ($row2['flag'] == 1) {
                $khenThuong[] = $row2;
            } else {
                $kyLuat[] = $row2;
            }
        }
    } 
} else {
    echo "<div style='text-align:center; color:red;padding:50px;'>❌ Bạn chưa đăng nhập!</div>";
    exit;
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Thông tin nhân viên</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Tổng quan</a></li>
                        <?php if(isset($_SESSION['level']) && $_SESSION['level'] == 1): ?>
                            <li class="breadcrumb-item"><a href="danh-sach-nhan-vien.php?p=staff&a=list-staff">Danh sách nhân viên</a></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active">Thông tin nhân viên</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Profile Card -->
            <!-- Add this style section after the PHP code and before the content-wrapper div -->
            <style>
                .profile-section {
                    margin-bottom: 20px;
                }
                
                .profile-image img {
                    max-width: 100%;
                    height: auto;
                }
                
                .info-list dt {
                    font-weight: 600;
                }
                
                .table-card {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }
                
                @media screen and (max-width: 768px) {
                    .profile-image {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    
                    .info-list dt {
                        float: left;
                        width: 40%;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }
                    
                    .info-list dd {
                        margin-left: 42%;
                        word-break: break-word;
                    }
                    
                    .table-responsive {
                        margin: 0;
                        border: none;
                    }
                    
                    .mobile-table thead {
                        display: none;
                    }
                    
                    .mobile-table tr {
                        display: block;
                        border: 1px solid #dee2e6;
                        margin-bottom: 10px;
                        border-radius: 4px;
                    }
                    
                    .mobile-table td {
                        display: block;
                        text-align: right;
                        padding: 8px 10px;
                        border: none;
                        border-bottom: 1px solid #eee;
                    }
                    
                    .mobile-table td:last-child {
                        border-bottom: none;
                    }
                    
                    .mobile-table td:before {
                        content: attr(data-label);
                        float: left;
                        font-weight: bold;
                        color: #495057;
                    }
                    
                    .card-body {
                        padding: 10px;
                    }
                }
            </style>
            
            <!-- Update the profile card structure -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Mã nhân viên: <?php echo $row['ma_nv']; ?></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-2 col-md-3 col-sm-4">
                            <div class="profile-image">
                                <img src="../uploads/staffs/<?php echo $row['hinh_anh']; ?>" class="img-fluid rounded">
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-4 col-sm-8">
                            <dl class="row info-list">
                                <dt class="col-sm-4">Tên nhân viên:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_nv']; ?></dd>

                                <dt class="col-sm-4">Giới tính:</dt>
                                <dd class="col-sm-8"><?php echo ($row['gioi_tinh'] == 1) ? "Nam" : "Nữ"; ?></dd>

                                <dt class="col-sm-4">Ngày sinh:</dt>
                                <dd class="col-sm-8"><?php $date = date_create($row['ngay_sinh']); echo date_format($date, 'd-m-Y'); ?></dd>

                                <dt class="col-sm-4">Tình trạng HN:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_tinh_trang']; ?></dd>

                                <dt class="col-sm-4">Số CCCD:</dt>
                                <dd class="col-sm-8"><?php echo $row['so_cmnd']; ?></dd>

                                <dt class="col-sm-4">Ngày cấp:</dt>
                                <dd class="col-sm-8"><?php $ngayCap = date_create($row['ngay_cap_cmnd']); echo date_format($ngayCap, 'd-m-Y'); ?></dd>

                                <dt class="col-sm-4">Nơi cấp:</dt>
                                <dd class="col-sm-8"><?php echo $row['noi_cap_cmnd']; ?></dd>

                                <dt class="col-sm-4">Nguyên quán:</dt>
                                <dd class="col-sm-8"><?php echo $row['nguyen_quan']; ?></dd>

                                <dt class="col-sm-4">Quốc tịch:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_quoc_tich']; ?></dd>

                                <dt class="col-sm-4">Dân tộc:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_dan_toc']; ?></dd>

                                <dt class="col-sm-4">Tôn giáo:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_ton_giao']; ?></dd>
                            </dl>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12">
                            <dl class="row">
                                <dt class="col-sm-4">Số điện thoại:</dt>
                                <dd class="col-sm-8"><?php echo $row['so_dth']; ?></dd>

                                <dt class="col-sm-4">Nơi ở hiện tại:</dt>
                                <dd class="col-sm-8"><?php echo $row['ho_khau']; ?></dd>

                                <dt class="col-sm-4">Loại nhân viên:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_loai_nv']; ?></dd>

                                <dt class="col-sm-4">Trình độ:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_trinh_do']; ?></dd>

                                <dt class="col-sm-4">Chuyên môn:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_chuyen_mon']; ?></dd>

                                <dt class="col-sm-4">Bằng cấp:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_bang_cap']; ?></dd>

                                <dt class="col-sm-4">Phòng ban:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_phong_ban']; ?></dd>

                                <dt class="col-sm-4">Chức vụ:</dt>
                                <dd class="col-sm-8"><?php echo $row['ten_chuc_vu']; ?></dd>

                                <dt class="col-sm-4">Ngày vào làm:</dt>
                                <dd class="col-sm-8"><?php echo $row['ngay_vao_lam']; ?></dd>

                                <dt class="col-sm-4">Trạng thái:</dt>
                                <dd class="col-sm-8">
                                    <?php if($row['trang_thai'] == 1): ?>
                                        <span class="badge badge-success">Đang làm việc</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Đã nghỉ việc</span>
                                    <?php endif; ?>
                                </dd>

                                <dt class="col-sm-4">Mã số thuế:</dt>
                                <dd class="col-sm-8 badge-danger"><?php echo $row['ma_so_thue']; ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rewards Card -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Khen thưởng</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Mã KT</th>
                                <th>Số QĐ</th>
                                <th>Ngày QĐ</th>
                                <th>Tên Khen Thưởng</th>
                                <th>Hình thức</th>
                                <th>Số tiền</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($khenThuong as $kt): ?>
                            <tr>
                                <td><?= $kt['ma_kt'] ?></td>
                                <td><?= $kt['so_qd'] ?></td>
                                <td><?= $kt['ngay_qd'] ?></td>
                                <td><?= $kt['ten_khen_thuong'] ?></td>
                                <td><?= $kt['hinh_thuc'] ?></td>
                                <td><?= number_format($kt['so_tien'], 0, ',', '.') ?> VND</td>
                                <td><?= $kt['ghi_chu'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Discipline Card -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Kỷ luật</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Mã KT</th>
                                <th>Số QĐ</th>
                                <th>Ngày QĐ</th>
                                <th>Tên Kỷ Luật</th>
                                <th>Hình thức</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kyLuat as $kl): ?>
                            <tr>
                                <td><?= $kl['ma_kt'] ?></td>
                                <td><?= $kl['so_qd'] ?></td>
                                <td><?= $kl['ngay_qd'] ?></td>
                                <td><?= $kl['ten_khen_thuong'] ?></td>
                                <td><?= $kl['hinh_thuc'] ?></td>
                                <td><?= $kl['ghi_chu'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include('../layouts/footer.php'); ?>