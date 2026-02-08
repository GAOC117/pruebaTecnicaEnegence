<div class="container py-4">

    <div class="mb-3 d-flex align-items-center justify-content-start">
        <button wire:click="sincronizar" class="btn btn-primary me-3" wire:loading.attr="disabled">
            Sincronizar BD con API
        </button>

        @if($estadoSeleccionado)
            <div class="fs-5 fw-bold text-primary ms-3">
                Estado seleccionado: {{ $estadoSeleccionado }}
            </div>
        @endif

        <div wire:loading wire:target="sincronizar" class="spinner-border text-primary ms-3" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>

        @if (session()->has('success'))
            <div class="ms-3 alert alert-success mb-0 p-2">{{ session('success') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="ms-3 alert alert-danger mb-0 p-2">{{ session('error') }}</div>
        @endif
    </div>

    <div class="row mb-2">
        <div class="col-md-6">
            <h5>Estados</h5>
            <input type="text" class="form-control" placeholder="Buscar estado..."
                wire:model="buscarEstado" wire:keyup='filtrarEstados'>
            @if($estados->count() === 0 && $buscarEstado)
                <small class="text-danger">No se encontraron estados para "{{ $buscarEstado }}"</small>
            @endif
        </div>
        <div class="col-md-6">
            <h5>Municipios {{ $estadoSeleccionado ? "de ".$estadoSeleccionado : "" }}</h5>
            <input type="text" class="form-control" placeholder="{{ $estadoSeleccionado ? 'Buscar municipio de '.$estadoSeleccionado : 'Selecciona un estado primero' }}"
                wire:model="buscarMunicipio" wire:keyup='filtrarMunicipios'
                @if(!$estadoSeleccionado) disabled @endif>
            @if($municipios && $municipios->count() === 0 && $buscarMunicipio)
                <small class="text-danger">No se encontraron municipios para "{{ $buscarMunicipio }}"</small>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Tabla de estados --}}
        <div class="col-md-6">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th wire:click="ordenarEstados" style="cursor:pointer">
                            Estado
                            @if($ordenEstados === 'asc') &#9650; @else &#9660; @endif
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estados as $estado)
                        <tr wire:key="estado-{{ $estado->id }}" wire:click="selectEstado({{ $estado->id }})" style="cursor:pointer"
                            class="{{ $estadoSeleccionadoId === $estado->id ? 'table-primary' : '' }}">
                            <td>{{ $estado->nombre }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="1" class="text-center text-secondary">No hay estados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $estados->links(data: ['scrollTo' => false]) }}
        </div>

        {{-- Tabla de municipios --}}
        <div class="col-md-6">
            @if($municipios && $municipios->count() > 0)
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                           <th wire:click="ordenarMunicipios" style="cursor:pointer">
                            Municipio
                            @if($ordenMunicipios === 'asc') &#9650; @else &#9660; @endif
                        </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($municipios as $municipio)
                            <tr>
                                <td>{{ $municipio->nombre }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center text-secondary">No hay municipios</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $municipios->links(data: ['scrollTo' => false]) }}
            @else
                <p class="text-secondary">Selecciona un estado para ver sus municipios.</p>
            @endif
        </div>
    </div>
</div>
