<?php
// Configuración de la conexión a la base de datos
$host = "localhost";
$dbname = "residencias_db";
$username = "root"; // Cambia esto si tu usuario de MySQL es diferente
$password = ""; // Cambia esto si tienes una contraseña para MySQL

// Conectar a la base de datos
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener los datos del formulario
$correo_institucional = $_POST['institutionalEmail'];
$contrasena = password_hash($_POST['createPassword'], PASSWORD_BCRYPT); // Cifra la contraseña
$nombre_empresa = $_POST['nombreEmpresa'];
$proyecto_asignado = $_POST['proyectoAsignado'];
$matricula = $_POST['matricula'];
$nombre = $_POST['nombre'];
$apellido_paterno = $_POST['apellidoPaterno'];
$apellido_materno = $_POST['apellidoMaterno'];
$carrera = $_POST['carrera'];
$telefono = $_POST['telefono'];
$observaciones = $_POST['observaciones'];

// Insertar en la tabla Usuarios
try {
    $sqlUsuarios = "INSERT INTO Usuarios (nombre, apellido_paterno, apellido_materno, correo_electronico, contrasena, tipo_usuario) 
                    VALUES (:nombre, :apellido_paterno, :apellido_materno, :correo_institucional, :contrasena, 'Alumno')";
    $stmtUsuarios = $conn->prepare($sqlUsuarios);
    $stmtUsuarios->execute([
        ':nombre' => $nombre,
        ':apellido_paterno' => $apellido_paterno,
        ':apellido_materno' => $apellido_materno,
        ':correo_institucional' => $correo_institucional,
        ':contrasena' => $contrasena
    ]);
    $id_usuario = $conn->lastInsertId(); // Obtener el ID del usuario insertado
} catch (PDOException $e) {
    die("Error al insertar en Usuarios: " . $e->getMessage());
}

// Insertar en la tabla Alumnos
try {
    $sqlAlumnos = "INSERT INTO Alumnos (id_alumno, matricula, empresa, proyecto_asignado, carrera, telefono_alumno, observaciones) 
                   VALUES (:id_alumno, :matricula, :empresa, :proyecto_asignado, :carrera, :telefono, :observaciones)";
    $stmtAlumnos = $conn->prepare($sqlAlumnos);
    $stmtAlumnos->execute([
        ':id_alumno' => $id_usuario,
        ':matricula' => $matricula,
        ':empresa' => $nombre_empresa,
        ':proyecto_asignado' => $proyecto_asignado,
        ':carrera' => $carrera,
        ':telefono' => $telefono,
        ':observaciones' => $observaciones
    ]);
} catch (PDOException $e) {
    die("Error al insertar en Alumnos: " . $e->getMessage());
}

// Confirmar inserción exitosa y cerrar conexión
echo "<script>
        alert('Su registro fue realizado correctamente');
        window.location.href = 'Index.html';
    </script>";
$conn = null;
?>
