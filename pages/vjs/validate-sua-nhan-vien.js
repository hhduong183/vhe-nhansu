// Validate form sửa nhân viên
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editStaffForm');  // Changed form ID
    if (!form) return;

    // Thêm dấu * cho các trường bắt buộc
    const requiredFields = form.querySelectorAll('label:has(span.text-danger)');
    requiredFields.forEach(label => {
        label.style.fontWeight = 'bold';
    });

    // Hàm kiểm tra CMND/CCCD
    function validateCMND(cmnd) {
        return /^\d{9}$|^\d{12}$/.test(cmnd);
    }

    // Hàm kiểm tra số điện thoại
    function validatePhone(phone) {
        return /^(0|\+84)\d{9}$/.test(phone);
    }

    // Hàm tạo và hiển thị thông báo lỗi
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        const errorDiv = formGroup.querySelector('.error-message') || document.createElement('div');
        
        errorDiv.className = 'error-message text-danger';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;

        if (!formGroup.querySelector('.error-message')) {
            formGroup.appendChild(errorDiv);
        }
        
        input.classList.add('is-invalid');
    }

    // Hàm xóa thông báo lỗi
    function clearError(input) {
        const formGroup = input.closest('.form-group');
        const errorDiv = formGroup.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
        input.classList.remove('is-invalid');
    }

    // Kiểm tra khi submit form
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Xóa tất cả thông báo lỗi cũ
        form.querySelectorAll('.error-message').forEach(error => error.remove());
        form.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));

        // Kiểm tra các trường input
        const requiredInputs = form.querySelectorAll('input[type="text"], input[type="date"]');
        requiredInputs.forEach(input => {
            if (input.hasAttribute('required') || input.closest('.form-group').querySelector('label span.text-danger')) {
                if (!input.value.trim()) {
                    isValid = false;
                    showError(input, 'Vui lòng nhập thông tin này');
                } else {
                    clearError(input);
                }
            }
        });

        // Kiểm tra các trường select
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            if (select.closest('.form-group').querySelector('label span.text-danger')) {
                if (select.value === 'chon') {
                    isValid = false;
                    showError(select, 'Vui lòng chọn một giá trị');
                } else {
                    clearError(select);
                }
            }
        });

        // Kiểm tra định dạng CMND/CCCD
        const cmndInput = form.querySelector('input[name="CMND"]');
        if (cmndInput && cmndInput.value && !validateCMND(cmndInput.value)) {
            isValid = false;
            showError(cmndInput, 'CMND/CCCD không hợp lệ (9 hoặc 12 số)');
        }

        // Kiểm tra định dạng số điện thoại
        const phoneInput = form.querySelector('input[name="soDienthoai"]');
        if (phoneInput && phoneInput.value && !validatePhone(phoneInput.value)) {
            isValid = false;
            showError(phoneInput, 'Số điện thoại không hợp lệ');
        }

        if (!isValid) {
            e.preventDefault();
            // Cuộn đến trường lỗi đầu tiên
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Xóa thông báo lỗi khi người dùng nhập liệu
    form.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('input', function() {
            clearError(this);
        });
        element.addEventListener('change', function() {
            clearError(this);
        });
    });
});