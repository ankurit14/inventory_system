<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="viewTitle"></h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <!-- Voucher Image Block -->
                <div class="text-center mb-4">
                    <div style="
                        display:inline-block;
                        padding:8px;
                        border:2px solid #ccc;
                        border-radius:12px;
                        background:#f8f9fa;
                    ">
                        <img id="voucherImage"
                             src=""
                             style="max-width:250px; border-radius:8px;">
                    </div>
                </div>

                <div class="row mt-4">

                    <!-- LEFT SIDE -->
                    <div class="col-md-6 border-end">
                        <h4 class="text-primary mb-3">Person Details</h4>
                        <p><strong>Receiver Type:</strong> <span id="p_type"></span></p>
                        <p><strong>Name:</strong> <span id="p_name"></span></p>
                        <p><strong>Approved By:</strong> <span id="p_apv"></span></p>
                        <p><strong>Date:</strong> <span id="p_date"></span></p>
                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="col-md-6">
                        <h4 class="text-primary mb-3">Payment Details</h4>
                        <p><strong>Amount:</strong> ₹ <span id="p_amount"></span></p>
                        <p><strong>Payment Method:</strong> <span id="p_method"></span></p>

                     <div id="chequeBlock">
    <p><strong>Cheque No. / Reference No.:</strong> <span id="cheque_no"></span></p>
</div>

                    </div>
                    <div class="col-md-12">
                        <p><strong>Notes:</strong> <span id="p_notes"></span></p>
                    </div>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

