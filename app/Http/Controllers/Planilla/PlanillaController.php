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
use Carbon\Carbon;
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
            ->join('regimen_laboral', 'estaciondetrabajo.idRegimenLaboral', '=', 'regimen_laboral.idRegimenLaboral')
            ->whereIn('tareo.idDatoContable', function($query) use ($dia_inicio, $dia_fin) {
                $query->select(DB::raw('MAX(idDatoContable)'))
                    ->from('tareo')
                    ->whereBetween('Fecha', [$dia_inicio, $dia_fin])
                    ->groupBy('idContrato');
            })
            ->groupBy('contrato.idContrato', 'tareo.idDatoContable', 'persona.Nombres', 'persona.DNI', 'persona.ApellidoPaterno', 'estaciondetrabajo.NombreEstacionDeTrabajo', 'fondodepension.NombreEntidad', 'datoscontables.SueldoBase', 'persona.Email','regimen_laboral.idRegimenLaboral')
            ->select('contrato.idContrato', 'tareo.idDatoContable', 'persona.Nombres', 'persona.DNI', 'persona.ApellidoPaterno', 'estaciondetrabajo.NombreEstacionDeTrabajo', 'persona.Email', 'fondodepension.NombreEntidad', 'regimen_laboral.idRegimenLaboral', DB::raw('MAX(datoscontables.SueldoBase) AS SueldoBase'))
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

        // return ($total_horas);



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
    public function DiasDeTardanza($idContrato, $fecha_inicio, $fecha_fin){
        $idCondicionDescansoProgramado = 2;

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
    public function TotalHorasExtras($sueldo, $idContrato, $dia_inicio, $dia_fin)
    {
        $horasExtras = Tareo::join('horasextras', 'tareo.idHorasExtras', '=', 'horasextras.idHorasExtras')
            ->where('tareo.idContrato', $idContrato)
            ->whereBetween('tareo.Fecha', [$dia_inicio, $dia_fin])
            ->get();

        $hora25 = 0;
        $hora35 = 0;
        $sueldo_bruto = $sueldo;
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
        // $porcentaje = Contrato::join('empleado', 'contrato.idEmpleado', '=', 'empleado.idEmpleado')
        //     ->join('fondodepension', 'empleado.idFondoDePension', '=', 'fondodepension.idFondoDePension')
        //     ->select('fondodepension.NombreEntidad')
        //     ->where('contrato.idContrato', $idContrato)->first();
        // $monto_porcentaje = $porcentaje->PorcentajeDeDescuento;
        // $nombrePension = $porcentaje->NombreEntidad;
        // $descuento_fondo = 0;
        // $penciones = [
        //     'ONP' => 13.00,
        //     'AFP INTEGRA' => 11.84,
        //     'AFP HABITAD'=> 11.84,
        //     'AFP PRIMA' => 11.84,
        //     'AFP PROFUTURO' => 11.70
        // ];
        // foreach ($penciones as $nombre => $porc) {
        //     if($nombrePension == $nombre){
        //         $descuento_fondo = round((($rem_asegurable * $porc)/100),2);
        //     }else{
        //         $descuento_fondo = 0;
        //     }
        // }
        // return ($descuento_fondo);

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
    function calcularRetencionQuintaCategoria(
        float $sueldo_base_mensual,
        int $uit_anual,
        int $mes_actual,
        float $ingresos_acumulados_hasta_mes_anterior = 0,
        float $retenciones_acumuladas_hasta_mes_anterior = 0
    ): float {

        // 1. Cálculo de la Renta Bruta Proyectada Anual
        $sueldos_restantes = (12 - $mes_actual);
        $gratificaciones_restantes = 0;

        // Proyección de gratificaciones
        if ($mes_actual <= 6) { // Si estamos antes o en junio, se proyectan ambas gratificaciones
            $gratificaciones_restantes += $sueldo_base_mensual; // Julio
        }
        if ($mes_actual <= 11) { // Si estamos antes o en noviembre, se proyecta la de diciembre
            $gratificaciones_restantes += $sueldo_base_mensual; // Diciembre
        }

        $renta_bruta_proyectada = $ingresos_acumulados_hasta_mes_anterior +
                                ($sueldo_base_mensual * ($sueldos_restantes + 1)) +
                                $gratificaciones_restantes;

        // 2. Deducción de 7 UIT
        $deduccion_7_uit = 7 * $uit_anual;
        $renta_neta_imponible = max(0, $renta_bruta_proyectada - $deduccion_7_uit);

        // 3. Cálculo del Impuesto Anual Proyectado (según tramos)
        $impuesto_anual_proyectado = 0;

        // Tramos de la SUNAT (valores en UIT)
        $tramo1_limite_uit = 5;
        $tramo2_limite_uit = 20;
        $tramo3_limite_uit = 35;
        $tramo4_limite_uit = 45;

        $tramo1_monto = $tramo1_limite_uit * $uit_anual;
        $tramo2_monto = ($tramo2_limite_uit - $tramo1_limite_uit) * $uit_anual;
        $tramo3_monto = ($tramo3_limite_uit - $tramo2_limite_uit) * $uit_anual;
        $tramo4_monto = ($tramo4_limite_uit - $tramo3_limite_uit) * $uit_anual;

        // Aplicación de tasas
        if ($renta_neta_imponible > 0) {
            if ($renta_neta_imponible <= $tramo1_monto) {
                $impuesto_anual_proyectado = $renta_neta_imponible * 0.08;
            } elseif ($renta_neta_imponible <= ($tramo1_monto + $tramo2_monto)) {
                $impuesto_anual_proyectado = ($tramo1_monto * 0.08) +
                                            (($renta_neta_imponible - $tramo1_monto) * 0.14);
            } elseif ($renta_neta_imponible <= ($tramo1_monto + $tramo2_monto + $tramo3_monto)) {
                $impuesto_anual_proyectado = ($tramo1_monto * 0.08) +
                                            ($tramo2_monto * 0.14) +
                                            (($renta_neta_imponible - ($tramo1_monto + $tramo2_monto)) * 0.17);
            } elseif ($renta_neta_imponible <= ($tramo1_monto + $tramo2_monto + $tramo3_monto + $tramo4_monto)) {
                $impuesto_anual_proyectado = ($tramo1_monto * 0.08) +
                                            ($tramo2_monto * 0.14) +
                                            ($tramo3_monto * 0.17) +
                                            (($renta_neta_imponible - ($tramo1_monto + $tramo2_monto + $tramo3_monto)) * 0.20);
            } else {
                $impuesto_anual_proyectado = ($tramo1_monto * 0.08) +
                                            ($tramo2_monto * 0.14) +
                                            ($tramo3_monto * 0.17) +
                                            ($tramo4_monto * 0.20) +
                                            (($renta_neta_imponible - ($tramo1_monto + $tramo2_monto + $tramo3_monto + $tramo4_monto)) * 0.30);
            }
        }

        // 4. Cálculo de la retención para el mes actual
        $impuesto_pendiente_retener = $impuesto_anual_proyectado - $retenciones_acumuladas_hasta_mes_anterior;

        // Asegurarse de que no se retenga más de lo que se debe o un valor negativo
        $meses_restantes_incluido_actual = (12 - $mes_actual) + 1;
        if ($meses_restantes_incluido_actual <= 0) {
            return 0; // No hay más meses para retener
        }

        $retencion_mes_actual = $impuesto_pendiente_retener / $meses_restantes_incluido_actual;

        // La retención no debe ser negativa (podría pasar si se retuvo de más en meses anteriores)
        return max(0, round($retencion_mes_actual, 2));
    }
    function CalcularDescuento5taCategoria($sueldoMensual, $fecha)
    {
        $UIT = 5350;
        $ingresoAnual = $sueldoMensual * 12; //Mi proyeccion por mensual
        $ingresoBonificacion = $sueldoMensual * 2; // Bono

        $ingresoProyeccionAnual = $ingresoAnual + $ingresoBonificacion; //Total de proyeccion anual

        // Deducción estandar de 7 UIT
        $deduccion = 7 * $UIT;

        // Base imponible anual
        $baseImponible = max(0, $ingresoProyeccionAnual - $deduccion);

        // Tramos de impuesto anual según UIT multiples
        $tramos = [
            5 * $UIT => 0.08,   // 8% para exceso sobre 0 hasta 5 UIT (en base imponible)
            20 * $UIT => 0.14,  // 14% para exceso sobre 5 hasta 20 UIT
            35 * $UIT => 0.17,  // 17% para exceso sobre 20 hasta 35 UIT
            45 * $UIT => 0.20,  // 20% para exceso sobre 35 hasta 45 UIT
            PHP_INT_MAX => 0.30 // 30% para exceso sobre 45 UIT
        ];

        $fechaCarbon = Carbon::parse($fecha);
        $numeroMes = $fechaCarbon->month;

        $impuesto = 0;
        $limiteAnterior = 0;

        foreach ($tramos as $limite => $tasa) {
            if ($baseImponible > $limiteAnterior) {
                $exceso = min($baseImponible, $limite) - $limiteAnterior;
                $impuesto += $exceso * $tasa;
                $limiteAnterior = $limite;
            } else {
                break;
            }
        }
        $retencionBase = round($impuesto / 12, 2);

        // Obtener el mes (1 a 12) desde la fecha (formato yyyy-mm-dd)
        // $numeroMes = (int)date('n', strtotime($fecha));

        $retencion = 0.0;

        if ($numeroMes >= 1 && $numeroMes <= 3) { // enero-marzo /12
            $retencion = $retencionBase;
        } elseif ($numeroMes == 4) {
            // Abril: (impuesto anual - (retencionBase * 3)) / 9
            $retencion = ($impuesto - ($retencionBase * 3)) / 9;
        } elseif ($numeroMes >= 5 && $numeroMes <= 7) {
            // Mayo a julio: (impuesto anual - (retencionBase * 4)) / 8
            $retencion = ($impuesto - ($retencionBase * 4)) / 8;
        } elseif ($numeroMes == 8) {
            // Agosto: (impuesto anual - (retencionBase * 7)) / 5
            $retencion = ($impuesto - ($retencionBase * 7)) / 5;
        } elseif ($numeroMes >= 9 && $numeroMes <= 11) {
            // Septiembre a noviembre: (impuesto anual - (retencionBase * 8)) / 4
            $retencion = ($impuesto - ($retencionBase * 8)) / 4;
        } elseif ($numeroMes == 12) {
            // Diciembre: impuesto anual - (retencionBase * 11)
            $retencion = $impuesto - ($retencionBase * 11);
        } else {
            // En caso de mes inválido, regresamos 0
            $retencion = 0.0;
        }

        return round($retencion, 2);


        // Retornamos el impuesto mensual que se debe descontar
        // return round(($impuesto / 12),2);
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
