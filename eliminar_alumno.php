<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "residencias_db";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar que se haya recibido el ID del alumno
if (isset($_POST['id_alumno'])) {
    $id_alumno = $_POST['id_alumno'];

    // Iniciar una transacción para asegurarnos de que todo se borra correctamente
    $conn->begin_transaction();

    try {
        // Eliminar registros en la tabla Calificaciones si existen (ya que tiene relación)
        $sql_calificaciones = "DELETE FROM Calificaciones WHERE id_alumno = ?";
        $stmt_calificaciones = $conn->prepare($sql_calificaciones);
        $stmt_calificaciones->bind_param("i", $id_alumno);
        $stmt_calificaciones->execute();

        // Eliminar registros en la tabla Asignaciones si existen (ya que tiene relación)
        $sql_asignaciones = "DELETE FROM Asignaciones WHERE id_alumno = ?";
        $stmt_asignaciones = $conn->prepare($sql_asignaciones);
        $stmt_asignaciones->bind_param("i", $id_alumno);
        $stmt_asignaciones->execute();

        // Aquí puedes agregar más tablas si es necesario, siempre asegurándote de que existan relaciones

        // Eliminar el alumno de la tabla Alumnos
        $sql_alumno = "DELETE FROM Alumnos WHERE id_alumno = ?";
        $stmt_alumno = $conn->prepare($sql_alumno);
        $stmt_alumno->bind_param("i", $id_alumno);
        $stmt_alumno->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir a la página de éxito o mostrar mensaje de éxito
        header("Location: AppSuperUsuarios.php ?eliminado=success");
        exit();
    } catch (Exception $e) {
        // Si ocurre algún error, deshacer los cambios
        $conn->rollback();
        echo "Error al eliminar el registro: " . $e->getMessage();
    }
} else {
    echo "No se recibió el ID del alumno.";
}

// Cerrar la conexión
$conn->close();
?>
