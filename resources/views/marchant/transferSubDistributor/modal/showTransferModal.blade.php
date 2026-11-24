<div class="modal fade" tabindex="-1" id="showTransferDetailModal" data-keyboard="false" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content transfer-detail-modal">
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title transfer-detail-title">Transfer Information</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="fas fa-times"></i>
                </div>
            </div>

            <div class="modal-body pt-3">
                <div class="transfer-detail-info">
                    <div class="transfer-detail-row">
                        <span class="transfer-detail-label">ID</span>
                        <span class="transfer-detail-value" id="modal_transfer_id"></span>
                    </div>
                    <div class="transfer-detail-row">
                        <span class="transfer-detail-label">From</span>
                        <span class="transfer-detail-value" id="modal_from_user"></span>
                    </div>
                    <div class="transfer-detail-row">
                        <span class="transfer-detail-label">To</span>
                        <span class="transfer-detail-value" id="modal_to_user"></span>
                    </div>
                    <div class="transfer-detail-row">
                        <span class="transfer-detail-label">Amount</span>
                        <span class="transfer-detail-value">
                            <span id="modal_amount"></span>
                            <i class="fas fa-gem transfer-diamond-icon" title="Diamond"></i>
                        </span>
                    </div>
                    <div class="transfer-detail-row">
                        <span class="transfer-detail-label">Status</span>
                        <span class="transfer-detail-value" id="modal_status"></span>
                    </div>
                    <div class="transfer-detail-row">
                        <span class="transfer-detail-label">Created On</span>
                        <span class="transfer-detail-value" id="modal_created_on"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
