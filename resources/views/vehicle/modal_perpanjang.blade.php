<div class="modal fade" id="modalPerpanjangVehicle" tabindex="-1" aria-labelledby="perpanjangVehicleLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('vehicle.update', $vehicle->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perpanjang Masa Berlaku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicle_id }}">
                <input type="hidden" name="vehicle_type" value="{{ $vehicle->vehicle_type }}">
                <input type="hidden" name="type" value="{{ $vehicle->type }}">
                <input type="hidden" name="position" value="{{ $vehicle->position }}">
                <input type="hidden" name="pic_user_id" value="{{ $vehicle->pic_user_id }}">

                <div class="mb-3">
                    <label>Service Terakhir Baru</label>
                    <input type="date" name="service_terakhir" class="form-control"
                        value="{{ $vehicle->service_terakhir }}"
                        min="{{ $vehicle->service_terakhir }}"
                        >
                </div>

                <div class="mb-3">
                    <label>Perpanjang STNK</label>
                    <input type="date" name="subscription_stnk" class="form-control"
                        value="{{ $vehicle->subscription_stnk }}"
                        min="{{ $vehicle->subscription_stnk }}"
                        >
                </div>

                <div class="mb-3">
                    <label>Perpanjang KIR</label>
                    <input type="date" name="subscription_kir" class="form-control"
                        value="{{ $vehicle->subscription_kir }}"
                        min="{{ $vehicle->subscription_kir }}">
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