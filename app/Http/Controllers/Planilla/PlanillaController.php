<?php

namespace App\Http\Controllers\Planilla;

use App\Http\Controllers\Controller;
use App\Models\Adelanto;
use App\Models\Contrato;
use App\Models\Datoscontable;
use App\Models\Estaciondetrabajo;
use App\Models\Otrosdescuento;
use App\Models\Periodo;
use App\Models\Prestamo;
use App\Models\Bono;
use App\Models\Tareo;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\returnSelf;

class PlanillaController extends Controller
{
    public function index()
    {
        $periodos = Periodo::all();

        return view('rrhh.planilla.index', compact('periodos'));
    }

    public function BuscarPlanilla(Request $request)
    {
        $idPeriodo = $request->input('periodo');
        $periodo = Periodo::find($idPeriodo);
        $num_dias = $periodo->CantidadDeDias;
        // $num_dias = 30;
        $dia_inicio = $periodo->DiaDeInicioDelPeriodo;
        $dia_fin = $periodo->DiaDeFinDelPeriodo;

        $tareos = Tareo::join('contrato', 'tareo.idContrato', '=', 'contrato.idContrato')
            ->join('empleado', 'contrato.idEmpleado', '=', 'empleado.idEmpleado')
            ->join('persona', 'empleado.idPersona', '=', 'persona.idPersona')
            ->join('datoscontables', 'contrato.idContrato', '=', 'datoscontables.idContrato')
            ->join('estaciondetrabajo', 'contrato.idEstacionTrabajo', '=', 'estaciondetrabajo.idEstacionDeTrabajo')
            ->join('fondodepension', 'empleado.idFondoDePension', '=', 'fondodepension.idFondoDePension')
            ->whereIn('tareo.idDatoContable', function($query) use ($dia_inicio, $dia_fin) {
                $query->select(DB::raw('MAX(idDatoContable)'))
                    ->from('tareo')
                    ->whereBetween('Fecha', [$dia_inicio, $dia_fin])
                    ->groupBy('idContrato');
            })
            ->groupBy('contrato.idContrato', 'tareo.idDatoContable', 'persona.Nombres', 'persona.DNI', 'persona.ApellidoPaterno', 'estaciondetrabajo.NombreEstacionDeTrabajo', 'fondodepension.NombreEntidad', 'datoscontables.SueldoBase', 'persona.Email')
            ->select('contrato.idContrato', 'tareo.idDatoContable', 'persona.Nombres', 'persona.DNI', 'persona.ApellidoPaterno', 'estaciondetrabajo.NombreEstacionDeTrabajo', 'persona.Email', 'fondodepension.NombreEntidad', DB::raw('MAX(datoscontables.SueldoBase) AS SueldoBase'))
            ->get();

        $datos_contables = Datoscontable::all();

        // Obtener el reintegro para cada contrato
        $reintegros = [];
        foreach ($tareos as $tareo) {
            $reintegro = Bono::where('idContrato', $tareo->idContrato)
                            ->where('idPeriodo', $idPeriodo)
                            ->sum('Reintegro');
            $reintegros[$tareo->idContrato] = $reintegro;
        }

        // Listado de las estaciones en la que se tareo entre las fechas del periodo
        $estaciones = Estaciondetrabajo::join('tareo', 'estaciondetrabajo.idEstacionDeTrabajo', '=', 'tareo.idEstacionDeTrabajo')
            ->whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->groupBy('estaciondetrabajo.idEstacionDeTrabajo', 'estaciondetrabajo.NombreEstacionDeTrabajo', 'estaciondetrabajo.estado', 'estaciondetrabajo.idRegimenLaboral')
            ->select('estaciondetrabajo.*')
            ->distinct()
            ->get();

        // Contando las estaciones
        $num_estaciones = $estaciones->count();

        // Pasar los reintegros a la vista
        return view('rrhh.planilla.planilla', compact('periodo', 'dia_inicio', 'dia_fin', 'num_dias', 'tareos', 'datos_contables', 'estaciones', 'num_estaciones', 'reintegros'));
    }

