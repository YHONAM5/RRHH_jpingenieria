<div class="card">
    <div class="card-header bg-info">
        Reporte de bonos
    </div>
    <div class="card-body">
        <form id="form-bonos" action="{{ route('buscar.bono') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="">Seleccione periodo:</label>
                <select class="form-control" name="periodo" id="" onchange="this.form.submit()">
                    <option hidden value="">Seleccione</option>
                    @foreach ($periodos as $item)
                        <option value="{{ $item->idPeriodo }}">{{ $item->NombrePeriodo }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header bg-info">
        <b>Bonos para el periodo</b>
    </div>
    @if (isset($bonos) && count($bonos) > 0)
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-hover" id="tabla-bonos">
                        <thead>
                            <tr class="bg-warning text-center">
                                <th colspan="6">BONOS</th> <!-- Cambiado de 5 a 6 -->
                            </tr>
                            <tr>
                                <th>N°</th>
                                <th>Nombres</th>
                                <th>Tipo</th>
                                <th>Dias correspondientes</th>
                                <th>Monto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bonos as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->Nombres }}</td>
                                    <td>{{ $item->descripcion}}</td>
                                    <td>{{ $item->CantidadDias }}</td>
                                    <td>
                                        @php
                                            $sueldo_base = $item->SueldoBase;
                                            $monto = $item->idTipoBono == 1 ? ($sueldo_base / 30)*$item->CantidadDias: $item->Monto;
                                        @endphp
                                        {{ round($monto,2) }}
                                    </td>
                                    <td class="text-center">
                                        <button data-id="{{ $item->idBonos }}" class="btn btn-danger btn-sm btn-eliminar-bono"><i class="fas fa-trash"></i></button>
                                        <a href="{{ route('detalles.bono', ['idPersona' => $item->idPersona, 'idPeriodo' => $idPeriodo]) }}" class="btn btn-info btn-sm"><i class="fas fa-info-circle"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card-body">
            <div class="alert alert-primary">
                No se encontraron resultados
            </div>
        </div>
    @endif
</div>
