<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class BeaconMonitor extends Component
{
    public $beacons = [];

    public function refreshBeacons()
    {
        $user_id = auth()->id() ?? 1000;
        $cacheKey = "beacons_user_{$user_id}";
        $this->beacons = Cache::get($cacheKey, []);
    }

    public function deleteBeacon($macAddress)
    {
        $user_id = auth()->id() ?? 1000;
        $cacheKey = "beacons_user_{$user_id}";
        $beacons = Cache::get($cacheKey, []);

        if (isset($beacons[$macAddress])) {
            unset($beacons[$macAddress]);
            Cache::put($cacheKey, $beacons, 3600);
        }

        // atualiza a tabela
        $this->beacons = $beacons;
    }

    public function render()
    {
        $this->refreshBeacons();
        return view('livewire.beacon-monitor');
    }
}
