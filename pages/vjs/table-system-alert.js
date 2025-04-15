$(document).ready(function () {
    const table = $('#dataTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        buttons: ["copy", "csv", "excel", "pdf", "print"]
    });

    table.buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');

    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('add') || urlParams.has('update') || urlParams.has('del')) {
        let message = '';
        if (urlParams.has('add')) message = 'Thêm thành công';
        if (urlParams.has('update')) message = 'Cập nhật thành công';
        if (urlParams.has('del')) message = 'Xóa thành công';

        Swal.fire({
            icon: 'success',
            title: 'Thành công',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    }
});

function editItem(modalId, data) {
    for (const [key, value] of Object.entries(data)) {
        $(`#${modalId} [name="${key}"]`).val(value);
    }
    $(`#${modalId}`).modal('show');
}

function deleteItem(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: "Bạn có chắc muốn xóa mục này?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete=${id}`;
        }
    });
}
