<?php
require_once __DIR__ .  "/../db/ConexionPostgresql.php";

$opcion = $_POST["opt"];
switch ($opcion) {
    case "obtenerEstudiantes":
        obtenerEstudiantes();
        break;
    case "guardarEstudiantes":
        guardarEstudiantesNew();
        break;
    case "obtenerMovimientos":
        obtenerMatriculas();
        break;
    case "matricularEstudiante":
        matricularEstudiante();
        break;
    case "eliminarMatricula":
        eliminarMatricula();
        break;
    case "buscarEstudiante":
        obtenerEstudiantes();
        break;
    default:
        echo json_encode(array('error' => 'Opción no válida.'));
        break;
}
function eliminarMatricula() {
    // Verificar si se ha recibido la variable id por POST
    $matricula = $_POST['producto'];

    if (!isset($matricula['id'])) {
        return json_encode(array('success' => false, 'message' => 'ID de la matrícula no proporcionado.'));
    }

    // Obtener el ID de la matrícula a eliminar
    $idMatricula = (int)$matricula['id'];
    $conexion = new ConexionPostgresql(); // Asumiendo que tienes una clase para manejar la conexión
    $pdo = $conexion->obtenerConexion();

    // Inicializar la respuesta
    $respuesta = array('success' => false, 'message' => '');

    try {
        // Iniciar la transacción
        $pdo->beginTransaction();

        // Eliminar la matrícula
        $stmtEliminar = $pdo->prepare("DELETE FROM public.matriculas WHERE id = :id");
        $stmtEliminar->bindParam(':id', $idMatricula, PDO::PARAM_INT);
        
        if ($stmtEliminar->execute()) {
            // Confirmar la transacción si la eliminación fue exitosa
            $pdo->commit();
            $respuesta['success'] = true;
            $respuesta['message'] = "Matrícula eliminada exitosamente.";
        } else {
            // Si falla, revertir la transacción
            $pdo->rollBack();
            $respuesta['message'] = "Error al eliminar la matrícula.";
        }

        // Cerrar el statement
        $stmtEliminar->closeCursor();
    } catch (PDOException $e) {
        // Revertir en caso de error
        $pdo->rollBack();
        $respuesta['message'] = "Error en sentencia SQL: " . $e->getMessage();
    } finally {
        // Cerrar la conexión
        $pdo = null;
    }

    // Devolver la respuesta en formato JSON
    echo json_encode($respuesta);
}



function matricularEstudiante()
{
    // Crear instancia de conexión
    $conexion = new ConexionPostgresql();
    $pdo = $conexion->obtenerConexion();
    $response = array();
    // Verificar si se recibió correctamente la solicitud POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $matricula = $_POST['producto'];
        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            // Insertar nuevo registro
            $sql = "INSERT INTO public.matriculas (id_estudiante, curso, tipo, fecha_matricula) 
                   VALUES (:id_estudiante, :curso, :tipo, now())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_estudiante', $matricula['id'], PDO::PARAM_INT); // Asumir que el id del estudiante es un entero
            $stmt->bindParam(':curso', $matricula['curso'], PDO::PARAM_STR);
            $stmt->bindParam(':tipo', $matricula['tipo'], PDO::PARAM_STR);
            $stmt->execute();

            // Confirmar transacción
            $pdo->commit();
            $response = array('success' => true,'message'=> 'Estudiante Matriculado');
        } catch (PDOException $e) {
            // Rollback en caso de error
            $pdo->rollBack();
            // Preparar la respuesta JSON de error
            $errorInfo = $e->errorInfo;
            $sqlState = $errorInfo[0];  // Código SQLSTATE
            $mensaje = "";
            if($sqlState==23505){
                $mensaje = "El estudiante ya se encuentra registrado";
            }else{
                $mensaje = $e->getMessage();
            }
            $response = array('success' => false, 'message' => 'Error: ' . $mensaje);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}


