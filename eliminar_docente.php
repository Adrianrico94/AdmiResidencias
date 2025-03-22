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

    $sql = "DELETE FROM Docentes WHERE id_docente=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_docente);

    if ($stmt->execute()) {
        echo "<script>alert('Registro eliminado exitosamente.'); window.location.href = 'AppSuperUsuarios.php';</script>";
    } else {
        echo "<script>alert('Error al eliminar el registro: {$stmt->error}'); window.history.back();</script>";
    }
}

$conn->close();
?>
