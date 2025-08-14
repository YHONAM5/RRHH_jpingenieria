<?php

namespace App\Http\Controllers\Tareos;

use App\Http\Controllers\Controller;
use App\Models\Datoscontable;
use App\Models\Estaciondetrabajo;
use App\Models\Horasextra;
use App\Models\RegimenLaboral;
use App\Models\Tareo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Foreach_;

class RegistroTareoController extends Controller
{
    public function tareo_router(Request $request)
    {
        try {
            // --- 1. RECOLECCIÓN DE DATOS (Sin cambios) ---
            $fecha_descanso = $request->input('dias_descanso', []);
            $fecha_registro = $request->input('fecha');
            $hora_inicio = $request->input('hora_inicio');
            $hora_fin = $request->input('hora_fin');
            $contratos = $request->input('contratos');
            $idEstacion = $request->input('idEstacion');
            $horario = $request->input('horario');

            $fechas_inicio = $request->input('fechas_inicio_rango', []);
            $fechas_fin = $request->input('fechas_fin_rango', []);

            $fechas_rango_total = [];
            foreach ($fechas_inicio as $index => $inicio) {
                $fin = $fechas_fin[$index] ?? null;
                if (!$inicio || !$fin) continue;
                $periodo = CarbonPeriod::create($inicio, $fin);
                foreach ($periodo as $fecha) {
                    $fechas_rango_total[] = $fecha->toDateString();
                }
            }

            $dias_descansos = array_unique(array_merge($fecha_descanso, $fechas_rango_total));
            $fechaBase = Carbon::parse($fecha_registro);
            $mes = $fechaBase->month;
            $anio = $fechaBase->year;

            // --- 2. VALIDACIÓN PREVIA (La nueva lógica) ---
            // Primero, verificamos si ya existe algún tareo para los contratos seleccionados en el mes y año dados.
            $tareoExistente = Tareo::whereIn('idContrato', $contratos)
                                   ->whereMonth('Fecha', $mes)
                                   ->whereYear('Fecha', $anio)
                                   ->exists(); // Usamos exists() por ser la consulta más eficiente.

            // Si se encuentra al menos un registro, detenemos todo y mostramos la advertencia.
            if ($tareoExistente) {
                return redirect()->route('tareos')->with('', 'Ya existe al menos un tareo registrado para uno de los trabajadores en el mes seleccionado. No se ha realizado ningún registro.');
            }

            // --- 3. INSERCIÓN DE DATOS (Solo si la validación pasa) ---
            $dias_en_el_mes = $fechaBase->daysInMonth;
            $currentFecha = $fechaBase->copy()->startOfMonth();

            for ($i = 0; $i < $dias_en_el_mes; $i++) {
                $fechaRegistro = $currentFecha->toDateString();
                $dayOfWeek = $currentFecha->dayOfWeekIso; // 1 (Lunes) a 7 (Domingo)
                $esDescanso = in_array($fechaRegistro, $dias_descansos);

                foreach ($contratos as $idContrato) {
                    $iddatoscontables = Datoscontable::where('idContrato', $idContrato)->first();
                    if (!$iddatoscontables) continue;

                    $tareo = new Tareo;
                    $tareo->idContrato = $idContrato;
                    $tareo->Fecha = $fechaRegistro;
                    $tareo->idDatoContable = $iddatoscontables->idDatosContables;
                    $tareo->idEstacionDeTrabajo = $idEstacion;

                    if ($esDescanso) {
                        $tareo->idCondicionDeTareo = 7; // Descanso
                        $tareo->HoraDeIngreso = '00:00';
                        $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                        $tareo->HoraDeFinDeAlmuerzo = '00:00';
                        $tareo->HoraDeSalida = '00:00';
                    } else {
                        $idRegimen = Estaciondetrabajo::where('idEstacionDeTrabajo', $idEstacion)->value('idRegimenLaboral');
                        $tareo->idCondicionDeTareo = ($horario == 0 && $dayOfWeek < 6) ? 1 : 12;

                        if ($idRegimen == 1) {
                            if ($dayOfWeek == 6) { // Sábado
                                $tareo->HoraDeIngreso = $hora_inicio;
                                $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                                $tareo->HoraDeFinDeAlmuerzo = '00:00';
                                $tareo->HoraDeSalida = '13:30';
                            } else { // Lunes a Viernes
                                $tareo->HoraDeIngreso = $hora_inicio;
                                $tareo->HoraDeInicioDeAlmuerzo = '13:00';
                                $tareo->HoraDeFinDeAlmuerzo = '13:45';
                                $tareo->HoraDeSalida = $hora_fin;
                            }
                        } else {
                            $tareo->HoraDeIngreso = $hora_inicio;
                            $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                            $tareo->HoraDeFinDeAlmuerzo = '00:00';
                            $tareo->HoraDeSalida = $hora_fin;
                        }
                    }
                    $tareo->save();
                }
                $currentFecha->addDay();
            }

            return redirect()->route('tareos')->with('success', 'Registro de tareo por router exitoso.');
        } catch (\Exception $e) {
            return redirect()->route('tareos')->with('error', 'Ocurrió un error inesperado al registrar los tareos: ' . $e->getMessage());
        }
    }

