<div class="modal fade" tabindex="-1" id="showBalanceAdjustDetailModal" data-keyboard="false" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content adjust-detail-modal">
            <div class="modal-header border-0 pb-0">
                <h3 class="adjust-detail-title">Adjust Balance Information</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="fas fa-times"></i>
                </div>
            </div>

            <div class="modal-body pt-3">
                <div class="adjust-detail-info">
                    <div class="adjust-detail-row">
                        <span class="adjust-detail-label">ID</span>
                        <span class="adjust-detail-value" id="modal_adjust_id"></span>
                    </div>
                    <div class="adjust-detail-row">
                        <span class="adjust-detail-label">User</span>
                        <span class="adjust-detail-value" id="modal_adjust_user"></span>
                    </div>
                    <div class="adjust-detail-row">
                        <span class="adjust-detail-label">Amount</span>
                        <span class="adjust-detail-value">
                            <span id="modal_adjust_amount"></span>
                            <i class="fas fa-gem adjust-diamond-icon" title="Diamond"></i>
                        </span>
                    </div>
                    <div class="adjust-detail-row">
                        <span class="adjust-detail-label">Status</span>
                        <span class="adjust-detail-value" id="modal_adjust_type"></span>
                    </div>
                    <div class="adjust-detail-row">
                        <span class="adjust-detail-label">Created On</span>
                        <span class="adjust-detail-value" id="modal_adjust_created_on"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
