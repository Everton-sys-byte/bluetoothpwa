<div>
    <!-- Tabela com polling -->
    <div wire:poll.2s>
        <h3>Beacons Monitor</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>MAC Address</th>
                    <th>Nome</th>
                    <th>Distance</th>
                    <th>Last Update</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($beacons as $beacon)
                    <tr>
                        <td>{{ $beacon['mac_address'] }}</td>
                        <td>{{ $beacon['name'] ?? '-' }}</td>
                        <td>{{ $beacon['distance'] }}</td>
                        <td>{{ $beacon['updated_at'] }}</td>
                        <td>
                            <button x-data 
                                x-on:click="$dispatch('open-delete-confirmation', {mac:'{{$beacon['mac_address']}}'})"
                                class="btn btn-danger btn-sm">
                                Excluir
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Nenhum beacon detectado</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="myModalWithAlpine p-2" 
        x-data="{ showDeleteConfirmation: false, mac:null }" 
        x-show="showDeleteConfirmation"
        x-on:open-delete-confirmation.window="showDeleteConfirmation = true; mac= $event.detail.mac" 
        x-on:close-delete-confirmation.window="showDeleteConfirmation = false"
        x-transition
        x-cloak>
        <div class="myModalDialog">
            <div class="modal-header d-flex justify-content-end">
                <button class="btn btn-danger" x-on:click="$dispatch('close-delete-confirmation')">X</button>
            </div>
            <div class="modal-body mt-3">
                Deseja exluir o Beacon de Mac Adress: <strong><span x-text="mac"></span></strong>?
            </div>
            <div class="modal-bottom mt-3">
                <button class="btn btn-success" x-on:click="$wire.deleteBeacon(mac)">Confirmar</button>
            </div>
        </div>
    </div>

    {{-- <div x-data="{ open: false }">
        <button class="btn btn-primary" x-on:click="open = !open">Toggle Content</button>

        <div x-show="open" x-transition>
            Content...
        </div>
    </div> --}}
</div>
