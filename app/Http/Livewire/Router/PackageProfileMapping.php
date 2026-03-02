<?php

namespace App\Http\Livewire\Router;

use Livewire\Component;
use App\Models\{Router, InternetPackage, PackageRouterProfile};
use Illuminate\Support\Str;

class PackageProfileMapping extends Component
{
    public int $routerId;
    public array $rows = []; // [package_id => ['name'=>..,'ros_profile'=>..]]

    public function mount(int $routerId)
    {
        $this->routerId = $routerId;
        $this->loadRows();
    }

    public function render()
    {
        $router = Router::findOrFail($this->routerId);
        return view('livewire.router.package-profile-mapping', ['router'=>$router, 'rows'=>$this->rows])->extends('adminlte::page');
    }

    public function loadRows()
    {
        $pkgs = InternetPackage::where('is_active',true)->orderBy('name')->get();
        $maps = PackageRouterProfile::where('router_id',$this->routerId)->get()->keyBy('package_id');

        $this->rows = [];
        foreach ($pkgs as $p) {
            $this->rows[$p->id] = [
                'package_name' => $p->name,
                'ros_profile'  => $maps->get($p->id)->ros_profile ?? '',
            ];
        }
    }

    public function save()
    {
        foreach ($this->rows as $packageId => $row) {
            if (!trim($row['ros_profile'])) continue;
            PackageRouterProfile::updateOrCreate(
                ['router_id'=>$this->routerId, 'package_id'=>$packageId],
                ['ros_profile'=>trim($row['ros_profile'])]
            );
        }
        $this->dispatchBrowserEvent('toast',['type'=>'success','message'=>'Mapping saved']);
        $this->loadRows();
    }
}