// function matricularEstudiante_()
// {
//     // Verificar si se recibió correctamente la solicitud POST
//     if ($_SERVER["REQUEST_METHOD"] === "POST") {
//         // Obtener los datos del movimiento desde AJAX
//         $matricula = $_POST['producto']; // Convertir JSON a array asociativo 

//         // Validar que los campos requeridos no estén vacíos
//         $camposObligatorios = ['id', 'curso', 'tipo'];
//         foreach ($camposObligatorios as $campo) {
//             if (empty($matricula[$campo]) && $matricula[$campo] !== '0') {
//                 // Devolver la respuesta con error si algún campo está vacío
//                 $response = array('success' => false, 'message' => "El campo '$campo' no puede estar vacío.");
//                 header('Content-Type: application/json');
//                 echo json_encode($response);
//                 exit; // Detener la ejecución si hay un campo vacío
//             }
//         }

//         // Crear instancia de conexión
//         $conexion = new ConexionPostgresql();
//         $pdo = $conexion->obtenerConexion();

//         try {
//             $pdo->beginTransaction();

//             // Obtener el fecha_nacimiento actual del nombre
//             $stmtfecha_nacimiento = $pdo->prepare("SELECT fecha_nacimiento FROM public.nombre WHERE id = :id");
//             $stmtfecha_nacimiento->bindParam(':id', $nombre['id'], PDO::PARAM_INT);
//             $stmtfecha_nacimiento->execute();

//             if ($stmtfecha_nacimiento->rowCount() > 0) {
//                 $nombreData = $stmtfecha_nacimiento->fetch(PDO::FETCH_ASSOC);
//                 $fecha_nacimientoActual = $nombreData['fecha_nacimiento'];
//                 $nuevofecha_nacimiento = $fecha_nacimientoActual;

//                 // Calcular el nuevo fecha_nacimiento según el tipo de movimiento
//                 if ($nombre['tipo'] === 'I') {
//                     // Ingreso: sumar la cantidad al fecha_nacimiento
//                     $nuevofecha_nacimiento += (int)$nombre['cantidad'];
//                 } elseif ($nombre['tipo'] === 'E') {
//                     // Egreso: restar la cantidad del fecha_nacimiento
//                     if ($fecha_nacimientoActual < (int)$nombre['cantidad']) {
//                         // Validar que no se quede en negativo
//                         $response = array('success' => false, 'message' => "No se puede realizar el egreso. El fecha_nacimiento resultante sería negativo.");
//                         header('Content-Type: application/json');
//                         echo json_encode($response);
//                         exit; // Detener la ejecución si el fecha_nacimiento sería negativo
//                     }
//                     $nuevofecha_nacimiento -= (int)$nombre['cantidad'];
//                 } else {
//                     $response = array('success' => false, 'message' => "Tipo de movimiento no válido.");
//                     header('Content-Type: application/json');
//                     echo json_encode($response);
//                     exit; // Detener la ejecución si el tipo no es válido
//                 }

//                 // Insertar el nuevo movimiento en la tabla historial
//                 $sql = "INSERT INTO historial (fknombre, cantidad, tipo, fecha) 
//                         VALUES (:fknombre, :cantidad, :tipo, now())";

//                 // Preparar la consulta
//                 $stmt = $pdo->prepare($sql);

//                 // Bind de parámetros
//                 $stmt->bindValue(':fknombre', $nombre['id'], PDO::PARAM_INT);
//                 $stmt->bindValue(':cantidad', $nombre['cantidad'], PDO::PARAM_INT);
//                 $stmt->bindValue(':tipo', $nombre['tipo'], PDO::PARAM_STR); // 'I' para Ingreso, 'E' para Egreso

//                 // Ejecutar la consulta para guardar el movimiento
//                 $stmt->execute();

//                 // Actualizar el fecha_nacimiento en la tabla nombre
//                 $stmtActualizarfecha_nacimiento = $pdo->prepare("UPDATE public.nombre SET fecha_nacimiento = :nuevofecha_nacimiento WHERE id = :id");
//                 $stmtActualizarfecha_nacimiento->bindParam(':nuevofecha_nacimiento', $nuevofecha_nacimiento, PDO::PARAM_INT);
//                 $stmtActualizarfecha_nacimiento->bindParam(':id', $nombre['id'], PDO::PARAM_INT);
//                 $stmtActualizarfecha_nacimiento->execute();

