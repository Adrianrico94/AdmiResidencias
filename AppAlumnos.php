<?php
session_start();

// Tiempo de inactividad máximo (en segundos)
$inactive_time = 600; // 1 minuto

// Verificar si la sesión ha expirado
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive_time) {
  session_unset(); // Elimina todas las variables de sesión
  session_destroy(); // Destruye la sesión
  header("Location: Index.html"); // Redirige a la página de inicio de sesión
  exit();
}

// Actualizar la hora de la última actividad
$_SESSION['last_activity'] = time();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_type'])) {
  header("Location: Index.html"); // Redirige si no está logueado
  exit();
}

// Iniciar la sesión para acceder a los datos guardados en la sesión
if (isset($_SESSION['user_email'])) {
  $user_email = $_SESSION['user_email'];

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

  // Consulta para obtener los datos del alumno
  $sql_alumno = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.correo_electronico, 
                          a.telefono_alumno, a.matricula, a.proyecto_asignado, a.id_empresa,.a.notificacion
                   FROM Usuarios u
                   JOIN Alumnos a ON u.id_usuario = a.id_alumno
                   WHERE u.correo_electronico = ?";
  $stmt_alumno = $conn->prepare($sql_alumno);
  $stmt_alumno->bind_param("s", $user_email);
  $stmt_alumno->execute();
  $result_alumno = $stmt_alumno->get_result();

  // Mostrar los datos del alumno
  if ($result_alumno->num_rows > 0) {
    $row_alumno = $result_alumno->fetch_assoc();
    $id_alumno = $row_alumno['id_usuario'];  // Almacenar el id_alumno
    $nombre_alumno = $row_alumno['nombre'];
    $apellido_paterno = $row_alumno['apellido_paterno'];
    $apellido_materno = $row_alumno['apellido_materno'];
    $correo_alumno = $row_alumno['correo_electronico'];
    $telefono_alumno = $row_alumno['telefono_alumno'];
    $matricula = $row_alumno['matricula']; // Obtener la matrícula
    $proyecto_asignado = $row_alumno['proyecto_asignado']; // Obtener el proyecto asignado
    $id_empresa = $row_alumno['id_empresa'];  // Obtener el id de la empresa asociada
    $notificacion = $row_alumno['notificacion'];

    // Consulta para obtener los datos de la empresa asociada al alumno
    $sql_empresa = "SELECT e.nombre_empresa, e.correo_empresa, e.contacto_empresa, e.tutor_asignado, 
                               e.horario_asistencia, e.dias_asistencia
                        FROM empresa e
                        WHERE e.id_empresa = ?";
    $stmt_empresa = $conn->prepare($sql_empresa);
    $stmt_empresa->bind_param("i", $id_empresa);
    $stmt_empresa->execute();
    $result_empresa = $stmt_empresa->get_result();

    // Validar y mostrar los datos de la empresa
    if ($result_empresa->num_rows > 0) {
      $row_empresa = $result_empresa->fetch_assoc();
      $nombre_empresa = $row_empresa['nombre_empresa'] ?: "En proceso de asignación";
      $correo_empresa = $row_empresa['correo_empresa'] ?: "En proceso de asignación";
      $contacto_empresa = $row_empresa['contacto_empresa'] ?: "En proceso de asignación";
      $tutor_asignado = $row_empresa['tutor_asignado'] ?: "En proceso de asignación";
      $horario_asistencia = $row_empresa['horario_asistencia'] ?: "En proceso de asignación";
      $dias_asistencia = $row_empresa['dias_asistencia'] ?: "En proceso de asignación";

      // echo "<h3>Empresa Asignada</h3>";
      // echo "<p>Nombre de la Empresa: $nombre_empresa</p>";
      // echo "<p>Correo de la Empresa: $correo_empresa</p>";
      // echo "<p>Contacto de la Empresa: $contacto_empresa</p>";
      // echo "<p>Tutor Asignado: $tutor_asignado</p>";
      // echo "<p>Horario de Asistencia: $horario_asistencia</p>";
      // echo "<p>Días de Asistencia: $dias_asistencia</p>";
    } else {
      $mensaje_proceso = "<p>Datos de la empresa: En proceso de asignación</p>";
    }

    // Cerrar la consulta de la empresa
    $stmt_empresa->close();
  } else {
    echo "No se encontró el alumno con el correo electrónico proporcionado.";
  }
  // Consulta para obtener el docente asignado al alumno
  $sql_docente = "SELECT d.id_docente, CONCAT(u_docente.nombre, ' ', u_docente.apellido_paterno, ' ', u_docente.apellido_materno) AS nombre_completo_docente,
  d.telefono_docente, d.correo_institucional, d.clave_profesor, d.observaciones
FROM Docentes d
JOIN Asignaciones asg ON d.id_docente = asg.id_docente
JOIN Alumnos a ON asg.id_alumno = a.id_alumno
JOIN Usuarios u_alumno ON a.id_alumno = u_alumno.id_usuario
JOIN Usuarios u_docente ON d.correo_institucional = u_docente.correo_electronico
WHERE u_alumno.correo_electronico = ?";
  $stmt_docente = $conn->prepare($sql_docente);
  $stmt_docente->bind_param("s", $user_email);
  $stmt_docente->execute();
  $result_docente = $stmt_docente->get_result();

  // Verificar si se encontró el docente
  if ($result_docente->num_rows > 0) {
    $row_docente = $result_docente->fetch_assoc();
    $nombre_completo_docente = $row_docente['nombre_completo_docente'];
    $telefono_docente = $row_docente['telefono_docente'];
    $correo_institucional = $row_docente['correo_institucional'];
    $clave_profesor = $row_docente['clave_profesor'];
    $observaciones = $row_docente['observaciones'];

    // Mostrar la información del docente asignado

    // echo "<p><strong>Nombre:</strong> $nombre_completo_docente</p>";
    // echo "<p><strong>Teléfono:</strong> $telefono_docente</p>";
    // echo "<p><strong>Correo Institucional:</strong> $correo_institucional</p>";
    // echo "<p><strong>Clave Profesor:</strong> $clave_profesor</p>";
    // echo "<p><strong>Observaciones:</strong> $observaciones</p>";
  } else {
    // echo "<p>No se encontró un docente asignado a este alumno.</p>";
  }

  // Cerrar conexiones
  $stmt_docente->close();

  // Cerrar la conexión a la base de datos
  $stmt_alumno->close();
  $conn->close();
}
?>




