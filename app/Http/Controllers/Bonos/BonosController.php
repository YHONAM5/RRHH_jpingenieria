<?php

namespace App\Http\Controllers\Bonos;

use App\Http\Controllers\Controller;
use App\Models\Bono;
use App\Models\Datoscontable;
use App\Models\Estaciondetrabajo;
use App\Models\Periodo;
use App\Models\Persona;
use App\Models\TipoBono;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BonosController extends Controller
{
    public function index()
    {
        $estaciones = Estaciondetrabajo::all();
        $personas = Persona::join('empleado', 'empleado.idPersona', '=', 'persona.idPersona')
            ->join('cargo', 'cargo.idCargo', '=', 'empleado.idCargo')
            ->join('contrato', 'contrato.idEmpleado', '=', 'empleado.idEmpleado')
            ->join('estaciondetrabajo', 'estaciondetrabajo.idEstacionDeTrabajo', '=', 'contrato.idEstacionTrabajo')
            ->where('contrato.idCondicionDeContrato', 1)
            ->get();

        $periodos = Periodo::all();

        $tiposBonos = TipoBono::all();

        return view('rrhh.bonos.index', compact('personas', 'estaciones', 'periodos', 'tiposBonos'));
    }

    public function buscar_bonos(Request $request)
    {
        // Obtener estaciones y personas
        $estaciones = Estaciondetrabajo::all();
        $personas = Persona::join('empleado', 'empleado.idPersona', '=', 'persona.idPersona')
            ->join('cargo', 'cargo.idCargo', '=', 'empleado.idCargo')
            ->join('contrato', 'contrato.idEmpleado', '=', 'empleado.idEmpleado')
            ->join('estaciondetrabajo', 'estaciondetrabajo.idEstacionDeTrabajo', '=', 'contrato.idEstacionTrabajo')
            ->where('contrato.idCondicionDeContrato', 1)
            ->get();

        // Obtener todos los periodos
        $periodos = Periodo::all();

        // Obtener el periodo seleccionado
        $idPeriodo = $request->input('periodo');

        // Obtener los tipos de bonos
        $tiposBonos = TipoBono::all();

        // Filtrar los bonos por periodo
        $bonos = Bono::join('contrato', 'bonos.idContrato', '=', 'contrato.idContrato')
            ->join('empleado', 'contrato.idEmpleado', '=', 'empleado.idEmpleado')
            ->join('persona', 'empleado.idPersona', '=', 'persona.idPersona')
            ->join('tipo_bono', 'bonos.idTipoBono', '=', 'tipo_bono.idTipoBono')
            ->join('datoscontables', 'datoscontables.idContrato', '=', 'contrato.idContrato')
            ->where('bonos.idPeriodo', $idPeriodo)
            ->get();

        // Devolver la vista con los datos correspondientes
        if ($bonos->count() > 0) {
            return view('rrhh.bonos.index', compact('personas', 'estaciones', 'periodos', 'bonos', 'idPeriodo', 'tiposBonos'));
        } else {
            return view('rrhh.bonos.index', compact('personas', 'estaciones', 'periodos', 'idPeriodo', 'tiposBonos'));
        }
    }

    public function registro_bono(Request $request)
    {
        try {
            // Validar los datos del formulario
            // $request->validate([
            //     'monto' => 'nullable|numeric', // `nullable` permite que el campo esté vacío
            //     'fecha' => 'required|date',
            //     'idPeriodo' => 'required|integer',
            //     'bono_declarado' => 'nullable|numeric', // `nullable` permite que el campo esté vacío
            // ]);

            // Obtener los valores de los campos
            $idContrato = $request->input('idContrato');
            $idPeriodo = $request->input('idPeriodo');
            $bonoDias = $request->input('bono_dias');
            $idTipoBono = $request->input('idTipoBono');
            $bonoHoras = $request->input('bono_horas');
            $fecha = $request->input('fecha');
            $monto = $request->input('monto');
            $bonoDeclarado = $request->input('bono_declarado');

            // Validar que al menos uno de los dos campos tenga un valor
            // if (empty($bonoDeclarado)) {
            //     return response()->json(['error' => true, 'message' => 'Debes ingresar al menos un valor en para un Bono o Monto del Bono Declarado.']);
            // }

            // Crear una nueva instancia de Bono
            $bono = new Bono;
            $bono->idContrato = $idContrato;
            $bono->idPeriodo = $idPeriodo;
            $bono->CantidadDias = $bonoDias;
            $bono->CantidadHoras = $bonoHoras;
            $bono->idTipoBono = $idTipoBono;
            // $bono->Reintegro = $monto;
            $bono->Monto = $bonoDeclarado; // Asignar el bono declarado, si existe
            $bono->save();

            return response()->json(['success' => true, 'message' => 'Reintegro y/o Bono Declarado registrados correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Error al registrar el bono: ' . $e->getMessage()]);
        }
    }



    public function eliminar_bono(Request $request)
    {
        $idBono = $request->input('id');

        try {
            $bono = Bono::find($idBono);
            $bono->delete();

            return response()->json(['success' => true, 'message' => 'Bono eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el bono: ' . $e->getMessage()]);
        }
    }

    public function detallesBono($idPersona, $idPeriodo)
    {
        $persona = Persona::find($idPersona);
        $periodo = Periodo::find($idPeriodo);
        $PrimerFecha = $periodo->DiaDeInicioDelPeriodo;
        $SegundaFecha = $periodo->DiaDeFinDelPeriodo;

        $bonos = Bono::join('contrato', 'bono.idContrato', '=', 'contrato.idContrato')
            ->join('empleado', 'contrato.idEmpleado', '=', 'empleado.idEmpleado')
            ->join('persona', 'empleado.idPersona', '=', 'persona.idPersona')
            ->where('persona.idPersona', $idPersona)
            ->whereBetween('bono.Fecha', [$PrimerFecha, $SegundaFecha])
            ->select('bono.*')
            ->get();

        return view('rrhh.bonos.detalles', compact('persona', 'bonos', 'periodo'));
    }

    public function descargarPdf($idPersona, $idPeriodo)
    {
        $persona = Persona::find($idPersona);
        $periodo = Periodo::find($idPeriodo);

        $PrimerFecha = $periodo->DiaDeInicioDelPeriodo;
        $SegundaFecha = $periodo->DiaDeFinDelPeriodo;

        $bonos = Bono::join('contrato', 'bono.idContrato', '=', 'contrato.idContrato')
            ->join('empleado', 'contrato.idEmpleado', '=', 'empleado.idEmpleado')
            ->join('persona', 'empleado.idPersona', '=', 'persona.idPersona')
            ->where('persona.idPersona', $idPersona)
            ->whereBetween('bono.Fecha', [$PrimerFecha, $SegundaFecha])
            ->select('bono.*', 'persona.Nombres')
            ->get();

        $pdf = Pdf::loadView('rrhh.bonos.pdf', compact('persona', 'periodo', 'bonos'));
        return $pdf->download('detalles_bonos.pdf');
    }
}