//                 // Confirmar transacción
//                 $pdo->commit();

//                 // Preparar la respuesta JSON
//                 $response = array('success' => true, 'message' => 'Movimiento registrado exitosamente');
//             } else {
//                 $response = array('success' => false, 'message' => "No se encontró el nombre con ID: " . $nombre['id']);
//             }
//         } catch (PDOException $e) {
//             // Rollback en caso de error
//             $pdo->rollBack();

//             // Preparar la respuesta JSON de error
//             $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
//         }

//         // Devolver la respuesta como JSON
//         header('Content-Type: application/json');
//         echo json_encode($response);
//     } else {
//         // Si no es una solicitud POST válida
//         header("HTTP/1.1 405 Method Not Allowed");
//         exit;
//     }
// }


function matricularEstudiante_old()
{
    // Verificar si se recibió correctamente la solicitud POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Obtener los datos del movimiento desde AJAX
        $nombre = $_POST['nombre']; // Convertir JSON a array asociativo 

        // Validar que los campos requeridos no estén vacíos
        $camposObligatorios = ['id', 'cantidad', 'tipo'];
        foreach ($camposObligatorios as $campo) {
            if (empty($nombre[$campo]) && $nombre[$campo] !== '0') {
                // Devolver la respuesta con error si algún campo está vacío
                $response = array('success' => false, 'message' => "El campo '$campo' no puede estar vacío.");
                header('Content-Type: application/json');
                echo json_encode($response);
                exit; // Detener la ejecución si hay un campo vacío
            }
        }

        // Crear instancia de conexión
        $conexion = new ConexionPostgresql();
        $pdo = $conexion->obtenerConexion();

        try {
            $pdo->beginTransaction();

            // Insertar el nuevo movimiento en la tabla historial
            $sql = "INSERT INTO historial (fknombre, cantidad, tipo, fecha) 
                    VALUES (:fknombre, :cantidad, :tipo, now())";

            // Preparar la consulta
            $stmt = $pdo->prepare($sql);

            // Bind de parámetros
            $stmt->bindValue(':fknombre', $nombre['id'], PDO::PARAM_INT);
            $stmt->bindValue(':cantidad', $nombre['cantidad'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $nombre['tipo'], PDO::PARAM_STR); // 'I' para Ingreso, 'E' para Egreso

            // Ejecutar la consulta
            $stmt->execute();

            // Confirmar transacción
            $pdo->commit();

            // Preparar la respuesta JSON
            $response = array('success' => true, 'message' => 'Movimiento registrado exitosamente');
        } catch (PDOException $e) {
            // Rollback en caso de error
            $pdo->rollBack();

            // Preparar la respuesta JSON de error
            $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }

        // Devolver la respuesta como JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Si no es una solicitud POST válida
        header("HTTP/1.1 405 Method Not Allowed");
        exit;
    }
}


