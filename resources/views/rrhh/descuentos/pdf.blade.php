<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .img img {
            height: 80px;
        }
        .header h3 {
            margin: 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .table .thHeader {
            background-color: #d3d3d3;
            font-weight: bold;
        }
        .table .tdBody {
            text-align: center;
        }
        .table h3 {
            margin-top: 0;
        }
        .text-center {
            text-align: center;
        }
        .mt-3 {
            margin-top: 1rem;
        }
    </style>
    <title>Detalles de Descuentos</title>
</head>
<body class="body" id="body">
    <div class="PreeBol" id="PreeBol">
        <div class="tablePreboleta">
            <div class="header">
                <div class="img">
                    {{-- LOGO --}}
                    <img src="{{ public_path('img/logo_jp.png') }}" alt="Logo JP">
                </div>
                <center>
                    <h3>DETALLE DE DESCUENTOS<br>{{ $periodo->NombrePeriodo }}</h3>
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
                            <td class="tdBody">Jr Ancash 304 Alto Libertad - Cerro Colorado. Arequipa</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table">
                <table>
                    <tr>
                        <th class="thHeader" colspan="3">Datos del trabajador</th>
                    </tr>
                    <tr>
                        <th class="thHeader">DNI</th>
                        <th class="thHeader">APELLIDO</th>
                        <th class="thHeader">NOMBRE</th>
                    </tr>
                    <tbody>
                        <tr>
                            <td class="tdBody cod"><b>{{ $persona->DNI }}</b></td>
                            <td class="tdBody">{{ strtoupper($persona->ApellidoPaterno.' '.$persona->ApellidoMaterno) }}</td>
                            <td class="tdBody">{{ strtoupper($persona->Nombres) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table">
                <h3>Adelantos</h3>
                @if ($adelantos->isEmpty())
                    <p>No hay adelantos registrados en este periodo.</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Documento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($adelantos as $adelanto)
                                <tr>
                                    <td>{{ $adelanto->idAdelanto }}</td>
                                    <td>{{ date('d/m/Y', strtotime($adelanto->FechaDeDeposito)) }}</td>
                                    <td>{{ $adelanto->MontoAAdelantar }}</td>
                                    <td>
                                        @if ($adelanto->LinkDeSolicitud)
                                            <a href="{{ public_path('storage/'.$adelanto->LinkDeSolicitud) }}" target="_blank">Ver documento</a>
                                        @else
                                            No disponible
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="table mt-3">
                <h3>Préstamos</h3>
                @if ($prestamos->isEmpty())
                    <p>No hay préstamos registrados en este periodo.</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prestamos as $prestamo)
                                <tr>
                                    <td>{{ $prestamo->idPrestamo }}</td>
                                    <td>{{ date('d/m/Y', strtotime($prestamo->fecha)) }}</td>
                                    <td>{{ $prestamo->monto }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="table mt-3">
                <h3>Otros Descuentos</h3>
                @if ($otros->isEmpty())
                    <p>No hay otros descuentos registrados en este periodo.</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($otros as $otro)
                                <tr>
                                    <td>{{ $otro->idOtrosDescuentos }}</td>
                                    <td>{{ date('d/m/Y', strtotime($otro->fecha)) }}</td>
                                    <td>{{ $otro->monto }}</td>
                                    <td>{{ $otro->motivo }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</body>
</html>