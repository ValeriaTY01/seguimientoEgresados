<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$rolRaw = $_SESSION['rol'] ?? 'Invitado';
$rol = ucfirst(strtolower($rolRaw));

// Asociar carpeta base por rol
$carpetasBase = [
    'Administrador'      => 'includes/',
    'Jefe departamento'  => 'includes/',
    'Jefe vinculación'   => 'includes/',
    'Dba'                => 'includes/',
    'Egresado'           => '',
    'Invitado'           => ''
];
$carpeta = $carpetasBase[$rol] ?? '';

// Menú base para roles similares
$menuGestionEgresados = [
    'GESTIÓN' => [
        ['GESTIÓN DE EGRESADOS', 'egresados.php'],
        ['DESCARGA DE INFORMACIÓN', 'exportar.php'],
        ['AVISOS', 'avisos.php']
    ],
    'ANÁLISIS DE ENCUESTAS' => [
        ['VISUALIZACIÓN DE RESULTADOS', 'resultados.php'],
    ],
    'REPORTES' => [
        ['REPORTES GENERALES', 'reporte.php'],
        ['HISTORIAL DE CONSULTAS', 'historial.php']
    ]
];

// Definición de menú por rol
$menus = [
    'Egresado' => [
        'INFORMACIÓN ESCOLAR' => [
            ['SOPORTE TÉCNICO', 'soporte.php']
        ],
        'ENCUESTAS' => [
            ['ENCUESTA PARA EGRESADOS', 'encuestas.php'],
            ['VER RESPUESTAS', 'respuestas.php']
        ]
    ],
    'Administrador' => $menuGestionEgresados + [
        'ANÁLISIS DE ENCUESTAS' => [
            ['VISUALIZACIÓN DE RESULTADOS', 'resultados.php'],
            ['PERIODOS DE LEVANTAMIENTO', 'periodos.php']
        ],
        'RETROALIMENTACIÓN' => [
            ['SOPORTE TÉCNICO', 'soporte.php']
        ]
    ],

    'Jefe departamento' => [
        'GESTIÓN ACADÉMICA' => [
            ['DIRECTORIO DE EGRESADOS', 'egresados.php'],
            ['INTEGRACIÓN DE DATOS (EXCEL)', 'importar.php'],
            ['DESCARGA DE INFORMACIÓN', 'exportar.php']
        ],
        'ANÁLISIS DE ENCUESTAS' => [
            ['VISUALIZACIÓN DE RESULTADOS', 'resultados.php'],
            ['ESTADO DE PARTICIPACIÓN', 'estado_encuestas.php']
        ],
        'REPORTES' => [
            ['GENERACIÓN DE INFORMES', 'reporte.php'],
            ['HISTORIAL DE CONSULTAS', 'historial.php']
        ]
    ],
    'Jefe vinculación' => $menuGestionEgresados + [
        'ANÁLISIS DE ENCUESTAS' => [
            ['VISUALIZACIÓN DE RESULTADOS', 'resultados.php'],
            ['PERIODOS DE LEVANTAMIENTO', 'periodos.php']
        ]
    ],
    'Dba' => [
        'USUARIOS Y SEGURIDAD' => [
            ['GESTIÓN DE USUARIOS', 'usuarios.php'],
            ['RESPALDOS DEL SISTEMA', 'backup.php']
        ],
        'DATOS Y MIGRACIÓN' => [
            ['EXPORTAR/IMPORTAR DATOS', 'import_export.php'],
            ['HISTORIAL DE CAMBIOS', 'logs.php']
        ]
    ],
    'Invitado' => []
];

$menuActual = $menus[$rol] ?? $menus['Invitado'];
?>

<div class="nav">
    <ul class="menu">
        <?php foreach ($menuActual as $categoria => $opciones): ?>
            <li class="submenu">
                <a href="#"><?= htmlspecialchars($categoria) ?></a>
                <ul class="submenu-items">
                    <?php foreach ($opciones as list($texto, $archivo)): ?>
                        <li>
                            <a href="<?= htmlspecialchars($carpeta . $archivo) ?>">
                                <?= htmlspecialchars($texto) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>

        <?php if ($rol !== 'Invitado'): ?>
            <li><a href="logout.php">CERRAR SESIÓN</a></li>
        <?php endif; ?>
    </ul>
</div>
