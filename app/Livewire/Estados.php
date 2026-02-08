<?php

namespace App\Livewire;

use App\Models\Estado;
use Livewire\Component;
use App\Models\Municipio;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Facades\Http;

class Estados extends Component
{

    use WithPagination, WithoutUrlPagination;
    protected $paginationTheme = 'bootstrap';

    public $ordenEstados = 'asc';
    public $ordenMunicipios = 'asc';

    public $buscarEstado = '';
    public $buscarMunicipio = '';

    public $estadoSeleccionadoId = null;
    public $estadoSeleccionado = null;


//funcion que hace consumo de API e inserta en BAse de datos
    public function sincronizar()
    {

        set_time_limit(300);

        try {

            DB::transaction(function () {

            //consumo api para los estados
                $estadosResponse = Http::get(
                    config('services.copomex.base_url') . '/get_estados?token=' . config('services.copomex.token')
                );


                if (!$estadosResponse->successful()) {

                    if ($estadosResponse->json()["error_message"] !== null) {
                        throw new \Exception($estadosResponse->json()["error_message"]);
                    } else {

                        throw new \Exception('Error consultando API');
                    }
                }

                $estadosData = $estadosResponse->json();

                if (!isset($estadosData['response']['estado'])) {


                    throw new \Exception('Respuesta inválida API estados');
                }

                //por cada estado lo inserto en la bd, uso firstOrCreate para evitar duplicados (idempotencia)
                foreach ($estadosData['response']['estado'] as $nombreEstado) {

                    $estado = Estado::firstOrCreate([
                        'nombre' => $nombreEstado
                    ]);

                    //la url necesita los espacios como %20
                    $estadoEncoded = rawurlencode($nombreEstado);

                    //consumo la api para obtener los municioios
                    $municipiosResponse = Http::get(
                        config('services.copomex.base_url') . "/get_municipio_por_estado/$estadoEncoded?token=" . config('services.copomex.token')
                    );

                    if (!$municipiosResponse->successful()) {

                        if ($municipiosResponse->json()["error_message"] !== null) {
                            throw new \Exception($municipiosResponse->json()["error_message"]);
                        } else {

                            throw new \Exception('Error consultando API');
                        }
                    }

                    $municipiosData = $municipiosResponse->json();

                    if (!isset($municipiosData['response']['municipios'])) {
                        throw new \Exception("Respuesta inválida municipios $nombreEstado");
                    }
                    //inserto cada munucipio a la bd usando firstOrCreate para evitar otra vez duplicados (idempotencia)
                    foreach ($municipiosData['response']['municipios'] as $nombreMunicipio) {

                        Municipio::firstOrCreate([
                            'nombre' => $nombreMunicipio,
                            'estado_id' => $estado->id
                        ]);
                    }
                }
            });

            session()->flash('success', 'Catálogo sincronizado correctamente');
        } catch (\Throwable $e) {

            session()->flash('error', 'Error en sincronización: ' . $e->getMessage());
        }
    }

//funcion para cuando selecciono un estado de la tabla
    public function selectEstado($id)
    {
        $this->estadoSeleccionadoId = $id;
        $this->estadoSeleccionado = Estado::find($this->estadoSeleccionadoId)?->nombre ?? null;
        $this->buscarMunicipio = '';
        $this->resetPage('paginaMunicipios');
    }


//funciones para ordenar los estados o munucipios
    public function ordenarEstados()
    {
        $this->ordenEstados = $this->ordenEstados === 'asc' ? 'desc' : 'asc';
        $this->resetPage('paginaEstados');
    }

    public function ordenarMunicipios()
    {
        $this->ordenMunicipios = $this->ordenMunicipios === 'asc' ? 'desc' : 'asc';
        $this->resetPage('paginaMunicipios');
    }



//funciones para la busqueda de estados o municipios
    public function filtrarEstados()
    {
        $this->resetPage('paginaEstados');
    }

    public function filtrarMunicipios()
    {

        $this->resetPage('paginaMunicipios');
    }



    public function render()
    {

        $municipios = null; //aun no se selecciona algun estado

        $estados = Estado::when($this->buscarEstado, function ($query) {
            $query->where('nombre', 'LIKE', '%' . $this->buscarEstado . '%');
        })->orderBy('nombre', $this->ordenEstados)->paginate(10, ['*'], 'paginaEstados');

        if ($this->estadoSeleccionadoId) {

            $municipios = Municipio::where('estado_id', $this->estadoSeleccionadoId)
                ->when($this->buscarMunicipio, function ($query) {
                    $query->where('nombre', 'LIKE', '%' . $this->buscarMunicipio . '%');
                })->orderBy('nombre', $this->ordenMunicipios)->paginate(10, ['*'], 'paginaMunicipios');
        }





        return view(
            'livewire.estados',
            [
                'estados' => $estados,
                'municipios' => $municipios,
            ]
        );
    }
}
