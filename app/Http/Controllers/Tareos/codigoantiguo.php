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


<?php

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



            //Rango de fechas
            // $rango = $request->input('rango_fechas');

            // [$fecha_inicio, $fecha_fin] = explode(' - ', $rango);

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
