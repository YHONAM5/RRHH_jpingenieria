$(document).ready(function(){

    //LLENAR FORMULARIO CON DATOS PARA EL TAREO
    $('.btn-tareo').on('click', function(){
        //SI TIENE TAREO
        if($(this).data('tareo')){
            let idTareo = $(this).data('tareo')
            //console.log(idTareo)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            $.ajax({
                url: buscarTareoId,
                method: 'POST',
                data: {'idtareo': idTareo},
                success: function(rpta){
                    $('#btnEliminarTareo').show();
                    $('#formTareo input[name="tareo"]').val(rpta.idTareo).hide()
                    $('#formTareo input[name="contrato"]').val('').hide()
                    $('#formTareo select[name="estacion"] option[value="'+ rpta.idEstacionDeTrabajo +'"]').prop('selected', true)
                    $('#formTareo select[name="condicionTareo"] option[value="'+ rpta.idCondicionDeTareo +'"]').prop('selected', true)
                    $('#formTareo input[name="fecha"]').val(moment(rpta.Fecha).utcOffset(0).format('YYYY-MM-DD'))
                    let hora = moment.utc(rpta.HoraDeIngreso).utcOffset(0)
                    $('#formTareo input[name="horaIngreso"]').val(hora.format('HH:mm'))
                    hora = moment.utc(rpta.HoraDeInicioDeAlmuerzo).utcOffset(0)
                    $('#formTareo input[name="inicioAlmuerzo"]').val(hora.format('HH:mm'))
                    hora = moment.utc(rpta.HoraDeFinDeAlmuerzo).utcOffset(0)
                    $('#formTareo input[name="finAlmuerzo"]').val(hora.format('HH:mm'))
                    hora = moment.utc(rpta.HoraDeSalida).utcOffset(0)
                    $('#formTareo input[name="horaSalida"]').val(hora.format('HH:mm'))

                },
                error: function(xhr, status, error){
                    const errorMessage = xhr.responseText; // Obtener el mensaje de error del servidor
                    const errorObj = JSON.parse(errorMessage);
                    console.log(errorObj.error);

                    Swal.fire({
                        title: 'ATENCION',
                        text: errorObj.error,
                        icon: 'warning',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
        }
        //NO TIENE TAREO
        else if($(this).data('contrato')){
            //LIMPIAMOS LOS INPUTS
            $('#btnEliminarTareo').hide();
            $('#formTareo input[name="tareo"]').val('').hide()
            $('#formTareo input[name="contrato"]').val('')
            $('#formTareo select option[value=""]').prop('selected', true)
            $('#formTareo input[name="horaIngreso"]').val('')
            $('#formTareo input[name="inicioAlmuerzo"]').val('')
            $('#formTareo input[name="finAlmuerzo"]').val('')
            $('#formTareo input[name="horaSalida"]').val('')

            let contrato = $(this).data('contrato')
            let fecha = $(this).data('fecha')

            $('#formTareo input[name="contrato"]').val(contrato).hide()
            $('#formTareo input[name="fecha"]').val(fecha)
        }

    });

    $('#btnGuardarTareo').on('click', function(){
        const form = $('#formTareo').serialize()

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        $.ajax({
            url: guardarEditarTareo,
            method: 'POST',
            data: form,
            success: function(rpta){
                Swal.fire({
                    title: 'EXITO',
                    text: rpta.mensaje,
                    icon: 'success',
                    confirmButtonText: 'ACEPTAR'
                }).then(function(){
                    location.reload();
                });
            },
            error: function(xhr, status, error){
                const errorMessage = xhr.responseText; // Obtener el mensaje de error del servidor
                const errorObj = JSON.parse(errorMessage);

                Swal.fire({
                    title: 'ATENCION',
                    text: errorObj.error,
                    icon: 'warning',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
    })

    //Nuevo boton para para agregar pendientes de pago segun el tipo de tareo
    //=========================================================
    $('#guardarPendientes').on('click', function(){
        const form = $('#formTareo').serialize()

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        $.ajax({
            url: guardarPendientes,
            method: 'POST',
            data: form,
            success: function(rpta){
                Swal.fire({
                    title: 'EXITO',
                    text: rpta.mensaje,
                    icon: 'success',
                    confirmButtonText: 'ACEPTAR'
                }).then(function(){
                    location.reload();
                });
            },
            error: function(xhr, status, error){
                const errorMessage = xhr.responseText; // Obtener el mensaje de error del servidor
                const errorObj = JSON.parse(errorMessage);

                Swal.fire({
                    title: 'ATENCION',
                    text: errorObj.error,
                    icon: 'warning',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
    })
    //=========================================================

    //ELIMINAR TAREO
    $('#btnEliminarTareo').on('click', function(){
        const idTareo = $('#tareo').val(); // Obtener el valor del campo de entrada con el ID "idTareo"
        console.log(idTareo);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        $.ajax({
            url: eliminarTareo,
            method: 'POST',
            data: {
                idTareo: idTareo // Incluir el valor del campo de entrada en los datos enviados
            },
            success: function(rpta){
                Swal.fire({
                    title: 'EXITO',
                    text: rpta.mensaje,
                    icon: 'success',
                    confirmButtonText: 'ACEPTAR'
                }).then(function(){
                    location.reload();
                });
            },
            error: function(xhr, status, error){
                const errorMessage = xhr.responseText; // Obtener el mensaje de error del servidor
                const errorObj = JSON.parse(errorMessage);

                Swal.fire({
                    title: 'ERROR',
                    text: errorObj.error,
                    icon: 'warning',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });

})

$(document).ready(function() {
    // Manejar el evento de cambio del select de estación de trabajo
    $('select[name="estacion"]').on('change', function() {
        const selectedValue = $(this).val();

        // Actualizar los valores de los campos de entrada según el valor seleccionado
        if (selectedValue === '1' || selectedValue === '2') {
            $('input[name="horaIngreso"]').val('08:00');
            $('input[name="inicioAlmuerzo"]').val('13:00');
            $('input[name="finAlmuerzo"]').val('14:00');
            $('input[name="horaSalida"]').val('17:15');
        } else  if (selectedValue === '3') {
            $('input[name="horaIngreso"]').val('06:00');
            $('input[name="inicioAlmuerzo"]').val('00:00');
            $('input[name="finAlmuerzo"]').val('00:00');
            $('input[name="horaSalida"]').val('18:00');
        } else  if (selectedValue === '4' || selectedValue === '5' || selectedValue === '6' || selectedValue === '7' || selectedValue === '8') {
            $('input[name="horaIngreso"]').val('06:00');
            $('input[name="inicioAlmuerzo"]').val('13:00');
            $('input[name="finAlmuerzo"]').val('14:00');
            $('input[name="horaSalida"]').val('16:45');
        }else{
            $('input[name="horaIngreso"]').val('08:00');
            $('input[name="inicioAlmuerzo"]').val('13:00');
            $('input[name="finAlmuerzo"]').val('14:00');
            $('input[name="horaSalida"]').val('17:15');
        }
    });
    $('select[name="condicionTareo"]').on('change', function() {
        const selectedValue = $(this).val();
        let mensaje = '';

        if (selectedValue === '1') { // Normal
            $('input[name="horaIngreso"]').val('08:00');
            $('input[name="inicioAlmuerzo"]').val('13:00');
            $('input[name="finAlmuerzo"]').val('13:45');
            $('input[name="horaSalida"]').val('17:15');

            $('#grupoHoraIngreso').show();
            $('#grupoAlmuerzo').show();
            $('#grupoHoraSalida').show();

            $('#grupoBonos').hide();

            mensaje = "Registro normal de asistencia con horarios completos.";
        } else if (selectedValue === '2') { // Tardanza
            $('input[name="horaIngreso"]').val('09:00');
            $('input[name="inicioAlmuerzo"]').val('13:00');
            $('input[name="finAlmuerzo"]').val('13:45');
            $('input[name="horaSalida"]').val('17:15');

            $('#grupoHoraIngreso').show();
            $('#grupoAlmuerzo').show();
            $('#grupoHoraSalida').show();

            mensaje= "Registro con tardanza. Especifique la hora real de ingreso.";
        } else if (selectedValue === '3') { // Ausencia / descanso médico / descanso programado
            $('input[name="horaIngreso"]').val('00:00');
            $('input[name="inicioAlmuerzo"]').val('00:00');
            $('input[name="finAlmuerzo"]').val('00:00');
            $('input[name="horaSalida"]').val('00:00');

            // Ocultar solo el contenedor visual
            $('#grupoHoraIngreso').hide();
            $('#grupoAlmuerzo').hide();
            $('#grupoHoraSalida').hide();
            $('#grupoBonos').hide();

            mensaje = "Ausencia del trabajador. No se registran horarios.";
        } else if(selectedValue === '4' || selectedValue === '7' || selectedValue === '14' ){
            $('input[name="horaIngreso"]').val('00:00');
            $('input[name="inicioAlmuerzo"]').val('00:00');
            $('input[name="finAlmuerzo"]').val('00:00');
            $('input[name="horaSalida"]').val('00:00');

            // Ocultar solo el contenedor visual
            $('#grupoHoraIngreso').hide();
            $('#grupoAlmuerzo').hide();
            $('#grupoHoraSalida').hide();
            $('#grupoBonos').hide();

            mensaje = "Descansos justificados. No se registran horarios.";
        } else if (selectedValue === '12') { // Nocturno
            $('input[name="horaIngreso"]').val('22:00');
            $('input[name="inicioAlmuerzo"]').val('01:00');
            $('input[name="finAlmuerzo"]').val('01:30');
            $('input[name="horaSalida"]').val('06:00');
            mensaje = "Horario nocturno. Aplicar sobretasa correspondiente.";
        } else if (selectedValue === '13') { // Medio tiempo
            $('input[name="horaIngreso"]').val('08:00');
            $('input[name="inicioAlmuerzo"]').val('00:00');
            $('input[name="finAlmuerzo"]').val('00:00');
            $('input[name="horaSalida"]').val('12:00');
            mensaje ="Trabajador de medio tiempo.";
        } else if(selectedValue === '11'){
            $('input[name="horaIngreso"]').val('08:00');
            $('input[name="inicioAlmuerzo"]').val('13:00');
            $('input[name="finAlmuerzo"]').val('13:45');
            $('input[name="horaSalida"]').val('17:15');
            mensaje ="Trabajo en dia de descanso. Aplica sobretasa correspondiente.";
        } else if(selectedValue ==='16'){
            $('#grupoBonos').show();

            $('#grupoHoraIngreso').hide();
            $('#grupoAlmuerzo').hide();
            $('#grupoHoraSalida').hide();
            $('#btnGuardarTareo').hide();
        } else {
            // Default
            $('input[name="horaIngreso"]').val('00:00');
            $('input[name="inicioAlmuerzo"]').val('00:00');
            $('input[name="finAlmuerzo"]').val('00:00');
            $('input[name="horaSalida"]').val('00:00');

            // Mostrar los campos si se selecciona otra condición
            $('#grupoHoraIngreso').show();
            $('#grupoAlmuerzo').show();
            $('#grupoHoraSalida').show();

            mensaje = "Especifique la hora correspondiente ala condición de tareo.";
        }
        // Mostrar mensaje
        if (mensaje) {
            $('#mensajeInfo').text(mensaje);
            $('#infoCondicion').fadeIn();
        } else {
            $('#infoCondicion').fadeOut();
        }
    });
});
