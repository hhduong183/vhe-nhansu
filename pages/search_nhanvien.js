function searchEmployee() {
    let query = document.getElementById("nhanvien").value;
    if (query.length < 2) {
        document.getElementById("employeeList").style.display = "none";
        return;
    }
    
    fetch("search_nhanvien.php?q=" + query)
        .then(response => response.json())
        .then(data => {
            let dropdown = document.getElementById("employeeList");
            dropdown.innerHTML = "";
            if (data.length > 0) {
                dropdown.style.display = "block";
                data.forEach(emp => {
                    let item = document.createElement("div");
                    item.className = "dropdown-item";
                    item.innerText = emp.ten;
                    item.onclick = function() {
                        document.getElementById("nhanvien").value = emp.ten;
                        document.getElementById("nhanvien_id").value = emp.id;
                        dropdown.style.display = "none";
                    };
                    dropdown.appendChild(item);
                });
            } else {
                dropdown.style.display = "none";
            }
        });
}