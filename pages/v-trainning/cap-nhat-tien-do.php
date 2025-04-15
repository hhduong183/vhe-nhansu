<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

if(isset($_SESSION['username']) && isset($_SESSION['level'])) {
    if($_SESSION['level'] == 1) {
        if(isset($_POST['save'])) {
            $id = $_POST['id'];
            $tienDo = $_POST['tienDo'];
            $trangThai = $_POST['trangThai'];
            $nguoiSua = $_SESSION['username'];
            $ngaySua = date("Y-m-d H:i:s");

            // Get course ID for redirect
            $query = "SELECT id_khoa_hoc FROM khoa_hoc_nhan_vien WHERE id = '$id'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_array($result);
            $khoaHocId = $row['id_khoa_hoc'];

            // Update progress
            $update = "UPDATE khoa_hoc_nhan_vien SET 
                      tien_do = '$tienDo',
                      trang_thai = '$trangThai',
                      nguoi_sua = '$nguoiSua',
                      ngay_sua = '$ngaySua'
                      WHERE id = '$id'";
            mysqli_query($conn, $update);

            header("Location: ds-hoc-vien.php?id=" . $khoaHocId);
        }
    }
    else {
        header('Location: index.php');
    }
}
else {
    header('Location: ' . BASE_URL . '/pages/dang-nhap.php');
}
?>