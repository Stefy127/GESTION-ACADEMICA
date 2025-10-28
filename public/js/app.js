// Aplicación de Gestión Académica - JavaScript Moderno

// Configuración global
const App = {
    config: {
        apiUrl: '/api',
        chartColors: {
            primary: '#6366f1',
            secondary: '#64748b',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#06b6d4'
        }
    },
    
    // Inicialización
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupNotifications();
    },
    
    // Configurar event listeners
    setupEventListeners() {
        // Formularios con validación en tiempo real
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
        });
        
        // Botones de acción
        document.querySelectorAll('[data-action]').forEach(button => {
            button.addEventListener('click', this.handleAction.bind(this));
        });
        
        // Auto-hide alerts
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                this.fadeOut(alert);
            }, 5000);
        });
    },
    
    // Inicializar componentes
    initializeComponents() {
        // Tooltips
        this.initTooltips();
        
        // Modales
        this.initModals();
        
        // Tablas con filtros
        this.initDataTables();
        
        // Gráficos
        this.initCharts();
    },
    
    // Configurar notificaciones
    setupNotifications() {
        this.notificationContainer = document.createElement('div');
        this.notificationContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        this.notificationContainer.style.zIndex = '9999';
        document.body.appendChild(this.notificationContainer);
    },
    
    // Mostrar notificación
    showNotification(message, type = 'info', duration = 5000) {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert">
                <div class="toast-header">
                    <i class="bi bi-${this.getIconForType(type)} text-${type} me-2"></i>
                    <strong class="me-auto">${this.getTitleForType(type)}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        this.notificationContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });
        
        toast.show();
        
        // Limpiar después de que se oculte
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    },
    
    // Obtener icono según tipo
    getIconForType(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    },
    
    // Obtener título según tipo
    getTitleForType(type) {
        const titles = {
            success: 'Éxito',
            error: 'Error',
            warning: 'Advertencia',
            info: 'Información'
        };
        return titles[type] || 'Notificación';
    },
    
    // Manejar envío de formularios
    handleFormSubmit(event) {
        const form = event.target;
        const formData = new FormData(form);
        
        // Validación básica
        if (!this.validateForm(form)) {
            event.preventDefault();
            return false;
        }
        
        // Mostrar loading
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            this.showLoading(submitButton);
        }
        
        // Si es AJAX
        if (form.dataset.ajax === 'true') {
            event.preventDefault();
            this.submitFormAjax(form, formData);
        }
    },
    
    // Validar formulario
    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Este campo es obligatorio');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });
        
        // Validación de email
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                this.showFieldError(field, 'Ingrese un email válido');
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // Mostrar error en campo
    showFieldError(field, message) {
        this.clearFieldError(field);
        
        field.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    },
    
    // Limpiar error de campo
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    },
    
    // Validar email
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    // Mostrar loading en botón
    showLoading(button) {
        const originalText = button.innerHTML;
        button.dataset.originalText = originalText;
        button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Cargando...';
        button.disabled = true;
    },
    
    // Ocultar loading en botón
    hideLoading(button) {
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
            button.disabled = false;
        }
    },
    
    // Manejar acciones
    handleAction(event) {
        const button = event.target.closest('[data-action]');
        const action = button.dataset.action;
        
        switch (action) {
            case 'delete':
                this.confirmDelete(button);
                break;
            case 'edit':
                this.editItem(button);
                break;
            case 'view':
                this.viewItem(button);
                break;
            default:
                console.log('Acción no reconocida:', action);
        }
    },
    
    // Confirmar eliminación
    confirmDelete(button) {
        const itemName = button.dataset.itemName || 'este elemento';
        
        if (confirm(`¿Está seguro de que desea eliminar ${itemName}?`)) {
            this.executeDelete(button);
        }
    },
    
    // Ejecutar eliminación
    async executeDelete(button) {
        const url = button.dataset.url;
        
        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Elemento eliminado correctamente', 'success');
                
                // Remover fila de la tabla
                const row = button.closest('tr');
                if (row) {
                    this.fadeOut(row, () => row.remove());
                }
            } else {
                this.showNotification(result.message || 'Error al eliminar', 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error de conexión', 'error');
        }
    },
    
    // Efecto fade out
    fadeOut(element, callback) {
        element.style.transition = 'opacity 0.3s ease';
        element.style.opacity = '0';
        
        setTimeout(() => {
            if (callback) callback();
        }, 300);
    },
    
    // Inicializar tooltips
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    },
    
    // Inicializar modales
    initModals() {
        // Configuración global de modales
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', () => {
                // Focus en el primer input
                const firstInput = modal.querySelector('input, textarea, select');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });
    },
    
    // Inicializar tablas de datos
    initDataTables() {
        // Implementación básica de filtros
        document.querySelectorAll('.data-table').forEach(table => {
            this.addTableFilters(table);
        });
    },
    
    // Agregar filtros a tabla
    addTableFilters(table) {
        const headers = table.querySelectorAll('th[data-filter]');
        
        headers.forEach(header => {
            const filterType = header.dataset.filter;
            const columnIndex = Array.from(header.parentNode.children).indexOf(header);
            
            // Crear input de filtro
            const filterInput = document.createElement('input');
            filterInput.type = 'text';
            filterInput.className = 'form-control form-control-sm';
            filterInput.placeholder = `Filtrar ${header.textContent.trim()}`;
            
            // Insertar después del header
            header.parentNode.insertBefore(filterInput, header.nextSibling);
            
            // Event listener para filtro
            filterInput.addEventListener('input', () => {
                this.filterTable(table, columnIndex, filterInput.value);
            });
        });
    },
    
    // Filtrar tabla
    filterTable(table, columnIndex, filterValue) {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cell = row.children[columnIndex];
            if (cell) {
                const cellText = cell.textContent.toLowerCase();
                const shouldShow = cellText.includes(filterValue.toLowerCase());
                row.style.display = shouldShow ? '' : 'none';
            }
        });
    },
    
    // Inicializar gráficos
    initCharts() {
        // Los gráficos se inicializan en el dashboard
        if (typeof loadCharts === 'function') {
            loadCharts();
        }
    }
};

