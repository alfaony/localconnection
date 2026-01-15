<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use RouterOS\Client;
use RouterOS\Query;
use App\Models\SettingCompany;
use Auth;

class MikrotikProfileController extends Controller
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

    // GET /mikrotik-profile
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $perPage = (int) ($request->get('per_page', 25));
        $page = max(1, (int) ($request->get('page', 1)));

        $rows = $this->getClient()->query(new Query('/ppp/profile/print'))->read();

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $rows = array_values(array_filter($rows, function ($r) use ($needle) {
                $hay = mb_strtolower(($r['name'] ?? '').' '.($r['rate-limit'] ?? '').' '.($r['remote-address'] ?? '').' '.($r['local-address'] ?? ''));
                return str_contains($hay, $needle);
            }));
        }

        $total = count($rows);
        $slice = array_slice($rows, ($page - 1) * $perPage, $perPage);
        $p = new LengthAwarePaginator($slice, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query()
        ]);

        return view('mikrotik.profile.index', [
            'profiles' => $p,
            'search'   => $q,
            'perPage'  => $perPage,
        ]);
    }

    // GET /mikrotik-profile/create
    public function create()
    {
        return view('mikrotik.profile.create');
    }

    // POST /mikrotik-profile
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:64',
            'rate_limit'     => 'nullable|string|max:128', // ex: "20M/20M 20M/20M 20M/20M 1/1"
            'remote_address' => 'nullable|string|max:64',  // pool name or IP
            'local_address'  => 'nullable|string|max:64',
            'only_one'       => 'nullable|in:yes,no',      // default no
            'comment'        => 'nullable|string|max:255',
        ]);

        $client = $this->getClient();

        // Cek existing by name
        $found = $client->query((new Query('/ppp/profile/print'))->where('name', $data['name']))->read();
        if (!empty($found)) {
            return back()->withErrors(['name' => 'Nama profile sudah ada di router.'])->withInput();
        }

        $add = (new Query('/ppp/profile/add'))->equal('name', $data['name']);
        if (!empty($data['rate_limit']))     $add->equal('rate-limit', $data['rate_limit']);
        if (!empty($data['remote_address'])) $add->equal('remote-address', $data['remote_address']);
        if (!empty($data['local_address']))  $add->equal('local-address', $data['local_address']);
        if (isset($data['only_one']))        $add->equal('only-one', $data['only_one']);
        if (isset($data['comment']))         $add->equal('comment', $data['comment']);

        $client->query($add)->read();

        return redirect()->route('mikrotik-profile.index')->with('success', 'Profile dibuat.');
    }

    // GET /mikrotik-profile/{name}/edit
    public function edit(string $name)
    {
        $client = $this->getClient();
        $rows = $client->query((new Query('/ppp/profile/print'))->where('name', $name))->read();
        $profile = collect($rows)->first();

        if (!$profile) {
            return redirect()->route('mikrotik-profile.index')->with('error', 'Profile tidak ditemukan.');
        }

        return view('mikrotik.profile.edit', compact('profile'));
    }

    // PUT /mikrotik-profile/{name}
    public function update(Request $request, string $name)
    {
        $data = $request->validate([
            'new_name'       => 'nullable|string|max:64',
            'rate_limit'     => 'nullable|string|max:128',
            'remote_address' => 'nullable|string|max:64',
            'local_address'  => 'nullable|string|max:64',
            'only_one'       => 'nullable|in:yes,no',
            'comment'        => 'nullable|string|max:255',
        ]);

        $client = $this->getClient();
        $rows = $client->query((new Query('/ppp/profile/print'))->where('name', $name))->read();
        $profile = collect($rows)->first();
        if (!$profile) {
            return redirect()->route('mikrotik-profile.index')->with('error', 'Profile tidak ditemukan.');
        }

        $set = (new Query('/ppp/profile/set'))->equal('.id', $profile['.id']);
        if (!empty($data['new_name']))       $set->equal('name', $data['new_name']);
        if (array_key_exists('rate_limit', $data) && $data['rate_limit'] !== null) $set->equal('rate-limit', $data['rate_limit']);
        if (array_key_exists('remote_address', $data) && $data['remote_address'] !== null) $set->equal('remote-address', $data['remote_address']);
        if (array_key_exists('local_address', $data) && $data['local_address'] !== null)   $set->equal('local-address', $data['local_address']);
        if (isset($data['only_one']))        $set->equal('only-one', $data['only_one']);
        if (isset($data['comment']))         $set->equal('comment', $data['comment']);

        $client->query($set)->read();

        return redirect()->route('mikrotik-profile.index')->with('success', 'Profile diperbarui.');
    }

    // DELETE /mikrotik-profile/{name}
    public function destroy(string $name)
    {
        $client = $this->getClient();
        $rows = $client->query((new Query('/ppp/profile/print'))->where('name', $name))->read();
        $profile = collect($rows)->first();
        if (!$profile) {
            return redirect()->route('mikrotik-profile.index')->with('error', 'Profile tidak ditemukan.');
        }

        // NOTE: RouterOS akan menolak delete jika profile dipakai secret aktif
        $client->query((new Query('/ppp/profile/remove'))->equal('.id', $profile['.id']))->read();

        return redirect()->route('mikrotik-profile.index')->with('success', 'Profile dihapus.');
    }
}