{{-- Unified delete confirmation modal — matches Billora Bootstrap RTL theme --}}
<div class="modal fade" id="appConfirmModal" tabindex="-1" role="dialog" aria-labelledby="appConfirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 420px;">
        <div class="modal-content border-0 shadow app-confirm-modal">
            <div class="modal-body text-center px-4 pt-5 pb-4">
                <div class="app-confirm-icon mb-3">
                    <i class="fe fe-trash-2"></i>
                </div>
                <h5 class="font-weight-bold mb-2" id="appConfirmModalTitle">تأكيد الحذف</h5>
                <p class="text-muted mb-0 px-2" id="appConfirmModalMessage">هل أنت متأكد من تنفيذ هذا الإجراء؟ لا يمكن التراجع عنه.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center px-4 pb-4 pt-0">
                <button type="button" class="btn btn-light px-4" data-dismiss="modal" id="appConfirmCancelBtn">إلغاء</button>
                <button type="button" class="btn btn-danger px-4" id="appConfirmOkBtn">نعم، احذف</button>
            </div>
        </div>
    </div>
</div>
