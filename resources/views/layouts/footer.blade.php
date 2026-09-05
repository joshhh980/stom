<footer class="main-footer text-sm">
    <strong>Copyright © {{ date('Y') }}. </strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>{{ config('settings.short_name', 'System') }} (by: <a href="mailto:oretnom23@gmail.com" target="blank">oretnom23</a>)</b> v1.0
    </div>
</footer>

<!-- Global AJAX Modals and Script Logic -->
<script>
  $(document).ready(function(){
     $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
     });

     window.viewer_modal = function($src = ''){
          start_loader()
          var t = $src.split('.')
          t = t[1] || ''
          if(t =='mp4'){
            var view = $("<video src='"+$src+"' controls autoplay></video>")
          }else{
            var view = $("<img src='"+$src+"' />")
          }
          $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
          $('#viewer_modal .modal-content').append(view)
          $('#viewer_modal').modal({
              show:true,
              backdrop:'static',
              keyboard:false,
              focus:true
          })
          end_loader()  
     }

     window.uni_modal = function($title = '' , $url='',$size=""){
        start_loader()
        $.ajax({
            url: $url,
            error: err => {
                console.log(err)
                alert("An error occurred")
            },
            success: function(resp){
                if(resp){
                    $('#uni_modal .modal-title').html($title)
                    $('#uni_modal .modal-body').html(resp)
                    if($size != ''){
                        $('#uni_modal .modal-dialog').addClass($size+' modal-dialog-centered')
                    }else{
                        $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md modal-dialog-centered")
                    }
                    $('#uni_modal').modal({
                      show:true,
                      backdrop:'static',
                      keyboard:false,
                      focus:true
                    })
                    end_loader()
                }
            }
        })
     }

     window._conf = function($msg='',$func='',$params = []){
        $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
        $('#confirm_modal .modal-body').html($msg)
        $('#confirm_modal').modal('show')
     }
  })
</script>

<!-- Scripts Bundle Dependencies -->
<script>$.widget.bridge('uibutton', $.ui.button)</script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('plugins/sparklines/sparkline.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
<script src="{{ asset('plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('dist/js/adminlte.js') }}"></script>