<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Alumnos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <script>
    // Función para cerrar sesión si no hay actividad del ratón
    let timeout;

    function resetTimer() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        window.location.href = "logout.php"; // Redirige al cerrar sesión
      }, 1990000); // 1 minuto = 60000 ms
    }

    // Eventos para detectar movimiento o clics
    window.onload = resetTimer;
    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer;
    window.ontouchstart = resetTimer;
    window.onscroll = resetTimer;
  </script>

  <style>
    /* Barra de navegación fija */
    .navbar-custom {
      background-color: #f8f9fa;
      position: sticky;
      /* Esto hace que se quede fija en la parte superior */
      top: 0;
      z-index: 1000;
    }

    .navbar-custom .nav-link,
    .navbar-custom .navbar-brand {
      color: #000;
    }

    .search-bar {
      max-width: 200px;
    }

    .btn-search {
      background-color: #28a745;
      color: white;
    }

    .btn-register {
      background-color: #007bff;
      color: white;
    }

    .profile-name {
      color: #000;
      font-weight: bold;
    }

    /* Estilos para el menú lateral estilo Teams */
    .sidebar {
      position: fixed;
      /* Esto hace que la barra lateral se quede fija */
      height: 100vh;
      width: 80px;
      background-color: #1b1e21;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 20px;
      z-index: 999;
      /* Asegura que la barra lateral esté encima del contenido */
    }

    .sidebar a {
      color: #b0b3b8;
      font-size: 14px;
      text-decoration: none;
      margin: 20px 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: color 0.3s ease, background-color 0.3s ease;
      width: 100%;
      padding: 10px 0;
    }

    .sidebar a i {
      font-size: 24px;
      margin-bottom: 5px;
    }

    .sidebar a:hover,
    .sidebar a.active {
      color: #ffffff;
      background-color: #3a3f44;
      border-radius: 10px;
    }

    /* Espacio para el contenido principal */
    .content {
      flex-grow: 1;
      padding: 20px;
      background-color: #f8f9fa;
      margin-left: 80px;
      /* Para que el contenido no se solape con la barra lateral */
      margin-top: 70px;
      /* Ajuste para evitar que el contenido quede debajo de la barra de navegación fija */
    }

    /* Estilos personalizados para el footer */
    footer {
      background-color: #1b1e21;
      color: white;
    }

    footer a {
      color: white;
      text-decoration: none;
    }

    footer a:hover {
      color: #adb5bd;
    }

    .footer-icons a {
      color: white;
    }

    .footer-icons a:hover {
      color: #adb5bd;
    }
  </style>
