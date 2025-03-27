// Dashboard JavaScript

// Menangani Tab UI
function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Nonaktifkan semua tab
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Aktifkan tab yang dipilih
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
}

// Fungsi untuk mengambil data dari server
async function fetchDashboardData(dateRange) {
    try {
        const response = await fetch(`/globalmorningenterprise/api/dashboard-data.php?date_range=${dateRange}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
        return null;
    }
}

// Fungsi untuk memperbarui Card Performa Artikel
function updateArticlePerformance(data) {
    // Update total counter
    document.getElementById('total-articles').textContent = data.articleCounts.total;
    document.getElementById('published-articles').textContent = data.articleCounts.published;
    document.getElementById('draft-articles').textContent = data.articleCounts.draft;
    document.getElementById('archived-articles').textContent = data.articleCounts.archived;

    // Buat chart untuk artikel yang dipublish tiap bulan
    const ctx = document.getElementById('monthly-publish-chart').getContext('2d');

    // Destroy chart lama jika ada
    if (window.monthlyPublishChart) {
        window.monthlyPublishChart.destroy();
    }

    window.monthlyPublishChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.monthlyPublished.map(item => item.month),
            datasets: [{
                label: 'Artikel Dipublish',
                data: data.monthlyPublished.map(item => item.count),
                backgroundColor: 'rgba(74, 108, 247, 0.2)',
                borderColor: 'rgba(74, 108, 247, 1)',
                borderWidth: 2,
                tension: 0.3,
                pointBackgroundColor: 'rgba(74, 108, 247, 1)',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Jumlah Artikel Dipublish (6 Bulan Terakhir)',
                    font: {
                        size: 14
                    }
                },
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Fungsi untuk memperbarui Panel Aktivitas Author
function updateAuthorActivity(data) {
    // Top 5 Authors berdasarkan jumlah artikel
    const topAuthorsByArticlesTable = document.getElementById('top-authors-by-articles');
    topAuthorsByArticlesTable.innerHTML = '';

    data.topAuthorsByArticles.forEach(author => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${author.username}</td>
            <td>${author.article_count}</td>
            <td>${author.average_views}</td>
        `;
        topAuthorsByArticlesTable.appendChild(row);
    });

    // Top 5 Authors berdasarkan jumlah views
    const topAuthorsByViewsTable = document.getElementById('top-authors-by-views');
    topAuthorsByViewsTable.innerHTML = '';

    data.topAuthorsByViews.forEach(author => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${author.username}</td>
            <td>${author.total_views}</td>
            <td>${author.article_count}</td>
        `;
        topAuthorsByViewsTable.appendChild(row);
    });

    // Tren kontribusi author (Line Chart)
    const ctx = document.getElementById('author-contribution-chart').getContext('2d');

    // Destroy chart lama jika ada
    if (window.authorContributionChart) {
        window.authorContributionChart.destroy();
    }

    // Mendapatkan data untuk chart
    const authorLabels = [...new Set(data.authorContributions.map(item => item.author))];
    const months = [...new Set(data.authorContributions.map(item => item.month))];

    // Membuat dataset untuk setiap author
    const datasets = authorLabels.map((author, index) => {
        const colors = [
            'rgba(74, 108, 247, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(245, 158, 11, 1)',
            'rgba(239, 68, 68, 1)',
            'rgba(107, 114, 128, 1)'
        ];

        const authorData = data.authorContributions.filter(item => item.author === author);
        const dataPoints = months.map(month => {
            const matchingItem = authorData.find(item => item.month === month);
            return matchingItem ? matchingItem.count : 0;
        });

        return {
            label: author,
            data: dataPoints,
            borderColor: colors[index % colors.length],
            backgroundColor: 'transparent',
            borderWidth: 2,
            tension: 0.3,
            pointBackgroundColor: colors[index % colors.length],
            pointRadius: 3
        };
    });

    window.authorContributionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Tren Kontribusi Author (6 Bulan Terakhir)',
                    font: {
                        size: 14
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Fungsi untuk memperbarui Komponen Analisa Konten
function updateContentAnalysis(data) {
    // Pie Chart untuk artikel per kategori
    const ctx = document.getElementById('category-pie-chart').getContext('2d');

    // Destroy chart lama jika ada
    if (window.categoryPieChart) {
        window.categoryPieChart.destroy();
    }

    // Membuat array warna dinamis
    const backgroundColors = [
        'rgba(74, 108, 247, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(239, 68, 68, 0.8)',
        'rgba(107, 114, 128, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(168, 85, 247, 0.8)',
        'rgba(236, 72, 153, 0.8)'
    ];

    window.categoryPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.articlesByCategory.map(item => item.category_name),
            datasets: [{
                data: data.articlesByCategory.map(item => item.count),
                backgroundColor: backgroundColors,
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Artikel per Kategori',
                    font: {
                        size: 14
                    }
                },
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Update rata-rata comment per artikel
    document.getElementById('avg-comments').textContent = data.averageComments.toFixed(1);

    // Update top 5 artikel dengan comment terbanyak
    const topCommentedArticlesList = document.getElementById('top-commented-articles');
    topCommentedArticlesList.innerHTML = '';

    data.topCommentedArticles.forEach(article => {
        const listItem = document.createElement('li');
        listItem.textContent = `${article.title} (${article.comment_count} komentar)`;
        topCommentedArticlesList.appendChild(listItem);
    });

    // Update top 5 artikel dengan view terbanyak
    const topViewedArticlesTable = document.getElementById('top-viewed-articles');
    topViewedArticlesTable.innerHTML = '';

    data.topViewedArticles.forEach(article => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${article.title}</td>
            <td>${article.views}</td>
            <td>${article.category_name}</td>
        `;
        topViewedArticlesTable.appendChild(row);
    });
}

// Fungsi utama untuk memperbarui dashboard
async function updateDashboard() {
    const dateRange = document.getElementById('date-range').value;
    const data = await fetchDashboardData(dateRange);

    if (data) {
        updateArticlePerformance(data);
        updateAuthorActivity(data);
        updateContentAnalysis(data);
    }
}

document.getElementById('date-range').addEventListener('change', function () {
    console.log('Filter changed to:', this.value);
    updateDashboard();
});

// Inisialisasi
document.addEventListener('DOMContentLoaded', function () {
    // Setup tab functionality
    setupTabs();

    // Load initial dashboard data
    updateDashboard();

    // Set up event listener for date filter
    document.getElementById('date-range').addEventListener('change', updateDashboard);

    // Simulate real-time polling (update every 5 minutes)
    setInterval(updateDashboard, 300000);
});