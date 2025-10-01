// Esperar a que el DOM se haya cargado completamente antes de ejecutar el código
document.addEventListener('DOMContentLoaded', function () {

    // Obtener el elemento del menú restringido por su ID
    const restrictedMenu = document.getElementById('restricted-menu');

    // Verificar si el usuario ha iniciado sesión almacenando un estado en sessionStorage
    if (sessionStorage.getItem('loggedIn')) {
        // Si el usuario ha iniciado sesión, mostrar el enlace de la intranet
        restrictedMenu.style.display = 'block';
    }
});
