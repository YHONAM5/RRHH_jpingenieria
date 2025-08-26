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
            modalTitle.className = 'btn btn-dark';
            modalTitle.textContent = `Registro para: ${selectedText}`;
        }

        $(modalTarget).modal('show');
    }
}

// EJECUCIÓN DESPUÉS DE QUE EL DOM ESTÉ LISTO
document.addEventListener("DOMContentLoaded", function() {
    const selectBono = document.getElementById('select-bono');
    if (selectBono) {
        selectBono.addEventListener('change', function() {
            const divMonto = document.getElementById('div-monto');
            const divDocumento = document.getElementById('div-documento-bono');
            const divComentario = document.getElementById('div-comentario-bono');

            // Siempre mostrar todos los campos ya que todos los casos son iguales
            divMonto.style.display = 'block';
            divDocumento.style.display = 'block';
            divComentario.style.display = 'block';
        });
    }

    // ENVÍO DE FORMULARIO DE BONOS
    const formRegistroBono = document.getElementById('form-registro-bono');
    if (formRegistroBono) {
        formRegistroBono.addEventListener('submit', function(event) {
            event.preventDefault(); // Evitar el envío predeterminado del formulario

            const formData = new FormData(this);

            // Realizar la solicitud HTTP con Fetch
            fetch(this.action, {
                method: this.method,
                body: formData
            })
            .then(function(response) {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Error en la solicitud');
                }
            })
            .then(function(data) {
                if (data.success) {
                    // Mostrar Sweet Alert de éxito
                    Swal.fire('¡Éxito!', data.message, 'success').then(function() {
                        // Cerrar el modal
                        $('#registro_bonos').modal('hide').on('hidden.bs.modal', function () {
                            $(this).find('form').trigger('reset');
                        });
                    });
                } else {
                    // Mostrar Sweet Alert de error
                    Swal.fire('¡Error!', data.message, 'error');
                }
            })
            .catch(function(error) {
                // Mostrar Sweet Alert de error en caso de error en la solicitud
                Swal.fire('¡Error!', error.message, 'error');
            });
        });
    }
});
