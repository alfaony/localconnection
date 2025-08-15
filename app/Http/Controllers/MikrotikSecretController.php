<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RouterOS\Client;
use RouterOS\Query;
use App\Models\SettingCompany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Auth;

class MikrotikSecretController extends Controller
{
    protected function getClient()
    {
        $company = Auth::user()->company;
        $settings = SettingCompany::byCompany($company->id)->where('menu', 'mikrotik')->get()->pluck('field_value', 'field_title'); // helper kamu yang ambil setting mikrotik dari DB

        return new Client([
            'host'     => $settings['mikrotik_host'],
            'user'     => $settings['mikrotik_username'],
            'pass'     => $settings['mikrotik_password'],
            'port'     => (int) $settings['mikrotik_port'],
            'ssl'      => filter_var($settings['mikrotik_ssl'], FILTER_VALIDATE_BOOLEAN),
            'timeout'  => 10,
        ]);
    }

    public function index()
    {
        $client = $this->getClient();
        
        // Get query parameters for pagination
        $perPage = request()->get('per_page', 25);
        $currentPage = request()->get('page', 1);
        $search = request()->get('q', '');

        // Fetch all secrets from Mikrotik
        $allSecrets = $client->query('/ppp/secret/print')->read();

        // Convert to collection for easier manipulation
        $secretsCollection = collect($allSecrets);

        // Apply search filter if exists
        if ($search) {
            $secretsCollection = $secretsCollection->filter(function ($secret) use ($search) {
                return stripos($secret['name'] ?? '', $search) !== false ||
                    stripos($secret['service'] ?? '', $search) !== false ||
                    stripos($secret['profile'] ?? '', $search) !== false ||
                    stripos($secret['remote-address'] ?? '', $search) !== false;
            });
        }

        // Implement manual pagination
        $currentPageItems = $secretsCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        
        $secrets = new LengthAwarePaginator(
            $currentPageItems,
            $secretsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        return view('mikrotik.secret.index', compact('secrets', 'search'));
    }

    public function create()
    {
        $profiles = $this->getClient()->query(new Query('/ppp/profile/print'))->read();
        $profiles = collect($profiles)->pluck('name')->filter()->sort()->values();
        return view('mikrotik.secret.create', compact('profiles')); // + data lain
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'password'        => 'required|string|min:4|max:191',
            'service'         => 'nullable|string|in:pppoe,pptp,l2tp,ovpn',
            'profile'         => 'required|string|max:64',
            'remote_address'  => 'nullable|string|max:64',
            'comment'         => 'nullable|string|max:255',
            'disabled'        => 'nullable|in:yes,no',
        ]);

        $client  = $this->getClient();
        $service = $data['service'] ?? 'pppoe';

        // 1) Validasi profile exist (hindari salah ketik)
        $exists = $client->query(
            (new Query('/ppp/profile/print'))->where('name', $data['profile'])
        )->read();
        if (empty($exists)) {
            return back()
                ->withErrors(['profile' => "PPP profile '{$data['profile']}' tidak ditemukan di router."])
                ->withInput();
        }

        // 2) Cek apakah secret sudah ada (idempotent)
        $found = $client->query(
            (new Query('/ppp/secret/print'))->where('name', $data['name'])
        )->read();
        $existing = !empty($found) ? $found[0] : null;

        if ($existing) {
            // UPDATE /ppp/secret/set
            $set = (new Query('/ppp/secret/set'))
                ->equal('.id', $existing['.id'])
                ->equal('password', $data['password'])
                ->equal('service',  $service)
                ->equal('profile',  $data['profile']);

            if (!empty($data['remote_address'])) $set->equal('remote-address', $data['remote_address']);
            if (isset($data['comment']))         $set->equal('comment', $data['comment']);
            if (isset($data['disabled']))        $set->equal('disabled', $data['disabled']);

            $client->query($set)->read();

            // Putus sesi aktif agar perubahan langsung terpakai
            $act = (new Query('/ppp/active/print'))->where('name', $data['name']);
            foreach ($client->query($act)->read() as $r) {
                if (!empty($r['.id'])) {
                    $client->query((new Query('/ppp/active/remove'))->equal('.id', $r['.id']))->read();
                }
            }

            return redirect()->route('mikrotik-secret.index')->with('success', 'Secret diperbarui.');
        }

