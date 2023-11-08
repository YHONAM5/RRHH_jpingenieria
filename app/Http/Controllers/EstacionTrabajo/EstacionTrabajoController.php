<?php

namespace App\Http\Controllers\EstacionTrabajo;

use App\Http\Controllers\Controller;
use App\Models\Estaciondetrabajo;
use Illuminate\Http\Request;

class EstacionTrabajoController extends Controller
{
    public function index(){
        $estaciones = Estaciondetrabajo::join('regimen_laboral','estaciondetrabajo.idRegimenLaboral','=','regimen_laboral.idRegimenLaboral')->get();
        return view('rrhh.estacion_trabajo.index',compact('estaciones'));
    }
}
