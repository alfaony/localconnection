<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RouterOS\Client;
use RouterOS\Query;
use App\Models\SettingCompany;
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
        $secrets = $client->query('/ppp/secret/print')->read();

        return view('mikrotik.secret.index', compact('secrets'));
    }

    public function create()
    {
        return view('mikrotik.secret.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string',
            'password'   => 'required|string',
            'service'    => 'required|string',
            'profile'    => 'required|string',
            'remote_address' => 'nullable|string',
        ]);

        $client = $this->getClient();
        $client->query('/ppp/secret/add', $validated)->read();

        return redirect()->route('mikrotik-secrets.index')->with('success', 'Secret berhasil dibuat.');
    }

    public function edit(string $username)
    {
        $client = $this->getClient();
        // cari by name (stabil), bukan .id
        $q = (new \RouterOS\Query('/ppp/secret/print'));
        $rows = $client->query($q)->read();
        $secret = $rows[0] ?? null;

        if (!$secret) {
            return redirect()->route('mikrotik-secrets.index')->with('error', 'Secret tidak ditemukan.');
        }

        return view('mikrotik.secret.edit', compact('secret'));
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

        $client = $this->getClient();
        $q = (new \RouterOS\Query('/ppp/secret/print'))->where('name', $username);
        $rows = $client->query($q)->read();
        $secret = $rows[0] ?? null;
        if (!$secret) {
            return redirect()->route('mikrotik-secrets.index')->with('error', 'Secret tidak ditemukan.');
        }

        $set = (new \RouterOS\Query('/ppp/secret/set'))->equal('.id', $secret['.id']);
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

        return redirect()->route('mikrotik-secrets.index')->with('success', 'Secret diperbarui.');
    }

    public function destroy($id)
    {
        $client = $this->getClient();
        $client->query((new Query('/ppp/secret/remove'))->equal('.id', $id))->read();

        return redirect()->route('mikrotik-secrets.index')->with('success', 'Secret berhasil dihapus.');
    }
    public function disconnect(string $username)
    {
        $client = $this->getClient();

        // cari sesi aktif user ini lalu remove
        $act = (new \RouterOS\Query('/ppp/active/print'))->where('name', $username);
        $rows = $client->query($act)->read();

        $count = 0;
        foreach ($rows as $r) {
            if (!empty($r['.id'])) {
                $client->query((new \RouterOS\Query('/ppp/active/remove'))->equal('.id', $r['.id']))->read();
                $count++;
            }
        }

        return back()->with('success', $count ? "Disconnected $count session(s)." : 'Tidak ada sesi aktif.');
    }
    
}