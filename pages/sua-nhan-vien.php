<?php
    require '../config.php';
    require '../plugins/function.php';
    
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

        // Lấy thông tin nhân viên cần sửa
        if(isset($_GET['id'])) {
            $id = decryptId($_GET['id']);
            $query = "SELECT * FROM nhanvien WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $employee = mysqli_fetch_assoc($result);
            
            if (!$employee) {
                die("Không tìm thấy nhân viên");
            }
        } else {
            die("Không tìm thấy ID nhân viên");
        }

        // Xử lý cập nhật nhân viên
        if(isset($_POST['save'])) {
            $error = array();
            $success = array();
            $showMess = false;

            // Validate dữ liệu
            $requiredFields = [
                'tenNhanVien' => 'Tên nhân viên',
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

            // Xử lý upload ảnh
            $hinhAnh = $employee['hinh_anh']; // Giữ ảnh cũ
            if ($_FILES['hinhAnh']['size'] > 0) {
                $target_dir = "../uploads/staffs/";
                $imageFileType = strtolower(pathinfo($_FILES["hinhAnh"]["name"], PATHINFO_EXTENSION));
                $hinhAnh = time() . "." . $imageFileType;
                $target_file = $target_dir . $hinhAnh;

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
                elseif (move_uploaded_file($_FILES["hinhAnh"]["tmp_name"], $target_file)) {
                    // Xóa ảnh cũ nếu không phải ảnh mặc định
                    if ($employee['hinh_anh'] != 'demo-3x4.jpg') {
                        @unlink($target_dir . $employee['hinh_anh']);
                    }
                } else {
                    $error['hinhAnh'] = 'Có lỗi xảy ra khi upload ảnh';
                }
            }

            // Nếu không có lỗi thì cập nhật vào database
            if (empty($error)) {
                $ngaySua = date("Y-m-d H:i:s");
                
                $query = "UPDATE nhanvien SET 
                        ten_nv = ?, gioi_tinh = ?, hinh_anh = ?, ngay_sinh = ?, nguyen_quan = ?,
                        so_cmnd = ?, ngay_cap_cmnd = ?, noi_cap_cmnd = ?, ho_khau = ?, so_dth = ?,
                        quoc_tich_id = ?, ton_giao_id = ?, dan_toc_id = ?, hon_nhan_id = ?,
                        loai_nv_id = ?, trinh_do_id = ?, chuyen_mon_id = ?, bang_cap_id = ?,
                        phong_ban_id = ?, to_nhom_id = ?, chuc_vu_id = ?, trang_thai = ?,
                        nguoi_sua_id = ?, ngay_sua = ?, ngay_vao_lam = ?, ma_so_thue = ?, so_bhxh = ?, ngay_kt_thuviec = ?
                        WHERE id = ?";

                $stmt = mysqli_prepare($conn, $query);
                if ($stmt === false) {
                    $error['error'] = 'Lỗi chuẩn bị câu lệnh: ' . mysqli_error($conn);
                } else {
                    mysqli_stmt_bind_param($stmt, "ssssssssssiiiiiiiiiiiiisssssi",
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
                        $_SESSION['idNhanVien'], // Changed from $row_acc['id'] to $_SESSION['idNhanVien']
                        $ngaySua,
                        $_POST['ngayVaolam'],
                        $_POST['maSoThue'],
                        $_POST['soBHXH'],
                        $_POST['ngayKTTV'],
                        $id
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        $success['success'] = 'Cập nhật thông tin thành công';
                        $showMess = true;
                        echo '<script>setTimeout("window.location=\'sua-nhan-vien.php?p=staff&a=list-staff&id=' . encryptId($id) . '\'",1000);</script>';
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
                    <h1>Sửa thông tin nhân viên</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="danh-sach-nhan-vien.php?p=staff&a=list-staff">Nhân viên</a></li>
                        <li class="breadcrumb-item active">Sửa nhân viên</li>
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

            <form method="POST" enctype="multipart/form-data" id="editStaffForm">
                <div class="row">
                    <!-- Cột 1: Thông tin cơ bản -->
                    <div class="col-md-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin cơ bản</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Mã nhân viên</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($employee['ma_nv']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tenNhanVien" placeholder="Nhập họ và tên"
                                           value="<?= htmlspecialchars($employee['ten_nv']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Giới tính <span class="text-danger">*</span></label>
                                    <select class="form-control" name="gioiTinh">
                                        <option value="chon">-- Chọn giới tính --</option>
                                        <option value="1" <?= $employee['gioi_tinh'] == '1' ? 'selected' : '' ?>>Nam</option>
                                        <option value="0" <?= $employee['gioi_tinh'] == '0' ? 'selected' : '' ?>>Nữ</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ngày sinh (m/d/y)<span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="ngaySinh"
                                           value="<?= $employee['ngay_sinh'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="soDienthoai" placeholder="Nhập số điện thoại"
                                           value="<?= htmlspecialchars($employee['so_dth']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Ảnh 3x4</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="hinhAnh" accept="image/*">
                                            <label class="custom-file-label">Chọn file ảnh</label>
                                        </div>
                                    </div>
                                    <?php if($employee['hinh_anh']): ?>
                                        <img src="../uploads/staffs/<?= htmlspecialchars($employee['hinh_anh']) ?>" 
                                             class="img-thumbnail mt-2" style="max-width: 200px;">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột 2: Thông tin công việc -->
                    <div class="col-md-4">
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
                                            <option value="<?= $pb['id'] ?>" <?= $employee['phong_ban_id'] == $pb['id'] ? 'selected' : '' ?>>
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
                                            <option value="<?= $tn['id'] ?>" <?= $employee['to_nhom_id'] == $tn['id'] ? 'selected' : '' ?>>
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
                                            <option value="<?= $cv['id'] ?>" <?= $employee['chuc_vu_id'] == $cv['id'] ? 'selected' : '' ?>>
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
                                            <option value="<?= $lnv['id'] ?>" <?= $employee['loai_nv_id'] == $lnv['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lnv['ten_loai_nv']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Trạng thái <span class="text-danger">*</span></label>
                                    <select class="form-control" name="trangThai">
                                        <option value="chon">-- Chọn trạng thái --</option>
                                        <option value="1" <?= $employee['trang_thai'] == '1' ? 'selected' : '' ?>>Đang làm việc</option>
                                        <option value="0" <?= $employee['trang_thai'] == '0' ? 'selected' : '' ?>>Đã nghỉ việc</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ngày vào làm <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="ngayVaolam"
                                           value="<?= $employee['ngay_vao_lam'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Ngày KTTV <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="ngayKTTV"
                                           value="<?= $employee['ngay_kt_thuviec'] ?>">
                                </div>
                                <!-- Thông tin trình độ -->
                                <div class="form-group">
                                    <label>Trình độ <span class="text-danger">*</span></label>
                                    <select class="form-control" name="trinhDo">
                                        <option value="chon">-- Chọn trình độ --</option>
                                        <?php foreach ($data['trinhDo'] as $td): ?>
                                            <option value="<?= $td['id'] ?>" <?= $employee['trinh_do_id'] == $td['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($td['ten_trinh_do']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Chuyên môn <span class="text-danger">*</span></label>
                                    <select class="form-control" name="chuyenMon">
                                        <option value="chon">-- Chọn chuyên môn --</option>
                                        <?php foreach ($data['chuyenMon'] as $cm): ?>
                                            <option value="<?= $cm['id'] ?>" <?= $employee['chuyen_mon_id'] == $cm['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cm['ten_chuyen_mon']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Bằng cấp <span class="text-danger">*</span></label>
                                    <select class="form-control" name="bangCap">
                                        <option value="chon">-- Chọn bằng cấp --</option>
                                        <?php foreach ($data['bangCap'] as $bc): ?>
                                            <option value="<?= $bc['id'] ?>" <?= $employee['bang_cap_id'] == $bc['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($bc['ten_bang_cap']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột 3: Thông tin cá nhân -->
                    <div class="col-md-4">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin cá nhân</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label>CMND/CCCD <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="CMND" placeholder="Nhập số CMND/CCCD"
                                                  value="<?= htmlspecialchars($employee['so_cmnd']) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Ngày cấp (m/d/y)<span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="ngayCap"
                                                  value="<?= $employee['ngay_cap_cmnd'] ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Nơi cấp <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="noiCap" placeholder="Nhập nơi cấp"
                                           value="<?= htmlspecialchars($employee['noi_cap_cmnd']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Nguyên quán <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nguyenQuan" placeholder="Nhập nguyên quán"
                                           value="<?= htmlspecialchars($employee['nguyen_quan']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Nơi ở hiện tại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="hoKhau" placeholder="Nhập nơi ở hiện tại"
                                           value="<?= htmlspecialchars($employee['ho_khau']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Tình trạng hôn nhân <span class="text-danger">*</span></label>
                                    <select class="form-control" name="honNhan">
                                        <option value="chon">-- Chọn tình trạng hôn nhân --</option>
                                        <?php foreach ($data['honNhan'] as $hn): ?>
                                            <option value="<?= $hn['id'] ?>" <?= $employee['hon_nhan_id'] == $hn['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($hn['ten_tinh_trang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Quốc tịch <span class="text-danger">*</span></label>
                                    <select class="form-control" name="quocTich">
                                        <option value="chon">-- Chọn quốc tịch --</option>
                                        <?php foreach ($data['quocTich'] as $qt): ?>
                                            <option value="<?= $qt['id'] ?>" <?= $employee['quoc_tich_id'] == $qt['id'] ? 'selected' : '' ?>>
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
                                            <option value="<?= $tg['id'] ?>" <?= $employee['ton_giao_id'] == $tg['id'] ? 'selected' : '' ?>>
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
                                            <option value="<?= $dt['id'] ?>" <?= $employee['dan_toc_id'] == $dt['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($dt['ten_dan_toc']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Mã số thuế</label>
                                    <input type="text" class="form-control" name="maSoThue" placeholder="Nhập mã số thuế"
                                           value="<?= htmlspecialchars($employee['ma_so_thue']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Số BHXH</label>
                                    <input type="text" class="form-control" name="soBHXH" placeholder="Nhập số BHXH"
                                           value="<?= htmlspecialchars($employee['so_bhxh']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nút submit -->
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary" name="save">Cập nhật</button>
                        <a href="danh-sach-nhan-vien.php?p=staff&a=list-staff" class="btn btn-default">Quay lại</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php include('../layouts/footer.php'); ?>

<script src="./vjs/validate-sua-nhan-vien.js"></script>