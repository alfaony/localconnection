@if($vehicles->count())
    <div class="alert alert-info">
        <h5>⚠️ Pengingat Kendaraan</h5>
        <ul class="mb-0">
            @foreach($vehicles as $v)
                <li>
                    <strong>{{ $v->vehicle_id }} {{ $v->vehicle_type }} {{ $v->type }}</strong>
                    <ul>
                        @if($v->subscription_stnk && $v->getStatusFor('subscription_stnk'))
                        <li>
                                Jatuh Tempo STNK:
                                <span class="badge bg-{{ $v->getColorStatusFor('subscription_stnk') }}">
                                    {{ $v->subscription_stnk }}
                                </span>
                        </li>
                        @endif
                        @if($v->subscription_kir && $v->getStatusFor('subscription_kir'))
                        <li>
                                Jatuh Tempo  KIR:
                                <span class="badge bg-{{ $v->getColorStatusFor('subscription_kir') }}">
                                    {{ $v->subscription_kir }}
                                </span>
                            </li>
                            @endif
                    </ul>

                </li>
            @endforeach
        </ul>
    </div>
@endif