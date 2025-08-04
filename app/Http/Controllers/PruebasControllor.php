<?php

namespace App\Http\Controllers\Tareos;

use App\Http\Controllers\Controller;
use App\Models\Datoscontable;
use App\Models\Horasextra;
use App\Models\Tareo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RegistroTareoController extends Controller
{
    public function tareo_router(Request $request)
    {
        try {

            $dias_descansos = $request->input('dias_descanso');
            $fecha_registro = $request->input('fecha');
            // $dias_trabajados = $request->input('dias_trabajados');
            // $dias_descansos = $request->input('dias_descansos');
            $hora_inicio = $request->input('hora_inicio');
            $hora_fin = $request->input('hora_fin');
            $contratos = $request->input('contratos');
            $idEstacion = $request->input('idEstacion');
            $horario = $request->input('horario');

            $fecha = Carbon::parse($fecha_registro);

            $dias_trabajados = 30; // Número de días trabajados a registrar

            $currentFecha = $fecha; // Variable para hacer seguimiento de la fecha actual

            $dias_registrados = 0; // Variable para hacer seguimiento de los días trabajados registrados

            //Iteramos para registrar cada día trabajado
            while ($dias_registrados < $dias_trabajados) {
                $fechaRegistro = $currentFecha->toDateString();
                $dayOfWeek = date('N', strtotime($currentFecha));

                $esDescanso = in_array($fechaRegistro, $dias_descansos);
                if (!$esDescanso) {
                    foreach ($contratos as $idContrato) {
                        $iddatoscontables = Datoscontable::where('idContrato', $idContrato)->first();



                        $tareo = new Tareo;
                        $tareo->idContrato = $idContrato;
                        $tareo->Fecha = $fechaRegistro;
                        $tareo->idDatoContable = $iddatoscontables->idDatosContables;

                        if ($horario == 0 && ($dayOfWeek != 6 || $dayOfWeek != 7)) {
                            $tareo->idCondicionDeTareo = $esDescanso ? 7 : 1; // 7 para descanso, 1 para trabajo normal
                        } else {
                            $tareo->idCondicionDeTareo = 12;
                        }


                        if($dayOfWeek == 6){
                            $tareo->HoraDeIngreso = $hora_inicio;
                            $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                            $tareo->HoraDeFinDeAlmuerzo = '00:00';
                            $tareo->HoraDeSalida = '13:30';
                        }elseif($dayOfWeek == 7){
                            $tareo->HoraDeIngreso = $hora_inicio;
                            $tareo->HoraDeIngreso = '00:00';
                            $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                            $tareo->HoraDeFinDeAlmuerzo = '00:00';
                            $tareo->HoraDeSalida = '00:00';
                        }else{
                            $tareo->HoraDeIngreso = $hora_inicio;
                            $tareo->HoraDeSalida = $hora_fin;

                            $tareo->HoraDeInicioDeAlmuerzo = '13:00';
                            $tareo->HoraDeFinDeAlmuerzo = '13:45';
                        }
                        // if ($idEstacion == 1 || $idEstacion == 3) {
                        //     if ($dayOfWeek == 6) {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeSalida = '13:30';
                        //     } elseif ($dayOfWeek == 7) {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeSalida = '16:00';
                        //     } else {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '13:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '13:45';
                        //         $tareo->HoraDeSalida = $hora_fin;
                        //     }
                        // } elseif ($idEstacion == 2 || $idEstacion == 3) {
                        //     if ($dayOfWeek == 6) {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeSalida = '13:00';
                        //     } elseif ($dayOfWeek == 7) {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeSalida = '15:30';
                        //     } else {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '13:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '13:45';
                        //         $tareo->HoraDeSalida = $hora_fin;
                        //     }
                        // } elseif ($idEstacion == 9) {
                        //     if ($dayOfWeek == 6) {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeSalida = '13:30';
                        //     } elseif ($dayOfWeek == 7) {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '00:00';
                        //         $tareo->HoraDeSalida = '16:00';
                        //     } else {
                        //         $tareo->HoraDeInicioDeAlmuerzo = '13:00';
                        //         $tareo->HoraDeFinDeAlmuerzo = '14:00';
                        //         $tareo->HoraDeSalida = $hora_fin;
                        //     }
                        // } else {
                        //     $tareo->HoraDeInicioDeAlmuerzo = '13:00';
                        //     $tareo->HoraDeFinDeAlmuerzo = '14:00';
                        //     $tareo->HoraDeSalida = $hora_fin;
                        // }

                        $tareo->idEstacionDeTrabajo = $idEstacion;
                        $tareo->save();
                    }
                } else {
                    // Si es un día de descanso, registramos el tareo con la condición de descanso
                    foreach ($contratos as $idContrato) {
                        $iddatoscontables = Datoscontable::where('idContrato', $idContrato)->first();
                        $tareo = new Tareo;
                        $tareo->idContrato = $idContrato;
                        $tareo->Fecha = $fechaRegistro;
                        $tareo->idDatoContable = $iddatoscontables->idDatosContables;
                        $tareo->idCondicionDeTareo = 7; // 7 para descanso
                        $tareo->HoraDeIngreso = '00:00';
                        $tareo->HoraDeInicioDeAlmuerzo = '00:00';
                        $tareo->HoraDeFinDeAlmuerzo = '00:00';
                        $tareo->HoraDeSalida = '00:00';
                        $tareo->idEstacionDeTrabajo = $idEstacion;
                        $tareo->save();
                    }
                }

                $currentFecha->addDay(); // Avanzar al siguiente día
                $dias_registrados++;
            }

            // $ultimoDiaTrabajo = $currentFecha->subDay(); // Obtener el último día de trabajo registrado

            // for ($i = 0; $i < $dias_descansos; $i++) {
            //     $fechaRegistro = $ultimoDiaTrabajo->addDay()->toDateString();

            //     foreach ($contratos as $idContrato) {
            //         $iddatoscontables = Datoscontable::where('idContrato', $idContrato)->first();
            //         $tareo = new Tareo;
            //         $tareo->idContrato = $idContrato;
            //         $tareo->Fecha = $fechaRegistro;
            //         $tareo->idDatoContable = $iddatoscontables->idDatosContables;
            //         $tareo->idCondicionDeTareo = 7;
            //         $tareo->HoraDeIngreso = $hora_inicio;
            //         $tareo->HoraDeInicioDeAlmuerzo = '13:00';
            //         $tareo->HoraDeFinDeAlmuerzo = '14:00';
            //         $tareo->HoraDeSalida = $hora_fin;
            //         $tareo->idEstacionDeTrabajo = $idEstacion;
            //         $tareo->save();
            //     }
            // }
            return redirect()->route('tareos')->with('success', 'Registro de tareo por router exitoso.');
        } catch (\Exception $e) {
            return redirect()->route('tareos')->with('error', 'Error al registrar.');
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
