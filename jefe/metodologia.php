<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'jefe departamento') {
    header("Location: ../login.php");
    exit();
}
include('../includes/header_admin.php');
include('../includes/menu.php');
?>

<div class="container">
    <h1 class="mt-4">Metodología Estadística</h1>

    <section class="mt-5">
        <h2>VI.4.11 Metodología Estadística</h2>
        <p>
            Para obtener resultados representativos de los egresados, se utiliza una metodología basada en muestreo. 
            La <strong>carrera</strong> y el <strong>año de egreso</strong> son los principales criterios para subdividir la población.
        </p>
    </section>

    <section class="mt-4">
        <h3>VI.4.11.1 Tamaño de muestra para un intervalo de confianza de una proporción</h3>
        <p>Se utiliza la siguiente fórmula para estimar el tamaño de muestra necesario:</p>
        <div style="margin-left: 20px;">
            <p><strong>n = (Z² * p * q * N) / (B² * (N - 1) + Z² * p * q)</strong></p>
            <ul>
                <li><strong>n</strong>: Tamaño de muestra</li>
                <li><strong>Z</strong>: Valor z para el nivel de confianza (1.645 para 90%)</li>
                <li><strong>p</strong>: Proporción esperada (se asume 0.5 si no se conoce)</li>
                <li><strong>q</strong>: 1 - p</li>
                <li><strong>N</strong>: Tamaño de la población (número total de egresados)</li>
                <li><strong>B</strong>: Error permisible (ej. 0.05)</li>
            </ul>
        </div>
        <p><strong>Ejemplo:</strong> Si hay 95 egresados, con un nivel de confianza del 90% y un margen de error del 5%:</p>
        <pre>
Z = 1.645
p = 0.5
q = 0.5
N = 95
B = 0.05

n = (1.645² * 0.5 * 0.5 * 95) / ((0.05² * (95 - 1)) + (1.645² * 0.5 * 0.5))
  = (0.6765 * 95) / (0.0025 * 94 + 0.6765)
  ≈ 49.1 → Se requieren al menos 49 egresados.
        </pre>
    </section>

    <section class="mt-4">
        <h3>VI.4.11.2 Cálculo de intervalos de confianza</h3>
        <p>
            Para cada proporción (ej. % de titulados, % que trabaja en su área), se calcula el intervalo de confianza:
        </p>
        <p>
            <strong>IC = p ± Z * √((p * q) / n)</strong>
        </p>
        <ul>
            <li>p: proporción observada</li>
            <li>n: tamaño de la muestra</li>
            <li>Z: valor z (1.645 para 90%)</li>
        </ul>
        <p>
            <strong>Ejemplo:</strong> Si el 70% de 49 egresados están titulados:<br>
            IC = 0.7 ± 1.645 * √((0.7 * 0.3) / 49) ≈ 0.7 ± 0.106 → [0.594, 0.806]
        </p>
    </section>

    <section class="mt-4">
        <h3>VI.4.11.3 Selección de elementos de la muestra</h3>
        <ul>
            <li>Los egresados son seleccionados de forma aleatoria.</li>
            <li>Se intenta contactar a cada uno hasta 5 veces si es necesario.</li>
            <li>Si la tasa de no respuesta supera el 20%, puede haber sesgo en los resultados.</li>
        </ul>
    </section>

    <section class="mt-4">
        <h3>VI.4.10 Presentación de Resultados</h3>
        <ul>
            <li>Se utilizan <strong>tablas de frecuencias y porcentajes</strong> para presentar los datos.</li>
            <li>Para <strong>grupos pequeños</strong>, se evita el uso de variables independientes y dependientes.</li>
            <li>Los resultados también se representan visualmente con <strong>gráficos de barras y pastel</strong>.</li>
        </ul>
    </section>
</div>
