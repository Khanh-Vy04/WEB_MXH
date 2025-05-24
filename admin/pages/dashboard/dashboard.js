(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Sidebar Toggler
    $('.sidebar-toggler').click(function () {
        $('.sidebar, .content').toggleClass("open");
        return false;
    });


    // Progress Bar
    $('.pg-bar').waypoint(function () {
        $('.progress .progress-bar').each(function () {
            $(this).css("width", $(this).attr("aria-valuenow") + '%');
        });
    }, {offset: '80%'});


    // Calender
    $('#calender').datetimepicker({
        inline: true,
        format: 'L'
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        items: 1,
        dots: true,
        loop: true,
        nav : false
    });


    // Chart Global Color
    Chart.defaults.color = "#6C7293";
    Chart.defaults.borderColor = "#000000";



    // Salse & Revenue Chart
    

    // Single Line Chart
    var lineChartCanvas = document.getElementById("line-chart");
    if (lineChartCanvas) {
        var ctx3 = lineChartCanvas.getContext("2d");
        var myChart3 = new Chart(ctx3, {
            type: "line",
            data: {
                labels: [50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150],
                datasets: [{
                    label: "Salse",
                    fill: false,
                    backgroundColor: "rgba(235, 22, 22, .7)",
                    data: [7, 8, 8, 9, 9, 9, 10, 11, 14, 14, 15]
                }]
            },
            options: {
                responsive: true
            }
        });
    }


    // Single Bar Chart
    var barChartCanvas = document.getElementById("bar-chart");
    if (barChartCanvas) {
        var ctx4 = barChartCanvas.getContext("2d");
        var myChart4 = new Chart(ctx4, {
            type: "bar",
            data: {
                labels: ["Italy", "France", "Spain", "USA", "Argentina"],
                datasets: [{
                    backgroundColor: [
                        "rgba(235, 22, 22, .7)",
                        "rgba(235, 22, 22, .6)",
                        "rgba(235, 22, 22, .5)",
                        "rgba(235, 22, 22, .4)",
                        "rgba(235, 22, 22, .3)"
                    ],
                    data: [55, 49, 44, 24, 15]
                }]
            },
            options: {
                responsive: true
            }
        });
    }


    // Pie Chart
    var pieChartCanvas = document.getElementById("pie-chart");
    if (pieChartCanvas) {
        var ctx5 = pieChartCanvas.getContext("2d");
        var myChart5 = new Chart(ctx5, {
            type: "pie",
            data: {
                labels: ["Italy", "France", "Spain", "USA", "Argentina"],
                datasets: [{
                    backgroundColor: [
                        "rgba(235, 22, 22, .7)",
                        "rgba(235, 22, 22, .6)",
                        "rgba(235, 22, 22, .5)",
                        "rgba(235, 22, 22, .4)",
                        "rgba(235, 22, 22, .3)"
                    ],
                    data: [55, 49, 44, 24, 15]
                }]
            },
            options: {
                responsive: true
            }
        });
    }


    // Doughnut Chart
    var doughnutChartCanvas = document.getElementById("doughnut-chart");
    if (doughnutChartCanvas) {
        var ctx6 = doughnutChartCanvas.getContext("2d");
        var myChart6 = new Chart(ctx6, {
            type: "doughnut",
            data: {
                labels: ["Italy", "France", "Spain", "USA", "Argentina"],
                datasets: [{
                    backgroundColor: [
                        "rgba(235, 22, 22, .7)",
                        "rgba(235, 22, 22, .6)",
                        "rgba(235, 22, 22, .5)",
                        "rgba(235, 22, 22, .4)",
                        "rgba(235, 22, 22, .3)"
                    ],
                    data: [55, 49, 44, 24, 15]
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    
})(jQuery);


// ===== SALES & REVENUE =====
function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

function updateDashboardStats() {
    // Khi chưa kết nối PHP, giữ nguyên số mặc định trong HTML
    
    // Khi đã kết nối PHP, uncomment đoạn code dưới đây:
    /*
    fetch('get_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('today-sale').textContent = formatMoney(data.today_sale);
            document.getElementById('total-sale').textContent = formatMoney(data.total_sale);
            document.getElementById('today-revenue').textContent = formatMoney(data.today_revenue);
            document.getElementById('total-revenue').textContent = formatMoney(data.total_revenue);
        })
        .catch(error => {
            console.error('Error fetching dashboard stats:', error);
        });
    */
}

// ===== WORLDWIDE SALES CHART =====
function initWorldwideSalesChart() {
    // Khi CHƯA có backend: dùng dữ liệu mẫu
    var productLabels = ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7"];
    var topProducts = [
        {
            name: 'Loa Lớn',
            data: [12, 19, 3, 5, 2, 3, 8]
        },
        {
            name: 'Loa Mini',
            data: [8, 11, 7, 6, 9, 10, 5]
        },
        {
            name: 'Radio',
            data: [5, 7, 8, 6, 7, 8, 9]
        }
    ];

    // Khởi tạo biểu đồ
    var canvas = document.getElementById("worldwide-sales");
    if (canvas) {
        var ctx1 = canvas.getContext("2d");
        window.myChart1 = new Chart(ctx1, {
            type: "bar",
            data: {
                labels: productLabels,
                datasets: topProducts.map(function(product, idx) {
                    return {
                        label: product.name,
                        data: product.data,
                        backgroundColor: [
                            '#683257',   
                            '#cf8ba3',  
                            '#d7a6b3'   
                        ][idx],
                        borderWidth: 1
                    };
                })
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: '#412d3b' }
                    }
                },
                scales: {
                    x: { ticks: { color: '#412d3b' } },
                    y: { beginAtZero: true, ticks: { color: '#412d3b' } }
                }
            }
        });
    }
}
/* có backend
function initWorldwideSalesChart() {
    var canvas = document.getElementById("worldwide-sales");
    if (!canvas) return;
    var ctx1 = canvas.getContext("2d");

    // Gọi API backend để lấy dữ liệu
    fetch('get_top_products_stats.php')
        .then(response => response.json())
        .then(data => {
            // data.labels: mảng tên tháng
            // data.products: mảng 3 sản phẩm, mỗi sản phẩm có name và data
            var chartData = {
                labels: data.labels,
                datasets: data.products.map(function(product, idx) {
                    return {
                        label: product.name,
                        data: product.data,
                        backgroundColor: [
                            'rgba(235, 22, 22, .7)',
                            'rgba(235, 22, 22, .5)',
                            'rgba(235, 22, 22, .3)'
                        ][idx],
                        borderWidth: 1
                    };
                })
            };

            // Nếu đã có biểu đồ thì cập nhật, chưa có thì tạo mới
            if (window.myChart1) {
                window.myChart1.data = chartData;
                window.myChart1.update();
            } else {
                window.myChart1 = new Chart(ctx1, {
                    type: "bar",
                    data: chartData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                labels: { color: '#fff' }
                            }
                        },
                        scales: {
                            x: { ticks: { color: '#fff' } },
                            y: { beginAtZero: true, ticks: { color: '#fff' } }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Lỗi khi lấy dữ liệu biểu đồ:', error);
        });
}
*/

// ===== SALES & REVENUE CHART =====
function initSalesRevenueChart() {
    // Code xử lý biểu đồ Sales & Revenue
    var salseRevenueCanvas = document.getElementById("salse-revenue");
    if (salseRevenueCanvas) {
        var ctx2 = salseRevenueCanvas.getContext("2d");
        var myChart2 = new Chart(ctx2, {
            type: "line",
            data: {
                labels: ["2016", "2017", "2018", "2019", "2020", "2021", "2022"],
                datasets: [{
                        label: "Salse",
                        data: [15, 30, 55, 45, 70, 65, 85],
                        backgroundColor: "rgba(104, 50, 87, 0.7)",
                        borderWidth: 1,
                        fill: true
                    },
                    {
                        label: "Revenue",
                        data: [99, 135, 170, 130, 190, 180, 270],
                        backgroundColor: "rgba(207, 139, 163, .7)",
                        borderWidth: 1,
                        fill: true
                    }
                ]
                },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: "#191C24", // Màu chữ legend
                        }
                    }
                },
                scales: {
                    x: { ticks: { color: "#191C24" } },
                    y: { ticks: { color: "#191C24" } }
                }
            }
        });
    }
}

// ===== RECENT SALES TABLE =====
function initRecentSalesTable() {
    // Code xử lý bảng Recent Sales
    // Ví dụ: phân trang, sắp xếp, lọc...
}

// ===== EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo tất cả các component khi trang load xong
    updateDashboardStats();
    initWorldwideSalesChart();
    initSalesRevenueChart();
    initRecentSalesTable();
});

// ===== AUTO UPDATE =====
// Cập nhật số liệu mỗi 5 phút
setInterval(updateDashboardStats, 5 * 60 * 1000);

// Handle sidebar dropdowns
$(document).ready(function() {
    // Handle sidebar dropdowns
    $('.sidebar .nav-link.dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).next('.dropdown-menu').toggleClass('show');
        $(this).attr('aria-expanded', function(i, attr) {
            return attr === 'true' ? 'false' : 'true';
        });
    });

    // Close sidebar dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.nav-item.dropdown').length) {
            $('.sidebar .dropdown-menu').removeClass('show');
            $('.sidebar .dropdown-toggle').attr('aria-expanded', 'false');
        }
    });
});