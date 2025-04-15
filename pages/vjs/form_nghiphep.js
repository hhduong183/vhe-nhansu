document.getElementById("leaveRequestForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Ngăn chặn tải lại trang
    
    var form = this;

    // Gửi dữ liệu bằng AJAX
    var formData = new FormData(form);
    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.text()) // Xử lý phản hồi từ server
    .then(data => {
        // Hiển thị modal thông báo thành công
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        
        // Xóa dữ liệu trên form sau khi gửi
        form.reset();
    })
    .catch(error => console.error('Lỗi:', error));
});

$(document).ready(function () {
    $("#leaveRequestForm").submit(function (event) {
        event.preventDefault(); // Ngăn chặn hành vi mặc định của form
        
        // Lấy dữ liệu form
        var formData = $(this).serialize();
        
        // Gửi AJAX để xử lý yêu cầu nghỉ phép
        $.post( "../de_xuat_nghi.php", formData, function (response) {
            // Kiểm tra nếu phản hồi thành công
            if (response.success) {
                // Hiển thị modal thông báo
                $("#successModal").modal("show");

                // Reset form sau khi gửi thành công
                $("#leaveRequestForm")[0].reset();
            } else {
                alert("Gửi đề xuất thất bại! Vui lòng thử lại.");
            }
        }, "json");
    });
});

