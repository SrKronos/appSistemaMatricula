<?php
session_start();
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']['nombre']) || empty($_SESSION['usuario']['fechahora'])) {
    header("Location: index.php");
    exit();
}
?>
<head>
    <meta charset="UTF-8">
    <title>Administrar Matrículas</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="../js/administrar_movimientos.js?<?php echo time(); ?>"></script>
</head>
<h2 class="titulo-pagina">Matrículas 📋🗳️🖨️</h2>
<div class="area_producto">
    <label for="inputProducto">Nombre:</label>
    <input type="text" id="inputProducto" class="inBuscar" placeholder="Escribe el nombre del producto...">
    <ul id="listaProductos" class="sugerencias"></ul>
</div>
<div class="area_stock">
    <label for="labelStock">Apellido:</label>
    <input type="text" id="labelStock" class="inBuscar" readonly>
</div>
<div class="area_cantidad">
    <label for="selectCurso">Curso:</label>
    <select id="selectCurso" class="inBuscar">
        <option value="Primero">Primero</option>
        <option value="Segundo">Segundo</option>
        <option value="Tercero">Tercero</option>
        <option value="Cuarto">Cuarto</option>
        <option value="Quinto">Quinto</option>
        <option value="Sexto">Sexto</option>
        <option value="Septimo">Séptimo</option>
        <option value="Octavo">Octavo</option>
        <option value="Noveno">Noveno</option>
        <option value="Decimo">Décimo</option>
        <option value="Primero Bachillerato">Primero Bachillerato</option>
        <option value="Segundo Bachillerato">Segundo Bachillerato</option>
        <option value="Tercero Bachillerato">Tercero Bachillerato</option>
    </select>
</div>
<div class="area_tipo">
    <label for="inputTipo">Tipo de Matricula:</label>
    <select id="inputTipo" class="inTipo">
        <option value="">Seleccione una opción</option>
        <option value="I">INGRESO</option>
        <option value="E">EGRESO</option>
    </select>
</div>
<div class="area_guardar">
    <button id="btnGuardarMovimiento" class="btnGuardar">Matricular</button>
</div>
<div class="banner-opciones">
    <div class="area_filtros">
        <div class="form-control">
            <label for="inlimite">Limite:</label>
            <input type="number" name="inlimite" id="inlimite" min=0 value="20">
        </div>
    </div>
</div>
<button id="btnGenerarPDF">Generar Reporte PDF</button>
<table id="tabla-exportar" class="tabla-exportar">
    <thead>
        <tr>
            <th>Código</th>
            <th>Estudiante</th>
            <th>Tipo Matrícula</th>
            <th>Fecha Matrícula</th>
            <th>Curso</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody id="productos-tbody" class="productos-tbody">
    </tbody>
</table>