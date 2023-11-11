<table class="table table-bordered table-hover">
    <thead class="bg-dark">
        <tr>
            <th colspan="5" class="text-center">Contratos de Trabajador</th>
        </tr>
        <tr>
            <th rowspan="2">#</th>
            <th rowspan="2">Fecha Inicio de Contrato</th>
            <th rowspan="2">Fecha Fin de Contrato</th>
            <th rowspan="2">Documento</th>
            <th colspan="2">Acciones</th>
        </tr>
        <tr>
            <th>Subir</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($contratos as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ date('d/m/Y',strtotime($item->FechaDeInicioDeContrato)) }}</td>
            <td>{{ date('d/m/Y',strtotime($item->FechaDeFinDeContrato)) }}</td>
            <td>
                @if ($item->contratopdf)
                    <a class="btn btn-light" href="{{ asset('storage').'/'.$item->contratopdf }}" target="_blank">Ver documento</a>
                @else
                    <a class="btn btn-light disabled" href="#" disabled>Ver documento</a>
                @endif
            </td>
            <td>
                @if ($item->contratopdf)
                    <button class="btn btn-dark btn-sm" disabled><i class="fas fa-plus-square"></i></button>
                @else
                    <button class="btn btn-dark btn-sm btnPerfilContratoOpen" data-idcontrato="{{ $item->idContrato }}" data-toggle="modal" data-target="#modalPerfilContrato"><i class="fas fa-plus-square"></i></button>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>