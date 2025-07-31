// Obtener todos los elementos con la clase "btn-eliminar-bono"
const btnEliminarBono = document.getElementsByClassName("btn-eliminar-bono");

// Recorrer los botones y agregar el evento de clic a cada uno
for (let i = 0; i < btnEliminarBono.length; i++) {
  btnEliminarBono[i].addEventListener("click", function() {
    const id = this.getAttribute("data-id");
    console.log("Bono ID: " + id);

    // Mostrar SweetAlert2 para confirmar la eliminación del bono
    Swal.fire({
      title: 'Confirmar eliminación',
      text: '¿Estás seguro de eliminar este bono?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        // Realizar la eliminación del bono mediante una solicitud AJAX
        $.ajax({
          url: '/bonos/eliminar',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          data: {
            id: id
          },
          success: function(response) {
            if (response.success) {
              // Eliminación exitosa, mostrar SweetAlert de éxito
              Swal.fire({
                title: 'Éxito',
                text: 'Bono eliminado correctamente',
                icon: 'success'
              }).then(() => {
                // Ejemplo: Recargar la página después de eliminar el bono
                location.reload();
              });
            } else {
              // Error al eliminar el bono, mostrar SweetAlert de error
              Swal.fire({
                title: 'Error',
                text: 'Error al eliminar el bono: ' + response.message,
                icon: 'error'
              });
            }
          },
          error: function(xhr) {
            // Error al realizar la solicitud AJAX, mostrar SweetAlert de error
            Swal.fire({
              title: 'Error',
              text: 'Error al realizar la solicitud',
              icon: 'error'
            });
          }
        });
      }
    });
  });
}
