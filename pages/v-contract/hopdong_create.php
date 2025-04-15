<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');


if(isset($_SESSION['username']) && isset($_SESSION['level']))
{
  // ----- Loại hợp đồng
  $loaihopdong = "SELECT id, ten_hop_dong FROM hop_dong_type";
  $resultLoaihopdong = mysqli_query($conn, $loaihopdong);
  $arrLoaihopdong = array();
  while ($rowLoaihopdong = mysqli_fetch_array($resultLoaihopdong)) 
  {
    $arrLoaihopdong[] = $rowLoaihopdong;
  }


  // ----- Mức lương
  $MucLuong = "SELECT * FROM luong_mucluong";
  $resultMucLuong = mysqli_query($conn, $MucLuong);
  $arrMucLuong = array();
  while ($rowMucLuong = mysqli_fetch_array($resultMucLuong)) 
  {
    $arrMucLuong[] = $rowMucLuong;
  }
  
    // ----- Phụ cấp TN
  $Trachnhiem = "SELECT * FROM luong_pctnh";
  $resultTrachnhiem = mysqli_query($conn, $Trachnhiem);
  $arrTrachnhiem = array();
  while ($rowTrachnhiem = mysqli_fetch_array($resultTrachnhiem)) 
  {
    $arrTrachnhiem[] = $rowTrachnhiem;
  }
  
  
    // ----- Phụ cấp nghề
  $PhucapNghe = "SELECT * FROM luong_pcnghe";
  $resultPhucapNghe = mysqli_query($conn, $PhucapNghe);
  $arrPhucapNghe = array();
  
  while ($rowPhucapNghe = mysqli_fetch_array($resultPhucapNghe)) 
  {
    $arrPhucapNghe[] = $rowPhucapNghe;
  }
  
}

