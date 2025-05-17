<?php
// Sólo iniciar sesión si no hay una ya activa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Tomar el rol, normalizándolo para coincidir con las claves del menú
$rolRaw = $_SESSION['rol'] ?? 'Invitado';
$rol    = ucfirst(strtolower($rolRaw));

// Definición de menús por rol
$menus = [
    'Egresado' => [
        'INFORMACIÓN ESCOLAR' => [
            ['SOPORTE TÉCNICO', 'soporte.php']
        ],
        'ENCUESTAS' => [
            ['ENCUESTA PARA EGRESADOS', 'encuestas.php'],
            ['VER RESPUESTAS', 'respuestas.php'],
        ]
    ],
    'Administrador' => [
        'GESTIÓN' => [
            ['GESTIÓN DE EGRESADOS', 'admin/egresados.php'],
            ['AVISOS', 'admin/avisos.php']
        ],
        'REPORTES' => [
            ['REPORTES GENERALES', 'admin/reportes.php'],
            ['CENSOS', 'admin/censos.php']
        ],
        'RETROALIMENTACIÓN' => [
            ['SOPORTE TÉCNICO', 'admin/soporte.php']
        ]
    ],
    'Jefe departamento' => [
        'GESTIÓN ACADÉMICA' => [
            ['DIRECTORIO DE EGRESADOS', 'jefe/egresados.php'],
            ['INTEGRACIÓN DE DATOS (EXCEL)', 'jefe/importar.php'],
            ['DESCARGA DE INFORMACIÓN', 'jefe/exportar.php']
        ],
        'ANÁLISIS DE ENCUESTAS' => [
            ['VISUALIZACIÓN DE RESULTADOS', 'jefe/resultados.php'],
            ['ESTADO DE PARTICIPACIÓN', 'jefe/estado_encuestas.php'],
            ['PERIODOS DE LEVANTAMIENTO', 'jefe/periodos.php'],
            ['METODOLOGÍA ESTADÍSTICA', 'jefe/metodologia.php'] 
        ],
        'REPORTES' => [
            ['GENERACIÓN DE INFORMES', 'jefe/reporte.php'],
            ['HISTORIAL DE CONSULTAS', 'jefe/historial.php']
        ]
    ],
    'Jefe vinculación' => [
        'RELACIONES' => [
            ['VÍNCULOS CON EMPRESAS', 'vinculacion/empresas.php'],
            ['ESTADÍSTICAS DE INSERCIÓN', 'vinculacion/estadisticas.php']
        ]
    ],
    'Dba' => [
        'USUARIOS Y SEGURIDAD' => [
            ['GESTIÓN DE USUARIOS', 'dba/usuarios.php'],
            ['RESPALDOS DEL SISTEMA', 'dba/backup.php']
        ],
        'DATOS Y MIGRACIÓN' => [
            ['EXPORTAR/IMPORTAR DATOS', 'dba/import_export.php'],
            ['HISTORIAL DE CAMBIOS', 'dba/logs.php']
        ]
    ],
    'Invitado' => []
];

// Obtener el menú seguro: si no existe la clave, usar Invitado
$menuActual = $menus[$rol] ?? $menus['Invitado'];
?>

<div class="nav">
    <ul class="menu">
        <?php foreach ($menuActual as $categoria => $opciones): ?>
            <li class="submenu">
                <a href="#"><?= htmlspecialchars($categoria) ?></a>
                <ul class="submenu-items">
                    <?php foreach ($opciones as list($texto, $url)): ?>
                        <li><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($texto) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>

        <?php if ($rol !== 'Invitado'): // solo usuarios autenticados ?>
            <li><a href="logout.php">CERRAR SESIÓN</a></li>
        <?php endif; ?>
    </ul>
</div>
