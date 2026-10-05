<!-- Back-to-top -->
<a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>
@include('layouts.flash-modal')
<!-- JQuery min js -->
<script src="{{URL::asset('assets/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap Bundle js -->
<script src="{{URL::asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- Ionicons js -->
<script src="{{URL::asset('assets/plugins/ionicons/ionicons.js')}}"></script>
<!-- Moment js -->
<script src="{{URL::asset('assets/plugins/moment/moment.js')}}"></script>

<!-- Rating js-->
<script src="{{URL::asset('assets/plugins/rating/jquery.rating-stars.js')}}"></script>
<script src="{{URL::asset('assets/plugins/rating/jquery.barrating.js')}}"></script>

<!--Internal  Perfect-scrollbar js -->
<script src="{{URL::asset('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/perfect-scrollbar/p-scroll.js')}}"></script>
<!--Internal Sparkline js -->
<script src="{{URL::asset('assets/plugins/jquery-sparkline/jquery.sparkline.min.js')}}"></script>
<!-- Custom Scroll bar Js-->
<script src="{{URL::asset('assets/plugins/mscrollbar/jquery.mCustomScrollbar.concat.min.js')}}"></script>
<!-- right-sidebar js -->
<script src="{{URL::asset('assets/plugins/sidebar/sidebar-rtl.js')}}"></script>
<script src="{{URL::asset('assets/plugins/sidebar/sidebar-custom.js')}}"></script>
<!-- Eva-icons js -->
<script src="{{URL::asset('assets/js/eva-icons.min.js')}}"></script>
@yield('js')
<!-- Sticky js -->
<script src="{{URL::asset('assets/js/sticky.js')}}"></script>
<!-- custom js -->
<script src="{{URL::asset('assets/js/custom.js')}}"></script><!-- Left-menu js-->
<script src="{{URL::asset('assets/plugins/side-menu/sidemenu.js')}}"></script>
<script>
(function ($) {
    if (!$ || !$('#appConfirmModal').length) return;

    var $modal = $('#appConfirmModal');
    var pendingForm = null;

    $(document).on('submit', 'form[data-confirm]', function (e) {
        var form = this;
        if (form.dataset.confirmAccepted === '1') {
            delete form.dataset.confirmAccepted;
            return true;
        }

        e.preventDefault();
        pendingForm = form;

        $('#appConfirmModalTitle').text(form.getAttribute('data-confirm-title') || 'تأكيد الحذف');
        $('#appConfirmModalMessage').text(form.getAttribute('data-confirm') || 'هل أنت متأكد من الحذف؟ لا يمكن التراجع عن هذا الإجراء.');
        $('#appConfirmOkBtn').text(form.getAttribute('data-confirm-ok') || 'نعم، احذف');

        var iconClass = form.getAttribute('data-confirm-icon') || 'fe fe-trash-2';
        $modal.find('.app-confirm-icon i').attr('class', iconClass);

        $modal.modal('show');
        return false;
    });

    $('#appConfirmOkBtn').on('click', function () {
        if (!pendingForm) return;
        var form = pendingForm;
        pendingForm = null;
        form.dataset.confirmAccepted = '1';
        $modal.modal('hide');
        form.submit();
    });

    $modal.on('hidden.bs.modal', function () {
        pendingForm = null;
    });
})(window.jQuery);

(function ($) {
    if (!$ || !$('#appToast').length) return;
    var $toast = $('#appToast');
    var $bar = $('#appToastBar');
    var hideTimer = null;
    var duration = 1500;

    function hideToast() {
        if (hideTimer) {
            clearTimeout(hideTimer);
            hideTimer = null;
        }
        $bar.removeClass('is-run');
        $toast.removeClass('is-show').addClass('is-hide');
        setTimeout(function () {
            $toast.removeClass('is-hide').attr('hidden', true);
        }, 300);
    }

    function showToast(type, message) {
        var isSuccess = type === 'success';
        if (hideTimer) clearTimeout(hideTimer);
        $toast.removeClass('is-hide');
        $toast.toggleClass('is-success', isSuccess).toggleClass('is-error', !isSuccess);
        $('#appToastIcon').attr('class', isSuccess ? 'fe fe-check' : 'fe fe-shield');
        $('#appToastMessage').text(message || '');
        $bar.removeClass('is-run');
        $toast.removeAttr('hidden').addClass('is-show');
        void $bar[0].offsetWidth;
        $bar.addClass('is-run');
        hideTimer = setTimeout(hideToast, duration);
    }

    $('#appToastClose').on('click', hideToast);

    var $data = $('#appFlashData');
    if ($data.length) {
        showToast($data.data('type') || 'success', $data.data('message') || '');
    }
})(window.jQuery);
</script>
<script>
document.addEventListener('click', function (e) {
    var addBtn = e.target.closest('.js-add-item');
    var delBtn = e.target.closest('button.del');
    var table = addBtn && addBtn.dataset.table ? document.getElementById(addBtn.dataset.table) : (delBtn ? delBtn.closest('table') : null);
    if (!table) return;
    setTimeout(function () {
        table.querySelectorAll('tbody tr').forEach(function (tr, i) {
            var cell = tr.querySelector('td.col-serial');
            if (cell) cell.textContent = String(i + 1);
        });
    }, 0);
});
</script>