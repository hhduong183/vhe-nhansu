<?php 

// create session
session_start();
require '../config.php';
if(isset($_SESSION['username']) && isset($_SESSION['level']))
{
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');
$acc = "SELECT * FROM nhanvien nv
JOIN chuc_vu cv ON nv.chuc_vu_id = cv.id
WHERE user_name = '$username'";
$result_acc = mysqli_query($conn, $acc);
$row_acc = mysqli_fetch_array($result_acc);

  // save
  if(isset($_POST['save']))
  {
    // create error array
    $error = array();
    $success = array();
    $showMess = false;

    // get value
    $user_name = $row_acc['user_name'];
    $oldPass = password_hash($_POST['oldPass'], PASSWORD_BCRYPT);
    $newPass = password_hash($_POST['newPass'], PASSWORD_BCRYPT);
    $reNewPass = password_hash($_POST['reNewPass'], PASSWORD_BCRYPT);

    // validate
    if(empty($_POST['oldPass']))
      $error['oldPass'] = 'Vui lòng nhập <b> mật khẩu cũ </b>';

    if(empty($_POST['newPass']))
      $error['newPass'] = 'Vui lòng nhập <b> mật khẩu mới </b>';

    if(empty($_POST['reNewPass']))
      $error['reNewPass'] = 'Vui lòng nhập lại <b> mật khẩu mới </b>';

    if(!empty($_POST['oldPass']) && !password_verify($_POST['oldPass'], $row_acc['mat_khau']))
      $error['errorPass'] = 'Mật khẩu cũ <b> không đúng </b>. Vui lòng thử lại!';

    if($_POST['newPass'] !== $_POST['reNewPass'])
      $error['checkNotSame'] = 'Mật khẩu mới không <b> trùng nhau </b>. Vui lòng thử lại!';


    // save to db
    if(!$error)
    {
      $showMess = true;
      // update record
      $update = " UPDATE nhanvien SET
                  mat_khau = '$newPass',
                  must_change_password = '0'
                  WHERE user_name = '$user_name'";   
      mysqli_query($conn, $update);
      $success['success'] = 'Thay đổi mật khẩu mới thành công.';
      echo '<script>setTimeout("window.location=\'doi-mat-khau.php?p=account&a=changepass\'",1000);</script>';
    }
  }

?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Đổi mật khẩu</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="index.php?p=index&a=statistic">Tổng quan</a></li>
              <li class="breadcrumb-item active">Đổi mật khẩu</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle" src="../uploads/staffs/<?php echo $row_acc['hinh_anh']; ?>" alt="User profile picture">
                </div>
                <h3 class="profile-username text-center"><?php echo $row_acc['ten_nv']; ?></h3>
                <p class="text-muted text-center"><?php echo $row_acc['ten_chuc_vu']; ?></p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Ngày tạo:</b> <span class="float-right">
                      <?php 
                        $date_cre = date_create($row_acc['ngay_tao']);
                        echo date_format($date_cre, 'd/m/Y');
                      ?>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <b>Ngày sửa:</b> <span class="float-right">
                      <?php 
                        $date_edi = date_create($row_acc['ngay_sua']);
                        echo date_format($date_edi, 'd/m/Y');
                      ?>
                    </span>
                  </li>
                  <li class="list-group-item">
                    <b>Trạng thái:</b> <span class="float-right">Đang hoạt động</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item">
                    <a class="nav-link active"  data-toggle="tab">Đổi mật khẩu</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <?php 
                    // show error
                    if(isset($error)) 
                    {
                      if($showMess == false)
                      {
                        echo "<div class='alert alert-danger alert-dismissible'>";
                        echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
                        echo "<h5><i class='icon fas fa-ban'></i> Lỗi!</h5>";
                        foreach ($error as $err) 
                        {
                          echo $err . "<br/>";
                        }
                        echo "</div>";
                      }
                    }
                  ?>

                  <?php 
                    // show success
                    if(isset($success)) 
                    {
                      if($showMess == true)
                      {
                        echo "<div class='alert alert-success alert-dismissible'>";
                        echo "<h5><i class='icon fas fa-check'></i> Thành công!</h5>";
                        foreach ($success as $suc) 
                        {
                          echo $suc . "<br/>";
                        }
                        echo "</div>";
                      }
                    }
                  ?>
                  
                  <div class="active tab-pane" id="settings">
                    <form class="form-horizontal" method="POST">
                      <div class="form-group row">
                        <label for="inputName" class="col-sm-2 col-form-label">Nhập mật khẩu cũ <b style="color: red;">*</b></label>
                        <div class="col-sm-10">
                          <input type="password" class="form-control" id="inputName" placeholder="Nhập mật khẩu cũ" name="oldPass" value="<?php echo isset($_POST['oldPass']) ? $_POST['oldPass'] : ''; ?>">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Nhập mật khẩu mới <b style="color: red;">*</b></label>
                        <div class="col-sm-10">
                          <input type="password" class="form-control" id="inputEmail" placeholder="Nhập mật khẩu mới" name="newPass" value="<?php echo isset($_POST['newPass']) ? $_POST['newPass'] : ''; ?>">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputName" class="col-sm-2 col-form-label">Nhập lại mật khẩu mới <b style="color: red;">*</b></label>
                        <div class="col-sm-10">
                          <input type="password" class="form-control" id="inputName" placeholder="Nhập lại mật khẩu mới" name="reNewPass" value="<?php echo isset($_POST['reNewPass']) ? $_POST['reNewPass'] : ''; ?>">
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="offset-sm-2 col-sm-10">
                          <button type="submit" class="btn btn-primary" name="save"><i class="fas fa-save"></i> Lưu lại</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <!-- /.content-wrapper -->
<?php
  // include
  include('../layouts/footer.php');
}
else
{
  // go to pages login
  header('Location: dang-nhap.php');
}

?>