<?php
session_start();

// Tiempo de inactividad máximo (en segundos)
$inactive_time = 60; // 1 minuto

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

  // Consulta para obtener el id_docente, nombre completo, telefono_docente, correo, clave_profesor y observaciones
  $sql_docente = "SELECT d.id_docente, d.telefono_docente, u.nombre, u.apellido_paterno, u.apellido_materno, 
                           u.correo_electronico, d.clave_profesor, d.observaciones 
                    FROM Docentes d 
                    JOIN Usuarios u ON d.correo_institucional = u.correo_electronico 
                    WHERE d.correo_institucional = ?";
  $stmt_docente = $conn->prepare($sql_docente);
  $stmt_docente->bind_param("s", $user_email);
  $stmt_docente->execute();
  $result_docente = $stmt_docente->get_result();

  // Verificar si se encontró el docente
  if ($result_docente->num_rows > 0) {
    $row_docente = $result_docente->fetch_assoc();
    $id_docente = $row_docente['id_docente']; // Obtener el id_docente
    $telefono_docente = $row_docente['telefono_docente']; // Obtener el teléfono del docente
    $nombre = $row_docente['nombre']; // Obtener el nombre
    $apellido_paterno = $row_docente['apellido_paterno']; // Obtener el primer apellido
    $apellido_materno = $row_docente['apellido_materno']; // Obtener el segundo apellido
    $nombre_completo = $nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno; // Construir el nombre completo
    $correo_docente = $row_docente['correo_electronico']; // Obtener el correo
    $clave_profesor = $row_docente['clave_profesor']; // Obtener la clave del profesor
    $observaciones = $row_docente['observaciones']; // Obtener las observaciones
  } else {
    echo "No se encontró un docente con el correo: $user_email";
  }


  // Mostrar la información del docente
  if (isset($nombre_completo) && isset($telefono_docente) && isset($correo_docente) && isset($clave_profesor) && isset($observaciones)) {
    // echo "<p>Nombre completo del docente: $nombre_completo</p>";
    // echo "<p>Nombre: $nombre</p>"; // Mostrar solo el nombre
    // echo "<p>Primer apellido: $apellido_paterno</p>"; // Mostrar solo el primer apellido
    // echo "<p>Teléfono del docente: $telefono_docente</p>";
    // echo "<p>Correo del docente: $correo_docente</p>";
    // echo "<p>Clave del profesor: $clave_profesor</p>";
    // echo "<p>Observaciones: $observaciones</p>";
  }




  // Mostrar la información del docente
  if (isset($nombre_completo) && isset($telefono_docente) && isset($correo_docente) && isset($clave_profesor) && isset($observaciones)) {


    // Consulta para obtener los alumnos asignados al docente
    $sql_alumnos = "SELECT a.id_alumno, a.matricula, CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo, 
    a.carrera, a.horario_asignado, u.correo_electronico, a.observaciones
    FROM Alumnos a 
    JOIN Asignaciones asg ON a.id_alumno = asg.id_alumno 
    JOIN Usuarios u ON a.id_alumno = u.id_usuario
    WHERE asg.id_docente = ?";

    $stmt_alumnos = $conn->prepare($sql_alumnos);
    $stmt_alumnos->bind_param("i", $id_docente);
    $stmt_alumnos->execute();
    $result_alumnos = $stmt_alumnos->get_result();

    // Mostrar la tabla de alumnos
    if ($result_alumnos->num_rows > 0) {
    } else {
      echo "<p>No hay alumnos asignados a este docente.</p>";
    }
  }

  // Cerrar conexiones
  $stmt_docente->close();
  $conn->close();
} else {
  echo "No se ha iniciado sesión.";
}


?>



