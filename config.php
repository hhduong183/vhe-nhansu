<?php
// Cấu hình thư mục gốc của project (ví dụ: 'vhe_qlns')
$projectFolder = 'vhe_tst'; // nếu đang nằm ở public_html/vhe_qlns/

// Định nghĩa ROOT_PATH để include file PHP
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
}

// Định nghĩa BASE_URL cho đường dẫn trên trình duyệt
if (!defined('BASE_URL')) {
    // define('BASE_URL', '/'); // sẽ là: /vhe_qlns
	define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST'].'/' );

}


	$conn = mysqli_connect("localhost", "evjmcxxjhosting_qlns", "22#}@gQ2hXyW", "evjmcxxjhosting_qlns");

	if (!$conn) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}

	
	// Set timezone 
	date_default_timezone_set('Asia/Ho_Chi_Minh');
	// set char set
	mysqli_set_charset($conn, 'utf8');
	

?>