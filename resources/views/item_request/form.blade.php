@csrf

<div class="form-group">
    <label for="category_id">Kategori</label>
    <select name="category_id" id="category_id" class="form-control" required>
        <option value="">-- Pilih Kategori --</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('category_id', $itemRequest->category_id ?? '') == $cat->id)>
                {{ $cat->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group mt-3">
    <label for="item_name">Nama Barang</label>
    <input type="text" name="item_name" id="item_name" class="form-control"
           value="{{ old('item_name', $itemRequest->item_name ?? '') }}" required>
</div>

<div class="form-group mt-3">
    <label for="description">Deskripsi</label>
    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $itemRequest->description ?? '') }}</textarea>
</div>

<div class="form-group mt-3">
    <label for="estimated_price">Estimasi Harga</label>
    <input type="number" name="estimated_price" id="estimated_price" class="form-control"
           value="{{ old('estimated_price', $itemRequest->estimated_price ?? '') }}" required>
</div>