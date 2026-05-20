<?php
// =============================================================================
// procesar_salida.php - Procesar inserción del despacho de hardware
// Módulo: salidas/procesar_salida.php
// Captura el POST del formulario y luego hace el proceso vocero ya que este es el procesador lo que procesa la informacion
// =============================================================================

require_once "../config/conexion.php";

// Validamos que la petición sea estrictamente POST ya que es el que mejor se adapta en este aspecto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_ingreso          = $_POST['id_ingreso'];
    $destino             = $_POST['destino'];
    $observaciones       = $_POST['observaciones'];
    $responsable_entrega = $_POST['responsable_entrega']; // Almacena el id_usuario (FK)

    // Validamos del lado del servidor que no vengan vacíos los datos obligatorios
    if (empty($id_ingreso) || empty($destino) || empty($responsable_entrega)) {
        header("Location: salida.php?id_ingreso=" . urlencode($id_ingreso) . "&error=" . urlencode("Por favor, complete todos los campos obligatorios."));
        exit();
    }

    try {
        // Inicia una transacción PDO por seguridad operativa
        $conexion->beginTransaction();

        // se deja que MySQL asigne la fecha actual con NOW() en fecha_salida
        $sql = "INSERT INTO salidas (id_ingreso, fecha_salida, destino, observaciones, responsable_entrega) 
                VALUES (:id_ingreso, NOW(), :destino, :observaciones, :responsable_entrega)";

        $sentencia = $conexion->prepare($sql);
        $sentencia->bindParam(':id_ingreso',          $id_ingreso);
        $sentencia->bindParam(':destino',             $destino);
        $sentencia->bindParam(':observaciones',       $observaciones);
        $sentencia->bindParam(':responsable_entrega', $responsable_entrega);

        if ($sentencia->execute()) {
            //  Si se  manejas estados en la  tabla de ingresos (ej: de 'Taller' a 'Entregado')
            //  se  podrías ejecutar un UPDATE aquí mismo antes del commit: para hacer el programa mejor para el ux del usuario
            // $sql_update = "UPDATE ingresos SET estado = 'Entregado' WHERE id_ingreso = :id_ingreso";
            // $conexion->prepare($sql_update)->execute([':id_ingreso' => $id_ingreso]);

            $conexion->commit();
            
            // Redirección limpia al listado principal con mensaje de éxito
            header("Location: index.php?mensaje=salida_exitosa");
            exit();
        }
    } catch (PDOException $error) {
        // Si la base de datos falla (por ejemplo, violación de clave foránea), se revierte cambios para proteger la base de datos y no llenarla de archivos basura o incompletos
        $conexion->rollBack();
        header("Location: salida.php?id_ingreso=" . urlencode($id_ingreso) . "&error=" . urlencode("Error BD: " . $error->getMessage()));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>