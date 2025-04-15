<?php

// create session
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');


    // Kiểm tra đăng nhập
if (!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
    header('Location: dang-nhap.php');
    exit;
}
	// include file
    include(ROOT_PATH . '/layouts/header.php');
    include(ROOT_PATH . '/layouts/topbar.php');
    include(ROOT_PATH . '/layouts/sidebar.php');

	// dem so luong nhan vien
	$nv = "SELECT count(id) as soluong FROM nhanvien";
	$resultNV = mysqli_query($conn, $nv);
	$rowNV = mysqli_fetch_array($resultNV);
	$tongNV = $rowNV['soluong'];


    // echo 'BASE_URL = ' . BASE_URL . '<br>';
    // echo 'ROOT_PATH = ' . ROOT_PATH . '<br>';
    // Lấy tháng hiện tại
    $thang_hien_tai = date('m');
    
	
// Combine all statistics queries into one
$statistics_query = "SELECT 
    (SELECT COUNT(id) FROM nhanvien) as total_nv,
    (SELECT COUNT(id) FROM nhanvien WHERE MONTH(ngay_sinh) = $thang_hien_tai) as total_sinh_nhat,
    (SELECT COUNT(id) FROM nhanvien WHERE trang_thai = 0) as total_nghi_viec,
    (SELECT COUNT(id) FROM chuc_vu) as total_chuc_vu,
    (SELECT COUNT(id) FROM phong_ban) as total_phong_ban,
    (SELECT COUNT(id) FROM tai_khoan) as total_tai_khoan,
    (SELECT SUM(thuc_lanh) FROM luong WHERE MONTH(ngay_cham) = MONTH(CURRENT_DATE) AND YEAR(ngay_cham) = YEAR(CURRENT_DATE)) as tong_luong,
    (SELECT COUNT(nhanvien_id) FROM khen_thuong_ky_luat WHERE MONTH(ngay_tao) = MONTH(CURRENT_DATE) AND YEAR(ngay_tao) = YEAR(CURRENT_DATE)) as total_khen_thuong,
    (SELECT SUM(so_tien) FROM khen_thuong_ky_luat WHERE MONTH(ngay_tao) = MONTH(CURRENT_DATE) AND YEAR(ngay_tao) = YEAR(CURRENT_DATE)) as tong_tien_khen_thuong";

$result_stats = mysqli_query($conn, $statistics_query);
$stats = mysqli_fetch_assoc($result_stats);

$tongNV = $stats['total_nv'];
$tongNV_SN = $stats['total_sinh_nhat'];
$tongNghiViec = $stats['total_nghi_viec'];
$tongchuc_vu = $stats['total_chuc_vu'];
$tongPB = $stats['total_phong_ban'];
$tongTK = $stats['total_tai_khoan'];
$tongtong_luong_thang_nay = number_format($stats['tong_luong'], 0, ',', '.');
$tongnv_khen_thuong = $stats['total_khen_thuong'];
$tongtien_khen_thuong = number_format($stats['tong_tien_khen_thuong'], 0, ',', '.');

// Add weather API caching
function getWeatherByCity($city, $apiKey) {
    $cache_file = sys_get_temp_dir() . '/weather_' . md5($city) . '.json';
    $cache_time = 1800; // 30 minutes

    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        return json_decode(file_get_contents($cache_file), true);
    }

    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=metric&lang=vi&appid=$apiKey";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        file_put_contents($cache_file, $response);
        return json_decode($response, true);
    }
    if ($httpCode == 404) {
        die("❌ Lỗi: Thành phố không tồn tại hoặc sai tên! 🏙️");
    } elseif ($httpCode == 401) {
        die("❌ Lỗi: API Key không hợp lệ! 🔑");
    } elseif ($httpCode != 200) {
        die("❌ Lỗi API: HTTP $httpCode");
    }

    return json_decode($response, true);
}

// Add RSS feed caching
function getRSSFeed($url, $cache_time = 1800) {
    $cache_file = sys_get_temp_dir() . '/rss_' . md5($url) . '.xml';
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        return simplexml_load_file($cache_file);
    }
    
    $rss_content = file_get_contents($url);
    if ($rss_content) {
        file_put_contents($cache_file, $rss_content);
        return simplexml_load_string($rss_content);
    }
    return false;
}

// 🔹 Thành phố cần lấy thời tiết
$city = "Hải Phòng, VN"; // Thử đổi thành "Hải Phòng, VN"
$apiKey = "28669ac9ec33aa9f2d834f2254166571"; // Thay bằng API Key thật của bạn

// Gọi API
$data = getWeatherByCity($city, $apiKey);

