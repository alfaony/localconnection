<div>
    <form wire:submit.prevent="save">
        <div class="mb-3">
            <label>Nama Promo</label>
            <input type="text" wire:model="name" class="form-control">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Jenis Promo</label>
            <select wire:model="type" class="form-control">
                <option value=""  selected>Pilih Jenis Promo</option>
                @foreach (config('custom.promo_type') as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Nilai</label>
            <input type="number" wire:model="value" class="form-control">
        </div>
        @if($this->type == 'free_months')
            <div class="mb-3">
                <label>Batas Waktu Pendaftaran</label>
                <input type="date" wire:model="register_date" class="form-control">
                <span class="form-text text-danger">Terhitung dari tanggal pendaftaran</span>
            </div>
        @endif

        <div class="mb-3">
            <label>Periode</label>
            <div class="d-flex gap-2">
                <input type="date" wire:model="start_date" class="form-control">
                <input type="date" wire:model="end_date" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label>Kuota (opsional)</label>
            <input type="number" wire:model="quota" class="form-control">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" wire:model="is_active" class="form-check-input" id="activeCheck">
            <label class="form-check-label" for="activeCheck">Aktif</label>
        </div>

        <button class="btn btn-primary" type="submit">Simpan Promo</button>
        <a href="{{ route('promo.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>