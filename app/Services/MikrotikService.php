<?php

namespace App\Http\Controllers;

use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HotspotUserController extends Controller
{
    public function __construct(private MikrotikService $mt) {}

    /** GET /mikrotik/hotspot-users */
    public function index(Request $req)
    {
        $filters = $req->only(['name','server','profile','disabled']);
        return response()->json($this->mt->listHotspotUsers($filters));
    }

    /** GET /mikrotik/hotspot-users/{id}  (id = .id mikrotik, contoh: *3) */
    public function show(string $id)
    {
        $row = $this->mt->getHotspotUserById($id);
        if (!$row) return response()->json(['message' => 'Not found'], 404);
        return response()->json($row);
    }

    /** POST /mikrotik/hotspot-users */
    public function store(Request $req)
    {
        $data = $req->validate([
            'server'       => 'required|string',
            'name'         => 'required|string',
            'password'     => 'required|string|min:4',
            'profile'      => 'nullable|string',
            'comment'      => 'nullable|string',
            'limit_uptime' => 'nullable|string',
            'disabled'     => ['nullable', Rule::in(['yes','no'])],
        ]);

        $res = $this->mt->createHotspotUser($data);
        return response()->json(['message' => 'Created', 'result' => $res], 201);
    }

    /** PUT /mikrotik/hotspot-users/{id} */
    public function update(Request $req, string $id)
    {
        $payload = $req->validate([
            'name'         => 'sometimes|string',
            'password'     => 'sometimes|string|min:4',
            'profile'      => 'sometimes|nullable|string',
            'comment'      => 'sometimes|nullable|string',
            'limit_uptime' => 'sometimes|nullable|string',
            'disabled'     => ['sometimes', Rule::in(['yes','no'])],
        ]);

        $res = $this->mt->updateHotspotUser($id, $payload);
        return response()->json(['message' => 'Updated', 'result' => $res]);
    }

    /** DELETE /mikrotik/hotspot-users/{id} */
    public function destroy(string $id)
    {
        $res = $this->mt->deleteHotspotUser($id);
        return response()->json(['message' => 'Deleted', 'result' => $res]);
    }
}