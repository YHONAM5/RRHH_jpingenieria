<?php

use App\Models\Bono;
use App\Models\Periodo;
use App\Models\Tareo;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use PhpParser\Node\Stmt\If_;

function tiempoTrabajado($tiempo1, $tiempo2, $tiempo3, $tiempo4){

    $sub1 = strtotime($tiempo2) - strtotime($tiempo1);
    $sub2 = strtotime($tiempo4) - strtotime($tiempo3);

    $subtotal = $sub1 - $sub2;

    $total = gmdate('H:i', $subtotal);

    return $total;
}
function tiempoTrabajadoTotal ($tiempo1, $tiempo2, $tiempo3, $tiempo4){
    $sub1 = strtotime($tiempo2) - strtotime($tiempo1);
    $sub2 = strtotime($tiempo4) - strtotime($tiempo3);

    $subtotal = $sub1 - $sub2;

    return $subtotal;
}

function calcularDominical($fecha,$contrato){

    $fecha1 = Carbon::parse($fecha);
    $fecha2 = Carbon::parse($fecha);

    if($fecha1->format('N') != 1 && $fecha1->format('N') != 7){
        $lunes = $fecha1->previous('Monday');
        $domingo = $fecha2->next('Sunday');
    }else if($fecha1->format('N') == 1){
        $lunes = $fecha1;
        $domingo = $fecha2->next('Sunday');
    }else if($fecha1->format('N') == 7){
        $lunes = $fecha1->previous('Monday');
        $domingo = $fecha2;
    }

    $sabado = $domingo->copy()->subDay();

    $condicionesQueSuman = [1, 2, 4, 6, 8, 9, 10, 12, 13, 15];

    $tareo = Tareo::where('idContrato', $contrato)
    ->whereBetween('Fecha',[$lunes, $sabado])
    ->whereIn('idCondicionDeTareo', $condicionesQueSuman)
    ->get();

    $totalHoras = 0;
    foreach ($tareo as $value) {

        $horaIngreso = strtotime($value->HoraDeIngreso);
        $horaSalida = strtotime($value->HoraDeSalida);
        $horaInicioAlmuerzo = strtotime($value->HoraDeInicioDeAlmuerzo);
        $horaFinAlmuerzo = strtotime($value->HoraDeFinDeAlmuerzo);

        $tiempoTrabajado = ($horaSalida - $horaIngreso) - ($horaFinAlmuerzo - $horaInicioAlmuerzo);
        $totalHoras += $tiempoTrabajado;
    }

    $totalHorasDecimal = $totalHoras;
    $evaluar = ($totalHorasDecimal/3600);

    if($evaluar >= 48){
        $horaSalidaDomingo = '16:00:00';
    }else if($evaluar < 48){
        $horaEntradaDomingo = '08:00:00';
        $horaSalidaDomingo = date('H:i:s',strtotime($horaEntradaDomingo) + ($totalHorasDecimal/6));
    }

    return [
        'horaSalidaDomingo' => $horaSalidaDomingo,
        'fechaDomingo'=> $domingo,
        'evaluar'=>$evaluar
    ];
}

//funcion para regimen normal = 1
function sueldoBrutoregimen1($sueldo_base, $idContrato, $fecha_inicio, $fecha_fin){
    // $registros = Tareo::where('idContrato', $idContrato)
    //     ->whereBetween('Fecha', [$fecha_inicio, $fecha_fin])
    //     ->where('idCondicionDeTareo', 2)
    //     ->get();
    // $tardanza = $registros->HoraDeIngreso;
    $sueldo_por_dia = $sueldo_base / 30;
    $sueldo_por_hora = $sueldo_por_dia / 8;

    $descuento_total = 0;

    $registros = Tareo::where('idContrato', $idContrato)
        ->whereBetween('Fecha', [$fecha_inicio, $fecha_fin])
        ->where('idCondicionDeTareo', 2)
        ->get();

    foreach ($registros as $registro) {
        $fecha = Carbon::parse($registro->Fecha);
        $dia_semana = $fecha->dayOfWeek; // 0 = domingo, 6 = sábado

        // Ignorar domingos
        if ($dia_semana === 0) {
            continue;
        }

        // Convertir HoraDeIngreso a Carbon
        $hora_ingreso = Carbon::createFromFormat('H:i:s', $registro->HoraDeIngreso);
        $hora_inicio = Carbon::createFromTime(8, 0, 0);
        $hora_tolerancia = Carbon::createFromTime(8, 15, 0);

        // Si llegó tarde
        if ($hora_ingreso->gt($hora_tolerancia)) {
            // Calcular horas completas de tardanza
            $horas_tarde = floor($hora_ingreso->diffInMinutes($hora_inicio) / 60);

            // Ajuste para sábado (solo 5.5h de trabajo)
            $horas_laborales = ($dia_semana === 6) ? 5.5 : 8;

            // No descontar más de lo trabajado
            $horas_tarde = min($horas_tarde, $horas_laborales);

            $descuento = $horas_tarde * $sueldo_por_hora;
            $descuento_total += $descuento;
        }
    }
    return number_format($descuento_total, 2);
}

