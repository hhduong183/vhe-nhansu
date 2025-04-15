<?php
// Bắt đầu session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kết nối database
require_once(__DIR__ . '/../config.php');

// Thiết lập thời gian timeout (10 phút)
$timeout = 10 * 60;

// Kiểm tra timeout hoạt động
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $inactive_time = time() - $_SESSION['LAST_ACTIVITY'];

    if ($inactive_time > $timeout) {
        // Hủy phiên và chuyển hướng
        session_unset();
        session_destroy();
        header('Location: ' .BASE_URL. 'pages/dang-nhap.php?timeout=true');
        exit();
    }
}

// Cập nhật thời gian hoạt động cuối
$_SESSION['LAST_ACTIVITY'] = time();

// Kiểm tra đăng nhập
if (!isset($_SESSION['idNhanVien'])) {
    header('Location: ' . BASE_URL . 'pages/dang-nhap.php?timeout=true');
    exit();
}

// Lấy thông tin tài khoản nhân viên
$username = $_SESSION['username'] ?? '';

if (!empty($username)) {
    $acc = "SELECT * FROM nhanvien WHERE user_name = '" . mysqli_real_escape_string($conn, $username) . "'";
    $result_acc = mysqli_query($conn, $acc);
    $row_acc = mysqli_fetch_array($result_acc);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="<?= BASE_URL ?>dist/images/favicon.ico" type="image/x-icon" />
  <title>VHE | <?= isset($pageTitle) ? $pageTitle : 'QUẢN LÝ NHÂN SỰ'; ?></title>
  <link rel="dns-prefetch" href="https://vhe.com.vn">
  <link rel="dns-prefetch" href="https://api.openweathermap.org">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Font Awesome 5 CDN -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
  
  

  <!-- Google Font: Source Sans Pro -->
  <!-- <link rel=" stylesheet" href="../dist/css/fonts/source-sans-pro.css"> -->
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <!--<link rel="stylesheet" href="<?= BASE_URL ?>plugins/ionicons/css/ionicons.min.css">-->
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= BASE_URL ?>dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/daterangepicker/daterangepicker.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/select2/css/select2.min.css">

  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Custom style -->
  <link rel="stylesheet" href="<?= BASE_URL ?>layouts/style.css">

  <!-- REQUIRED SCRIPTS -->
  <!-- jQuery -->
  <script src="<?= BASE_URL ?>plugins/jquery/jquery.min.js"></script>
  <!-- jQuery UI -->
  <script src="<?= BASE_URL ?>plugins/jquery-ui/jquery-ui.min.js"></script>
  <!-- Bootstrap 4 -->
  <!-- <script src="<?= BASE_URL ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script> -->
  <!-- overlayScrollbars -->
  <script src="<?= BASE_URL ?>plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- AdminLTE App -->
  <!-- <script src="<?= BASE_URL ?>dist/js/adminlte.min.js"></script> -->

  <!-- DataTables & Plugins -->
  <script src="<?= BASE_URL ?>plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="<?= BASE_URL ?>plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="<?= BASE_URL ?>plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="<?= BASE_URL ?>plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
  <script src="<?= BASE_URL ?>plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
  <script src="<?= BASE_URL ?>plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>

  <!-- Other plugins -->
  <script src="<?= BASE_URL ?>plugins/moment/moment.min.js"></script>
  <script src="<?= BASE_URL ?>plugins/daterangepicker/daterangepicker.js"></script>
  <script src="<?= BASE_URL ?>plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
  <script src="<?= BASE_URL ?>plugins/select2/js/select2.full.min.js"></script>
  <!--<script src="<?= BASE_URL ?>plugins/jspdf/jspdf.umd.min.js"></script>-->
  <!--<script src="<?= BASE_URL ?>plugins/html2canvas/html2canvas.min.js"></script>-->



  <!-- Bổ sung các thư viện phụ trợ cần thiết cho Buttons -->
<script src="<?= BASE_URL ?>plugins/jszip/jszip.min.js"></script>
<script src="<?= BASE_URL ?>plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?= BASE_URL ?>plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?= BASE_URL ?>plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?= BASE_URL ?>plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?= BASE_URL ?>plugins/datatables-buttons/js/buttons.colVis.min.js"></script>



  <script src="<?= BASE_URL ?>plugins/select2/js/select2.min.js"></script>
  <!-- Add before </head> -->
<link rel="stylesheet" href="<?= BASE_URL ?>plugins/sweetalert2/sweetalert2.min.css">
<script src="<?= BASE_URL ?>plugins/sweetalert2/sweetalert2.all.min.js"></script>
<!--<script data-cfasync="false" nonce="3525c09c-14a1-4400-bd27-97e866b0095a">try{(function(w,d){!function(j,k,l,m){if(j.zaraz)console.error("zaraz is loaded twice");else{j[l]=j[l]||{};j[l].executed=[];j.zaraz={deferred:[],listeners:[]};j.zaraz._v="5850";j.zaraz._n="3525c09c-14a1-4400-bd27-97e866b0095a";j.zaraz.q=[];j.zaraz._f=function(n){return async function(){var o=Array.prototype.slice.call(arguments);j.zaraz.q.push({m:n,a:o})}};for(const p of["track","set","debug"])j.zaraz[p]=j.zaraz._f(p);j.zaraz.init=()=>{var q=k.getElementsByTagName(m)[0],r=k.createElement(m),s=k.getElementsByTagName("title")[0];s&&(j[l].t=k.getElementsByTagName("title")[0].text);j[l].x=Math.random();j[l].w=j.screen.width;j[l].h=j.screen.height;j[l].j=j.innerHeight;j[l].e=j.innerWidth;j[l].l=j.location.href;j[l].r=k.referrer;j[l].k=j.screen.colorDepth;j[l].n=k.characterSet;j[l].o=(new Date).getTimezoneOffset();if(j.dataLayer)for(const t of Object.entries(Object.entries(dataLayer).reduce(((u,v)=>({...u[1],...v[1]})),{})))zaraz.set(t[0],t[1],{scope:"page"});j[l].q=[];for(;j.zaraz.q.length;){const w=j.zaraz.q.shift();j[l].q.push(w)}r.defer=!0;for(const x of[localStorage,sessionStorage])Object.keys(x||{}).filter((z=>z.startsWith("_zaraz_"))).forEach((y=>{try{j[l]["z_"+y.slice(7)]=JSON.parse(x.getItem(y))}catch{j[l]["z_"+y.slice(7)]=x.getItem(y)}}));r.referrerPolicy="origin";r.src="/cdn-cgi/zaraz/s.js?z="+btoa(encodeURIComponent(JSON.stringify(j[l])));q.parentNode.insertBefore(r,q)};["complete","interactive"].includes(k.readyState)?zaraz.init():j.addEventListener("DOMContentLoaded",zaraz.init)}}(w,d,"zarazData","script");window.zaraz._p=async bs=>new Promise((bt=>{if(bs){bs.e&&bs.e.forEach((bu=>{try{const bv=d.querySelector("script[nonce]"),bw=bv?.nonce||bv?.getAttribute("nonce"),bx=d.createElement("script");bw&&(bx.nonce=bw);bx.innerHTML=bu;bx.onload=()=>{d.head.removeChild(bx)};d.head.appendChild(bx)}catch(by){console.error(`Error executing script: ${bu}\n`,by)}}));Promise.allSettled((bs.f||[]).map((bz=>fetch(bz[0],bz[1]))))}bt()}));zaraz._p({"e":["(function(w,d){})(window,document)"]});})(window,document)}catch(e){throw fetch("/cdn-cgi/zaraz/t"),e;};</script></head>-->


  <script>
    $(document).ready(function() {
      $("#example1").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": true,
        // "dom": 'Blfrtip',
        "buttons": ["excel", "pdf", "print", "colvis"]
      }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
  </script>
  <style>
    html,
    body {
      height: 100vh;
    }

    .wrapper {
      min-height: 100vh;
      height: auto;
      display: flex;
      flex-direction: column;
    }

    .content-wrapper {
      flex: 1;
    }
  </style>
</head>