    //FUNCION PARA CALCULAR DIAS TAREAODS POR PERSONA
    public function TareoPorEstacion($idEstacion, $idRegimenLaboral, $dia_inicio, $dia_fin, $idContrato)
    {
        $tareo = Tareo::join('estaciondetrabajo', 'tareo.idEstacionDeTrabajo', '=', 'estaciondetrabajo.idEstacionDeTrabajo')
            ->whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->where('tareo.idEstacionDeTrabajo', $idEstacion)
            ->where('tareo.idContrato', $idContrato)
            ->get();

        $total_horas = 0;
        $monto_adicional = 0;
        $monto_restante = 0;
        foreach ($tareo as $item) {
            $hora_ingreso = $item->HoraDeIngreso;
            $hora_inicioalmuerzo = $item->HoraDeInicioDeAlmuerzo;
            $hora_finalmuerzo = $item->HoraDeFinDeAlmuerzo;
            $hora_salida = $item->HoraDeSalida;

            if ($item->idCondicionDeTareo !== 4 && $item->idCondicionDeTareo !== 6 && $item->idCondicionDeTareo !== 8 && $item->idCondicionDeTareo !== 9 && $item->idCondicionDeTareo !== 11) {
                if ($hora_ingreso < $hora_salida) {
                    $tiempo = strtotime($hora_salida) - strtotime($hora_ingreso);
                    $almuerzo = strtotime($hora_finalmuerzo) - strtotime($hora_inicioalmuerzo);
                    $total_horas += (($tiempo - $almuerzo) / 3600);
                } else if ($hora_ingreso > $hora_salida) {
                    $tiempo_antes_medianoche = strtotime('23:59:59') - strtotime($hora_ingreso);
                    $tiempo_despues_medianoche = strtotime($hora_salida) - strtotime('00:00:00');
                    $almuerzo = strtotime($hora_finalmuerzo) - strtotime($hora_inicioalmuerzo);

                    // Sumar las horas trabajadas antes y después de la medianoche
                    $total_horas += (($tiempo_antes_medianoche + $tiempo_despues_medianoche - $almuerzo) / 3600);
                }
            }
        }
        $total_dias = 0;
        foreach ($tareo as $item) {
            $dia = $item->idCondicionDeTareo;
            //Condiciones donde cuenta el sistema como dia trabajado
            $condiciones = [1,2,6,8,9,11,12,13,15];
            foreach ($condiciones as $id) {
                if($dia == $id){
                    $total_dias++;
                }
            }
        }

        if ($total_horas !== 0) {
            $monto_adicional = calcularMontoAdicional($dia_inicio, $idContrato, $idEstacion);
            $monto_restante = calcularMontoRestante($dia_fin, $idContrato, $idEstacion);

            if ($idRegimenLaboral === 2) {
                $total_horas = number_format($total_horas / 11, 1);
            } else if ($idRegimenLaboral === 1) {
                $total_horas = number_format((($total_horas + $monto_adicional) - $monto_restante) / 8, 1);
            }
            // codigo para los casos especificos
            if ($idRegimenLaboral === 2) {
		 $total_horas = floor($total_horas * 2) / 2;
		 $total_horas = number_format($total_horas, 1, '.', '');
		}

		//if ($total_horas == 30.7) {
		  //  $total_horas = 31;
		//}
		    // Redondear hacia arriba o hacia abajo en casos específicos
        if ($total_horas == 5.3 || $total_horas == 14.1 || $total_horas == 18.3 || $total_horas == 28.3) {
            $total_horas = floor($total_horas); // Redondea hacia abajo
        }




        }

    //    return ($total_horas);
       return ($total_dias);
    }
    public function DescansosProgramados($idContrato, $fecha_inicio, $fecha_fin){
        $idCondicionDescansoProgramado = 7;

        $cantidad = Tareo::where('idContrato', $idContrato)
            ->where('idCondicionDeTareo', $idCondicionDescansoProgramado)
            ->whereBetween('Fecha', [$fecha_inicio, $fecha_fin])
            ->count();
        return $cantidad;
    }
    public function DiasDeFalta($idContrato, $fecha_inicio, $fecha_fin){
        $idCondicionDescansoProgramado = 3;

        $cantidad = Tareo::where('idContrato', $idContrato)
            ->where('idCondicionDeTareo', $idCondicionDescansoProgramado)
            ->whereBetween('Fecha', [$fecha_inicio, $fecha_fin])
            ->count();
        return $cantidad;
    }


    //FUNCIONA PARA CALCULAR DESCANSO MEDICO
    public function DescansoMedico($idContrato, $dia_inicio, $dia_fin)
    {
        $descanso = Tareo::whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->where('idContrato', $idContrato)
            ->whereNotNull('idDescansoMedico')
            ->get();
        $descansos = $descanso->count();
        return ($descansos);
    }