function calcularTotalDescuetoRegimen1($sueldo_base, $idContrato, $fecha_inicio, $fecha_fin)
{
    // 1. Calcular el valor de una hora de trabajo.
    $sueldo_por_hora = ($sueldo_base / 30) / 8;

    // 2. Obtener los registros de tardanza.
    $registrosDeTardanza = Tareo::where('idContrato', $idContrato)
        ->whereBetween('Fecha', [$fecha_inicio, $fecha_fin])
        ->where('idCondicionDeTareo', 2)
        ->get();

    $descuentoTotalAcumulado = 0.0;

    // 3. Definir los umbrales clave una sola vez.
    $horaTolerancia = Carbon::createFromTimeString('08:15:00');
    $horaInicioSancionMultiple = Carbon::createFromTimeString('09:00:00');

    // 4. Iterar sobre cada tardanza.
    foreach ($registrosDeTardanza as $tareo) {
        $horaDeIngreso = Carbon::createFromTimeString($tareo->HoraDeIngreso);
        $horasDeSancion = 0;

        // --- LÓGICA DE CÁLCULO DINÁMICO ---

        // Solo se aplica sanción si la llegada es posterior a la tolerancia (08:15).
        if ($horaDeIngreso->isAfter($horaTolerancia)) {

            // Paso A: Todos los que llegan después de las 08:15 tienen, como mínimo, 1 hora de sanción.
            $horasDeSancion = 1;

            // Paso B: Si además llegan después de las 09:00, se calculan las horas adicionales.
            if ($horaDeIngreso->isAfter($horaInicioSancionMultiple)) {
                // Calculamos cuántas horas *completas* han pasado desde las 09:00.
                // diffInHours() devuelve el número de horas completas transcurridas.
                $horasAdicionales = $horaInicioSancionMultiple->diffInHours($horaDeIngreso);

                // Sumamos estas horas adicionales a la hora base de sanción.
                $horasDeSancion += $horasAdicionales;
            }
        }

        // Si se determinó una sanción, se acumula el descuento.
        if ($horasDeSancion > 0) {
            // Opcional: Limitar la sanción a las horas de la jornada para evitar descuentos excesivos.
            $horasDeSancion = min($horasDeSancion, 8.5); // No descontar más de 8.5 horas

            $descuentoDelDia = $horasDeSancion * $sueldo_por_hora;
            $descuentoTotalAcumulado += $descuentoDelDia;
        }
    }

    // 5. Devolver el monto total del descuento.
    return $descuentoTotalAcumulado;
}

