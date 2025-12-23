<!-- VOUCHER PRINT MODAL -->
<div class="modal fade" id="voucherPrintModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <!-- HEADER -->
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold" id="voucherPrintTitle">Expense Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body" id="voucherPrintArea">

        <!-- Company Header -->
        <div class="text-center mb-4">
          <h4 class="fw-bold mb-0">Your Company Name</h4>
          <small class="text-muted" id="voucherTypeText">Expense Payment Voucher</small>
          <hr>
        </div>

        <!-- Voucher Details -->
        <table class="table table-bordered table-sm">
          <tbody>
            <tr>
              <th width="30%">Receiver Type</th>
              <td id="vp_type"></td>
            </tr>
            <tr>
              <th>Receiver Name</th>
              <td id="vp_name"></td>
            </tr>
            <tr>
              <th>Received Amount</th>
              <td class="fw-bold text-success">₹ <span id="vp_amount"></span></td>
            </tr>
            <tr>
              <th>Payment Method</th>
              <td id="vp_method"></td>
            </tr>
            <tr id="vpChequeBlock">
              <th>Cheque Number</th>
              <td id="vp_cheque_no"></td>
            </tr>
            <tr>
              <th>Approved By</th>
              <td id="vp_apv"></td>
            </tr>
            <tr>
              <th>Notes</th>
              <td id="vp_notes"></td>
            </tr>
            <tr>
              <th>Date</th>
              <td id="vp_date"></td>
            </tr>
          </tbody>
        </table>

        <!-- Signatures -->
        <div class="row mt-5 text-center">
          <div class="col-6">
            <p class="mb-4">________________________</p>
            <strong>Receiver Signature</strong>
          </div>
          <div class="col-6">
            <p class="mb-4">________________________</p>
            <strong>Authorized Signature</strong>
          </div>
        </div>

      </div>

      <!-- FOOTER -->
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="printVoucher()">
          🖨 Print Voucher
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>

    </div>
  </div>
</div>
