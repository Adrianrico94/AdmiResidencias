<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_type'])) {
    header("Location: Index.html");
    exit();
}

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

// Obtener los datos del formulario
$nombre = $_POST['nombre'];
$apellido_paterno = $_POST['apellido_paterno'];
$apellido_materno = $_POST['apellido_materno'];
$correo_electronico = $_POST['correo_electronico'];
$contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
$tipo_usuario = $_POST['tipo_usuario'];

// Insertar en la tabla Usuarios
$sql = "INSERT INTO Usuarios (nombre, apellido_paterno, apellido_materno, correo_electronico, contrasena, tipo_usuario) 
        VALUES ('$nombre', '$apellido_paterno', '$apellido_materno', '$correo_electronico', '$contrasena', '$tipo_usuario')";

if ($conn->query($sql) === TRUE) {
    // Si es Docente, insertamos los datos adicionales en la tabla Docentes
    if ($tipo_usuario == 'Docente') {
        $id_docente = $conn->insert_id;  // Obtener el id del docente recién insertado
        $clave_profesor = $_POST['clave_profesor'];
        $carrera = $_POST['carrera'];
        $telefono_docente = $_POST['telefono_docente'];
        $correo_institucional = $_POST['correo_institucional'];
        $horario_asignado = $_POST['horario_asignado'];

        $sql_docente = "INSERT INTO Docentes (id_docente, clave_profesor, carrera, telefono_docente, correo_institucional, horario_asignado) 
                        VALUES ($id_docente, '$clave_profesor', '$carrera', '$telefono_docente', '$correo_institucional', '$horario_asignado')";

        if ($conn->query($sql_docente) === TRUE) {
            echo "<script>alert('Docente registrado exitosamente.');</script>";

        } else {
            echo "<script>alert('Error al registrar docente: " . $conn->error . "');</script>";

        }
    } else {
        echo "<script>alert('Superusuario registrado exitosamente.');</script>";

    }
} else {
    echo "<script>alert('Error al registrar usuario: " . $conn->error . "');</script>";

}

$conn->close();
?>