function calcularTotalHorasTAreasoParaRegimen1($sueldobase, $idContrato,$fechaInici,$fechafin){


    $totalDeMinFaltantes = 0;
    $pagoPorHora = ($sueldobase / 30) / 8;

    $tareos = Tareo::whereBetween('Fecha', [$fechaInici, $fechafin])
               ->where('idContrato', $idContrato)
               ->where('idCondicionDeTareo', 2)
               ->get();
    foreach ($tareos as $item){
        $totalDeMinFaltantes += calcularDiferenciaHoras($item);
    }
    $totalDeHorasTareodos = $totalDeMinFaltantes / 60;
    $totalDeMinutos = $totalDeMinFaltantes % 60;

    return $totalDeHorasTareodos * $pagoPorHora;

}
function calcularDiferenciaHoras(Tareo $tareo)
{

    $horaDeTolerancia = Carbon::parse('08:15:00')->format('H:i');

    $esSabado = $tareo->Fecha->isSaturday();
    $esDomigo = $tareo->Fecha->isSunday();
    $minutosRequeridos = $esSabado ? (5 * 60 + 30) : (8 * 60 + 30);

    $fecha = $tareo->Fecha->toDateString();
    $ingreso = Carbon::parse($tareo->HoraDeIngreso);
    $salida = Carbon::parse($tareo->HoraDeSalida);
    $inicioAlmuerzo = Carbon::parse($tareo->HoraDeInicioDeAlmuerzo);
    $finAlmuerzo = Carbon::parse($tareo->HoraDeFinDeAlmuerzo);

    $minutosAlmuerzo = $inicioAlmuerzo->diffInMinutes($finAlmuerzo);
    $minutosJornadaTotal = $ingreso->diffInMinutes($salida);
    $minutosTrabajados = $minutosJornadaTotal - $minutosAlmuerzo;

    $diferencia = $minutosRequeridos - $minutosTrabajados;

    return $diferencia;
    // Formateamos la diferencia para una visualización amigable (ej: "+00:30:00" o "-01:15:00")
    // $signo = $diferenciaEnMinutos >= 0 ? '+' : '-';
    // $diferenciaFormateada = $signo . CarbonInterval::minutes(abs($diferenciaEnMinutos))->cascade()->format('%H:%I:%S');
    // return [
    //     'minutos_trabajados' => $minutosTrabajados,
    //     'minutos_requeridos' => $minutosRequeridos,
    //     'diferencia_minutos' => $diferenciaEnMinutos,
    //     'diferencia_formato' => $diferenciaFormateada,
    // ];
}


