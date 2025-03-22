<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "residencias_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar que se haya recibido el idUsuario
if (isset($_POST['idUsuario']) && !empty($_POST['idUsuario'])) {
    $id_usuario = $_POST['idUsuario'];

    // Iniciar una transacción para garantizar que todas las operaciones se realicen correctamente
    $conn->begin_transaction();

    try {
        // Eliminar asignaciones relacionadas con el alumno (si es un alumno)
        $sql_asignacion = "DELETE FROM asignaciones WHERE id_alumno = ?";
        $stmt_asignacion = $conn->prepare($sql_asignacion);
        $stmt_asignacion->bind_param("i", $id_usuario);
        $stmt_asignacion->execute();

        // Eliminar el registro en la tabla de alumnos si es necesario
        $sql_alumno = "DELETE FROM alumnos WHERE id_alumno = ?";
        $stmt_alumno = $conn->prepare($sql_alumno);
        $stmt_alumno->bind_param("i", $id_usuario);
        $stmt_alumno->execute();

        // Eliminar el registro del usuario en la tabla Usuarios
        $sql_usuario = "DELETE FROM Usuarios WHERE id_usuario = ?";
        $stmt_usuario = $conn->prepare($sql_usuario);
        $stmt_usuario->bind_param("i", $id_usuario);
        $stmt_usuario->execute();

        // Si todo se ejecuta correctamente, hacer commit de la transacción
        $conn->commit();
        
        echo "<script>alert('Usuario y sus relaciones eliminadas exitosamente.'); window.location.href = 'Index.html';</script>";
    } catch (Exception $e) {
        // Si ocurre un error, hacer rollback de la transacción
        $conn->rollback();
        echo "<script>alert('Error al eliminar el usuario: {$e->getMessage()}'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('ID de usuario no proporcionado.'); window.history.back();</script>";
}

// Cerrar la conexión
$conn->close();
?>
