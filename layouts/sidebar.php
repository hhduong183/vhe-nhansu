<?php 
//session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require(__DIR__ . '/../plugins/function.php');


$encodedId = isset($_SESSION['idNhanVien']) ? $_SESSION['idNhanVien'] : 0;
$level = isset($_SESSION['level']) ? $_SESSION['level'] : 0;



// Kiểm tra nếu có ID nhân viên hợp lệ
if ($idNhanVien > 0) {
    // Chuẩn bị truy vấn
    $stmt = $conn->prepare("SELECT id, hinh_anh, ten_nv, user_quyen FROM nhanvien WHERE id = ?");
    $stmt->bind_param("i", $idNhanVien);
    $stmt->execute();
    $result = $stmt->get_result();
    $row_acc = $result->fetch_assoc();
} else {
    $row_acc = null; // Không có dữ liệu
}

    // get active sidebar
    if(isset($_GET['p']) && isset($_GET['a']))
    {
        $p = $_GET['p'];
        $a = $_GET['a'];
    }
$encodedId = isset($row_acc['id']) ? encryptId($row_acc['id']) : null;
?>

<!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Sidebar -->
  <div class="sidebar">
    <!-- User panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="<?php echo (!empty($row_acc['hinh_anh']) && $row_acc['hinh_anh'] !== 'demo-3x4.jpg') 
          ? BASE_URL.'uploads/staffs/' . htmlspecialchars($row_acc['hinh_anh'], ENT_QUOTES, 'UTF-8') 
          : BASE_URL.'uploads/staffs/demo-3x4.jpg'; ?>" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="#" class="d-block" style="color:white;"><?php echo isset($row_acc['ten_nv']) ? htmlspecialchars($row_acc['ten_nv'], ENT_QUOTES, 'UTF-8') : 'Không có tên'; ?></a>
        <?php 
        switch ($level) {
          case 1: echo '<div style="color:red;"><i class="fa fa-circle text-success"></i> Quản trị viên</div>'; break;
          case 2: echo '<div style="color:#fd7e14;"><i class="fa fa-circle text-success"></i> Trưởng phòng</div>'; break;
          case 3: echo '<div style="color:yellow;"><i class="fa fa-circle text-success"></i> Phó phòng</div>'; break;
          case 4: echo '<div style="color:#1cc34e;"><i class="fa fa-circle text-success"></i> Trưởng nhóm</div>'; break;
          default: echo '<div style="color:white;"><i class="fa fa-circle text-success"></i> Nhân viên</div>'; break;
        }
        ?>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Home -->
        <li class="nav-item">
          <a href="<?ROOT_PATH ?>/pages/index.php?p=index&a=statistic" class="nav-link <?php if($a == 'statistic') echo 'active'; ?>" title="Trang chủ">
            <i class="nav-icon fas fa-home"></i>
            <p>Home</p>
          </a>
        </li>

        <?php if ($row_acc['user_quyen'] != 0): ?>
        <!-- Quản lý nhân viên -->
        <li class="nav-item <?php if($p == 'staff') echo 'menu-open'; ?>">
              <a href="#" class="nav-link <?php if($p == 'staff') echo 'active'; ?>" title="Quản lý nhân viên">
                <i class="nav-icon fas fa-users"></i>
                <p>Quản lý Nhân viên<i class="right fas fa-angle-left"></i></p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="<?ROOT_PATH ?>/pages/danh-sach-nhan-vien.php?p=staff&a=list-staff" class="nav-link <?php if(($p == 'staff') && ($a == 'list-staff')) echo 'active'; ?>" title="Danh sách nhân viên">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Danh sách nhân viên</p>
                  </a>
                </li>

              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-contract/hopdong_list.php?p=staff&a=contract-list" class="nav-link <?php if(($p == 'staff') && ($a == 'contract-list')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Danh sách hợp đồng</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-salary/bangluong_list.php?p=staff&a=salary-list" class="nav-link <?php if(($p == 'staff') && ($a == 'salary-list')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Danh sách lương</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/theodoiphepnam.php?p=staff&a=anual-leave" class="nav-link <?php if(($p == 'staff') && ($a == 'anual-leave')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Theo dõi phép năm</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/nghiphep_tonghop.php?p=staff&a=anual-sum" class="nav-link <?php if(($p == 'staff') && ($a == 'anual-sum')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Tổng hợp phép năm</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/nguoi-phu-thuoc.php?p=staff&a=giam-tru" class="nav-link <?php if(($p == 'staff') && ($a == 'giam-tru')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Giảm trừ gia cảnh</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL ?>pages/v-trainning/ds-khoa-hoc.php?p=staff&a=trainning" class="nav-link <?php if(($p == 'staff') && ($a == 'trainning')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Khóa học</p>
                </a>
              </li>

            </ul>
        </li>

          <!-- Nhóm nhân viên -->
          <!-- <li class="nav-item <?php if($p == 'group') echo 'menu-open'; ?>">
          <a href="#" class="nav-link <?php if($p == 'group') echo 'active'; ?>" title="Nhóm nhân viên">
            <i class="nav-icon fas fa-layer-group"></i>
            <p>Nhóm nhân viên<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="tao-nhom.php?p=group&a=add-group" class="nav-link <?php if($a == 'add-group') echo 'active'; ?>" title="Tạo nhóm">
                <i class="far fa-circle nav-icon"></i>
                <p>Tạo nhóm</p>
              </a>
            </li>
              <li class="nav-item">
                <a href="danh-sach-nhom.php?p=group&a=list-group" class="nav-link <?php if(($p == 'group') && ($a =='list-group')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Danh sách nhóm</p>
                </a>
              </li>
            </ul>
          </li> -->

          <!-- Khen thưởng - Kỷ luật -->
        <li class="nav-item <?php if($p == 'bonus-discipline') echo 'menu-open'; ?>">
          <a href="#" class="nav-link <?php if($p == 'bonus-discipline') echo 'active'; ?>" title="Khen thưởng - Kỷ luật">
            <i class="nav-icon fas fa-star"></i>
            <p>Khen thưởng - Kỷ luật<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?ROOT_PATH ?>/pages/v-system/khen-thuong.php?p=bonus-discipline&a=bonus" class="nav-link <?php if($a == 'bonus') echo 'active'; ?>" title="Khen thưởng">
                <i class="far fa-circle nav-icon"></i>
                <p>Khen thưởng</p>
              </a>
            </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/ky-luat.php?p=bonus-discipline&a=discipline" class="nav-link <?php if(($p == 'bonus-discipline') && ($a =='discipline')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Kỷ luật</p>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

          <?php if ($row_acc['user_quyen'] == 1): ?>
        <!-- Cấu hình hệ thống -->
        <li class="nav-item <?php if($p == 'system') echo 'menu-open'; ?>">
          <a href="#" class="nav-link <?php if($p == 'system') echo 'active'; ?>" title="Cấu hình hệ thống">
            <i class="nav-icon fas fa-cog"></i>
            <p>Cấu hình hệ thống<i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?ROOT_PATH ?>/pages/v-system/phong-ban.php?p=system&a=room" class="nav-link <?php if($a == 'room') echo 'active'; ?>" title="Phòng ban">
                <i class="far fa-circle nav-icon"></i>
                <p>Phòng ban</p>
              </a>
            </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/phong-ban_bp.php?p=system&a=tonhom" class="nav-link <?php if(($p == 'system') && ($a == 'tonhom')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Các tổ nhóm,bộ phận</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/chuc-vu.php?p=system&a=position" class="nav-link <?php if(($p == 'system') && ($a == 'position')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Danh sách Chức vụ</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/trinh-do.php?p=system&a=level" class="nav-link <?php if(($p == 'system') && ($a == 'level')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Danh sách Trình độ</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/chuyen-mon.php?p=system&a=specialize" class="nav-link <?php if(($p == 'system') && ($a == 'specialize')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Kỹ năng Chuyên môn</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/bang-cap.php?p=system&a=certificate" class="nav-link <?php if(($p == 'system') && ($a == 'certificate')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Chứng chỉ Bằng cấp</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/loai-nhan-vien.php?p=system&a=employee-type" class="nav-link <?php if(($p == 'system') && ($a == 'employee-type')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Loại nhân viên</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-contract/hopdong_type.php?p=system&a=hopdong-type" class="nav-link <?php if(($p == 'system') && ($a == 'hopdong-type')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Quản lý loại hợp đồng</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/luong_cacmucluong.php?p=system&a=mucluong" class="nav-link <?php if(($p == 'system') && ($a == 'mucluong')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Các mức lương</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/phucap_trachnhiem.php?p=system&a=phucaptn" class="nav-link <?php if(($p == 'system') && ($a == 'phucaptn')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Phụ Cấp Trách nhiệm</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?ROOT_PATH ?>/pages/v-system/phucap_nghe.php?p=system&a=phucapnghe" class="nav-link <?php if(($p == 'system') && ($a == 'phucapnghe')) echo 'active'; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Phụ Cấp Nghề</p>
                </a>
              </li>
            </ul>
          </li>
          <?php endif; ?>

           <!-- Hồ sơ cá nhân -->
        <li class="nav-item">
          <a href="<?ROOT_PATH ?>/pages/thong-tin-nhan-vien.php?p=profile&a=view&id=<?php echo $encodedId; ?>" class="nav-link <?php if($p == 'profile' && $a == 'view') echo 'active'; ?>" title="Hồ sơ cá nhân">
            <i class="nav-icon far fa-user-circle"></i>
            <p>Hồ sơ cá nhân</p>
          </a>
        </li>

        <!-- Hợp đồng -->
        <li class="nav-item">
          <a href="<?ROOT_PATH ?>/pages/v-contract/hopdong_nhanvien.php?p=contract&a=view&id=<?php echo $idNhanVien; ?>" class="nav-link <?php if($p == 'contract' && $a == 'view') echo 'active'; ?>" title="Hợp đồng lao động">
            <i class="nav-icon far fa-file"></i>
            <p>Hợp đồng lao động</p>
          </a>
        </li>
        <!-- Người phụ thuộc -->
        <li class="nav-item">
          <a href="<?ROOT_PATH ?>/pages/nguoi-phu-thuoc_nv.php?p=giam-tru&a=view&id=<?php echo $idNhanVien; ?>" class="nav-link <?php if($p == 'giam-tru' && $a == 'view') echo 'active'; ?>" title="Giảm trừ gia cảnh">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>Người phụ thuộc</p>
          </a>
        </li>

        <!-- Nghỉ phép -->
        <li class="nav-item">
          <a href="<?ROOT_PATH ?>/pages/nghiphep_nv.php?p=annual&a=view&id=<?php echo $encodedId; ?>" class="nav-link <?php if($p == 'annual' && $a == 'view') echo 'active'; ?>" title="Nghỉ phép">
            <i class="nav-icon far fa-calendar-alt"></i>
            <p>Nghỉ phép</p>
          </a>
        </li>

        <!-- Bảng lương -->
        <li class="nav-item">
          <a href="<?ROOT_PATH ?>/pages/v-salary/bangluong_chitiet.php?p=salary&a=view&id=<?php echo $encodedId; ?>" class="nav-link <?php if($p == 'salary' && $a == 'view') echo 'active'; ?>" title="Bảng lương">
            <i class="nav-icon fas fa-money-bill-wave"></i>
            <p>Bảng lương</p>
          </a>
        </li>

        <!-- Đổi mật khẩu -->
        <li class="nav-item">
          <a href="<?ROOT_PATH ?>/pages/doi-mat-khau.php?p=profile&a=changepass" class="nav-link <?php if($p == 'profile' && $a == 'changepass') echo 'active'; ?>" title="Đổi mật khẩu">
            <i class="nav-icon fas fa-key"></i>
            <p>Đổi mật khẩu</p>
          </a>
        </li>

        <!-- Đăng xuất -->
        <li class="nav-item">
          <a href="<?ROOT_PATH ?>/pages/dang-xuat.php" class="nav-link" title="Đăng xuất">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Đăng xuất</p>
          </a>
        </li>

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