function obtenerMatriculas()
{
    $data = array();

    // Crear instancia de conexión
    $conexion = new ConexionPostgresql();
    $pdo = $conexion->obtenerConexion();

    // Validar y procesar los parámetros POST
    $limite = isset($_POST["limite"]) && is_numeric($_POST["limite"]) ? (int) $_POST["limite"] : 10;  // Definir un límite por defecto

    $query_buscado = "";

    // Si hay un texto de búsqueda
    if (isset($_POST["texto"]) && !empty($_POST["texto"])) {
        $textobuscado = htmlspecialchars($_POST["texto"]); // Evitar inyecciones XSS
        $query_buscado = " AND nombre LIKE :texto";  // Cambié "nombre" a "nombre" para ser consistente con la estructura de datos
    }

    // Construir la consulta SQL
    $query = "SELECT * FROM matriculados WHERE 1=1";  // Agregar "1=1" para facilitar la concatenación de condiciones
    $query_order = " ORDER BY id DESC";
    $query_limit = " LIMIT :limite";  // Usar un placeholder para el límite

    // Concatenar las condiciones de búsqueda
    $query .= $query_buscado . $query_order . $query_limit;

    // Preparar la consulta
    $stmtestudiantes = $pdo->prepare($query);

    // Bind del texto buscado si está presente
    if (!empty($query_buscado)) {
        $stmtestudiantes->bindValue(':texto', "%$textobuscado%", PDO::PARAM_STR);
    }

    // Bind del límite
    $stmtestudiantes->bindValue(':limite', $limite, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmtestudiantes->execute()) {
        $nombres = $stmtestudiantes->fetchAll(PDO::FETCH_ASSOC);
        $data['productos'] = $nombres;
    } else {
        echo json_encode(array('error' => 'Error al obtener los nombres.'));
        return;
    }

    // Cerrar la conexión
    $conexion->cerrarConexion();

    // Devolver los datos en formato JSON
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

function guardarEstudiantesNew()
{
    $accion = "Guardado";

    // Verificar si se recibió correctamente la solicitud POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Obtener los datos del nombre desde AJAX
        $estudiantes = $_POST['producto']; // Convertir JSON a array asociativo 

        // Validar que los campos requeridos no estén vacíos
        $camposObligatorios = ['estado', 'nombre', 'cedula', 'apellido', 'fecha_nacimiento'];
        foreach ($camposObligatorios as $campo) {
            if (empty($estudiantes[$campo]) && $estudiantes[$campo] !== '0') {
                // Devolver la respuesta con error si algún campo está vacío
                $response = array('success' => false, 'message' => "El campo '$campo' no puede estar vacío.");
                header('Content-Type: application/json');
                echo json_encode($response);
                exit; // Detener la ejecución si hay un campo vacío
            }
        }

        // Crear instancia de conexión
        $conexion = new ConexionPostgresql();
        $pdo = $conexion->obtenerConexion();

        try {
            $pdo->beginTransaction();

            // Verificar si es un nombre nuevo o existente
            if (empty($estudiantes['id']) || $estudiantes['id'] == 0) {
                // Insertar un nuevo nombre
                $sql = "INSERT INTO estudiantes (estado, nombre, cedula, apellido, fecha_nacimiento) 
                        VALUES (:estado, :nombre, :cedula, :apellido, :fecha_nacimiento)";
            } else {
                // Actualizar nombre existente
                $sql = "UPDATE estudiantes SET estado = :estado, nombre = :nombre, 
                        cedula = :cedula, apellido = :apellido, fecha_nacimiento = :fecha_nacimiento 
                        WHERE id = :codigo";
                $accion = "Actualizado";
            }

            // Preparar la consulta
            $stmt = $pdo->prepare($sql);

            // Bind de parámetros
            if (!empty($estudiantes['id'])) {
                $stmt->bindValue(':codigo', $estudiantes['id'], PDO::PARAM_INT);
            }
            $stmt->bindValue(':cedula', $estudiantes['cedula'], PDO::PARAM_STR); // Considerar si es float
            $stmt->bindValue(':estado', $estudiantes['estado'], PDO::PARAM_STR); // Booleano como texto en PostgreSQL === 'true' ? 't' : 'f'
            $stmt->bindValue(':nombre', $estudiantes['nombre'], PDO::PARAM_STR);
            $stmt->bindValue(':apellido', $estudiantes['apellido'], PDO::PARAM_STR);
            $stmt->bindValue(':fecha_nacimiento', $estudiantes['fecha_nacimiento'], PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt->execute();

            // Confirmar transacción
            $pdo->commit();

            // Preparar la respuesta JSON
            $response = array('success' => true, 'message' => 'Estudiante ' . $accion . ' Exitosamente');
        } catch (PDOException $e) {
            // Rollback en caso de error
            $pdo->rollBack();

            // Preparar la respuesta JSON de error
            $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }

        // Devolver la respuesta como JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Si no es una solicitud POST válida
        header("HTTP/1.1 405 Method Not Allowed");
        exit;
    }
}

function guardarEstudiantesNew_()
{
    $accion = "Guardado";
    // Verificar si se recibió correctamente la solicitud POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Obtener los datos del nombre desde AJAX
        $nombre = $_POST['nombre']; // Convertir JSON a array asociativo

        // Validar que los campos requeridos no estén vacíos
        $camposObligatorios = ['estado', 'nombre', 'cedula', 'apellido'];
        foreach ($camposObligatorios as $campo) {
            if (empty($nombre[$campo]) && $nombre[$campo] !== '0') {
                // Devolver la respuesta con error si algún campo está vacío
                $response = array('success' => false, 'message' => "El campo '$campo' no puede estar vacío.");
                header('Content-Type: application/json');
                echo json_encode($response);
                exit; // Detener la ejecución si hay un campo vacío
            }
        }

        // Crear instancia de conexión
        $conexion = new ConexionPostgresql();
        $pdo = $conexion->obtenerConexion();

        try {
            $pdo->beginTransaction();

            // Verificar si es un nombre nuevo o existente
            if (empty($nombre['codigo'])) {
                // Insertar un nuevo nombre
                $sql = "INSERT INTO nombre (estado, nombre, cedula, apellido, fecha_nacimiento) 
                        VALUES (:estado, :nombre,:cedula, :apellido, 0)";
            } else {
                // Actualizar nombre existente
                $sql = "UPDATE nombre SET estado = :estado, nombre = :nombre, 
                        cedula = :cedula, apellido = :apellido 
                        WHERE id = :codigo";
                $accion = "Actualizado";
            }

            // Preparar la consulta
            $stmt = $pdo->prepare($sql);

            // Bind de parámetros
            if (!empty($nombre['codigo'])) {
                $stmt->bindValue(':codigo', $nombre['codigo'], PDO::PARAM_INT);
            }
            $stmt->bindValue(':cedula', $nombre['cedula']);
            $stmt->bindValue(':estado', $nombre['estado'], PDO::PARAM_BOOL);
            $stmt->bindValue(':nombre', $nombre['nombre'], PDO::PARAM_STR);
            $stmt->bindValue(':apellido', $nombre['apellido'], PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt->execute();

            // Confirmar transacción
            $pdo->commit();

            // Preparar la respuesta JSON
            $response = array('success' => true, 'message' => 'nombre ' . $accion . ' Exitosamente');
        } catch (PDOException $e) {
            // Rollback en caso de error
            $pdo->rollBack();

            // Preparar la respuesta JSON de error
            $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }

        // Devolver la respuesta como JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Si no es una solicitud POST válida
        header("HTTP/1.1 405 Method Not Allowed");
        exit;
    }
}
function guardarnombre()
{
    // Verificar si se recibió correctamente la solicitud POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Obtener los datos del nombre desde AJAX
        // print_r($_POST['nombre']);
        // return 0;
        $nombre = $_POST['nombre']; // Convertir JSON a array asociativo

        // Crear instancia de conexión
        $conexion = new ConexionPostgresql();
        $pdo = $conexion->obtenerConexion();

        try {
            $pdo->beginTransaction();

            // Verificar si es un nombre nuevo o existente
            if (empty($nombre['codigo'])) {
                // Insertar un nuevo nombre
                $sql = "INSERT INTO nombre (estado, nombre, cedula, apellido) 
                        VALUES (:estado, :nombre,:cedula, :apellido)";
            } else {
                // Actualizar nombre existente
                $sql = "UPDATE nombre estado = :estado, nombre = :nombre, 
                        cedula = :cedula, apellido = :apellido 
                        WHERE id = :codigo";
            }

            // Preparar la consulta
            $stmt = $pdo->prepare($sql);

            // Bind de parámetros
            $stmt->bindValue(':codigo', $nombre['codigo'], PDO::PARAM_INT);
            $stmt->bindValue(':estado', $nombre['estado'], PDO::PARAM_BOOL);
            $stmt->bindValue(':nombre', $nombre['nombre'], PDO::PARAM_STR);
            $stmt->bindValue(':cedula', $nombre['cedula'], PDO::PARAM_STR);
            $stmt->bindValue(':apellido', $nombre['apellido'], PDO::PARAM_STR);

            // Ejecutar la consulta
            $stmt->execute();

            // Confirmar transacción
            $pdo->commit();

            // Preparar la respuesta JSON
            $response = array('success' => true);
        } catch (PDOException $e) {
            // Rollback en caso de error
            $pdo->rollBack();

            // Preparar la respuesta JSON de error
            $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
        }

        // Devolver la respuesta como JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Si no es una solicitud POST válida
        header("HTTP/1.1 405 Method Not Allowed");
        exit;
    }
}

function obtenerEstudiantes()
{
    $data = array();

    // Crear instancia de conexión
    $conexion = new ConexionPostgresql();
    $pdo = $conexion->obtenerConexion();

    // Validar y procesar los parámetros POST
    $limite = isset($_POST["limite"]) && is_numeric($_POST["limite"]) ? (int) $_POST["limite"] : 10;  // Definir un límite por defecto

    $query_buscado = "";

    // Si hay un texto de búsqueda
    if (isset($_POST["texto"]) && !empty($_POST["texto"])) {
        $textobuscado = htmlspecialchars($_POST["texto"]); // Evitar inyecciones XSS
        $query_buscado = " AND nombre LIKE :texto";  // Cambié "nombre" a "nombre" para ser consistente con la estructura de datos
    }

    // Construir la consulta SQL
    $query = "SELECT * FROM estudiantes WHERE 1=1";  // Agregar "1=1" para facilitar la concatenación de condiciones
    $query_order = " ORDER BY id DESC";
    $query_limit = " LIMIT :limite";  // Usar un placeholder para el límite

    // Concatenar las condiciones de búsqueda
    $query .= $query_buscado . $query_order . $query_limit;

    // Preparar la consulta
    $stmtestudiantes = $pdo->prepare($query);

    // Bind del texto buscado si está presente
    if (!empty($query_buscado)) {
        $stmtestudiantes->bindValue(':texto', "%$textobuscado%", PDO::PARAM_STR);
    }

    // Bind del límite
    $stmtestudiantes->bindValue(':limite', $limite, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmtestudiantes->execute()) {
        $estudiantes = $stmtestudiantes->fetchAll(PDO::FETCH_ASSOC);
        $data['productos'] = $estudiantes;
    } else {
        echo json_encode(array('error' => 'Error al obtener los nombres.'));
        return;
    }

    // Cerrar la conexión
    $conexion->cerrarConexion();

    // Devolver los datos en formato JSON
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

function obtenerEstudiantes_()
{
    $data = array();
    $conexion = new ConexionPostgresql();
    $pdo = $conexion->obtenerConexion();
    $query_filter = "";
    $query_buscado = "";
    $limite = $_POST["limite"];

    if (isset($_POST["texto"]) && !empty($_POST["texto"])) {
        $textobuscado = htmlspecialchars($_POST["texto"]); // Evitar inyecciones XSS
        $query_buscado = " AND nombre LIKE :texto";
    }


    // Obtener nombres
    $query = "SELECT * FROM nombre";
    $query_order = " ORDER BY codigo DESC";
    $query_limit = " LIMIT " . $limite;

    if (!empty($query_filter) || !empty($query_buscado)) {
        $query .= " WHERE " . $query_filter . $query_buscado;
    }

    $query .= $query_order . $query_limit;
    $stmtestudiantes = $pdo->prepare($query);

    if (!empty($query_buscado)) {
        $stmtestudiantes->bindValue(':texto', "%$textobuscado%", PDO::PARAM_STR);
    }

    if ($stmtestudiantes->execute()) {
        $nombres = $stmtestudiantes->fetchAll(PDO::FETCH_ASSOC);
        $data['nombres'] = $nombres;
    } else {
        echo json_encode(array('error' => 'Error al obtener los nombres.'));
        return;
    }

    $conexion->cerrarConexion();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
