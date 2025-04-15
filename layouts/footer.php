<!-- /.content-wrapper -->
<!-- <footer class="main-footer">
 <div class="float-right d-none d-sm-block">
   <b>Version</b> 1.3.30
 </div>
 <strong><a href="" target="_blank">Quản lý nhân sự - Viet Han Engineering</a>.</strong>
</footer> -->
 <footer class="main-footer ">
  <div style="display: flex; justify-content: space-between;"> 
    <div>
        <strong><a href="" target="_blank">Quản lý nhân sự - VHE <span>&reg; 2025</span></a>.</strong>
    </div>

    <div style="text-align:left; margin-right:100px;">
        <b>Version</b> v0.9.1-beta.2
    </div>
</footer>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
<ul class="nav nav-tabs nav-justified control-sidebar-tabs">
    <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
    <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
  </ul>
</aside>
<!-- /.control-sidebar -->
<!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
<div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="<?= BASE_URL ?>plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?= BASE_URL ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="<?= BASE_URL ?>plugins/select2/js/select2.full.min.js"></script>
<!-- InputMask -->
<script src="<?= BASE_URL ?>plugins/moment/moment.min.js"></script>
<script src="<?= BASE_URL ?>plugins/inputmask/jquery.inputmask.min.js"></script>
<!-- date-range-picker -->
<script src="<?= BASE_URL ?>plugins/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap datepicker -->
<script src="<?= BASE_URL ?>plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- bootstrap color picker -->
<script src="<?= BASE_URL ?>plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<!-- DataTables -->
<script src="<?= BASE_URL ?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- bootstrap time picker -->
<script src="<?= BASE_URL ?>plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<!-- BS-Stepper -->
<script src="<?= BASE_URL ?>plugins/bs-stepper/js/bs-stepper.min.js"></script>
<!-- dropzonejs -->
<script src="<?= BASE_URL ?>plugins/dropzone/min/dropzone.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= BASE_URL ?>dist/js/adminlte.min.js"></script>
<!-- CK Editor -->
<!-- <script src="../bower_components/ckeditor/ckeditor.js"></script> -->
<!-- <script src="https://cdn.ckeditor.com/4.25.0/standard/ckeditor.js"></script> -->

<!-- Buttons extension CSS & JS -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>

<!-- Optional: For Excel, PDF, and Print buttons -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>





<!-- AdminLTE App -->
<!-- Page script -->
<script>
  $(function() {
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    // CKEDITOR.replace('editor1')

    //Initialize Select2 Elements
    $('.select2').select2()
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })

    //Datemask dd/mm/yyyy
    $('#datemask').inputmask('dd/mm/yyyy', {
      'placeholder': 'dd/mm/yyyy'
    })
    //Datemask2 mm/dd/yyyy
    $('#datemask2').inputmask('mm/dd/yyyy', {
      'placeholder': 'mm/dd/yyyy'
    })
    //Money Euro
    $('[data-mask]').inputmask()

    //Date range picker
    $('#reservation').daterangepicker()
    //Date range picker with time picker
    $('#reservationtime').daterangepicker({
      timePicker: true,
      timePickerIncrement: 30,
      locale: {
        format: 'MM/DD/YYYY hh:mm A'
      }
    })
    //Date range as a button
    $('#daterange-btn').daterangepicker(
      {
        ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
      },
      function(start, end) {
        $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
      }
    )

    //Date picker
    $('#datepicker').datetimepicker({
      format: 'L'
    })

    // datatable

    $('#example3').DataTable({
      "lengthMenu": [6, 10, 25, 50, 100],
      "pageLength": 6,
      "responsive": true,
      "autoWidth": false
    })
    $('#example4').DataTable({
      "responsive": true,
      "autoWidth": false
    })
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true
    })

    //Bootstrap Switch
    $('[data-bootstrap-switch]').each(function(){
      $(this).bootstrapSwitch('state', $(this).prop('checked'))
    })

    //Colorpicker
    $('.my-colorpicker1').colorpicker()
    //color picker with addon
    $('.my-colorpicker2').colorpicker()

    // send value to modal
    $('#exampleModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget) // Button that triggered the modal
      var recipient = button.data('whatever') // Extract info from data-* attributes
      // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
      // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
      var modal = $(this)
      modal.find('.modal-title').text('New message to ' + recipient)
      modal.find('.modal-body input').val(recipient)
    })
  })

  // tinh phu cap / AJAX
  $('#tinhPhuCap').click(function() {

    var idNhanVien = $('#idNhanVien').val();
    var soNgayCong = $('#soNgayCong').val();

    // kiem tra rong
    if (soNgayCong != '') {
      $.ajax({
        url: "ajax.php",
        method: "POST",
        data: {
          idNhanVien: idNhanVien,
          soNgayCong: soNgayCong
        },
        dataType: "JSON",
        success: function(data) {
          document.getElementById('phuCap').value = data;
        }
      })
    } else {
      alert("ERROR! Vui lòng nhập số ngày công.");
    }
  })
</script>
<script>
    let timeout = 10 * 60 * 1000; // 10 phút

    let logoutTimer = setTimeout(autoLogout, timeout);

    function resetTimer() {
        clearTimeout(logoutTimer);
        logoutTimer = setTimeout(autoLogout, timeout);
    }

    function autoLogout() {
        alert("Bạn đã bị đăng xuất do không hoạt động trong 10 phút.");
        window.location.href = "../dang-xuat.php";
      }

    // Lắng nghe các sự kiện hoạt động của người dùng
    document.addEventListener("mousemove", resetTimer);
    document.addEventListener("keypress", resetTimer);
    document.addEventListener("click", resetTimer);
    document.addEventListener("scroll", resetTimer);
</script>

</body>

</html>