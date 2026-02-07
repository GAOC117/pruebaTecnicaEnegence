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

     use WithPagination,WithoutUrlPagination;
    protected $paginationTheme = 'bootstrap';

    public $orden = 'asc';



    public $buscarEstado = '';
    public $buscarMunicipio = '';

    public $cargandoMunicipios = false;


    public $estadoSeleccionadoId = null;
    public $estadoSeleccionado = null;

  

    public function sincronizar()
    {


        set_time_limit(300);

        try {

            DB::transaction(function () {

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

                foreach ($estadosData['response']['estado'] as $nombreEstado) {

                    $estado = Estado::firstOrCreate([
                        'nombre' => $nombreEstado
                    ]);

                    $estadoEncoded = rawurlencode($nombreEstado);


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


    public function selectEstado($id)
    {
        $this->estadoSeleccionadoId = $id;
        $this->buscarMunicipio = '';
        $this->resetPage('paginaMunicipios');
     return "estado seleccionado ".$this->estadoSeleccionado;
    }


    public function ordenar()
    {
        $this->orden = $this->orden === 'asc' ? 'desc' : 'asc';
           return "estado seleccionado ".$this->estadoSeleccionado;
    }


  

    public function filtrarEstados()
    {
        $this->resetPage('paginaEstados');
          return "estado seleccionado ".$this->estadoSeleccionado;
      
    }

    public function filtrarMunicipios()
    {
        
        $this->resetPage('paginaMunicipios');
            return "estado seleccionado ".$this->estadoSeleccionado;
    }

    public function render()
    {



        $municipios = null; //aun no se selecciona algun estado

        $estados = Estado::when($this->buscarEstado, function ($query) {
            $query->where('nombre', 'LIKE', '%' . $this->buscarEstado . '%');
        })->orderBy('nombre', $this->orden)->paginate(10, ['*'], 'paginaEstados');

        if ($this->estadoSeleccionadoId) {

            $municipios = Municipio::where('estado_id', $this->estadoSeleccionadoId)
                ->when($this->buscarMunicipio, function ($query) {
                    $query->where('nombre', 'LIKE', '%' . $this->buscarMunicipio . '%');
                })->orderBy('nombre', $this->orden)->paginate(10, ['*'], 'paginaMunicipios');

                $this->estadoSeleccionado = Estado::find($this->estadoSeleccionadoId)?->nombre ?? null;

        }




        
        return view(
            'livewire.estados',
            [
                'estados' => $estados,
                'municipios' => $municipios,
                'estadoSeleccionado' => $this->estadoSeleccionado
            ]
        );

           
    }
}
