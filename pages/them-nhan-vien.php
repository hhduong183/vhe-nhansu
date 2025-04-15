<?php
    require '../config.php';
// create session
session_start();
$level = isset($_SESSION['level']) ? $_SESSION['level'] : 0;
if(isset($_SESSION['username']) && isset($_SESSION['level']))
{
    //include file
    include('../layouts/header.php');
    include('../layouts/topbar.php');
    include('../layouts/sidebar.php');


    // Lấy dữ liệu từ các bảng liên quan
    $queries = [
        'quocTich' => "SELECT id, ten_quoc_tich FROM quoc_tich",
        'tonGiao' => "SELECT id, ten_ton_giao FROM ton_giao",
        'danToc' => "SELECT id, ten_dan_toc FROM dan_toc",
        'loaiNhanVien' => "SELECT id, ten_loai_nv FROM loai_nv",
        'trinhDo' => "SELECT id, ten_trinh_do FROM trinh_do",
        'chuyenMon' => "SELECT id, ten_chuyen_mon FROM chuyen_mon",
        'bangCap' => "SELECT id, ten_bang_cap FROM bang_cap",
        'phongBan' => "SELECT id, ten_phong_ban FROM phong_ban",
        'toNhom' => "SELECT id, ten_nhom FROM to_nhom",
        'chucVu' => "SELECT id, ten_chuc_vu FROM chuc_vu",
        'honNhan' => "SELECT id, ten_tinh_trang FROM tinh_trang_hon_nhan"
    ];

    $data = [];
    foreach ($queries as $key => $query) {
        $result = mysqli_query($conn, $query);
        $data[$key] = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Xử lý thêm nhân viên
    if(isset($_POST['save'])) {
        $error = array();
        $success = array();
        $showMess = false;

        // Validate dữ liệu
        $requiredFields = [
            'tenNhanVien' => 'Tên nhân viên',
            'maNhanVien' => 'Mã nhân viên',
            'soDienthoai' => 'Số điện thoại',
            'CMND' => 'CMND/CCCD',
            'ngayCap' => 'Ngày cấp',
            'noiCap' => 'Nơi cấp',
            'hoKhau' => 'Hộ khẩu',
            'ngaySinh' => 'Ngày sinh',
            'ngayVaolam' => 'Ngày vào làm'
        ];

        $selectFields = [
            'honNhan' => 'Tình trạng hôn nhân',
            'quocTich' => 'Quốc tịch',
            'danToc' => 'Dân tộc',
            'tonGiao' => 'Tôn giáo',
            'loaiNhanVien' => 'Loại nhân viên',
            'bangCap' => 'Bằng cấp',
            'trangThai' => 'Trạng thái',
            'gioiTinh' => 'Giới tính',
            'phongBan' => 'Phòng ban',
            'toNhom' => 'Tổ/Nhóm',
            'chucVu' => 'Chức vụ',
            'trinhDo' => 'Trình độ',
            'chuyenMon' => 'Chuyên môn'
        ];

        // Kiểm tra các trường bắt buộc
        foreach ($requiredFields as $field => $label) {
            if (empty($_POST[$field])) {
                $error[$field] = "Vui lòng nhập $label";
            }
        }

        // Kiểm tra các trường select
        foreach ($selectFields as $field => $label) {
            if ($_POST[$field] == 'chon') {
                $error[$field] = "Vui lòng chọn $label";
            }
        }

        // Kiểm tra mã nhân viên đã tồn tại chưa
        if (!isset($error['maNhanVien'])) {
            $maNhanVien = $_POST['maNhanVien'];
            $query = "SELECT COUNT(*) AS count FROM nhanvien WHERE ma_nv = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $maNhanVien);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if ($row['count'] > 0) {
                $error['maNhanVien'] = 'Mã nhân viên đã tồn tại';
            }
            mysqli_stmt_close($stmt);
        }

        // Xử lý upload ảnh
        $hinhAnh = 'demo-3x4.jpg'; // Ảnh mặc định
        if ($_FILES['hinhAnh']['size'] > 0) {
            $target_dir = "../uploads/staffs/";
            $hinhAnh = basename($_FILES["hinhAnh"]["name"]);
            $target_file = $target_dir . $hinhAnh;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Kiểm tra định dạng file
            $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($imageFileType, $allowedTypes)) {
                $error['hinhAnh'] = 'Chỉ chấp nhận file ảnh dạng JPG, JPEG, PNG & GIF';
            }
            // Kiểm tra kích thước file
            elseif ($_FILES["hinhAnh"]["size"] > 5000000) {
                $error['hinhAnh'] = 'File ảnh không được lớn hơn 5MB';
            }
            // Upload file
            elseif (!move_uploaded_file($_FILES["hinhAnh"]["tmp_name"], $target_file)) {
                $error['hinhAnh'] = 'Có lỗi xảy ra khi upload ảnh';
            }
        }

        // Nếu không có lỗi thì thêm vào database
        if (empty($error)) {
            $matkhau = password_hash($_POST['soDienthoai'], PASSWORD_BCRYPT);
            $ngayTao = date("Y-m-d H:i:s");

            $query = "INSERT INTO nhanvien (ma_nv, ten_nv, gioi_tinh, hinh_anh, ngay_sinh, nguyen_quan, 
                    so_cmnd, ngay_cap_cmnd, noi_cap_cmnd, ho_khau, so_dth, quoc_tich_id, ton_giao_id, dan_toc_id, 
                    hon_nhan_id, loai_nv_id, trinh_do_id, chuyen_mon_id, bang_cap_id, phong_ban_id, to_nhom_id, 
                    chuc_vu_id, trang_thai, nguoi_tao_id, ngay_tao, ngay_vao_lam, user_name, mat_khau,
                    ma_so_thue, so_bhxh) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $query);
            if ($stmt === false) {
                $error['error'] = 'Lỗi chuẩn bị câu lệnh: ' . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($stmt, "ssssssssssssiiiiiiiiiiiissssss",
                    $_POST['maNhanVien'],
                    $_POST['tenNhanVien'],
                    $_POST['gioiTinh'],
                    $hinhAnh,
                    $_POST['ngaySinh'],
                    $_POST['nguyenQuan'],
                    $_POST['CMND'],
                    $_POST['ngayCap'],
                    $_POST['noiCap'],
                    $_POST['hoKhau'],
                    $_POST['soDienthoai'],
                    $_POST['quocTich'],
                    $_POST['tonGiao'],
                    $_POST['danToc'],
                    $_POST['honNhan'],
                    $_POST['loaiNhanVien'],
                    $_POST['trinhDo'],
                    $_POST['chuyenMon'],
                    $_POST['bangCap'],
                    $_POST['phongBan'],
                    $_POST['toNhom'],
                    $_POST['chucVu'],
                    $_POST['trangThai'],
                    $row_acc['id'],
                    $ngayTao,
                    $_POST['ngayVaolam'],
                    $_POST['maNhanVien'],
                    $matkhau,
                    $_POST['maSoThue'],
                    $_POST['soBHXH']
            );

            if (mysqli_stmt_execute($stmt)) {
                $success['success'] = 'Thêm nhân viên thành công';
                $showMess = true;
                echo '<script>setTimeout("window.location=\'them-nhan-vien.php?p=staff&a=add-staff\'",1000);</script>';
            } else {
                $error['error'] = 'Có lỗi xảy ra: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
    }
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Thêm nhân viên mới</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="danh-sach-nhan-vien.php?p=staff&a=list-staff">Nhân viên</a></li>
                        <li class="breadcrumb-item active">Thêm nhân viên</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php
            if (isset($error) && !empty($error)) {
                echo "<div class='alert alert-danger alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                        <h4><i class='icon fa fa-warning'></i> Lỗi!</h4>";
                foreach ($error as $err) {
                    echo $err . "<br/>";
                }
                echo "</div>";
            }
            if (isset($success) && !empty($success)) {
                echo "<div class='alert alert-success alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                        <h4><i class='icon fa fa-check'></i> Thành công!</h4>";
                foreach ($success as $succ) {
                    echo $succ . "<br/>";
                }
                echo "</div>";
            }
            ?>

            <form method="POST" enctype="multipart/form-data" id="addStaffForm">
                <div class="row">
                    <!-- Thông tin cơ bản -->
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin cơ bản</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Mã nhân viên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="maNhanVien" placeholder="Nhập mã nhân viên"
                                           value="<?= isset($_POST['maNhanVien']) ? htmlspecialchars($_POST['maNhanVien']) : '' ?>">
                                </div>
                                <div class="form-group">
                                    <label>Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tenNhanVien" placeholder="Nhập họ và tên"
                                           value="<?= isset($_POST['tenNhanVien']) ? htmlspecialchars($_POST['tenNhanVien']) : '' ?>">
                                </div>
                                <div class="form-group">
                                    <label>Giới tính <span class="text-danger">*</span></label>
                                    <select class="form-control" name="gioiTinh">
                                        <option value="chon">-- Chọn giới tính --</option>
                                        <option value="1" <?= isset($_POST['gioiTinh']) && $_POST['gioiTinh'] == '1' ? 'selected' : '' ?>>Nam</option>
                                        <option value="0" <?= isset($_POST['gioiTinh']) && $_POST['gioiTinh'] == '0' ? 'selected' : '' ?>>Nữ</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ngày sinh <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="ngaySinh"
                                           value="<?= isset($_POST['ngaySinh']) ? $_POST['ngaySinh'] : '' ?>">
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="soDienthoai" placeholder="Nhập số điện thoại"
                                           value="<?= isset($_POST['soDienthoai']) ? htmlspecialchars($_POST['soDienthoai']) : '' ?>">
                                </div>
                                <div class="form-group">
                                    <label>Ảnh 3x4</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="hinhAnh" accept="image/*">
                                            <label class="custom-file-label">Chọn file ảnh</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin công việc -->
                    <div class="col-md-6">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin công việc</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Phòng ban <span class="text-danger">*</span></label>
                                    <select class="form-control" name="phongBan">
                                        <option value="chon">-- Chọn phòng ban --</option>
                                        <?php foreach ($data['phongBan'] as $pb): ?>
                                            <option value="<?= $pb['id'] ?>" <?= isset($_POST['phongBan']) && $_POST['phongBan'] == $pb['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($pb['ten_phong_ban']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tổ/Nhóm <span class="text-danger">*</span></label>
                                    <select class="form-control" name="toNhom">
                                        <option value="chon">-- Chọn tổ/nhóm --</option>
                                        <?php foreach ($data['toNhom'] as $tn): ?>
                                            <option value="<?= $tn['id'] ?>" <?= isset($_POST['toNhom']) && $_POST['toNhom'] == $tn['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tn['ten_nhom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Chức vụ <span class="text-danger">*</span></label>
                                    <select class="form-control" name="chucVu">
                                        <option value="chon">-- Chọn chức vụ --</option>
                                        <?php foreach ($data['chucVu'] as $cv): ?>
                                            <option value="<?= $cv['id'] ?>" <?= isset($_POST['chucVu']) && $_POST['chucVu'] == $cv['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cv['ten_chuc_vu']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Loại nhân viên <span class="text-danger">*</span></label>
                                    <select class="form-control" name="loaiNhanVien">
                                        <option value="chon">-- Chọn loại nhân viên --</option>
                                        <?php foreach ($data['loaiNhanVien'] as $lnv): ?>
                                            <option value="<?= $lnv['id'] ?>" <?= isset($_POST['loaiNhanVien']) && $_POST['loaiNhanVien'] == $lnv['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lnv['ten_loai_nv']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Trạng thái <span class="text-danger">*</span></label>
                                    <select class="form-control" name="trangThai">
                                        <option value="chon">-- Chọn trạng thái --</option>
                                        <option value="1" <?= isset($_POST['trangThai']) && $_POST['trangThai'] == '1' ? 'selected' : '' ?>>Đang làm việc</option>
                                        <option value="0" <?= isset($_POST['trangThai']) && $_POST['trangThai'] == '0' ? 'selected' : '' ?>>Đã nghỉ việc</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ngày vào làm <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="ngayVaolam"
                                           value="<?= isset($_POST['ngayVaolam']) ? $_POST['ngayVaolam'] : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin cá nhân -->
                    <div class="col-md-12">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin cá nhân</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label>CMND/CCCD <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="CMND" placeholder="Nhập số CMND/CCCD"
                                                    value="<?= isset($_POST['CMND']) ? htmlspecialchars($_POST['CMND']) : '' ?>">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Ngày cấp <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="ngayCap"
                                                    value="<?= isset($_POST['ngayCap']) ? $_POST['ngayCap'] : '' ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Nơi cấp <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="noiCap" placeholder="Nhập nơi cấp"
                                                   value="<?= isset($_POST['noiCap']) ? htmlspecialchars($_POST['noiCap']) : '' ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Nguyên quán <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nguyenQuan" placeholder="Nhập nguyên quán"
                                                   value="<?= isset($_POST['nguyenQuan']) ? htmlspecialchars($_POST['nguyenQuan']) : '' ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Nơi ở hiện tại <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="hoKhau" placeholder="Nhập nơi ở hiện tại"
                                                   value="<?= isset($_POST['hoKhau']) ? htmlspecialchars($_POST['hoKhau']) : '' ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Tình trạng hôn nhân <span class="text-danger">*</span></label>
                                            <select class="form-control" name="honNhan">
                                                <option value="chon">-- Chọn tình trạng hôn nhân --</option>
                                                <?php foreach ($data['honNhan'] as $hn): ?>
                                                    <option value="<?= $hn['id'] ?>" <?= isset($_POST['honNhan']) && $_POST['honNhan'] == $hn['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($hn['ten_tinh_trang']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Quốc tịch <span class="text-danger">*</span></label>
                                            <select class="form-control" name="quocTich">
                                                <option value="chon">-- Chọn quốc tịch --</option>
                                                <?php foreach ($data['quocTich'] as $qt): ?>
                                                    <option value="<?= $qt['id'] ?>" <?= isset($_POST['quocTich']) && $_POST['quocTich'] == $qt['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($qt['ten_quoc_tich']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Tôn giáo <span class="text-danger">*</span></label>
                                            <select class="form-control" name="tonGiao">
                                                <option value="chon">-- Chọn tôn giáo --</option>
                                                <?php foreach ($data['tonGiao'] as $tg): ?>
                                                    <option value="<?= $tg['id'] ?>" <?= isset($_POST['tonGiao']) && $_POST['tonGiao'] == $tg['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($tg['ten_ton_giao']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Dân tộc <span class="text-danger">*</span></label>
                                            <select class="form-control" name="danToc">
                                                <option value="chon">-- Chọn dân tộc --</option>
                                                <?php foreach ($data['danToc'] as $dt): ?>
                                                    <option value="<?= $dt['id'] ?>" <?= isset($_POST['danToc']) && $_POST['danToc'] == $dt['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($dt['ten_dan_toc']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Mã số thuế</label>
                                            <input type="text" class="form-control" name="maSoThue" placeholder="Nhập mã số thuế"
                                                   value="<?= isset($_POST['maSoThue']) ? htmlspecialchars($_POST['maSoThue']) : '' ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Số BHXH</label>
                                            <input type="text" class="form-control" name="soBHXH" placeholder="Nhập số BHXH"
                                                   value="<?= isset($_POST['soBHXH']) ? htmlspecialchars($_POST['soBHXH']) : '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin trình độ -->
                    <div class="col-md-12">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin trình độ</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Trình độ <span class="text-danger">*</span></label>
                                            <select class="form-control" name="trinhDo">
                                                <option value="chon">-- Chọn trình độ --</option>
                                                <?php foreach ($data['trinhDo'] as $td): ?>
                                                    <option value="<?= $td['id'] ?>" <?= isset($_POST['trinhDo']) && $_POST['trinhDo'] == $td['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($td['ten_trinh_do']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Chuyên môn <span class="text-danger">*</span></label>
                                            <select class="form-control" name="chuyenMon">
                                                <option value="chon">-- Chọn chuyên môn --</option>
                                                <?php foreach ($data['chuyenMon'] as $cm): ?>
                                                    <option value="<?= $cm['id'] ?>" <?= isset($_POST['chuyenMon']) && $_POST['chuyenMon'] == $cm['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cm['ten_chuyen_mon']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Bằng cấp <span class="text-danger">*</span></label>
                                            <select class="form-control" name="bangCap">
                                                <option value="chon">-- Chọn bằng cấp --</option>
                                                <?php foreach ($data['bangCap'] as $bc): ?>
                                                    <option value="<?= $bc['id'] ?>" <?= isset($_POST['bangCap']) && $_POST['bangCap'] == $bc['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($bc['ten_bang_cap']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nút submit -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary" name="save">Thêm nhân viên</button>
                            <a href="danh-sach-nhan-vien.php?p=staff&a=list-staff" class="btn btn-default">Quay lại</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
<?php include('../layouts/footer.php'); ?>

<script src="./vjs/validate-them-nhan-vien.js"></script>
