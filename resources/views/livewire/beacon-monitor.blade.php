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
                            <button wire:click="deleteBeacon('{{ $beacon['mac_address'] }}')"
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

    <div x-data="{ open: false}">
        <button class="btn btn-primary" x-on:click="open = !open" >Toggle Content</button>
    
        <div x-show="open" x-transition>
            Content...
        </div>
    </div>

</div>