function calcularMontoAdicional($dia_inicio, $idContrato, $idEstacion)
    {
        $fecha_inicio = new DateTime($dia_inicio);
        $dia_semana = $fecha_inicio->format('N'); // Obtener el día de la semana (1: lunes, 7: domingo)

        // Verificar si los días no son lunes ni domingo
        if ($dia_semana > 1 && $dia_semana < 7) {
            $monto_adicional = 0;

            // Obtener el lunes de la misma semana
            $fecha_lunes = clone $fecha_inicio;
            $fecha_lunes->modify('last Monday');

            // Calcular la diferencia en días entre el lunes y un día antes de $dia_inicio
            $diferencia_dias = $fecha_lunes->diff($fecha_inicio)->days;

            // Verificar si existe tareo para cada día en la semana previa a $dia_inicio
            for ($i = 0; $i < $diferencia_dias; $i++) {
                $fecha_dia = clone $fecha_lunes;
                $fecha_dia->add(new DateInterval("P{$i}D"));

                // Verificar si existe tareo para el día actual
                $existe_tareo = Tareo::where('Fecha', $fecha_dia->format('Y-m-d'))
                    ->whereIn('idCondicionDeTareo', [1, 2, 11, 12, 13, 4, 6, 7, 8, 9])
                    ->where('idContrato', $idContrato)
                    ->where('tareo.idEstacionDeTrabajo', $idEstacion)
                    ->exists();

                if ($existe_tareo) {
                    // Obtener el tareo para el día actual
                    $tareo = Tareo::where('Fecha', $fecha_dia->format('Y-m-d'))
                        ->whereIn('idCondicionDeTareo', [1, 2, 11, 12, 13, 4, 6, 7, 8, 9])
                        ->where('idContrato', $idContrato)
                         ->where('tareo.idEstacionDeTrabajo', $idEstacion)
                        ->first();

                    $hora_ingreso = $tareo->HoraDeIngreso;
                    $hora_inicioalmuerzo = $tareo->HoraDeInicioDeAlmuerzo;
                    $hora_finalmuerzo = $tareo->HoraDeFinDeAlmuerzo;
                    $hora_salida = $tareo->HoraDeSalida;

                    $tiempo_trabajado = strtotime($hora_salida) - strtotime($hora_ingreso);
                    $tiempo_almuerzo = strtotime($hora_finalmuerzo) - strtotime($hora_inicioalmuerzo);
                    $total_horas_trabajadas = ($tiempo_trabajado - $tiempo_almuerzo) / 3600;

                    if ($total_horas_trabajadas > 8) {
                        $horas_extras = $total_horas_trabajadas - 8;
                        $minutos_extras = ($horas_extras - floor($horas_extras)) * 60;

                        $monto_adicional += round(convertirTiempoDecimal(floor($horas_extras), $minutos_extras), 1);
                    }
                }
            }

            return $monto_adicional;
        }

        return 0; // Retornar 0 si es lunes o domingo
    }

    function calcularMontoRestante($dia_fin, $idContrato, $idEstacion)
    {
        $fecha_fin = new DateTime($dia_fin);
        $dia_semana = $fecha_fin->format('N'); // Obtener el día de la semana (1: lunes, 7: domingo)

        if ($dia_semana === '6' || $dia_semana === '7') {
            return 0; // Retornar 0 si es sábado o domingo
        } else {
            // Encontrar el lunes de la semana
            $lunes_semana = clone $fecha_fin; // Crear una copia de la fecha de fin
            $lunes_semana->modify('last monday'); // Encontrar el último lunes
            $diferencia_dias = $fecha_fin->diff($lunes_semana)->days; // Calcular la diferencia de días

            $monto_restante = 0; // Variable para almacenar el monto adicional

            for ($i = 0; $i <= $diferencia_dias; $i++) {
                $fecha_dia = clone $lunes_semana;
                $fecha_dia->add(new DateInterval("P{$i}D"));

                // Verificar si existe tareo para el día actual
                $existe_tareo = Tareo::where('Fecha', $fecha_dia->format('Y-m-d'))
                    ->whereIn('idCondicionDeTareo', [1, 2, 11, 12, 13, 4, 6, 7, 8, 9])
                    ->where('idContrato', $idContrato)
                    ->where('tareo.idEstacionDeTrabajo', $idEstacion)
                    ->exists();

                if ($existe_tareo) {
                    // Obtener el tareo para el día actual
                    $tareo = Tareo::where('Fecha', $fecha_dia->format('Y-m-d'))
                        ->whereIn('idCondicionDeTareo', [1, 2, 11, 12, 13, 4, 6, 7, 8, 9])
                        ->where('idContrato', $idContrato)
                        ->where('tareo.idEstacionDeTrabajo', $idEstacion)
                        ->first();

                    $hora_ingreso = $tareo->HoraDeIngreso;
                    $hora_inicioalmuerzo = $tareo->HoraDeInicioDeAlmuerzo;
                    $hora_finalmuerzo = $tareo->HoraDeFinDeAlmuerzo;
                    $hora_salida = $tareo->HoraDeSalida;

                    $tiempo_trabajado = strtotime($hora_salida) - strtotime($hora_ingreso);
                    $tiempo_almuerzo = strtotime($hora_finalmuerzo) - strtotime($hora_inicioalmuerzo);
                    $total_horas_trabajadas = ($tiempo_trabajado - $tiempo_almuerzo) / 3600;

                    if ($total_horas_trabajadas > 8) {
                        $horas_extras = $total_horas_trabajadas - 8;
                        $minutos_extras = ($horas_extras - floor($horas_extras)) * 60;

                        $monto_restante += round(convertirTiempoDecimal(floor($horas_extras), $minutos_extras), 1);
                    }
                }
            }

            // Realizar otras operaciones o llamadas a funciones aquí
            // ...

            return $monto_restante; // Retornar el monto adicional calculado
        }
    }

    function convertirTiempoDecimal($horas, $minutos) {
        $decimal = $horas + ($minutos / 60);
        return $decimal;
    }

    // fjsooidf

    // function calcularTotalHorasTrabajadas($idContrato, $idEstacion, $fecha_periodo)
    // {
    //     $totalHoras = 0;
    //     $fecha = Carbon::parse($fecha_periodo);
    //     $periodo = Periodo::whereMonth('DiaDeInicioDelPeriodo', $fecha)
    //         ->whereYear('DiaDeInicioDelPeriodo', $fecha)
    //         ->orderBy('DiaDeInicioDelPeriodo', 'asc')
    //         ->first();

    //     $diasTareados = Tareo::join('estaciondetrabajo', 'tareo.idEstacionDeTrabajo', '=', 'estaciondetrabajo.idEstacionDeTrabajo')
    //         ->join('contrato', 'tareo.idContrato', '=', 'contrato.idContrato')
    //         ->whereBetween('tareo.Fecha', [$periodo->DiaDeInicioDelPeriodo, $periodo->DiaDeFinDelPeriodo])
    //         ->where('tareo.idEstacionDeTrabajo', $idEstacion)
    //         ->where('tareo.idContrato', $idContrato)
    //         ->get();
    //     foreach ($diasTareados as $dia) {
    //         $horaIngreso = $dia->HoraDeIngreso;
    //         $horaSalida = $dia->HoraDeSalida;
    //         $horaInicioAlmnuerzo = $dia->HoraDeInicioDeAlmuerzo;
    //         $horaFinAlmuerzo = $dia->HoraDeFinDeAlmuerzo;

    //         $sub1 = strtotime($horaSalida) - strtotime($horaIngreso);
    //         $sub2 = strtotime($horaFinAlmuerzo) - strtotime($horaInicioAlmnuerzo);

    //         $subtotal = $sub1 - $sub2;
    //         $totalHoras += $subtotal;
    //     }

    //     return gmdate('H:i', $totalHoras);
    // }

    function calcularTotalHorasTrabajadas($idContrato, $idEstacion, $fecha_periodo)
    {
        $totalSegundos = 0;
        $fecha = Carbon::parse($fecha_periodo);

        $periodo = Periodo::whereMonth('DiaDeInicioDelPeriodo', $fecha->month)
            ->whereYear('DiaDeInicioDelPeriodo', $fecha->year)
            ->orderBy('DiaDeInicioDelPeriodo', 'asc')
            ->first();

        if (!$periodo) {
            return '00:00';
        }

        $diasTareados = Tareo::join('estaciondetrabajo', 'tareo.idEstacionDeTrabajo', '=', 'estaciondetrabajo.idEstacionDeTrabajo')
            ->join('contrato', 'tareo.idContrato', '=', 'contrato.idContrato')
            ->whereBetween('tareo.Fecha', [$periodo->DiaDeInicioDelPeriodo, $periodo->DiaDeFinDelPeriodo])
            ->where('tareo.idEstacionDeTrabajo', $idEstacion)
            ->where('tareo.idContrato', $idContrato)
            ->get();

        foreach ($diasTareados as $dia) {
            if (!$dia->HoraDeIngreso || !$dia->HoraDeSalida ||
                !$dia->HoraDeInicioDeAlmuerzo || !$dia->HoraDeFinAlmuerzo) {
                continue;
            }

            $horaIngreso = $dia->HoraDeIngreso;
            $horaSalida = $dia->HoraDeSalida;
            $horaInicioAlmuerzo = $dia->HoraDeInicioDeAlmuerzo;
            $horaFinAlmuerzo = $dia->HoraDeFinDeAlmuerzo;

            $fechaBase = $dia->Fecha;

            try {
                $ingresoCarbon = Carbon::parse($fechaBase . ' ' . $horaIngreso);
                $salidaCarbon = Carbon::parse($fechaBase . ' ' . $horaSalida);
                $inicioAlmuerzoCarbon = Carbon::parse($fechaBase . ' ' . $horaInicioAlmuerzo);
                $finAlmuerzoCarbon = Carbon::parse($fechaBase . ' ' . $horaFinAlmuerzo);

                $horasTotales = $salidaCarbon->diffInSeconds($ingresoCarbon);
                $horasAlmuerzo = $finAlmuerzoCarbon->diffInSeconds($inicioAlmuerzoCarbon);

                $horasEfectivas = $horasTotales - $horasAlmuerzo;
                $totalSegundos += $horasEfectivas;
            } catch (\Exception $e) {
                // Log the error or handle it as appropriate, e.g., skip this entry
                continue;
            }
        }

        $horas = floor($totalSegundos / 3600);
        $minutos = floor(($totalSegundos % 3600) / 60);

        return sprintf('%02d:%02d', $horas, $minutos);
    }
    //Funcion para calcular dias tareados por persona
    function totalDiasTareados ($tareos, $dia_inicio, $dia_fin){
        $totalDias = 0;
        $dia_inicio = Carbon::parse('2025-08-01');
        $dia_fin = Carbon::parse('2025-08-08');

        $periodos = CarbonPeriod::create($dia_inicio, $dia_fin);

        foreach ($periodos as $fecha) {
            echo $fecha->format('Y-m-d') . "\n";

            // Aquí va tu lógica por día

        }

        return $totalDias;
    }

    // Funcion para calcular dias de pendiente de pago
    function diasPendientesDePago($idContrato, $idPeriodo){
        $bonos = Bono::join('contrato', 'bonos.idContrato', '=', 'contrato.idContrato')
            ->where('bonos.idPeriodo', $idPeriodo)
            ->where('bonos.idContrato', $idContrato)
            ->where('idTipoBono', 1)
            ->get();
        $dias_pendientes = 0;
        $monto_bono = 0;
        foreach($bonos as $bono){
            $dias_pendientes += $bono->CantidadDias;
        }

        return $dias_pendientes;
    }
?>
