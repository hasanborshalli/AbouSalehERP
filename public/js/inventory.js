const stockValueChart = document.getElementById("stockValueChart");

if (stockValueChart && window.stockChartData) {
    new Chart(stockValueChart, {
        type: "bar",
        data: {
            labels: window.stockChartData.labels,
            datasets: [
                {
                    label: "Quantity",
                    data: window.stockChartData.values,
                    borderRadius: 4,
                    barThickness: 22,
                    backgroundColor: "rgba(95, 231, 234, 0.95)",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: "y",
            layout: { padding: { top: 8, right: 16, bottom: 8, left: 12 } },
            plugins: {
                legend: { position: "top", align: "center" },
                tooltip: { enabled: true },
            },
            scales: {
                y: {
                    grid: { display: false },
                    ticks: {
                        autoSkip: false,
                        color: "rgba(0,0,0,0.55)",
                        font: { size: 12 },
                    },
                    border: { display: false },
                },
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5,
                        color: "rgba(0,0,0,0.45)",
                        font: { size: 11 },
                    },
                    grid: {
                        color: "rgba(0,0,0,0.08)",
                        lineWidth: 1,
                    },
                    border: { display: false },
                },
            },
        },
    });
}
