<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "residencias_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hay errores de conexión
if ($conn->connect_error) {
    echo "<script>alert('No se pudo conectar a la base de datos'); window.location.href='Index.html';</script>";
    exit();
}

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir los valores del formulario
    $user_email = $_POST['username'];
    $user_password = $_POST['password'];
    $user_type = $_POST['tipo_usuario'];

    // Verificar que el tipo de usuario no esté vacío
    if (empty($user_type)) {
        echo "<script>alert('Por favor selecciona el tipo de usuario'); window.location.href='Index.html';</script>";
        exit();
    }

    // Consulta SQL para verificar si el usuario y el tipo existen en la base de datos
    $sql = "SELECT * FROM usuarios WHERE correo_electronico = ? AND LOWER(tipo_usuario) = LOWER(?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user_email, $user_type);

    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si se encontró el usuario con el tipo especificado
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Usar password_verify() para comparar la contraseña ingresada con el hash almacenado
        if (password_verify($user_password, $row['contrasena'])) {
            iniciarSesion($row, $user_email, $user_type);
        } elseif ($user_password === $row['contrasena']) {
            // Compatibilidad temporal para contraseñas en texto plano
            iniciarSesion($row, $user_email, $user_type);
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='Index.html';</script>";
        }
    } else {
        echo "<script>alert('Correo o tipo de usuario incorrecto'); window.location.href='Index.html';</script>";
    }

    $stmt->close();
}

// Función para iniciar sesión y redirigir según el tipo de usuario
function iniciarSesion($row, $user_email, $user_type) {
    session_start();
    $_SESSION['user_email'] = $user_email;
    $_SESSION['user_type'] = $user_type;
    $_SESSION['last_activity'] = time(); // Guardar la hora de la última actividad
    $_SESSION['user_name'] = $row['nombre']; // Almacenar el nombre del usuario


    // Redirigir según el tipo de usuario
    if ($user_type == 'alumno') {
        header("Location: AppAlumnos.php");
    } elseif ($user_type == 'docente') {
        header("Location: AppProfesores.php");
    } elseif ($user_type == 'superusuario') {
        header("Location: AppSuperUsuarios.php");
    }
    exit();
}

// Cerrar la conexión
$conn->close();
?>