    //FUNCIONA PARA CALCULAR DIAS CON GOCE
    public function DiasConGoce($idContrato, $dia_inicio, $dia_fin)
    {
        $dcg = Tareo::whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->where('idContrato', $idContrato)
            ->where('idCondicionDeTareo', 6)
            ->get();
        $dias_goce = $dcg->count();
        return ($dias_goce);
    }

    //FUNCION PARA CALCULAR VACACIONES
    public function Vacaciones($idContrato, $dia_inicio, $dia_fin)
    {
        $vacacion = Tareo::whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->where('idContrato', $idContrato)
            ->where('idCondicionDeTareo', 9)
            ->get();
        $vacaciones = $vacacion->count();
        return ($vacaciones);

    }

    //FUNCION PARA CALCULAR FERIADOS TRABAJADOS
    public function FeriadosTrabajados($idContrato, $dia_inicio, $dia_fin)
    {
        $feriados = Tareo::whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->where('idContrato', $idContrato)
            ->where('idCondicionDeTareo', 8)
            ->get();
        $feriados_trabajados = $feriados->count();
        return $feriados_trabajados;
    }

    //FUNCION PARA CALCULAR DESCANSOS TRABAJADOS
    public function DescansosTrabajados($idContrato, $dia_inicio, $dia_fin)
    {
        $descansos = Tareo::whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->where('idContrato', $idContrato)
            ->where('idCondicionDeTareo', 11)
            ->get();
        $descansos_trabajados = $descansos->count();
        return ($descansos_trabajados);
    }

    //FUNCION PARA CALCULAR ASIGNACION FAMILIAR
    public function AsignacionFamiliar($idDatoContable)
    {
        $datos_contables = Datoscontable::where('idDatosContables', $idDatoContable)
            ->first();
        if ($datos_contables) {
            $num_hijos = $datos_contables->NHijos;
            if ($num_hijos > 0) {
                $hijos = 113.0;
            } else {
                $hijos = 0;
            }
            return ($hijos);
        } else {
            $hijos = 0;
            return $hijos;
        }

    }

    //FUNCION PARA CALCULAR HORAS EXTRAS
    public function HorasExtras($idContrato, $dia_inicio, $dia_fin, $opcion)
    {
        $horasExtras = Tareo::join('horasextras', 'tareo.idHorasExtras', '=', 'horasextras.idHorasExtras')
            ->where('tareo.idContrato', $idContrato)
            ->whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->get();
        $totalHoras = 0;

        foreach ($horasExtras as $he) {
            if ($opcion == 25) {
                $totalHoras += $he->ValorDe25;
            } elseif ($opcion == 35) {
                $totalHoras += $he->ValorDe35;
            }
        }

        $horas = floor($totalHoras / 60);
        $minutos = $totalHoras % 60;

        return $horas . ":" . $minutos;
    }

    //FUNCION PARA CALCULAR TOTAL DE HORAS EXTRAS
    public function TotalHorasExtras($idContrato, $dia_inicio, $dia_fin)
    {
        $horasExtras = Tareo::join('horasextras', 'tareo.idHorasExtras', '=', 'horasextras.idHorasExtras')
            ->where('tareo.idContrato', $idContrato)
            ->whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->get();

        $hora25 = 0;
        $hora35 = 0;
        $sueldo_bruto = 1485.48;
        foreach ($horasExtras as $he) {
            $hora25 += $he->ValorDe25;
            $hora35 += $he->ValorDe35;
        }
        $sueldo_minuto = (($sueldo_bruto / 30) / 8) / 60;
        $totalDeHoraExtra = number_format((($sueldo_minuto * 1.25) * $hora25) + (($sueldo_minuto * 1.35) * $hora35), 2);

        return ($totalDeHoraExtra);
    }

    //FUNCION PARA CALCULAR PORCENTAJE DE FONDO
    public function PorcentajeFondo($idContrato, $rem_asegurable)
    {
        $porcentaje = Contrato::join('empleado', 'contrato.idEmpleado', '=', 'empleado.idEmpleado')
            ->join('fondodepension', 'empleado.idFondoDePension', '=', 'fondodepension.idFondoDePension')
            ->where('contrato.idContrato', $idContrato)->first();
        $monto_porcentaje = $porcentaje->PorcentajeDeDescuento;
        $descuento_fondo = round($rem_asegurable * $monto_porcentaje, 2);
        return ($descuento_fondo);
    }