// Funciones específicas del Dashboard
function loadCharts() {
    loadAsistenciaChart();
    loadHorariosChart();
}

let asistenciaChart = null;
let horariosChart = null;

function loadAsistenciaChart() {
    const ctx = document.getElementById('asistenciaChart');
    if (!ctx) return;
    
    // Destruir gráfico anterior si existe
    if (asistenciaChart) {
        asistenciaChart.destroy();
        asistenciaChart = null;
    }
    
    fetch('/dashboard/chart-data?type=asistencia_mensual')
        .then(response => response.json())
        .then(data => {
            console.log('Datos de asistencia recibidos:', data);
            
            // Preparar datasets según el tipo de datos
            let datasets = [];
            
            if (data.datasets && data.datasets.total && data.datasets.presentes) {
                // Datos con múltiples datasets (admin view)
                datasets = [
                    {
                        label: 'Total Clases',
                        data: data.datasets.total,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'Con Asistencia',
                        data: data.datasets.presentes,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    }
                ];
            } else {
                // Datos simples (docente view)
                datasets = [{
                    label: 'Mis Asistencias',
                    data: data.datasets?.total || data.data || [],
                    borderColor: App.config.chartColors.primary,
                    backgroundColor: App.config.chartColors.primary + '20',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }];
            }
            
            asistenciaChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: datasets.length > 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            console.log('Gráfico de asistencia cargado correctamente');
        })
        .catch(error => {
            console.error('Error loading chart:', error);
        });
}

function loadHorariosChart() {
    const ctx = document.getElementById('horariosChart');
    if (!ctx) return;
    
    // Destruir gráfico anterior si existe
    if (horariosChart) {
        horariosChart.destroy();
        horariosChart = null;
    }
    
    fetch('/dashboard/chart-data?type=horarios_por_dia')
        .then(response => response.json())
        .then(data => {
            console.log('Datos de horarios recibidos:', data);
            
            horariosChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        data: data.data || [],
                        backgroundColor: [
                            App.config.chartColors.primary,
                            App.config.chartColors.success,
                            App.config.chartColors.warning,
                            App.config.chartColors.info,
                            App.config.chartColors.danger
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
            console.log('Gráfico de horarios cargado correctamente');
        })
        .catch(error => {
            console.error('Error loading chart:', error);
        });
}

function loadRecentActivities() {
    const tbody = document.getElementById('activitiesTableBody');
    if (!tbody) return;
    
    // Simular datos de actividades
    const activities = [
        { user: 'Admin Sistema', action: 'Inició sesión', date: 'Hace 5 minutos', ip: '192.168.1.100' },
        { user: 'Juan Pérez', action: 'Registró asistencia', date: 'Hace 15 minutos', ip: '192.168.1.101' },
        { user: 'María González', action: 'Actualizó horario', date: 'Hace 1 hora', ip: '192.168.1.102' },
        { user: 'Carlos López', action: 'Creó nuevo grupo', date: 'Hace 2 horas', ip: '192.168.1.103' }
    ];
    
    tbody.innerHTML = activities.map(activity => `
        <tr>
            <td class="ps-4">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                            ${activity.user.split(' ').map(n => n[0]).join('')}
                        </div>
                    </div>
                    <div>
                        <div class="fw-semibold">${activity.user}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-light text-dark">${activity.action}</span>
            </td>
            <td>
                <small class="text-muted">${activity.date}</small>
            </td>
            <td>
                <code class="text-muted">${activity.ip}</code>
            </td>
        </tr>
    `).join('');
}

function refreshDashboard() {
    App.showNotification('Actualizando dashboard...', 'info', 2000);
    
    // Recargar gráficos
    loadCharts();
    
    // Recargar actividades
    loadRecentActivities();
    
    setTimeout(() => {
        App.showNotification('Dashboard actualizado', 'success', 2000);
    }, 1000);
}

function changePeriod(period) {
    App.showNotification(`Cambiando período a: ${period}`, 'info', 2000);
    // Implementar lógica de cambio de período
}

function refreshChart(chartType) {
    App.showNotification(`Actualizando gráfico: ${chartType}`, 'info', 2000);
    
    if (chartType === 'asistencia_mensual') {
        loadAsistenciaChart();
    }
}

function refreshActivities() {
    App.showNotification('Actualizando actividades...', 'info', 2000);
    loadRecentActivities();
}

function exportChart(chartType) {
    App.showNotification(`Exportando gráfico: ${chartType}`, 'info', 2000);
    // Implementar exportación de gráficos
}

function exportActivities() {
    App.showNotification('Exportando actividades...', 'info', 2000);
    // Implementar exportación de actividades
}

// Inicializar aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    App.init();
    
    // Cargar componentes específicos del dashboard
    if (document.getElementById('asistenciaChart')) {
        loadCharts();
        loadRecentActivities();
    }
});