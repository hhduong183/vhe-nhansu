<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
include(ROOT_PATH . '/layouts/header.php');
include(ROOT_PATH . '/layouts/topbar.php');
include(ROOT_PATH . '/layouts/sidebar.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Check session
if (!isset($_SESSION['username'], $_SESSION['level'], $_SESSION['idNhanVien'])) {
    die("Bạn chưa đăng nhập.");
}

$session_idNhanVien = $_SESSION['idNhanVien'];

// Get employee contracts
$sql = "SELECT hd.id, hd.ma_hop_dong, hd.ngay_bat_dau, hd.ngay_ket_thuc, 
        hd.muc_luong, hd.phucap_tnh, hd.phucap_nghe, hd.phucap_nha_tro, 
        hd.phucap_dac_biet, lhd.ten_hop_dong, hd.trang_thai
        FROM hop_dong_lao_dong hd 
        JOIN hop_dong_type lhd ON hd.loai_hop_dong_id = lhd.id 
        WHERE hd.nhanvien_id = ?
        ORDER BY hd.ngay_bat_dau DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session_idNhanVien);
$stmt->execute();
$result = $stmt->get_result();
$contracts = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Update the CSS section -->
<style>
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 0;
    }
    
    .contract-details {
        margin: 10px 0;
        border-radius: 4px;
    }
    
    .contract-card {
        border: 1px solid #ddd;
        margin-bottom: 10px;
        border-radius: 4px;
        background: #fff;
    }
    
    .contract-card .header {
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .contract-card .content {
        padding: 15px;
        display: none;
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
        background: #f4f4f4;
    }
    
    .contract-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .info-group {
        margin-bottom: 5px;
    }
    
    .info-label {
        font-weight: bold;
        color: #666;
    }
    
    @media screen and (max-width: 768px) {
        .content-wrapper {
            padding: 10px;
        }
        
        .box {
            margin-bottom: 10px;
        }
        
        .contract-info {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Update the HTML structure -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>Hợp Đồng Lao Động</h1>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-body">
                <?php foreach ($contracts as $index => $contract): ?>
                    <div class="contract-card">
                    <a href="#" onclick="toggleCard(<?= $index ?>); return false;">
                    
                        <div class="header">
                            <div>
                                <strong><?= htmlspecialchars($contract['ma_hop_dong']) ?></strong>
                                <span class="text-muted"> - <?= htmlspecialchars($contract['ten_hop_dong']) ?></span>
                            </div>
                            <div>
                                <span class="status-badge"><?= htmlspecialchars($contract['trang_thai']) ?></span>
                                <button class="btn btn-info btn-xs" onclick="toggleCard(<?= $index ?>)">
                                    
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            </div>
                    
                            </a>        
                    <div class="content" id="content_<?= $index ?>">
                        <div class="contract-info">
                            <div>
                                <div class="info-group">
                                    <div class="info-label">Ngày bắt đầu</div>
                                    <div><?= date('d-m-Y', strtotime($contract['ngay_bat_dau'])) ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Ngày kết thúc</div>
                                    <div><?= (!empty($contract['ngay_ket_thuc']) && $contract['ngay_ket_thuc'] != '0000-00-00') 
                                        ? date('d-m-Y', strtotime($contract['ngay_ket_thuc'])) 
                                        : 'Không xác định' ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Mức lương</div>
                                    <div><?= number_format($contract['muc_luong'],0,".",",") ?> VNĐ</div>
                                </div>
                            </div>
                            <div>
                                <div class="info-group">
                                    <div class="info-label">Phụ cấp trách nhiệm</div>
                                    <div><?= number_format($contract['phucap_tnh'],0,".",",") ?> VNĐ</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Phụ cấp nghề</div>
                                    <div><?= number_format($contract['phucap_nghe'],0,".",",") ?> VNĐ</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Phụ cấp nhà trọ</div>
                                    <div><?= number_format($contract['phucap_nha_tro'],0,".",",") ?> VNĐ</div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Phụ cấp đặc biệt</div>
                                    <div><?= number_format($contract['phucap_dac_biet'],0,".",",") ?> VNĐ</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<!-- Update the JavaScript -->
<script>
function toggleCard(index) {
    const content = document.getElementById('content_' + index);
    const allContents = document.querySelectorAll('[id^="content_"]');
    
    allContents.forEach(item => {
        if (item !== content && item.style.display === 'block') {
            item.style.display = 'none';
        }
    });
    
    content.style.display = content.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php 
$stmt->close();
$conn->close();
include(ROOT_PATH . '/layouts/footer.php')
?>