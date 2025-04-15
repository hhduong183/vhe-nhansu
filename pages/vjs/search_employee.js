function searchEmployee() {
    let query = document.getElementById("nhanvien").value.trim();
    let dropdown = document.getElementById("employeeList");

    if (query.length < 2) {
        dropdown.style.display = "none";
        return;
    }

    fetch("search_employee.php?q=" + query)
        .then(response => response.text()) // Lấy dữ liệu dạng HTML
        .then(data => {
            dropdown.innerHTML = data;
            dropdown.style.display = "block";

            document.querySelectorAll(".nhanvien-item").forEach(item => {
                item.addEventListener("click", function () {
                    let info = JSON.parse(this.getAttribute("data-info"));
                    document.getElementById("nhanvien").value = info.ten_nv;
                    document.getElementById("ma_nv").value = info.ma_nv;
                    document.getElementById("nhanvien_id").value = info.id; // 👈 Gán ID vào input hidden
                    dropdown.style.display = "none";
                });
            });
        })
        .catch(error => console.error("Lỗi tìm kiếm:", error));
}

// Ẩn dropdown khi click ra ngoài
document.addEventListener("click", function (event) {
    let dropdown = document.getElementById("employeeList");
    let input = document.getElementById("nhanvien");
    if (!input.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = "none";
    }
});