</head>

<body>

  <!-- Barra de navegación fija -->
  <nav class="border border-2 rounded-top-2 navbar navbar-expand-lg navbar-light navbar-custom">
    <div class="container-fluid">
      <a class="navbar-brand" href="Inicio.html">
        <img src="img/logo/rino.png" alt="Logo" width="70" height="40" />
      </a>
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link fw-bolder" style="color: #56212f;">ADMINISTRADOR DE RESIDENCIAS</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-bolder" style="color: #BC955B;">Alumno</a>
        </li>
      </ul>

      <form class="d-flex">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input class="form-control search-bar me-2" type="search" placeholder="Buscar" aria-label="Buscar" />
        </div>
        <button class="btn btn-search" type="submit">Buscar</button>
      </form>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item">
          <a class="nav-link" style="color: black; font-size: 18px" href="#"><i class="bi bi-bell-fill"></i></a>
        </li>

        <div class="dropdown">
          <a class="btn btn-outline-dark dropdown-toggle btn m-2 d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle" style="font-size: 20px; margin-right: 8px;"></i>
            <span style="font-size: 18px; margin-bottom: 0;"><?php echo "$nombre_alumno "; ?></span>
          </a>



          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#"><?php echo "Alumno"; ?></a></li>


            <li><a class="dropdown-item" href="#"><?php echo "<p>Nombre:  $nombre_alumno $apellido_paterno $apellido_materno</p>"; ?></a></li>




            <li><a class="dropdown-item" href="#"><?php echo "<p>Correo electrónico:  $correo_alumno</p>"; ?></a></li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li><a class="dropdown-item" href="#">

            <li><a class="dropdown-item" href="#"><?php echo "<p>Teléfono: $telefono_alumno</p>"; ?></a></li>
            </a></li>

          </ul>
        </div>



      </ul>
    </div>
  </nav>

  <!-- Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutModalLabel">
            Confirmar Cierre de Sesión
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que deseas cerrar sesión?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" onclick="cerrarSesion()">
            Cerrar sesión
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Contenedor principal con el menú lateral y el contenido -->
  <div class="d-flex">
    <!-- Sidebar estilo Teams -->
    <div class="sidebar d-flex flex-column align-items-center pt-0">
      <a href="#" class="text-center active">
        <i class="bi bi-house-door-fill"></i>
        <span>Inicio</span>
      </a>
      <a href="#alumnos" class="text-center">
        <i class="bi bi-person-fill"></i>
        <span>Alumno</span>
      </a>
      <a href="#residencias" class="text-center">
        <i class="bi bi-mortarboard-fill"></i>
        <span>Mi Residencias</span>
      </a>
      <a href="#" class="text-center" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-right"></i>
        <span>Salir</span>
      </a>
    </div>

    <!-- Contenido principal -->


    <div class="content p-4 mt-0 bg-white rounded shadow-sm">
      <!-- Encabezado principal con botón -->
      <div class="d-flex mb-4" style="background-color: #8a2036; padding: 20px; border-radius: 8px;">
        <h1 class="text-uppercase text-white m-0" style="padding-left: 70px;">RESIDENCIAS PROFESIONALES</h1>
        <div class="text-center" style="padding-left: 350px;">
          <p class="text-white fw-semibold mb-1">Estado de Residencias</p>
          <button id="estado"
            class="btn 
            <?php
            echo is_null($notificacion)
              ? 'btn-warning text-white'
              : ($notificacion ? 'btn-success text-white' : 'btn-danger text-white');
            ?> btn-sm px-4">
            <?php
            echo is_null($notificacion)
              ? 'En revisión'
              : ($notificacion ? 'Aprobada' : 'Rechazada');
            ?>
          </button>


        </div>
      </div>




      <!-- Datos principales -->
      <div class="col-md-12 pb-3" id="alumnos">
        <div class="card border-light shadow-sm h-100">
          <div class="card-body text-center">
            <h2 style="color: #BC955B;">Carrera</h2>
            <h4 class="text-muted">Ing. en Sistemas Computacionales</h4>
          </div>
        </div>
      </div>


      <!-- Información adicional -->
      <div class="row g-3 mt-4">
        <div class="card-header  text-white text-center" style="background-color: #8a2036;">
          <h2>Datos del Alumno</h2>
        </div>
        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Matrícula:</h6>
              <p class="text-muted"><?php echo "<p>$matricula</p>"; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark"><strong>Nombre:</strong></h6>
              <p class="mb-2">

                <?php echo "$nombre_alumno $apellido_paterno $apellido_materno"; ?>
              </p>

            </div>
          </div>
        </div>




        <div class="col-md-2 p-0">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark"><strong>Correo Electrónico:</strong></h6>
              <p class="mb-2"> <?php echo "<p> $correo_alumno</p>"; ?></p>

            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark"> <strong>Teléfono:</strong></h6>
              <p class="mb-2"> <?php echo "<p> $telefono_alumno</p>"; ?></p>
            </div>
          </div>
        </div>
        <!-- // echo "<p>Horario de Asistencia: $horario_asistencia</p>";
      // echo "<p>Días de Asistencia: $dias_asistencia</p>"; -->
        <div class="col-md-2 ">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Horario Escuela</h6>
              <?php

              echo '<p class="text-muted pe-2 ps-2">' . (!empty($horario_asistencia) ? $horario_asistencia : ' En proceso de asignación') . '</p>';
              ?>

            </div>
          </div>
        </div>
        <div class="col-md-2 ">
          <div class="card border-light shadow-sm h-100 " style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Horario Empresa</h6>
              <?php

              echo '<p class="text-muted pe-2 ps-2">' . (!empty($horario_asistencia) ? $horario_asistencia : ' En proceso de asignación') . '</p>';
              ?>

            </div>
          </div>
        </div>


        <div class="col-md-4 " style="margin-left: 200px;">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
            <h6 class="text-dark">Docente asignado</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($nombre_completo_docente) ?  $nombre_completo_docente : ' En proceso de asignación') . '</p>';

              ?>


              <h6 class="text-dark">Contacto</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($telefono_docente) ?  $telefono_docente : ' S/N') . '</p>';

              ?>
            </div>
          </div>
        </div>


        <div class="col-md-4">
          <div class="card border-light shadow-sm h-100" style="background-color: #F1E2DC;">
            <div class="card-body text-center">
              <h6 class="text-dark">Tutor asignado empresa</h6>
              <?php

              echo '<p class="text-muted">' . (!empty($tutor_asignado) ?  $tutor_asignado : ' En proceso de asignación') . '</p>';
              ?>


              <h6 class="text-dark">Contacto</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($contacto_empresa) ?   $contacto_empresa : ' S/N') . '</p>';

              ?>
            </div>
          </div>
        </div>


      </div>

      <!-- Información adicional -->
      <div class="row g-3 mt-5" id="residencias">
        <div class="card-header  text-white text-center" style="background-color: #8a2036;">
          <h2>Mi Residencias</h2>
        </div>
        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Matrícula</h6>
              <p class="text-muted"><?php echo "<p> $matricula</p>"; ?></p>

            </div>
          </div>
        </div>

        <div class="col-md-2">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Empresa</h6>
              <?php

              echo '<p class="text-muted">' . (!empty($nombre_empresa) ? $nombre_empresa : ' En proceso de asignación') . '</p>';
              ?>


            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Correo empresa</h6>
              <p class="text-muted">
                <?php

                echo '<p class="text-muted">' . (!empty($correo_empresa) ? $correo_empresa : ' En proceso de asignación') . '</p>';
                ?>

              </p>
            </div>
          </div>
        </div>

        <div class="col-md-5">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Proyecto asignado </h6>
              <p class="text-muted"><?php echo "<p> $proyecto_asignado</p>"; ?></p>


            </div>
          </div>
        </div>
        <div class="col-md-3">
          <!-- Botón para abrir el modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empresaModal">
            Ver Empresa Asignada
          </button>

          <!-- Modal -->
          <div class="modal fade" id="empresaModal" tabindex="-1" aria-labelledby="empresaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <!-- Encabezado del Modal -->
                <div class="modal-header">
                  <h5 class="modal-title" id="empresaModalLabel">Información de la Empresa Asignada</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="modal-body">
                  <!-- Contenido con Bootstrap Card para mejorar el diseño -->
                  <div class="card">
                    <div class="card-body">
                      <h3 class="card-title">Empresa Asignada</h3>
                      <ul class="list-group list-group-flush">
                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($nombre_empresa) ? $nombre_empresa : 'En proceso de revisión') . '</li>';
                        ?>

                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($correo_empresa) ? $correo_empresa : 'En proceso de revisión') . '</li>';
                        ?>
                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($contacto_empresa) ? $contacto_empresa : 'En proceso de revisión') . '</li>';
                        ?>
                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($tutor_asignado) ? $tutor_asignado : 'En proceso de revisión') . '</li>';
                        ?>
                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($horario_asistencia) ? $horario_asistencia : 'En proceso de revisión') . '</li>';
                        ?>

                        <?php
                        echo '<li class="list-group-item"><strong>Nombre de la Empresa:</strong> ' . (!empty($dias_asistencia) ? $dias_asistencia : 'En proceso de revisión') . '</li>';
                        ?>



                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Pie del Modal -->
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-light shadow-sm h-100" style="background-color: #efe1ca;">
            <div class="card-body text-center">
              <h6 class="text-dark">Docente asignado</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($nombre_completo_docente) ?  $nombre_completo_docente : ' En proceso de asignación') . '</p>';

              ?>


              <h6 class="text-dark">Contacto</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($telefono_docente) ?  $telefono_docente : 'S/N') . '</p>';

              ?>
            </div>
          </div>
        </div>


        <div class="col-md-4">
          <div class="card border-light shadow-sm h-100" style="background-color: #F1E2DC;">
            <div class="card-body text-center">
              <h6 class="text-dark">Tutor asignado empresa</h6>
              <?php

              echo '<p class="text-muted">' . (!empty($tutor_asignado) ?  $tutor_asignado : ' En proceso de asignación') . '</p>';
              ?>


              <h6 class="text-dark">Contacto</h6>
              <?php
              echo '<p class="text-muted">' . (!empty($contacto_empresa) ?   $contacto_empresa : ' S/N') . '</p>';

              ?>
            </div>
          </div>
        </div>




      </div>

    </div>




  </div>
  </div>

  <!-- Footer -->
  <footer class="text-center py-0 m-0">
    <div class="container">
      <div class="row">
        <div class="col-md-4 pt-4">
          <p style="font-size: 12px">
            © 2024 Tecnológico de Estudios Superiores de Cuautitlán Izcalli.
            Todos los derechos reservados.
          </p>
        </div>
        <div class="col-md-4" style="font-size: 14px">
          <h6>Contacto</h6>
          <p>
            Av. Nopaltepec s/n Col. La Perla C.P. 54740, Cuautitlán Izcalli,
            Estado de México
          </p>
          <p>Tel: (55) 58 64 31 70 - 71</p>
        </div>
        <div class="col-md-4 footer-icons pt-4 fs-5">
          <h6>Síguenos en:</h6>
          <a href="https://web.whatsapp.com/" class="me-2"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.facebook.com/Comunidad.Tesci" class="me-2"><i class="fab fa-facebook"></i></a>
          <a href="https://x.com/ComunidadTESCI?ref_src=twsrc%5Egoogle%7Ctwcamp%5Eserp%7Ctwgr%5Eauthor" class="me-2"><i
              class="fab fa-twitter"></i></a>
          <a href="https://www.instagram.com/comunidad.tesci/p/Cn4jF-CObxF/" class="me-2"><i
              class="fab fa-instagram"></i></a>
          <a href="https://tesci.edomex.gob.mx/"><i class="fas fa-globe"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts de Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>