else
{
  // go to pages login
  header('Location: dang-nhap.php');
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>HỢP ĐỒNG LAO ĐỘNG</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Tổng quan</a></li>
                        <li class="breadcrumb-item"><a href="hopdong_list.php?p=staff&a=contract-list">Hợp đồng</a></li>
                        <li class="breadcrumb-item active">Thêm hợp đồng</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Thêm mới hợp đồng</h3>
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
                            <div class="mb-5 position-relative" style="margin: 15px;">
                                <label class="form-label">Tìm nhân viên:</label>
                                <div class="input-group mb-3">
                                    <input type="text" id="search_nhanvien" class="form-control" placeholder="Nhập mã hoặc tên nhân viên">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                </div>
                                <div id="result_nhanvien" class="list-group position-absolute w-100" style="display: none; max-height: 250px; overflow-y: auto; background: white; z-index: 1000;"></div>
                                <form method="POST" action="export-contract.php" class="mt-4">  
                                    <input type="hidden" name="nhanvien_id" id="nhanvien_id">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ma_nhan_vien">Mã nhân viên <span class="text-danger">*</span></label>
                                                <input type="text" id="ma_nhan_vien" name="ma_nhan_vien" class="form-control" required readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="ho_ten">Họ tên</label>
                                                <input type="text" id="ho_ten" class="form-control" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="ngay_sinh">Ngày sinh</label>
                                                <input type="text" id="ngay_sinh" class="form-control" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="so_cmnd">Số CMND</label>
                                                <input type="text" id="so_cmnd" class="form-control" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="ho_khau">Nơi ở hiện tại</label>
                                                <input type="text" id="ho_khau" class="form-control" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="nguyen_quan">Nguyên quán</label>
                                                <input type="text" id="nguyen_quan" class="form-control" readonly>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label class="form-label">Ngày cấp:</label>
                                            <input type="text" id="ngay_cap" class="form-control" readonly>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label class="form-label">Nơi cấp:</label>
                                            <input type="text" id="noi_cap" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div style="margin-bottom: 15px;">
                                            <label class="form-label">Mã hợp đồng:<small id="maHopDongError"  style="color: red; font-size:14px;">*
                                               <?php if(isset($error['maHopDong'])) { echo $error['maHopDong']; } ?>
                                          </small></label>
                                            <input type="text" class="form-control" name="ma_hop_dong" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6" style="margin-bottom: 15px;">
                                                <label class="form-label">Ngày bắt đầu:<small id="startdateError"  style="color: red; font-size:14px;">*
                                                <?php if(isset($error['startdate'])) { echo $error['startdate']; } ?>
                                            </small></label>
                                                <input type="date" class="form-control" name="ngay_bat_dau" required>
                                            </div>
                                            <div class="col-md-6" style="margin-bottom: 15px;">
                                                <label class="form-label">Ngày kết thúc:</label>
                                                <input type="date" class="form-control" name="ngay_ket_thuc">
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                          <label>Loại hợp đồng:<small id="HopDongTypeError"  style="color: red; font-size:14px;">*
                                               <?php if(isset($error['HopDongType'])) { echo $error['HopDongType']; } ?>
                                          </small></label>
                                          <select class="form-control" name="loai_hop_dong" required>
                                              <option value="">--- Chọn loại hợp đồng ---</option>
                                          <?php 
                                            foreach ($arrLoaihopdong as $hd)
                                            {
                                              echo "<option value='".$hd['id']."' data-loaihopdong='".$hd['loaihopdong']."'>".$hd['ten_hop_dong']."</option>";
                                            }
                                          ?>
                                          </select>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label class="form-label">Mức lương:<small id="BacLuongError"  style="color: red; font-size:14px;">*
                                               <?php if(isset($error['BacLuong'])) { echo $error['BacLuong']; } ?>
                                          </small></label>
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <select class="form-control" name="BacLuong" id="BacLuong" required>
                                                        <option value="">Bậc lương</option>
                                                        <?php 
                                                        foreach ($arrMucLuong as $ml) {
                                                            echo "<option value='".$ml['muc_luong']."' data-mucluong='".$ml['muc_luong']."'>".$ml['bac_luong']."</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-7">
                                                    <input type="text" class="form-control" name="MucLuong" id="MucLuong" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label class="form-label">Phụ cấp trách nhiệm:</label>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <select class="form-control" name="nhomTN" id="nhomTN">
                                                        <option value="">Nhóm hưởng</option>
                                                        <?php 
                                                        foreach ($arrTrachnhiem as $tnh) {
                                                            echo "<option value='".$tnh['so_tien']."' data-trachnhiem='".$tnh['so_tien']."'>".$tnh['ten_nhom']."</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" name="Trachnhiem" id="Trachnhiem" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label class="form-label">Phụ cấp nghề:</label>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <select class="form-control" name="PCNNghe" id="PCNNghe">
                                                        <option value="">Nghề nghiệp</option>
                                                        <?php 
                                                        foreach ($arrPhucapNghe as $ml) {
                                                            echo "<option value='".$ml['so_tien']."' data-PhucapNghe='".$ml['so_tien']."'>".$ml['ten_phu_cap']."</option>";
                                                        }
                                                        echo "Phụ cấp nghề được chọn: " . $ml['so_tien'];
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" name="PhucapNghe" id="PhucapNghe" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Phụ cấp nhà trọ:</label>
                                                    <input type="text" class="form-control" name="nha_tro">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Phụ cấp đặc biệt:</label>
                                                    <input type="text" class="form-control" name="dac_biet">
                                                </div>
                                            </div>
                                        </div>
                                        <div style="margin-bottom: 15px;">
                                            <label class="form-label">Mức đóng BHXH:<small id="muc_bhxhError"  style="color: red; font-size:14px;">*
                                               <?php if(isset($error['muc_bhxh'])) { echo $error['muc_bhxh']; } ?>
                                          </small></label>
                                            <input type="text" class="form-control" name="muc_bhxh" required>
                                        </div>
                                    </div>
                                    <div style="padding:15px;">
                                        <input type="checkbox" id="contract-save" name="contract-save" value="save">
                                        <label for="contract-save"> Lưu dữ liệu hợp đồng</label>
                                        <button type="submit" id="submitbutton" class="btn btn-open-export btn-danger" style="margin: 25px;padding: 5px;">Xem trước</button>
                                    </div>
                                </form>
                             </div>
                          </div>
                </div>
            </div>
        </div>
    </section>
</div>    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#search_nhanvien").on("keyup", function() {
        let query = $(this).val();
        if (query.length > 1) {
            $.ajax({
                url: "<?ROOT_PATH ?>/pages/search_nhanvien.php",
                type: "GET",
                data: { query: query },
                success: function(data) {
                    $("#result_nhanvien").html(data).show();
                }
            });
        } else {
            $("#result_nhanvien").hide();
        }
    });

    $(document).on("click", ".nhanvien-item", function() {
        let nhanvien = $(this).data("info");
        $("#nhanvien_id").val(nhanvien.id);
        $("#ma_nhan_vien").val(nhanvien.ma_nv);
        $("#ho_ten").val(nhanvien.ten_nv);
        $("#ngay_sinh").val(nhanvien.ngay_sinh);
        $("#so_cmnd").val(nhanvien.so_cmnd);
        $("#ho_khau").val(nhanvien.ho_khau);
        $("#nguyen_quan").val(nhanvien.nguyen_quan);
        $("#ngay_cap").val(nhanvien.ngay_cap_cmnd);
        $("#noi_cap").val(nhanvien.noi_cap_cmnd);
        $("#result_nhanvien").hide();
    });

    $(document).click(function(e) {
        if (!$(e.target).closest("#search_nhanvien, #result_nhanvien").length) {
            $("#result_nhanvien").hide();
        }
    });
    // Update form submission handler
    $('form').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        $.ajax({
            url: 'export-contract.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response, status, xhr) {
                // Check if response is JSON (error message) or blob (file)
                const contentType = xhr.getResponseHeader('content-type');
                
                if (contentType && contentType.indexOf('application/json') !== -1) {
                    // Handle JSON response (usually error messages)
                    const reader = new FileReader();
                    reader.onload = function() {
                        const result = JSON.parse(this.result);
                        alert(result.message);
                        
                        if (result.success && result.redirect) {
                            window.location.href = result.redirect;
                        }
                    };
                    reader.readAsText(response);
                } else {
                    // Handle file download
                    const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    const fileName = xhr.getResponseHeader('content-disposition')?.split('filename=')[1]?.replace(/"/g, '') || 'contract.docx';
                    
                    a.href = url;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // If contract was saved, redirect after download
                    if ($('#contract-save').is(':checked')) {
                        setTimeout(function() {
                            window.location.href = 'hopdong_list.php?p=staff&a=contract-list';
                        }, 1000);
                    }
                }
            },
            error: function(xhr) {
                alert('Có lỗi xảy ra trong quá trình xử lý!');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
<script>
    function handleSelectChange(selectId, dataAttr, outputId) {
        document.getElementById(selectId).addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var value = selectedOption.getAttribute(dataAttr);

            // Chuyển đổi thành số và định dạng có dấu phân cách
            if (value) {
                var formattedValue = new Intl.NumberFormat('vi-VN').format(value);
                document.getElementById(outputId).value = formattedValue + " VND";
            } else {
                document.getElementById(outputId).value = "";
            }
        });
    }

    // Gọi hàm cho các trường hợp cụ thể
    handleSelectChange('BacLuong', 'data-mucluong', 'MucLuong');
    handleSelectChange('nhomTN', 'data-trachnhiem', 'Trachnhiem');
    handleSelectChange('PCNNghe', 'data-PhucapNghe', 'PhucapNghe');
</script>
<script>
        document.getElementById("contract-save").addEventListener("change", function() {
            let button = document.getElementById("submitbutton");

            if (this.checked) {
                button.textContent = "Tạo và Lưu"; // Thay đổi nội dung button
                button.classList.remove("btn-danger"); // Xóa class btn-primary
                button.classList.add("btn-success"); // Thêm class btn-danger (đổi màu nút)
            } else {
                button.textContent = "Xem trước"; // Nội dung mặc định
                button.classList.remove("btn-success");
                button.classList.add("btn-danger");
            }
        });
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php
  // include
  include(ROOT_PATH . '/layouts/footer.php');
  ?>
