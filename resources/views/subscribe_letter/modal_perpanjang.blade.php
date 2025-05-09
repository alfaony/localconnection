<div class="modal fade" id="modalPerpanjang" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('subscribe-letter.update', $letter->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perpanjang Masa Berlaku Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="name" value="{{ $letter->name }}">
                <input type="hidden" name="responsible_user_id" value="{{ $letter->responsible_user_id }}">

                <div class="mb-3">
                    <label>Tanggal Baru Berlaku Dari</label>
                    <input type="date" name="valid_from" class="form-control"
                        value="{{ $letter->valid_from}}" 
                        min="{{ $letter->valid_from }}"
                        required>
                </div>
                <div class="mb-3">
                    <label>Tanggal Baru Berlaku Sampai</label>
                    <input type="date" name="valid_until" class="form-control"
                        value="{{ $letter->valid_until}}" 
                        min="{{ $letter->valid_until }}"
                        required>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Perpanjang</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </form>
  </div>
</div>