// Kiểm tra dữ liệu trả về
if (!$data) {
    die("❌ Không có dữ liệu thời tiết!");
}

// Lấy thông tin thời tiết
$weather = [
    'condition'   => ucfirst($data['weather'][0]['description']),
    'temperature' => $data['main']['temp'] . "°C",
    'humidity'    => $data['main']['humidity'] . "%",
    'wind_speed'  => $data['wind']['speed'] . " m/s"
];


// Check if current user has birthday this month
$check_birthday = "SELECT id FROM nhanvien WHERE MONTH(ngay_sinh) = $thang_hien_tai AND id = " . $row_acc['id'];
$result_birthday = mysqli_query($conn, $check_birthday);
$has_birthday = mysqli_num_rows($result_birthday) > 0;

$thangSN=date('M')	;
?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Tổng quan
            <small>Phần mềm quản lý nhân sự</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
            <li class="active">Thống kê</li>
        </ol>
    </section>

    <section class="content">
        <?php if ($row_acc['user_quyen'] != 0) : ?>
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?php echo $tongNV; ?></h3>
                        <p>Nhân viên</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person"></i>
                    </div>
                    <a href="danh-sach-nhan-vien.php?p=staff&a=liststaff" class="small-box-footer">
                        Danh sách nhân viên <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $tongnv_khen_thuong; ?></h3>
                        <p>Số nhân viên khen thưởng</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="khen-thuong.php?p=bonus-discipline&a=bonus" class="small-box-footer">
                        Danh sách khen thưởng <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo $tongchuc_vu; ?></h3>
                        <p>Chức vụ</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="#" class="small-box-footer" onclick="return false;">
                        Danh sách chức vụ <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo $tongNV_SN; ?></h3>
                        <p>CBCNV sinh nhật tháng <?php echo $thang_hien_tai ;?> </p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <a href="ds-nhanvien.php?p=birthday&a=view" class="small-box-footer">
                        Danh sách CBCNV <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="ion ion-ios-gear-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Phòng ban</span>
                        <span class="info-box-number"><?php echo $tongPB; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tiền chi khen thưởng</span>
                        <span class="info-box-number"><?php echo $tongtien_khen_thuong; ?> đ</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="ion ion-ios-cart-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Lương trả trong tháng</span>
                        <span class="info-box-number"><?php echo ($tongtong_luong_thang_nay ?? 0); ?> đ</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="ion ion-close-circled"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Nhân viên nghỉ việc</span>
                        <span class="info-box-number"><?php echo $tongNghiViec; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-3">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-cloud"></i> Thời tiết Hải Phòng</h3>
                    </div>
                    <div class="box-body">
                        <ul class="list-unstyled">
                            <li><i class="fa fa-cloud"></i> <strong>Điều kiện:</strong> <?php echo ucfirst($weather['condition']); ?></li>
                            <li><i class="fa fa-thermometer-half"></i> <strong>Nhiệt độ:</strong> <?php echo $weather['temperature']; ?></li>
                            <li><i class="fa fa-tint"></i> <strong>Độ ẩm:</strong> <?php echo $weather['humidity']; ?></li>
                            <li><i class="fa fa-wind"></i> <strong>Tốc độ gió:</strong> <?php echo $weather['wind_speed']; ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php if($has_birthday): ?>
            <div class="col-md-9">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fas fa-birthday-cake"></i> Happy Birthday!</h3>
                    </div>
                    <div class="box-body">
                        <div class="text-center">
                            <h4>🎉 Happy Birthday to You! 🎉</h4>
                            <p>Chúc mừng sinh nhật! Chúc bạn một năm mới tràn đầy niềm vui và hạnh phúc!</p>
                            <div class="birthday-animation" style="font-size: 40px; margin: 20px 0;">
                                🎂 🎈 🎁
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-md-<?php echo $has_birthday ? '12' : '9'; ?>">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-newspaper-o"></i> Thông tin mới - vhe.com.vn</h3>
                    </div>
                    <div class="box-body">
                        <?php
                        // Replace RSS loading code with cached version
                        $rss = getRSSFeed("https://vhe.com.vn/feed/");
                        $i = 0;
                        foreach ($rss->channel->item as $item) {
                            echo '<div class="post">';
                            echo '<h4><a href="' . $item->link . '">' . $item->title . '</a></h4>';
                            echo '<p class="text-muted">' . $item->description . '</p>';
                            echo '<hr>';
                            echo '</div>';
                            $i++;
                            if ($i >= 5) break;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div><!-- /.content-wrapper -->


<?php
	// include
	include('../layouts/footer.php');
	?>