<?php
// Incluir archivo de conexión a la base de datos
include('AppSuperUsuarios.php');

// Comprobar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $tipo_usuario = $_POST['tipo_usuario'];
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $correo_electronico = $_POST['correo_electronico'];
    $contrasena = $_POST['contrasena'];

    // Validaciones
    $error = '';

    if (empty($tipo_usuario) || empty($nombre) || empty($apellido_paterno) || empty($apellido_materno) || empty($correo_electronico) || empty($contrasena)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Verificar si el correo electrónico ya existe en la base de datos
        $query = "SELECT * FROM Usuarios WHERE correo_electronico = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $correo_electronico);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "El correo electrónico ya está registrado.";
        }
    }

    // Si no hay errores, proceder con la inserción
    if (empty($error)) {
        // Insertar el nuevo usuario
        $query = "INSERT INTO Usuarios (nombre, apellido_paterno, apellido_materno, correo_electronico, contrasena, tipo_usuario) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $nombre, $apellido_paterno, $apellido_materno, $correo_electronico, password_hash($contrasena, PASSWORD_DEFAULT), $tipo_usuario);
        $stmt->execute();

        // Si el tipo de usuario es Docente, agregar información adicional en la tabla Docentes
        if ($tipo_usuario === 'Docente') {
            $clave_profesor = $_POST['clave_profesor'];
            $carrera = $_POST['carrera'];
            $telefono_docente = $_POST['telefono_docente'];
            $correo_institucional = $_POST['correo_institucional'];
            $observaciones = $_POST['observaciones'];
            $horario_asignado = $_POST['horario_asignado'];

            // Insertar los datos del docente
            $id_docente = $conn->insert_id; // Obtener el ID del usuario recién insertado
            $query_docente = "INSERT INTO Docentes (id_docente, clave_profesor, carrera, telefono_docente, correo_institucional, observaciones, horario_asignado)
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_docente = $conn->prepare($query_docente);
            $stmt_docente->bind_param("issssss", $id_docente, $clave_profesor, $carrera, $telefono_docente, $correo_institucional, $observaciones, $horario_asignado);
            $stmt_docente->execute();
        }

        // Redirigir a la página de superusuarios con un mensaje de éxito
        echo "<script>
                alert('Usuario registrado exitosamente.');
                window.location.href = 'AppSuperUsuarios.php';
              </script>";
    } else {
        // Mostrar el error en un alert
        echo "<script>
                alert('$error');
                window.location.href = 'AppSuperUsuarios.php';
              </script>";
    }
}
?>
