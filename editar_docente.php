<?php
// Configuración de conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$database = "residencias_db";

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_docente = $_POST['id_docente'];
    $clave_profesor = $_POST['clave_profesor'];
    $carrera = $_POST['carrera'];
    $telefono_docente = $_POST['telefono_docente'];
    $correo_institucional = $_POST['correo_institucional'];
    $observaciones = $_POST['observaciones'];
    $horario_asignado = $_POST['horario_asignado'];

    // Datos del usuario
    $id_usuario = $_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $correo_electronico = $_POST['correo_electronico'];
    $contrasena = $_POST['contrasena'];

    // Actualización de los datos del docente
    $sql_docente = "UPDATE Docentes SET 
                    clave_profesor=?, 
                    carrera=?, 
                    telefono_docente=?, 
                    correo_institucional=?, 
                    observaciones=?, 
                    horario_asignado=? 
                    WHERE id_docente=?";

    $stmt_docente = $conn->prepare($sql_docente);
    $stmt_docente->bind_param("ssssssi", $clave_profesor, $carrera, $telefono_docente, $correo_institucional, $observaciones, $horario_asignado, $id_docente);

    // Actualización de los datos del usuario
    $sql_usuario = "UPDATE Usuarios SET 
                    nombre=?, 
                    apellido_paterno=?, 
                    apellido_materno=?, 
                    correo_electronico=?, 
                    contrasena=? 
                    WHERE id_usuario=?";
    
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("sssssi", $nombre, $apellido_paterno, $apellido_materno, $correo_electronico, $contrasena, $id_usuario);

    // Ejecutar las actualizaciones
    if ($stmt_docente->execute() && $stmt_usuario->execute()) {
        echo "<script>alert('Registro actualizado exitosamente.'); window.location.href = 'AppSuperUsuarios.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el registro: {$stmt_docente->error} | {$stmt_usuario->error}'); window.history.back();</script>";
    }
}

$conn->close();
?>
