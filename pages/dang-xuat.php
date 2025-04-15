<?php 

	session_start();
	session_unset();
	session_destroy();
	header("Location: dang-nhap.php?logged_out=true");
?>