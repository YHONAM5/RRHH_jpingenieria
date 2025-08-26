<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/preboleta1.css">
    <title>Pre boleta trabajadores</title>
</head>

<body class="body" id="body">
    <!--<button onclick="" id="create_pdf" class="btn btn-success">Convertir</button>-->
    <div class="PreeBol" id="PreeBol">
        <div class="tablePreboleta">
            <div class="header">
                <div class="img">
                    {{-- LOGO --}}
                    <img src="/public/img/logo_awl.png" alt="Logo JP">
                </div>
                <center>
                    <h3>DETALLE DE PAGO<br>{{ $periodo->NombrePeriodo }}</h3>
                </center>
            </div>
            <div class="table">
                <table>
                    <tr>
                        <th class="thHeader">RUC</th>
                        <th class="thHeader">RAZÓN SOCIAL</th>
                        <th class="thHeader">DIRECCIÓN</th>
                    </tr>
                    <tbody>
                        <tr>
                            <td class="tdBody">20454300654</td>
                            <td class="tdBody">JP INGENIERIA Y SERVICIOS S.R.L.</td>
                            <td class="tdBody">
                                Jr Ancash 304 Alto Libertad - Cerro Colorado. Arequipa
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="table">
                <Table>
                    <tr>
                        <th class="thHeader" colspan="3">
                            Datos del trabajador
                        </th>
                    </tr>
                    <tr>
                        <th class="thHeader">DNI </th>
                        <th class="thHeader">APELLIDO</th>
                        <th class="thHeader">NOMBRE</th>
                    </tr>
                    <tbody>
                        <tr>
                            <td class="tdBody cod"> <b>
                                    {{-- DNI --}}
                                    {{ $empleado->DNI }}
                                </b> </td>
                            <td class="tdBody">
                                {{-- Apellidos --}}
                                {{ strtoupper($empleado->ApellidoPaterno . ' ' . $empleado->ApellidoMaterno) }}
                            </td>
                            <td class="tdBody">
                                {{-- Nombres --}}
                                {{ strtoupper($empleado->Nombres) }}
                            </td>
                        </tr>
                    </tbody>
                </Table>
            </div>
            <?php
            // Convertir la colección en array
            $estaciones_array = $estaciones->toArray();
            $divisor_dias = 30;

            // Filtrar las estaciones con días trabajados mayor a 0
            $estaciones_filtradas = array_filter($estaciones_array, function ($estacion) use ($dia_inicio, $dia_fin, $idContrato) {
                $tareo_estacion = new \App\Http\Controllers\Planilla\PlanillaController();
                $tareo_estacion_value = $tareo_estacion->TareoPorEstacion($estacion['idEstacionDeTrabajo'], $estacion['idRegimenLaboral'], $dia_inicio, $dia_fin, $idContrato);
                return floatval($tareo_estacion_value) > 0;
            });
            $num_estaciones_filtradas = count($estaciones_filtradas);
            ?>
            <div class="table">
                <table>
                    <tr>
                        <th class="thHeader" colspan="{{ $num_estaciones_filtradas + 2 }}">
                            DATOS DEL TRABAJADOR VINCULADOS A LA RELACIÓN LABORAL
                        </th>
                    </tr>
                    <tr>
                        <th class="thHeader" colspan="{{ $num_estaciones_filtradas }}">
                            Días trabajados
                        </th>
                        <!--<th colspan="1"></th>-->
                        <th class="thHeader" colspan="3" rowspan="2">
                            Sueldo Básico
                        </th>
                    </tr>
                    <tr>
                        @foreach ($estaciones_filtradas as $item)
                            <th class="th3 p-1 text-center align-middle thHeader">{{ $item['NombreEstacionDeTrabajo'] }}
                            </th>
                        @endforeach
                    </tr>
                    <tbody>
                        <tr>
                            @php
                                $total_dias_tareados = 0; // Mover la declaración de la variable fuera del bucle
                                $regimeLaboral = 0;
                            @endphp

                            @foreach ($estaciones_filtradas as $estacion)
                                <td class="tdBody">
                                    @php
                                        $tareo_estacion = new \App\Http\Controllers\Planilla\PlanillaController();
                                        $tareo_estacion_value = $tareo_estacion->TareoPorEstacion(
                                            $estacion['idEstacionDeTrabajo'],
                                            $estacion['idRegimenLaboral'],
                                            $dia_inicio,
                                            $dia_fin,
                                            $idContrato,
                                        );
                                        echo $tareo_estacion_value;
                                        $regimeLaboral = $estacion['idRegimenLaboral'];

                                        $total_dias_tareados += floatval($tareo_estacion_value); // Convertir a float y sumar directamente
                                    @endphp
                                </td>
                            @endforeach
                            <!--<td></td>-->
                            <td class="tdBody" colspan="3">
                                <!-- SUELDO BASICO -->
                                {{ $dato_contable->SueldoBase }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <div class="tableDetalle">
                <div class="table1">
                    <h5>REMUNERACIONES</h5>
                    <div class="table1body">
                        <table>
                            <tbody>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Sueldo computable total</td>
                                    <td class="tdDetalle">
                                        {{-- Sueldo computable --}}
                                        @php
                                            $planillaController = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $dias_descanso_programado = $planillaController->DescansosProgramados(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            $dias_faltas = $planillaController->DiasDeFalta(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            $dias_tardanza = $planillaController->DiasDeTardanza(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            $sueldo_base = $dato_contable->SueldoBase;
                                            $dias_justificados = $total_dias_tareados + $dias_descanso_programado;

                                            //  $sueldo_bruto = round(($dato_contable->SueldoBase/$num_dias)*($total_dias_tareados-$cantidad_dias_pendientes),2);

                                            if ($regimeLaboral == 1) {
                                                if ($dias_faltas == 0 && $dias_tardanza == 0) {
                                                    $sueldo_bruto = $sueldo_base;
                                                } else {
                                                    $sueldo_por_dia = $sueldo_base / $divisor_dias;
                                                    $totalDescuentoAplicada =
                                                        $sueldo_por_dia * $dias_faltas +
                                                        calcularTotalHorasTAreasoParaRegimen1(
                                                            $sueldo_base,
                                                            $idContrato,
                                                            $dia_inicio,
                                                            $dia_fin,
                                                        );
                                                    $sueldo_bruto = $sueldo_base - $totalDescuentoAplicada;
                                                }
                                            } elseif ($regimeLaboral == 2) {
                                                if ($dias_justificados == $num_dias) {
                                                    $sueldo_bruto = $sueldo_base;
                                                } else {
                                                    $sueldo_por_dia = $sueldo_base / $divisor_dias;
                                                    $sueldo_bruto = $sueldo_base - $sueldo_por_dia * $dias_faltas;
                                                }
                                            }
                                            echo 'S/' . round($sueldo_bruto, 2);
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Asignación familiar</td>
                                    <td class="tdDetalle">
                                        {{-- Asignacion familiar --}}
                                        @php
                                            $asig_familiar = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $asignacion_familiar_value = $asig_familiar->AsignacionFamiliar(
                                                $idDatoContable,
                                            );
                                            echo 'S/' . $asignacion_familiar_value;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Remuneración vacacional</td>
                                    <td class="tdDetalle">
                                        <!-- Vacaciones -->
                                        @php
                                            $vacaciones_trabajador = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $vacaciones_dias = $vacaciones_trabajador->Vacaciones(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            $vacaciones = round(
                                                ($dato_contable->SueldoBase / $num_dias) * $vacaciones_dias,
                                                2,
                                            );
                                            echo 'S/' . $vacaciones;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Días con goce de haber</td>
                                    <td class="tdDetalle">
                                        {{-- Dias con goce de haber --}}
                                        @php
                                            $dias_con_goce = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $dias_con_goce_dias = $dias_con_goce->DiasConGoce(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            $dias_con_goce_haber = round(
                                                ($dato_contable->SueldoBase / $num_dias) * $dias_con_goce_dias,
                                                2,
                                            );
                                            echo 'S/' . $dias_con_goce_haber;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Descanso médico</td>
                                    <td class="tdDetalle">
                                        {{-- Descanso Medico --}}
                                        @php
                                            $descanso_medico = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $descanso_medico_dias = $descanso_medico->DescansoMedico(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            $descanso_medico = round(
                                                ($dato_contable->SueldoBase / $num_dias) * $descanso_medico_dias,
                                                2,
                                            );
                                            echo 'S/' . $descanso_medico;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Días pendientes de pago</td>
                                    <td class="tdDetalle">
                                        @php
                                            $cantidad_dias_pendientes = diasPendientesDePago($idContrato, $idPeriodo); // Calcular dias_pendientes
                                        @endphp
                                        {{-- Dias pendientes --}}
                                        {{-- @if ($cantidad_dias_pendientes > 0)
                                            @php
                                                $dias_pendientes = round(
                                                    ($dato_contable->SueldoBase / $divisor_dias) *
                                                        $cantidad_dias_pendientes,
                                                    2,
                                                );
                                                echo 'S/' . $dias_pendientes;
                                            @endphp
                                        @else
                                            @php
                                                $dias_pendientes = 0;
                                            @endphp
                                        @endif --}}
                                        @php
                                            if ($cantidad_dias_pendientes > 0) {
                                                $dias_pendientes = round(($sueldo_base / $divisor_dias) * $cantidad_dias_pendientes, 2);

                                            } else {
                                                $dias_pendientes = 0;
                                            }
                                            echo 'S/ '.$dias_pendientes;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Feriados trabajados</td>
                                    <td class="tdDetalle">
                                        {{-- Feriados trabajados --}}
                                        @php
                                            $feriados_trabajados = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $feriados_trabajados_dias = $feriados_trabajados->FeriadosTrabajados(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            $feriado = round(
                                                (($dato_contable->SueldoBase / $divisor_dias)*2) * $feriados_trabajados_dias,
                                                2,
                                            );
                                            echo 'S/' . $feriado;

                                        @endphp

                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Horas extras</td>
                                    <td class="tdDetalle">
                                        {{-- Horas extras --}}
                                        @php
                                            $total_horaextra = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $horas_extras_value = $total_horaextra->TotalHorasExtras(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            echo 'S/' . $horas_extras_value;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Días trabajados en descanso</td>
                                    <td class="tdDetalle">
                                        {{-- Descansos trabajados --}}
                                        @php
                                            $descansos_trabajados = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $descansos_trabajados_dias = $descansos_trabajados->DescansosTrabajados(
                                                $idContrato,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            $descansos_trabajados = round(
                                                ($dato_contable->SueldoBase / $num_dias) * $descansos_trabajados_dias,
                                                2,
                                            );
                                            echo 'S/' . $descansos_trabajados;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Otros</td>
                                    <td class="tdDetalle deta">
                                        <?php
                                        echo 'S/ 0.00';
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tableFooter">
                        <div class="box1">Total Remuneraciones</div>
                        <div class="box2">
                            {{-- Total remuneracion --}}
                            @php
                                $remuneracion_asegurable = round(
                                    $sueldo_bruto +
                                        $dias_pendientes +
                                        $descanso_medico +
                                        $dias_con_goce_haber +
                                        $vacaciones +
                                        $feriado +
                                        $descansos_trabajados +
                                        $asignacion_familiar_value +
                                        $horas_extras_value,
                                    2,
                                );
                                echo 'S/' . $remuneracion_asegurable;
                            @endphp
                        </div>
                    </div>

                </div>

                <div class="table1" style="margin-left: 38px">
                    <h5>RETENCIONES/DESCUENTOS</h5>
                    <div class="table1body">
                        <table>
                            <tbody>
                                <tr>
                                    <td style="font-weight: bolder; height: 35px;" class="tdDetalle">Sistema de
                                        pensiones</td>
                                    <td class="tdDetalle"
                                        style="display: block;height: auto;border-bottom: 1px solid #ccc">
                                        <center>
                                            {{-- Sistema de pensiones --}}
                                            {{ strtoupper($empleado->NombreEntidad) }}
                                        </center>
                                    </td>
                                    <td align="right" class="tdDetalle" style="display: block;height: auto">
                                        <center>
                                            {{-- Monto --}}
                                            @php
                                                $porcentaje_fondo = new \App\Http\Controllers\Planilla\PlanillaController();
                                                $porcentaje_fondo_value = $porcentaje_fondo->PorcentajeFondo(
                                                    $idContrato,
                                                    $remuneracion_asegurable,
                                                );
                                                echo 'S/' . $porcentaje_fondo_value;
                                            @endphp
                                        </center>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Pension Alimenticia</td>
                                    <td align="right" class="tdDetalle">
                                        {{-- Pension Alimenticia --}}
                                        @php
                                            $pension_alimenticia = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $pension_alimenticia_value = $pension_alimenticia->PensionAlimenticia(
                                                $idDatoContable,
                                                $remuneracion_asegurable,
                                            );
                                            echo 'S/' . $pension_alimenticia_value;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Retención de 5ta</td>
                                    <td align="right" class="tdDetalle">
                                        {{-- 5ta --}}
                                        @php
                                            $quinta_categoria = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $quinta_categoria_value = $quinta_categoria->CalcularDescuento5taCategoria(
                                                $sueldo_base + $asignacion_familiar_value, $dia_fin
                                            );
                                            echo 'S/' . $quinta_categoria_value;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Adelantos</td>

                                    <td align="right" class="tdDetalle">
                                        {{-- Adelantos --}}
                                        @php
                                            $adelantos = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $adelantos_value = $adelantos->Adelantos(
                                                $idDatoContable,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            echo 'S/' . $adelantos_value;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Préstamos</td>

                                    <td align="right" class="tdDetalle">
                                        {{-- Prestamos --}}
                                        @php
                                            $prestamos = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $prestamos_value = $prestamos->Prestamos(
                                                $idDatoContable,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            echo 'S/' . $prestamos_value;
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Cuentas a rendir</td>

                                    <td align="right" class="tdDetalle">
                                        {{-- Cuentas a rendir --}}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder;" class="tdDetalle">Descuentos</td>

                                    <td align="right" class="tdDetalle">
                                        {{-- Otros --}}
                                        @php
                                            $otros_descuentos = new \App\Http\Controllers\Planilla\PlanillaController();
                                            $otros_descuentos_value = $otros_descuentos->OtrosDescuentos(
                                                $idDatoContable,
                                                $dia_inicio,
                                                $dia_fin,
                                            );
                                            echo 'S/' . $otros_descuentos_value;
                                        @endphp
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="tableFooter">
                        <div class="box1">Total Descuentos -</div>
                        <div class="box2">
                            {{-- Total descuentos --}}
                            @php
                                $total_descuentos = round(
                                    $porcentaje_fondo_value +
                                        $quinta_categoria_value +
                                        $adelantos_value +
                                        $prestamos_value +
                                        $otros_descuentos_value +
                                        $pension_alimenticia_value,
                                    2,
                                );
                                echo 'S/' . $total_descuentos;
                            @endphp
                        </div>
                    </div>
                    <div class="tableFooter">
                        <div class="box1">Reintegros +</div>
                        <div class="box2">
                            {{-- Reintegros --}}
                            @php
                                echo 'S/' . $reintegro;
                            @endphp
                        </div>
                    </div>

                    <div class="tableFooter">
                        <div class="box1">Bono +</div>
                        <div class="box2">
                            {{-- Bono --}}
                            @php
                                echo 'S/' . ($bonoDeclarado ?? '0.00');
                            @endphp
                        </div>
                    </div>

                    <div class="tableFooter">
                        <div class="box1">Neto a pagar</div>
                        <div class="box2">
                            {{-- Neto a pagar --}}
                            @php
                                $total_neto =
                                    $remuneracion_asegurable - $total_descuentos + $reintegro + $bonoDeclarado;
                                echo 'S/' . $total_neto;
                            @endphp
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>

</html>
