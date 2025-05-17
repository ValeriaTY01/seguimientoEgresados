function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'none';
    }
}

window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});


// Registrar el plugin de etiquetas para Chart.js
Chart.register(ChartDataLabels);

// Función para renderizar gráficas
function renderChart(idCanvas, labels, data) {
    const ctx = document.getElementById(idCanvas)?.getContext('2d');
    if (!ctx) return;

    const backgroundColors = labels.map((_, index) => {
        const colors = ['#4CAF50', '#FFC107', '#F44336', '#2196F3', '#9C27B0', '#FF5722'];
        return colors[index % colors.length];
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Porcentaje',
                data: data,
                backgroundColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'right',
                    formatter: (value) => value + '%',
                    color: '#000',
                    font: { weight: 'bold' }
                },
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => context.parsed.x + '%'
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: value => value + '%'
                    }
                }
            }
        }
    });
}
