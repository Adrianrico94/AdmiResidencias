<?php
// Conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$database = "residencias_db";

$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Comprobar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_alumno = $_POST['id_alumno'];
    $matricula = $_POST['matricula'];
    $empresa = $_POST['empresa'];
    $proyecto_asignado = $_POST['proyecto_asignado'];
    $carrera = $_POST['carrera'];
    $ingreso = $_POST['ingreso'];
    $egreso = $_POST['egreso'];
    $telefono_alumno = $_POST['telefono_alumno'];
    $telefono_profesor_asignado = $_POST['telefono_profesor_asignado'];
    $observaciones = $_POST['observaciones'];
    $horario_asignado = $_POST['horario_asignado'];

    // Actualizar los datos en la base de datos
    $sql = "UPDATE alumnos SET
            matricula = ?, empresa = ?, proyecto_asignado = ?, carrera = ?, 
            ingreso = ?, egreso = ?, telefono_alumno = ?, telefono_profesor_asignado = ?, 
            observaciones = ?, horario_asignado = ? 
            WHERE id_alumno = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", $matricula, $empresa, $proyecto_asignado, $carrera, 
                      $ingreso, $egreso, $telefono_alumno, $telefono_profesor_asignado, 
                      $observaciones, $horario_asignado, $id_alumno);

    if ($stmt->execute()) {
        echo "<script>alert('Registro actualizado correctamente.'); window.location.href = 'AppSuperUsuarios.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar el registro: " . $stmt->error . "'); window.history.back();</script>";
    }
}
?>
