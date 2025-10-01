🏕 Boot Camp — Desarrollo Web con CRUD y Acceso Restringido
Autor: Héctor Arciniega Valencia.

📌 Objetivo

Guiar paso a paso desde cero para construir un sitio web con:
	•	Registro e inicio de sesión de usuarios.
	•	Acceso privado restringido.
	•	CRUD completo (Crear, Leer, Actualizar, Eliminar).
	•	Estética uniforme y profesional.

⸻

📖 Índice General

Introducción
	•	¿Qué es un stack de desarrollo local? (XAMPP, MAMP, WAMP, etc.)
	•	Requisitos previos (instalación, configuración por defecto).
	•	Creación de la base de datos inicial (db.sql).
	•	Explicación del método CRUD.

⸻

Lección 1 — Conexión a la base de datos
	•	Archivo: conexion.php.
	•	Explicación línea por línea de cómo abrir la conexión con MySQL.
	•	Manejo de errores básicos de conexión.

⸻

Lección 2 — Crear usuario (Registro)
	•	Archivo: registrar.php.
	•	Formulario HTML con validaciones.
	•	Inserción en tabla usuarios.
	•	Hash seguro de contraseñas (password_hash).
	•	Mensajes claros en caso de éxito o error.

⸻

Lección 3 — Iniciar sesión
	•	Archivo: iniciar_sesion.php.
	•	Validación de correo y contraseña (password_verify).
	•	Manejo de sesiones ($_SESSION).
	•	Redirección a zona privada.

⸻

Lección 4 — Zona privada (Autenticación obligatoria)
	•	Archivo: privado.php.
	•	Restricción de acceso: solo usuarios logueados.
	•	Bienvenida personalizada.
	•	Listado de usuarios en tabla.
	•	Navegación a CRUD.

⸻

Lección 5 — Cerrar sesión
	•	Archivo: salir.php.
	•	Destrucción segura de la sesión.
	•	Redirección automática con mensaje de confirmación.

⸻

Lección 6 — Editar usuario
	•	Archivo: editar.php.
	•	Precarga de datos en formulario.
	•	Actualización de nombre y correo.
	•	Cambio opcional de contraseña.
	•	Mensajes claros de actualización o error (ej. correo duplicado).

⸻

Lección 7 — Eliminar usuario
	•	Archivo: eliminar.php.
	•	Validación de ID en la URL.
	•	Verificación de existencia previa.
	•	Eliminación segura.
	•	Confirmación antes de ejecutar la acción.

⸻

Lección 8 — Estética y usabilidad
	•	Archivo: estilo.css.
	•	Estilos profesionales y modernos (tarjetas, formularios, botones, tablas, mensajes).
	•	Accesibilidad (enfoque visual en campos, colores de éxito/error).
	•	Plantilla reutilizable para todas las páginas.

⸻

📂 Estructura final del proyecto

proyecto_crud/
├─ db.sql               (script de base de datos)
├─ conexion.php         (conexión + inicio de sesión PHP)
├─ registrar.php        (registro de usuarios)
├─ iniciar_sesion.php   (login de usuarios)
├─ privado.php          (zona privada con tabla de usuarios)
├─ editar.php           (editar usuario)
├─ eliminar.php         (eliminar usuario)
├─ salir.php            (cerrar sesión)
└─ estilo.css           (hoja de estilos global)


⸻

✅ Resultado esperado

Al final del Boot Camp, cada alumno podrá:
	1.	Instalar un stack de desarrollo local y administrar phpMyAdmin.
	2.	Crear una base de datos con tabla de usuarios.
	3.	Programar un flujo completo de registro e inicio de sesión.
	4.	Gestionar datos con un CRUD completo.
	5.	Mantener una interfaz simple, clara y profesional.

⸻

🧩 Ejercicios de práctica
	•	Cambiar colores en estilo.css.
	•	Agregar un campo extra al formulario de registro (ej. teléfono).
	•	Mostrar solo los usuarios creados por la cuenta logueada.
	•	Exportar datos a CSV desde phpMyAdmin.

 ⚠️ IMPORTANTE: Las lecciones deberán ser implementadas en sus proyectos, tanto para los que participan en prototipos como los que están en EasyProjects. 
 📌 Este requisito es indispensable para ser sumado al valor del examen del 2do. periodo.

⸻
