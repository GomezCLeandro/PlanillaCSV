<?php

try {

    //Se recibe y valida el archivo csv
    if (!file_exists($_FILES['archivo']['tmp_name']) || !is_uploaded_file($_FILES['archivo']['tmp_name'])) {
        throw new Exception("No se recibio el archivo", 400);
    }

    $datos = array();

    $linea =1;
    $tmp_name = $_FILES['archivo']['tmp_name'];
    if (($handle = fopen($tmp_name, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

            if (count($data) == 7) {

                $arrayTmp = array();

                // Validacion nombre
                if (!empty($data[0])) {
                    if (!preg_match('~[0-9]+~', $data[0])) {
                        $arrayTmp['nombre'] = trim($data[0]);
                    } else {
                        throw new Exception("Documento no valido (Un nombre contiene un numero) $data[0]}", 400);
                    }
                } else {
                    $arrayTmp['nombre'] = '';
                }

                // Validacion Apellido
                if (!empty($data[1])) {
                    if (!preg_match('~[0-9]+~', $data[1])) {
                        $arrayTmp['apellido'] = trim($data[1]);
                    } else {
                        throw new Exception("Documento no valido (Un apeliido contiene un numero) $data[1]", 400);
                    }
                } else {
                    $arrayTmp['apellido'] = '';
                }

                // Validacion Documento
                if (!empty($data[2])) {
                    if (is_numeric($data[2])) {
                        $arrayTmp['documento'] = (int) trim($data[2]);
                    } else {
                        throw new Exception("Documento no valido (un numero de documento contiene caracteres no numericos) $data[2]", 400);
                    }
                } else {
                    $arrayTmp['documento'] = '';
                }

                // Validacion Organismo
                if (!empty($data[3])) {
                    if (!preg_match('~[0-9]+~', $data[3])) {
                        $arrayTmp['organismo'] = trim($data[3]);
                    } else {
                        throw new Exception("Documento no valido (Un nombre contiene un numero) $data[3]}", 400);
                    }
                } else {
                    $arrayTmp['organismo'] = '';
                }

                // Validacion Viatico
                if (!empty($data[4])) {
                    if (is_numeric($data[4])) {
                        $arrayTmp['viatico'] = (int) trim($data[4]);
                    } else {
                        throw new Exception("Documento no valido (el numero de viatico contiene caracteres no numericos) $data[4]", 400);
                    }
                } else {
                    $arrayTmp['viatico'] = '';
                }

                //Validacion Inicio Fecha
                $formatoFecha = "d/m/Y";

                if (!empty($data[5])) {
                    $d = DateTime::createFromFormat($formatoFecha, $data[5]);
                    if ($d && $d->format($formatoFecha) == $data[5]) {
                        $arrayTmp['fechaInicioViaje'] = trim($data[5]);
                    } else {
                        throw new Exception("Documento no valido (fecha de inicio de viaje no valido ) $data[5]", 400);
                    }
                } else {
                    $arrayTmp['fechaInicioViaje'] = '';
                }

                //Validacion Fin Fecha
                if (!empty($data[6])) {
                    $d = DateTime::createFromFormat($formatoFecha, $data[6]);
                    if ($d && $d->format($formatoFecha) == $data[6]) {
                        $arrayTmp['fechaFinViaje'] = trim($data[6]);
                    } else {
                        throw new Exception("Documento no valido (fecha de fin de viaje no valido) $data[6]",400);
                    }
                } else {
                    $arrayTmp['fechaFinViaje'] = '';
                }

                $datos[] = $arrayTmp;
                $linea++;
            } else {
                throw new Exception("Documento no valido, una de las filas no contiene la cantidad de datos necesaria (linea $linea)", 400);
            }
        }
        fclose($handle);
    }

    //Carga a base de datos

    $servidor = 'localhost';
    $baseDatos = 'testcsv';
    $usuario = 'root';
    $password = "";

    //Conexion con la Base de Datos

    $mysqli = new mysqli($servidor, $usuario, '', $baseDatos);
    if ($mysqli->connect_errno) {
        echo "Fallo al conectar a MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }

    //Se recorre el array datos y se guarda uno por uno las personas 
    foreach ($datos as $persona) {
        $sqlInsert = "INSERT INTO `testcsv`.`viaje` (`nombre`, `apellido`, `documento`, `organismo`, `viatico`, `fecha_inicio_viaje`, `fecha_fin_viaje`) VALUES ";

        $sqlValor = "('".$persona['nombre']."','".$persona['apellido']."','".$persona['documento']."','".$persona['organismo']."','".$persona['viatico']."','".$persona['fechaInicioViaje']."','".$persona['fechaFinViaje']."');";
        
        $sqlFinal = $sqlInsert . $sqlValor;
        $mysqli->query($sqlFinal);
    }

    echo json_encode(
        array(
            'status' => true,
            'code' => 200,
            'datos' => $datos
        )
    );

} catch (Exception $e) {
    http_response_code($e->getCode());
    echo json_encode(
        array(
            'status' => false,
            'code' => $e->getCode(),
            'message' => $e->getMessage()
        )
    );
}
