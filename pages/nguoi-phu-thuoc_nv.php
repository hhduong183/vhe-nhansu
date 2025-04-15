<?php
session_start();
include('../config.php');
include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');

// Protect access
if (!isset($_SESSION['username']) || !isset($_SESSION['level'])) {
    header("Location: dang-nhap.php");
    exit();
}

// Optimize query with index hint and selected fields
$nhan_vien_id = $_SESSION['idNhanVien'];
$stmt = $conn->prepare("
    SELECT 
        npt.ho_ten,
        npt.ngay_sinh,
        npt.quan_he,
        npt.mst_nguoi_phu_thuoc,
        npt.loai_giay_to,
        npt.so_giay_to,
        npt.start_time,
        npt.end_time
    FROM nguoi_phu_thuoc npt 
    USE INDEX (idx_nhan_vien_id)
    WHERE npt.nhan_vien_id = ?
");
$stmt->bind_param("i", $nhan_vien_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Add viewport meta tag for mobile responsiveness -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Add mobile-friendly styles -->
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    table.table {
        width: 100%;
        table-layout: fixed;
    }
    
    table.table th, table.table td {
        vertical-align: middle !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Column widths */
    table.table th:nth-child(1) { width: 5%; }
    table.table th:nth-child(2) { width: 15%; }
    table.table th:nth-child(3) { width: 10%; }
    table.table th:nth-child(4) { width: 10%; }
    table.table th:nth-child(5) { width: 15%; }
    table.table th:nth-child(6) { width: 15%; }
    table.table th:nth-child(7) { width: 10%; }
    table.table th:nth-child(8) { width: 10%; }
    table.table th:nth-child(9) { width: 10%; }

    @media screen and (max-width: 768px) {
        .table-responsive {
            border: 0;
        }
        
        table.table {
            table-layout: auto;
        }
        
        .table-responsive tbody tr {
            display: block;
            margin-bottom: 1rem;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            border-radius: 4px;
        }
        
        .table-responsive tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        
        .table-responsive tbody td:last-child {
            border-bottom: none;
        }
        
        .table-responsive tbody td:before {
            content: attr(data-label);
            font-weight: 500;
            margin-right: 1rem;
            margin-left: 1rem;
            color: #495057;
        }
        
        .table-responsive thead {
            display: none;
        }
        
        /* Reset column widths for mobile */
        table.table th, table.table td {
            width: auto !important;
            white-space: normal;
        }
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Thông tin người phụ thuộc</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body"> <!-- Remove padding for better table alignment -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0" id="example1">
                            <thead class="thead-light">
                                <tr>
                                    <th>STT</th>
                                    <th>Người phụ thuộc</th>
                                    <th>Ngày sinh</th>
                                    <th>Quan hệ</th>
                                    <th>MST người phụ thuộc</th>
                                    <th>Loại giấy tờ</th>
                                    <th>Số giấy tờ</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 1; while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="STT"><?= $stt++ ?></td>
                                    <td data-label="Người phụ thuộc"><?= htmlspecialchars($row['ho_ten']) ?></td>
                                    <td data-label="Ngày sinh"><?= date('d/m/Y', strtotime($row['ngay_sinh'])) ?></td>
                                    <td data-label="Quan hệ"><?= htmlspecialchars($row['quan_he']) ?></td>
                                    <td data-label="MST người phụ thuộc"><?= htmlspecialchars($row['mst_nguoi_phu_thuoc']) ?></td>
                                    <td data-label="Loại giấy tờ"><?= htmlspecialchars($row['loai_giay_to']) ?></td>
                                    <td data-label="Số giấy tờ"><?= htmlspecialchars($row['so_giay_to']) ?></td>
                                    <td data-label="Ngày bắt đầu"><?= date('d/m/Y', strtotime($row['start_time'])) ?></td>
                                    <td data-label="Ngày kết thúc"><?= $row['end_time'] !== NULL ? date('d/m/Y', strtotime($row['end_time'])) : "" ?></td>
                                </tr>
                                <?php endwhile ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
$stmt->close();
include('../layouts/footer.php'); 
?>