@if($letters->count())
    <div class="alert alert-info">
        <h5>⚠️ Pengingat Surat Langganan</h5>
        <ul class="mb-0">
            @foreach($letters as $l)
                <li>
                    <strong>{{ $l->name }}</strong>
                    – Berlaku Sampai:
                    <span class="badge bg-{{ $l->getColorStatusFor('valid_until') }}">
                        {{ $l->valid_until }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif