<?php 
require_once('../db/conexion.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

if ($conexion->connect_error) {
    echo json_encode(["exito" => false, "mensaje" => "Conexión a la base de datos fallida."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['datos']) || !isset($data['periodo'])) {
    echo json_encode(["exito" => false, "mensaje" => "Datos incompletos o JSON inválido."]);
    exit;
}

$periodo = intval($data['periodo']);
$datos = $data['datos'];
$errores = [];
$currentYear = date("Y");

$preguntasTexto = [];
$resTipo = $conexion->query("SELECT ID_PREGUNTA FROM PREGUNTA WHERE TIPO = 'texto'");
while ($row = $resTipo->fetch_assoc()) {
    $preguntasTexto[] = intval($row['ID_PREGUNTA']);
}

try {
    $conexion->begin_transaction();
    $empresasPorCurp = [];

    foreach ($datos as $nombreSeccion => $registros) {
        if ($nombreSeccion === 'Empresas') {
            foreach ($registros as $fila) {
                $filaVacia = true;
                foreach ($fila as $valor) {
                    if (!is_null($valor) && trim($valor) !== '') {
                        $filaVacia = false;
                        break;
                    }
                }
                if ($filaVacia) continue;

                $curp = strtoupper(trim($fila['CURP'] ?? ''));

                $tipoOrganismo = $fila['Tipo de Organismo'] ?? null;
                $giro = $fila['Giro'] ?? null;
                $razonSocial = $fila['Razón Social'] ?? null;
                $calle = $fila['Calle'] ?? null;
                $numero = $fila['Número'] ?? null;
                $colonia = $fila['Colonia'] ?? null;
                $codigoPostal = $fila['Código Postal'] ?? null;
                $ciudad = $fila['Ciudad'] ?? null;
                $municipio = $fila['Municipio'] ?? null;
                $estado = $fila['Estado'] ?? null;
                $telefono = $fila['Teléfono'] ?? null;
                $email = $fila['Email'] ?? null;
                $paginaWeb = $fila['Página Web'] ?? null;
                $jefeNombre = $fila['Jefe Inmediato Nombre'] ?? null;
                $jefePuesto = $fila['Jefe Inmediato Puesto'] ?? null;

                $datosEmpresa = [
                    $tipoOrganismo, $giro, $razonSocial, $calle, $numero, $colonia,
                    $codigoPostal, $ciudad, $municipio, $estado, $telefono,
                    $email, $paginaWeb, $jefeNombre, $jefePuesto
                ];

                $todoVacio = true;
                foreach ($datosEmpresa as $v) {
                    if (!is_null($v) && trim($v) !== '') {
                        $todoVacio = false;
                        break;
                    }
                }

                if ($todoVacio) continue;

                $stmtEmp = $conexion->prepare("INSERT INTO EMPRESA (
                    TIPO_ORGANISMO, GIRO, RAZON_SOCIAL, CALLE, NUMERO, COLONIA,
                    CODIGO_POSTAL, CIUDAD, MUNICIPIO, ESTADO, TELEFONO, EMAIL,
                    PAGINA_WEB, JEFE_INMEDIATO_NOMBRE, JEFE_INMEDIATO_PUESTO
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                if (!$stmtEmp->bind_param("sssssssssssssss",
                    $tipoOrganismo, $giro, $razonSocial, $calle, $numero, $colonia,
                    $codigoPostal, $ciudad, $municipio, $estado, $telefono,
                    $email, $paginaWeb, $jefeNombre, $jefePuesto
                ) || !$stmtEmp->execute()) {
                    $errores[] = "Error al insertar empresa para CURP $curp: " . $stmtEmp->error;
                } else {
                    $idEmpresaInsertada = $stmtEmp->insert_id;

                    if ($curp !== '') {
                        $empresasPorCurp[$curp] = $idEmpresaInsertada;
                    }
                }

                $stmtEmp->close();
            }
        }

        foreach ($registros as $fila) {
            $curp = strtoupper(trim($fila['CURP'] ?? ''));

            $filaVacia = true;
            foreach ($fila as $valor) {
                if (!is_null($valor) && trim($valor) !== '') {
                    $filaVacia = false;
                    break;
                }
            }
            if ($filaVacia) continue;

            if (!$curp) {
                $errores[] = "CURP faltante en fila: " . json_encode($fila);
                continue;
            }

            if ($nombreSeccion === 'Egresados') {
                $numControl = trim($fila['No. Control'] ?? '');
                if (!$numControl) {
                    $errores[] = "No. Control faltante para egresado en fila: " . json_encode($fila);
                    continue;
                }

                $nombre = $fila['Nombre'] ?? '';
                $apellidoPaterno = $fila['Apellido Paterno'] ?? '';
                $apellidoMaterno = $fila['Apellido Materno'] ?? '';
                $fechaNacimiento = $fila['Fecha Nacimiento'] ?? '';
                $sexo = $fila['Sexo'] ?? '';
                $estadoCivil = $fila['Estado Civil'] ?? '';
                $calle = $fila['Calle'] ?? '';
                $colonia = $fila['Colonia'] ?? '';
                $codigoPostal = $fila['Código Postal'] ?? '';
                $ciudad = $fila['Ciudad'] ?? '';
                $municipio = $fila['Municipio'] ?? '';
                $estado = $fila['Estado'] ?? '';
                $email = $fila['Correo'] ?? '';
                $telefono = $fila['Teléfono'] ?? '';
                $carrera = $fila['Carrera'] ?? '';
                $fechaEgreso = $fila['Fecha de Egreso'] ?? '';
                $titulado = (isset($fila['Titulado']) && $fila['Titulado'] === 'Sí') ? 1 : 0;

                if ($fechaNacimiento) {
                    $anioNacimiento = intval(substr($fechaNacimiento, 0, 4));
                    if ($anioNacimiento < 1930 || $anioNacimiento > $currentYear) {
                        $errores[] = "Año de nacimiento inválido ($anioNacimiento) para CURP $curp.";
                        continue;
                    }
                }

                if ($fechaEgreso) {
                    $anioEgreso = intval(substr($fechaEgreso, 0, 4));
                    if ($anioEgreso < 1950 || $anioEgreso > $currentYear) {
                        $errores[] = "Año de egreso inválido ($anioEgreso) para CURP $curp.";
                        continue;
                    }
                }

                $stmtE = $conexion->prepare("SELECT CURP FROM EGRESADO WHERE CURP = ?");
                $stmtE->bind_param("s", $curp);
                $stmtE->execute();
                $stmtE->store_result();

                if ($stmtE->num_rows > 0) {
                    $updateE = $conexion->prepare("UPDATE EGRESADO SET NOMBRE=?, APELLIDO_PATERNO=?, APELLIDO_MATERNO=?, 
                        NUM_CONTROL=?, FECHA_NACIMIENTO=?, SEXO=?, ESTADO_CIVIL=?, CALLE=?, COLONIA=?, CODIGO_POSTAL=?, 
                        CIUDAD=?, MUNICIPIO=?, ESTADO=?, EMAIL=?, TELEFONO=?, CARRERA=?, FECHA_EGRESO=?, TITULADO=? 
                        WHERE CURP=?");

                    $updateE->bind_param("sssssssssssssssssis", 
                        $nombre, $apellidoPaterno, $apellidoMaterno,
                        $numControl, $fechaNacimiento, $sexo, $estadoCivil,
                        $calle, $colonia, $codigoPostal, $ciudad, $municipio,
                        $estado, $email, $telefono, $carrera, $fechaEgreso,
                        $titulado, $curp
                    );
                    $updateE->execute();
                } else {
                    $insertE = $conexion->prepare("INSERT INTO EGRESADO (CURP, NUM_CONTROL, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO,
                        FECHA_NACIMIENTO, SEXO, ESTADO_CIVIL, CALLE, COLONIA, CODIGO_POSTAL, CIUDAD, MUNICIPIO, ESTADO,
                        EMAIL, CONTRASENA, TELEFONO, CARRERA, FECHA_EGRESO, TITULADO)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?)");

                    $insertE->bind_param("ssssssssssssssssssi", 
                        $curp, $numControl, $nombre, $apellidoPaterno, $apellidoMaterno,
                        $fechaNacimiento, $sexo, $estadoCivil, $calle, $colonia,
                        $codigoPostal, $ciudad, $municipio, $estado, $email,
                        $telefono, $carrera, $fechaEgreso, $titulado
                    );
                    $insertE->execute();
                }
            }
            $curp = strtoupper(trim($fila['CURP'] ?? ''));

            if ($curp !== '') {
                $stmtCuest = $conexion->prepare("SELECT ID_CUESTIONARIO FROM CUESTIONARIO_RESPUESTA WHERE CURP = ? AND ID_PERIODO = ?");
                $stmtCuest->bind_param("si", $curp, $periodo);
                $stmtCuest->execute();
                $stmtCuest->bind_result($idCuestionario);
            
                if ($stmtCuest->fetch()) {
                    $stmtCuest->close();

                    $idCuestionarioExistente = $idCuestionario;

                    if (!empty($empresasPorCurp[$curp])) {
                        $idEmpresa = $empresasPorCurp[$curp];

                        $stmtUpdate = $conexion->prepare("UPDATE CUESTIONARIO_RESPUESTA SET ID_EMPRESA = ? WHERE ID_CUESTIONARIO = ? AND (ID_EMPRESA IS NULL OR ID_EMPRESA = 0)");
                        $stmtUpdate->bind_param("ii", $idEmpresa, $idCuestionarioExistente);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                    }
                
                    $idCuestionario = $idCuestionarioExistente;
                } else {
                    $stmtCuest->close();

                    $idEmpresa = $empresasPorCurp[$curp] ?? null;
                
                    if ($idEmpresa !== null) {
                        $insCuest = $conexion->prepare("INSERT INTO CUESTIONARIO_RESPUESTA (CURP, ID_PERIODO, ID_EMPRESA) VALUES (?, ?, ?)");
                        $insCuest->bind_param("sii", $curp, $periodo, $idEmpresa);
                    } else {
                        $insCuest = $conexion->prepare("INSERT INTO CUESTIONARIO_RESPUESTA (CURP, ID_PERIODO) VALUES (?, ?)");
                        $insCuest->bind_param("si", $curp, $periodo);
                    }
                
                    if (!$insCuest->execute()) {
                        $errores[] = "Error al insertar CUESTIONARIO_RESPUESTA para CURP $curp: " . $insCuest->error;
                    }
                
                    $idCuestionario = $insCuest->insert_id;
                    $insCuest->close();
                }
                
            }
            

            $opcionesPorPregunta = [];
            $resOpc = $conexion->query("SELECT ID_PREGUNTA, TEXTO, ID_OPCION FROM OPCION_RESPUESTA");
            while ($row = $resOpc->fetch_assoc()) {
                $texto = trim(strtolower($row['TEXTO']));
                $opcionesPorPregunta[$row['ID_PREGUNTA']][$texto] = $row['ID_OPCION'];
            }

            foreach ($fila as $clave => $valor) {
                if (!is_numeric($clave) || $clave === '') continue;
                if ($valor === null || trim($valor) === '') continue;

                $idPregunta = intval($clave);

                if (in_array($idPregunta, $preguntasTexto)) {
                    $null = null;
                    $insResp = $conexion->prepare("INSERT INTO RESPUESTA (ID_CUESTIONARIO, ID_PREGUNTA, ID_OPCION, RESPUESTA_TEXTO) VALUES (?, ?, ?, ?)");
                    if (!$insResp->bind_param("iiis", $idCuestionario, $idPregunta, $null, $valor) || !$insResp->execute()) {
                        $errores[] = "Error al insertar texto para CURP $curp, pregunta $idPregunta: " . $insResp->error;
                    }
                    $insResp->close();
                } else {
                    $idOpcion = null;

                    if (is_numeric($valor) && intval($valor) !== 0) {
                        $idOpcion = intval($valor);
                    } else {
                        $textoNormalizado = trim(strtolower($valor));
                        if (isset($opcionesPorPregunta[$idPregunta][$textoNormalizado])) {
                            $idOpcion = $opcionesPorPregunta[$idPregunta][$textoNormalizado];
                        } else {
                            $errores[] = "Texto '$valor' no mapea a ninguna opción para pregunta $idPregunta (CURP: $curp)";
                            continue;
                        }
                    }

                    $stmtCheck = $conexion->prepare("SELECT 1 FROM OPCION_RESPUESTA WHERE ID_OPCION = ? AND ID_PREGUNTA = ?");
                    $stmtCheck->bind_param("ii", $idOpcion, $idPregunta);
                    $stmtCheck->execute();
                    $stmtCheck->store_result();

                    if ($stmtCheck->num_rows === 0) {
                        $errores[] = "ID_OPCION $idOpcion no pertenece a ID_PREGUNTA $idPregunta (CURP: $curp)";
                        $stmtCheck->close();
                        continue;
                    }
                    $stmtCheck->close();

                    $insResp = $conexion->prepare("INSERT INTO RESPUESTA (ID_CUESTIONARIO, ID_PREGUNTA, ID_OPCION, RESPUESTA_TEXTO) VALUES (?, ?, ?, NULL)");
                    if (!$insResp->bind_param("iii", $idCuestionario, $idPregunta, $idOpcion) || !$insResp->execute()) {
                        $errores[] = "Error al insertar opción para CURP $curp, pregunta $idPregunta: " . $insResp->error;
                    }
                    $insResp->close();
                }
            }
        }
    }

    $conexion->commit();
    echo json_encode(["exito" => true, "errores" => $errores]);
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(["exito" => false, "mensaje" => $e->getMessage(), "errores" => $errores]);
}
?>
