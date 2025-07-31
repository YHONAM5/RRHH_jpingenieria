<div class="card">
    <div class="card-header bg-info">
        <!-- Puedes agregar un título o dejarlo vacío -->
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="">Seleccione persona para registrar bono:</label>
            <select class="form-control select_personal" onchange="openModalBonos(this)" data-modal-target="#registro_bonos">
                <option hidden value=""></option>
                @foreach ($personas as $item)
                <option value="{{ $item->idContrato }}">{{ strtoupper($item->Nombres.' '.$item->ApellidoPaterno.' '.$item->ApellidoMaterno) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<script>
    // INICIALIZANDO SELECT2
    $(document).ready(function () {
        $('.select_personal').select2({
            width: '100%',
            theme: "classic",
            placeholder: "Seleccione Persona",
            allowClear: true
        });
    });

    // ABRIR EL MODAL CON EL ID CONTRATO
    function openModalBonos(select) {
        const modalTarget = select.getAttribute('data-modal-target');
        const selectedOption = select.value;
        const selectedText = select.options[select.selectedIndex].text;

        if (modalTarget) {
            const inputContrato = document.getElementById('idContrato');
            if (inputContrato) {
                inputContrato.value = selectedOption;
            }

            const modalTitle = document.querySelector(`${modalTarget} .modal-title`);
            if (modalTitle) {
                modalTitle.textContent = `Registro para: ${selectedText}`;
            }

            $(modalTarget).modal('show');
        }
    }
</script>
