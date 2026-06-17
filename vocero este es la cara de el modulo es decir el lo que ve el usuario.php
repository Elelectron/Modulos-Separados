<?php
// =============================================================================
// salida.php - Formulario de Registro de Salida de Equipos
// Módulo: salidas/salida.php
// Permite registrar el despacho final de un hardware usando la relación de BD.
// =============================================================================

$titulo_pagina = "Registrar Salida - Metro Telecom";
$root = "../";

require_once "../config/conexion.php";

// Verificamos si se ha recibido el id_ingreso válido por la URL
if (isset($_GET['id_ingreso'])) {
    $id_ingreso = $_GET['id_ingreso'];

    try {
        // 1. vocero esto se utiliza para  Consultamos datos del ingreso original para dar contexto visual al operador
        $sql_ingreso = "SELECT * FROM ingresos WHERE id_ingreso = :id_ingreso";
        $stmt_ingreso = $conexion->prepare($sql_ingreso);
        $stmt_ingreso->bindParam(':id_ingreso', $id_ingreso);
        $stmt_ingreso->execute();
        $ingreso_data = $stmt_ingreso->fetch(PDO::FETCH_ASSOC);

        // 2. ademas este se comunica de manera eficaz con varios modulos para garantizar la eficiencia
        // para poblar el FK 'responsable_entrega' con personal activo
        $sql_usuarios = "SELECT id_usuario, nombre, apellido FROM usuarios WHERE estado = 'Activo'";
        $stmt_usuarios = $conexion->query($sql_usuarios);
        $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $error) {
        header("Location: index.php?error=" . urlencode("Error en base de datos: " . $error->getMessage()));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

require_once "../componentes/encabezado.php";
?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Registrar Salida de Almacén</h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row row-cards">
      <div class="col-12">
        
        <form action="procesar_salida.php" method="POST" class="card">
          
          <input type="hidden" name="id_ingreso" value="<?php echo htmlspecialchars($id_ingreso); ?>">

          <div class="card-header">
            <h3 class="card-title">Formulario de Despacho Técnico</h3>
          </div>

          <div class="card-body">
            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger">
                Error: <?php echo htmlspecialchars($_GET['error']); ?>
              </div>
            <?php endif; ?>

            <?php if ($ingreso_data): ?>
              <div class="alert alert-info bg-body-tertiary border-start border-info border-3 mb-4">
                <strong>Equipo Vinculado:</strong> 
                Ingreso #<?php echo htmlspecialchars($id_ingreso); ?> — 
                <?php echo htmlspecialchars($ingreso_data['descripcion'] ?? 'Componente en Taller'); ?>
              </div>
            <?php endif; ?>

            <div class="row row-cards">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label required">Destino / Departamento Receptor</label>
                  <select name="destino" class="form-select" required>
                    <option value="">Seleccione el destino...</option>
                    <option value="Telefonía">Telefonía</option>
                    <option value="Radio">Radio</option>
                    <option value="CCTV">CCTV</option>
                    <option value="Anuncios">Anuncios</option>
                    <option value="Redes">Redes</option>
                    <option value="Almacén de Desecho">Almacén de Desecho / Chatarra</option>  
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label required">Responsable de Entrega</label>
                  <select name="responsable_entrega" class="form-select" required>
                    <option value="">Seleccione el usuario que entrega...</option>
                    <?php foreach ($usuarios as $usuario): ?>
                      <option value="<?php echo $usuario['id_usuario']; ?>">
                        <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="col-md-12">
                <div class="mb-3">
                  <label class="form-label">Observaciones de la Salida</label>
                  <textarea name="observaciones" class="form-control" rows="3" placeholder="Detalles de la entrega (ej: se entrega probado en rack con cables de alimentación)..." required></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer text-end">
            <a href="index.php" class="btn btn-link">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Registro de Salida</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php require_once "../componentes/pie_pagina.php"; ?>