    //FUNCION PARA CALCULAR QUINTA CATEGORIA
    function QuintaCategoria($sueldo_base)
    {
    	// cambiar la uit en febrero a 5150 ya que se acaba de actualizarz22
        $uit = 5350;
        //$sueldo = 15000;
        $asignacionFamiliar = 0;
        $sueldoAnual = round($sueldo_base * 12, 2);
        $gratificacion = round(($sueldo_base * 2) + (($sueldo_base * 0.09) * 2), 2);
        $totalAnual = $sueldoAnual + $gratificacion;
        $monto = round($totalAnual - ($uit * 7), 2);
        $tramo1 = $uit * 5;
        $tramo1Porcen = $tramo1 * 0.08;
        $tramo2 = $uit * 15;
        $tramo2Porcen = $tramo2 * 0.14;
        $tramo3 = $uit * 15;
        $tramo3Porcen = $tramo3 * 0.17;
        $qcate = 0;

        // TRAMO 1
        if ($monto <= (5 * $uit) && $monto > 0) {
            $t1 = $monto * 0.08;
            $qcate = round(($t1 / 12), 2);
        } else {
            // TRAMO 2
            if ($monto > (5 * $uit) && $monto <= (20 * $uit)) {
                $monto = $monto - (5 * $uit);
                $t2 = $monto * 0.14;
                $qcate = round(($tramo1Porcen + $t2) / 12, 2);
            } else {
                // TRAMO 3
                if ($monto > (20 * $uit) && $monto <= (35 * $uit)) {
                    $monto = $monto - (20 * $uit);
                    $sumatramos = $tramo1Porcen + $tramo2Porcen;
                    $t3 = $monto * 0.17;
                    $qcate = round(($sumatramos + $t3) / 12, 2);
                } else {
                    // TRAMO 4
                    if ($monto > (35 * $uit) && $monto <= (45 * $uit)) {
                        $monto = $monto - ($uit * 35);
                        $sumatramos = $tramo1Porcen + $tramo2Porcen + $tramo3Porcen;
                        $t4 = $monto * 0.2;
                        $qcate = round(($sumatramos + $t4) / 12, 2);
                    }
                }
            }
        }
        return (double) $qcate;
    }

    //FUNCION PARA CALCULAR ADELANTOS
    public function Adelantos($idDatoContable, $dia_inicio, $dia_fin)
    {
        $adelantos = Adelanto::whereBetween('adelanto.FechaDeDeposito', [$dia_inicio, $dia_fin])
            ->where('idDatosContables', $idDatoContable)
            ->get();
        $monto_adelantos = 0;
        foreach ($adelantos as $adelanto) {
            round($monto_adelantos += $adelanto->MontoAAdelantar, 2);
        }
        return ($monto_adelantos);

    }

    //FUNCION PARA CALCULAR PRESTAMOS
    public function Prestamos($idDatoContable, $dia_inicio, $dia_fin)
    {
        $prestamos = Prestamo::whereBetween('fecha', [$dia_inicio, $dia_fin])
            ->where('idDatosContables', $idDatoContable)
            ->get();
        $monto_prestamos = 0;
        foreach ($prestamos as $prestamo) {
            round($monto_prestamos += $prestamo->monto, 2);
        }
        return ($monto_prestamos);
    }

    //FUNCION PARA CALCULAR OTROS DESCUENTOS
    public function OtrosDescuentos($idDatoContable, $dia_inicio, $dia_fin)
    {
        $otros_descuentos = Otrosdescuento::whereBetween('fecha', [$dia_inicio, $dia_fin])
            ->where('idDatosContables', $idDatoContable)
            ->get();
        $monto_otrosdescuentos = 0;
        foreach ($otros_descuentos as $otros) {
            round($monto_otrosdescuentos += $otros->monto, 2);
        }
        return ($monto_otrosdescuentos);
    }

    //FUNCION PARA CALCULAR PENSION ALIMENTICIA
    public function PensionAlimenticia($idDatoContable, $sueldo_bruto)
    {
        $pension = Datoscontable::find($idDatoContable);
        if (!empty($pension)) {
            $total_pension = round(($sueldo_bruto * $pension->pensionAlimenticia) / 100, 2);
        } else {
            $total_pension = 0;
        }
        return ($total_pension);
    }

    //FUNCION PARA CALCULAR EL APORTE A ESSALUD
    public function AporteSeguroEssalud($total_neto)
    {
        $sueldo_minimo = 1025;
        if ($total_neto < $sueldo_minimo) {
            $aporte_essalud = $sueldo_minimo * 0.09;
        } else {
            $aporte_essalud = $total_neto * 0.09;
        }
        return $aporte_essalud;
    }

}
