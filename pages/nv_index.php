<?php

// include file
include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');
	
	
// API Key của OpenWeatherMap
$apiKey = "YOUR_OPENWEATHER_API_KEY";
$city = "Hai Phong";
$apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&lang=vi&appid=$apiKey";

// Gửi yêu cầu API và lấy dữ liệu
$response = file_get_contents($apiUrl);
$weatherData = json_decode($response, true);

$weather = [
    "condition" => $weatherData["weather"][0]["description"],
    "temperature" => $weatherData["main"]["temp"] . "°C",
    "humidity" => $weatherData["main"]["humidity"] . "%",
    "wind_speed" => $weatherData["wind"]["speed"] . " m/s"
];

// Lấy dữ liệu bài viết từ RSS của VHE
$rssUrl = "https://vhe.com.vn/feed/";
$rssFeed = simplexml_load_file($rssUrl);
$articles = [];

if ($rssFeed) {
    $count = 0;
    foreach ($rssFeed->channel->item as $item) {
        if ($count >= 5) break;
        $articles[] = [
            "title" => (string) $item->title,
            "link" => (string) $item->link
        ];
        $count++;
    }
}
?>
<div class="content-wrapper" >
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>
			Tổng quan
			<small>Phần mềm quản lý nhân sự VHE</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="nv_index.php?p=index&a=nv"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
			<li class="active">Thống kê</li>
		</ol>
	</section>


	<!-- Main content -->
	<section class="content">
		<!-- Small boxes (Stat box) -->
		<div class="row">
            <h1 class="text-center">Chào mừng bạn đến với hệ thống!</h1>
            
            <h3 class="mt-4">📌 5 bài viết mới nhất từ VHE:</h3>
            <ul class="list-group">
                <?php foreach ($articles as $article): ?>
                    <li class="list-group-item">
                        <a href="<?php echo $article['link']; ?>" target="_blank">
                            <?php echo $article['title']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <h3 class="mt-4">🌦 Thời tiết Hải Phòng hôm nay:</h3>
            <p><strong>Điều kiện:</strong> <?php echo ucfirst($weather['condition']); ?></p>
            <p><strong>Nhiệt độ:</strong> <?php echo $weather['temperature']; ?></p>
            <p><strong>Độ ẩm:</strong> <?php echo $weather['humidity']; ?></p>
            <p><strong>Tốc độ gió:</strong> <?php echo $weather['wind_speed']; ?></p>
        </div>
	</section>
</div>
<?php
	// include
	include('../layouts/footer.php');
?>