<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profesores</title>
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
      }, 200000); // 1 minuto = 60000 ms
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
          <a class="nav-link fw-bolder" href="#">ADMINISTRADOR DE RESIDENCIAS</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-bolder" href="#">PROFESORES</a>
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
          <a class="btn btn-outline-dark dropdown-toggle btn m-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle " style="font-size: 25px;"></i>
            <span style="font-size: 18px;"><?php echo "$nombre $apellido_paterno"; ?></span>
          </a>


          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#"><?php echo "Docente"; ?></a></li>

            <li><a class="dropdown-item" href="#"><?php echo "<p> $nombre_completo</p>"; ?></a></li>




            <li><a class="dropdown-item" href="#"><?php echo $_SESSION['user_email']; ?></a></li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li><a class="dropdown-item" href="#">

            <li><a class="dropdown-item" href="#"><?php echo "<p>Teléfono: $telefono_docente</p>"; ?></a></li>
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
      <a href="#" class="text-center">
        <i class="bi bi-person-fill"></i>
        <span>Mis datos</span>
      </a>
      <a href="#resitentes" class="text-center">
        <i class="bi bi-mortarboard-fill"></i>
        <span>Residentes</span>
      </a>
      <a href="#" class="text-center" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-right"></i>
        <span>Salir</span>
      </a>
    </div>

    <!-- Contenido principal -->
    <div class="content pt-0 mt-0 ">
      <div class="col-md-12 p-0" w>
        <div class="card border-light shadow-sm " style="background-color: #8a2036; color: white;">
          <div class="card-body text-center">

            <p class="mb-2"> <?php echo "<h1>Proyección General de residencias</h1>"; ?></p>
            <h6 class="text-dark text-white"><strong>
                <h2><?php echo "<p> Profesores </p>"; ?></h2>
              </strong></h6>

          </div>
        </div>
      </div>


      <div class="col-md-4 pt-3" style="height: 150px;">
        <div class="card  shadow-sm" style="background-color: #efe1ca;">
          <div class="card-body text-center p-0">
            <h6 class="text-dark">
              <strong>
                <h2 style="color: #bf955a;"><?php echo "<p> $nombre_completo</p>"; ?></h2>
              </strong>
            </h6>
            <p>Ahora puedes visualizar a los residentes asignados.</p>
          </div>
        </div>
      </div>





      <div class="col-12">
        <div class="card rounded-3" style="background-color: #efe1ca;">
          <div class="text-center my-2 mb-0 flex-grow-1 fs-6 fs-md-4 pt-2">
            <h3 id="resitentes">ALUMNOS ASIGNADOS</h3>

            <?php
            // Código ya existente de sesión y conexión

            echo '<div class="tabla-scroll" style="max-height: 200px; overflow-y: auto;">'; // Contenedor con scroll
            echo '<table class="table table-striped table-hover table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nombre</th>
                            <th>Carrera</th>
                            <th>Horario</th>
                            <th>Correo</th>
                            <th>Más detalles</th>
                        </tr>
                    </thead>
                    <tbody>';

            while ($row_alumno = $result_alumnos->fetch_assoc()) {
              echo "<tr>
                        <td>{$row_alumno['matricula']}</td>
                        <td>{$row_alumno['nombre_completo']}</td>
                        <td>{$row_alumno['carrera']}</td>
                        <td>{$row_alumno['horario_asignado']}</td>
                        <td>{$row_alumno['correo_electronico']}</td>
                        <td>
                            <!-- Botón para abrir el Modal -->
                            <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#modal_{$row_alumno['id_alumno']}'>
                                Mostrar
                            </button>
                            <!-- Modal -->
                            <div class='modal fade' id='modal_{$row_alumno['id_alumno']}' tabindex='-1' aria-labelledby='modalLabel_{$row_alumno['id_alumno']}' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <!-- Cabecera del Modal -->
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='modalLabel_{$row_alumno['id_alumno']}'>Detalles del Alumno</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <!-- Cuerpo del Modal -->
                                        <div class='modal-body'>
                                            <p><strong>Matrícula:</strong> {$row_alumno['matricula']}</p>
                                            <p><strong>Nombre Completo:</strong> {$row_alumno['nombre_completo']}</p>
                                            <p><strong>Carrera:</strong> {$row_alumno['carrera']}</p>
                                            <p><strong>Horario Asignado:</strong> {$row_alumno['horario_asignado']}</p>
                                            <p><strong>Correo:</strong> {$row_alumno['correo_electronico']}</p>

                                            <!-- Mostrar las observaciones en el modal -->
                                           <div class='mb-3'>
                                                <label for='observaciones' class='form-label'><strong>Observaciones:</strong></label>
                                                    <textarea 
                                                        id='observaciones' 
                                                        class='form-control' 
                                                        rows='4' 
                                                        readonly 
                                                        style='resize: none; overflow-y: scroll;'>
{$row_alumno['observaciones']}
                                                    </textarea>
                                            </div>
                                        </div>
                                        <!-- Pie del Modal -->
                                        <div class='modal-footer d-flex justify-content-center'>
                                          

                                            <!-- Botón para Aceptar el Proyecto y actualizar la notificación -->
                                            <form action='' method='POST' class='d-inline'>
                                                <input type='hidden' name='id_alumno' value='{$row_alumno['id_alumno']}'>
                                                <button type='submit' name='aceptar_proyecto' class='btn btn-success'>Aceptar Proyecto</button>
                                            </form>

                                            <!-- Botón para Rechazar el Proyecto y actualizar la notificación a 0 -->
                                            <form action='' method='POST' class='d-inline'>
                                                <input type='hidden' name='id_alumno' value='{$row_alumno['id_alumno']}'>
                                                <button type='submit' name='rechazar_proyecto' class='btn btn-danger'>Rechazar Proyecto</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>";
            }

            echo '</tbody></table>';
            echo '</div>'; // Cierre del contenedor

            // Código PHP para procesar la actualización al hacer clic en "Aceptar Proyecto"
            if (isset($_POST['aceptar_proyecto'])) {
              $id_alumno = $_POST['id_alumno'];

              // Configuración de la conexión a la base de datos
              $servername = "localhost";
              $username = "root";
              $password = "";
              $dbname = "residencias_db";

              $conn = new mysqli($servername, $username, $password, $dbname);

              // Verificar si hay errores de conexión
              if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
              }

              // Verificar si el ID del alumno existe
              $check_sql = "SELECT * FROM alumnos WHERE id_alumno = ?";
              $check_stmt = $conn->prepare($check_sql);
              $check_stmt->bind_param("i", $id_alumno);
              $check_stmt->execute();
              $result = $check_stmt->get_result();

              if ($result->num_rows == 0) {
                echo "<script>alert('El ID del alumno no existe.');</script>";
                exit();
              }

              // Si el alumno existe, proceder con la actualización
              $sql = "UPDATE alumnos SET notificacion = 1 WHERE id_alumno = ?";
              $stmt = $conn->prepare($sql);

              if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . $conn->error);
              }

              $stmt->bind_param("i", $id_alumno);
              $stmt->execute();

              if ($stmt->affected_rows > 0) {
                // Actualización exitosa
              } else {
                echo "<script>alert('No se pudo actualizar la notificación.');</script>";
              }

              $stmt->close();
              $conn->close();
            }

            // Código PHP para procesar la actualización al hacer clic en "Rechazar Proyecto"
            if (isset($_POST['rechazar_proyecto'])) {
              $id_alumno = $_POST['id_alumno'];

              // Configuración de la conexión a la base de datos
              $servername = "localhost";
              $username = "root";
              $password = "";
              $dbname = "residencias_db";

              $conn = new mysqli($servername, $username, $password, $dbname);

              // Verificar si hay errores de conexión
              if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
              }

              // Verificar si el ID del alumno existe
              $check_sql = "SELECT * FROM alumnos WHERE id_alumno = ?";
              $check_stmt = $conn->prepare($check_sql);
              $check_stmt->bind_param("i", $id_alumno);
              $check_stmt->execute();
              $result = $check_stmt->get_result();

              if ($result->num_rows == 0) {
                echo "<script>alert('El ID del alumno no existe.');</script>";
                exit();
              }

              // Si el alumno existe, proceder con la actualización
              $sql = "UPDATE alumnos SET notificacion = 0 WHERE id_alumno = ?";
              $stmt = $conn->prepare($sql);

              if ($stmt === false) {
                die('Error en la preparación de la consulta: ' . $conn->error);
              }

              $stmt->bind_param("i", $id_alumno);
              $stmt->execute();

              if ($stmt->affected_rows > 0) {
                // Actualización exitosa
              } else {
                echo "<script>alert('No se pudo actualizar la notificación.');</script>";
              }

              $stmt->close();
              $conn->close();
            }
            ?>








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