<div class="modal fade" tabindex="-1" id="showModal" data-keyboard="false" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content end-user-order-modal">
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title end-user-order-title">Order Information</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="fas fa-times"></i>
                </div>
            </div>

            <div class="modal-body pt-3">
                <div class="end-user-order-info">
                    <div class="end-user-order-row">
                        <span class="end-user-order-label">Order Id</span>
                        <span class="end-user-order-value" id="modal_order_id"></span>
                    </div>
                    <div class="end-user-order-row">
                        <span class="end-user-order-label">IMO ID</span>
                        <span class="end-user-order-value" id="modal_imo_id"></span>
                    </div>
                    <div class="end-user-order-row">
                        <span class="end-user-order-label">Amount</span>
                        <span class="end-user-order-value">
                            <span id="modal_amount"></span>
                            <i class="fas fa-gem end-user-diamond-icon" title="Diamond"></i>
                        </span>
                    </div>
                    <div class="end-user-order-row">
                        <span class="end-user-order-label">Status</span>
                        <span class="end-user-order-value" id="modal_status"></span>
                    </div>
                    <div class="end-user-order-row">
                        <span class="end-user-order-label">Created On</span>
                        <span class="end-user-order-value" id="modal_created_on"></span>
                    </div>
                    <div class="end-user-order-row end-user-bot-only" style="display: none;">
                        <span class="end-user-order-label">Bot status</span>
                        <span class="end-user-order-value" id="modal_bot_status"></span>
                    </div>
                    <div class="end-user-order-row end-user-bot-only" style="display: none;">
                        <span class="end-user-order-label">BOT</span>
                        <span class="end-user-order-value" id="modal_bot"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