    public function tareo_individual(Request $request)
    {
        try {
            $idContrato = $request->input('idContrato');
            $condicion_tareo = $request->input('condicion_tareo');
            $estacion = $request->input('estacion');
            $fecha = $request->input('fecha');
            $hora_ingreso = $request->input('hora_ingreso');
            $hora_inicio_almuerzo = $request->input('hora_inicio_almuerzo');
            $hora_fin_almuerzo = $request->input('hora_fin_almuerzo');
            $hora_salida = $request->input('hora_salida');
            $iddatoscontables = Datoscontable::where('idContrato', $idContrato)->first();

            $tareoExistente = Tareo::where('idContrato', $idContrato)
                                   ->where('Fecha', $fecha)
                                   ->exists();

            if ($tareoExistente) {
                return redirect()->route('tareos')->with('error', 'Ya existe un tareo para este trabajador en la fecha seleccionada.');
            }

            if ($condicion_tareo == 8) {
                for ($i = 1; $i <= 2; $i++) {
                    if ($i == 2) {
                        $condicion_tareo = 1;
                    }
                    $tareo = new Tareo;
                      $tareo->idContrato = $idContrato;
                    $tareo->idDatoContable = $iddatoscontables->idDatosContables;
                    $tareo->Fecha = $fecha;
                    $tareo->HoraDeIngreso = $hora_ingreso;
                    $tareo->HoraDeInicioDeAlmuerzo = $hora_inicio_almuerzo;
                    $tareo->HoraDeFinDeAlmuerzo = $hora_fin_almuerzo;
                    $tareo->HoraDeSalida = $hora_salida;
                    $tareo->idCondicionDeTareo = $condicion_tareo;
                    $tareo->idEstacionDeTrabajo = $estacion;
                    $tareo->save();
                }
                return redirect()->route('tareos')->with('success', 'Registro de tareo individual exitoso.');
            } else {
                $tareo = new Tareo;
                $tareo->idContrato = $idContrato;
                $tareo->idDatoContable = $iddatoscontables->idDatosContables;
                $tareo->Fecha = $fecha;
                $tareo->HoraDeIngreso = $hora_ingreso;
                $tareo->HoraDeInicioDeAlmuerzo = $hora_inicio_almuerzo;
                $tareo->HoraDeFinDeAlmuerzo = $hora_fin_almuerzo;
                $tareo->HoraDeSalida = $hora_salida;
                $tareo->idCondicionDeTareo = $condicion_tareo;
                $tareo->idEstacionDeTrabajo = $estacion;
                $tareo->save();
                return redirect()->route('tareos')->with('success', 'Registro de tareo individual exitoso.');
            }


        } catch (\Exception $e) {
            return redirect()->route('tareos')->with('error', 'Error al registrar.');
        }
    }

    public function tareo_horaextra(Request $request)
    {
        try {
            $fecha = $request->input('fecha');
            $hora = $request->input('hora_extra');
            $idContrato = $request->input('idContrato');

            list($horas, $minutos) = explode(':', $hora);
            $minutos_totales = ($horas * 60) + $minutos;

            if ($minutos_totales > 120) {
                $valor25 = 120;
                $valor35 = $minutos_totales - 120;
            } else {
                $valor25 = $minutos_totales;
                $valor35 = 0;
            }

            $hora_extra = new Horasextra;
            $hora_extra->HoraDeRegistro = $fecha;
            $hora_extra->ValorDe25 = $valor25;
            $hora_extra->ValorDe35 = $valor35;
            $hora_extra->save();
            $idHoraExtra = $hora_extra->idHorasExtras;

            $tareo = Tareo::where('idContrato', $idContrato)->where('Fecha', $fecha)->first();

            if ($tareo === null) {
                throw new \Exception('No se encontró ningún registro de Tareo para el trabajador en esa fecha.');
            }

            $tareo->idHorasExtras = $idHoraExtra;
            $tareo->save();

            return redirect()->route('horasextras')->with('success', 'Registro de horas extras exitoso.');
        } catch (\Exception $e) {
            return redirect()->route('horasextras')->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

}
