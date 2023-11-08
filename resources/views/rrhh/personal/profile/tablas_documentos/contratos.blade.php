<table class="table table-bordered table-hover">
    <thead class="bg-dark">
        <tr>
            <th colspan="4" class="text-center">Contratos de Trabajador</th>
        </tr>
        <tr>
            <th>#</th>
            <th>Fecha Inicio de Contrato</th>
            <th>Fecha Fin de Contrato</th>
            <th>Documento</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($contratos as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ date('d/m/Y',strtotime($item->FechaDeInicioDeContrato)) }}</td>
            <td>{{ date('d/m/Y',strtotime($item->FechaDeFinDeContrato)) }}</td>
            <td>
                <a class="btn btn-light" href="{{ asset('storage').'/'.$item->contratopdf }}" target="_blank">Ver documento</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>