        // 3) CREATE /ppp/secret/add
        $add = (new Query('/ppp/secret/add'))
            ->equal('name',     $data['name'])
            ->equal('password', $data['password'])
            ->equal('service',  $service)
            ->equal('profile',  $data['profile']);

        if (!empty($data['remote_address'])) $add->equal('remote-address', $data['remote_address']);
        if (isset($data['comment']))         $add->equal('comment', $data['comment']);
        if (isset($data['disabled']))        $add->equal('disabled', $data['disabled']); // default no

        $client->query($add)->read();

        return redirect()->route('mikrotik-secret.index')->with('success', 'Secret berhasil dibuat.');
    }

    public function edit(string $username)
    {
        $profiles = $this->getClient()->query(new Query('/ppp/profile/print'))->read();
        $profiles = collect($profiles)->pluck('name')->filter()->sort()->values();

        $client = $this->getClient();
        // cari by name (stabil), bukan .id
        $q = (new Query('/ppp/secret/print'));
        $rows = $client->query($q)->read();
        $rows = array_filter($rows, function ($r) use ($username) {
            return !empty($r['.id']) && $r['.id'] === $username;
        });
        
        $secret = collect($rows)->first();

        if (!$secret) {
            return redirect()->route('mikrotik-secret.index')->with('error', 'Secret tidak ditemukan.');
        }

        return view('mikrotik.secret.edit', compact('secret','profiles'));
    }

    public function update(Request $request, string $username)
    {
        $validated = $request->validate([
            'password'        => 'nullable|string|min:4',
            'service'         => 'nullable|string',
            'profile'         => 'nullable|string',
            'remote_address'  => 'nullable|string',
            'comment'         => 'nullable|string',
            'disabled'        => 'nullable|in:yes,no',
            'kick'            => 'nullable|boolean',
        ]);

        $profiles = $this->getClient()->query(new Query('/ppp/profile/print'))->read();
        $profiles = collect($profiles)->pluck('name')->filter()->sort()->values();

        $client = $this->getClient();
        // cari by name (stabil), bukan .id
        $q = (new Query('/ppp/secret/print'));
        $rows = $client->query($q)->read();
        $rows = array_filter($rows, function ($r) use ($username) {
            return !empty($r['.id']) && $r['.id'] === $username;
        });

        $secret = collect($rows)->first();

        if (!$secret) {
            return redirect()->route('mikrotik-secret.index')->with('error', 'Secret tidak ditemukan.');
        }

        $set = (new \RouterOS\Query('/ppp/secret/set'))->equal('.id', $secret['.id']);
        // dd($set);
        foreach (['password','service','profile','comment','disabled','remote_address'] as $k) {
            if ($k === 'remote_address' && isset($validated[$k])) $set->equal('remote-address', $validated[$k]);
            elseif (isset($validated[$k])) $set->equal(str_replace('_','-',$k), $validated[$k]);
        }
        $client->query($set)->read();

        if ($request->boolean('kick', true)) {
            // disconnect active session supaya perubahan langsung berlaku
            $act = (new \RouterOS\Query('/ppp/active/print'))->where('name', $username);
            foreach ($client->query($act)->read() as $row) {
                if (!empty($row['.id'])) {
                    $client->query((new \RouterOS\Query('/ppp/active/remove'))->equal('.id', $row['.id']))->read();
                }
            }
        }

        return redirect()->route('mikrotik-secret.index')->with('success', 'Secret diperbarui.');
    }

    public function destroy($id)
    {
        $client = $this->getClient();
        $client->query((new Query('/ppp/secret/remove'))->equal('.id', $id))->read();

        return redirect()->route('mikrotik-secret.index')->with('success', 'Secret berhasil dihapus.');
    }
    public function disconnect(string $username)
    {
        $username = trim($username);
        $client   = $this->getClient();   // sudah kamu punya di controller ini

        // 1) coba exact match
        $p = (new Query('/ppp/secret/print'))->where('name', $username);
        $rows = $client->query($p)->read();
        $data = collect($rows)->first();

        $q = (new Query('/ppp/secret/set'))->equal('.id', $data['.id']);
        $data = [
            'disabled' => 'yes'
        ];
        foreach ($data as $k => $v) {
            if ($v === null) continue;
            $q->equal(str_replace('_', '-', $k), $v);
        }
        $client->query($q)->read();


        // dd($res);
        // dd($res, $username);

        // 2) fallback: scan semua, match case-insensitive
        // if (empty($rows)) {
        //     $all  = $client->query(new Query('/ppp/active/print'))->read();
        //     $rows = array_values(array_filter($all, function ($r) use ($username) {
        //         $n = isset($r['name']) ? trim($r['name']) : '';
        //         return $n !== '' && Str::lower($n) === Str::lower($username);
        //     }));
        // }

        // if (empty($rows)) {
        //     return back()->with('success', "Tidak ada sesi aktif untuk {$username}.");
        // }

        // // 3) remove setiap sesi yang ketemu
        // $count = 0;
        // foreach ($rows as $r) {
        //     if (!empty($r['.id'])) {
        //         $client->query(
        //             (new Query('/ppp/active/remove'))->equal('.id', $r['.id'])
        //         )->read();
        //         $count++;
        //     }
        // }

        return back()->with('success', "Disconnected  session(s) untuk {$username}.");
    }

    public function reconnect(string $username)
    {
        $username = trim($username);
        $client   = $this->getClient();

        // 1) Temukan secret user
        $q = (new Query('/ppp/secret/print'))->where('name', $username);
        $secRows = $client->query($q)->read();

        $secret = collect($secRows)->first();
        if (!$secret) {
            return back()->with('error', "Secret '{$username}' tidak ditemukan.");
        }

        // 2) Pastikan enabled (disabled=no)
        if (($secret['disabled'] ?? 'false') === 'true') {
            $client->query(
                (new Query('/ppp/secret/set'))->equal('.id', $secret['.id'])->equal('disabled', 'no')
            )->read();
        }

        // 3) (Opsional) Hard flap: paksa disconnect-reenable untuk kasus bandel
        //    Aktifkan jika butuh: set $hardFlap = true;
        $hardFlap = false;
        if ($hardFlap) {
            // disable -> kecil delay -> enable
            $client->query((new Query('/ppp/secret/set'))->equal('.id', $secret['.id'])->equal('disabled', 'yes'))->read();
            usleep(300000); // 300ms
            $client->query((new Query('/ppp/secret/set'))->equal('.id', $secret['.id'])->equal('disabled', 'no'))->read();
        }

        // 4) Kick semua sesi aktif agar klien auto redial
        //    Coba exact match; kalau kosong, fallback scan case-insensitive
        $actRows = $client->query(
            (new Query('/ppp/active/print'))->where('name', $username)
        )->read();

        if (empty($actRows)) {
            $all = $client->query(new Query('/ppp/active/print'))->read();
            $actRows = array_values(array_filter($all, function ($r) use ($username) {
                $n = isset($r['name']) ? trim($r['name']) : '';
                return $n !== '' && Str::lower($n) === Str::lower($username);
            }));
        }

        $count = 0;
        foreach ($actRows as $r) {
            if (!empty($r['.id'])) {
                $client->query((new Query('/ppp/active/remove'))->equal('.id', $r['.id']))->read();
                $count++;
            }
        }

        return back()->with('success', $count
            ? "Reconnect dikirim: {$count} sesi di-kick. Klien akan auto redial."
            : "Tidak ada sesi aktif. Secret sudah aktif; klien akan terhubung saat dial.");
    }
}