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

        // Gestión de ausencias
        this.initAusencias();
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

    decodeHtml(html) {
        const txt = document.createElement('textarea');
        txt.innerHTML = html || '';
        return txt.value;
    },

    initAusencias() {
        console.log('Inicializando gestión de ausencias...');
        
        // Usar delegación de eventos para el botón, ya que puede no existir al inicio
        document.addEventListener('click', (e) => {
            if (e.target.closest('#btnNuevaAusencia')) {
                e.preventDefault();
                console.log('Botón de nueva ausencia clickeado');
                this.openAusenciaModal();
            }
        });

        // Inicializar modal si existe
        const modalEl = document.getElementById('ausenciaModal');
        if (modalEl) {
            this.ausenciaModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            this.ausenciaModalLabel = document.getElementById('ausenciaModalLabel');
            this.ausenciaForm = document.getElementById('ausenciaForm');
            this.ausenciaError = document.getElementById('ausenciaError');
            this.ausenciaSubmitBtn = document.getElementById('ausenciaSubmitBtn');
            this.ausenciaArchivoActual = document.getElementById('ausenciaArchivoActual');
            this.ausenciaSelect = document.getElementById('ausencia_asistencia');
            this.ausenciaFecha = document.getElementById('ausencia_fecha');
            this.ausenciaJustificacion = document.getElementById('ausencia_justificacion');
            this.ausenciaEstado = document.getElementById('ausencia_estado');
            this.ausenciaDocenteId = document.getElementById('ausencia_docente_id');
            this.ausenciaDocenteDisplay = document.getElementById('ausencia_docente_display');
            this.ausenciaIdInput = document.getElementById('ausencia_id');

            if (this.ausenciaSelect) {
                this.ausenciaSelect.addEventListener('change', () => this.handleAusenciaAsistenciaChange(this.ausenciaSelect));
            }

            if (this.ausenciaForm) {
                this.ausenciaForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.submitAusenciaForm();
                });
            }
        }

        // Delegación de eventos para botones de editar y eliminar
        document.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-editar-ausencia');
            if (editBtn) {
                e.preventDefault();
                this.openAusenciaModal(editBtn.dataset);
            }

            const deleteBtn = e.target.closest('.btn-eliminar-ausencia');
            if (deleteBtn) {
                e.preventDefault();
                this.openDeleteAusenciaModal(deleteBtn.dataset.id);
            }
        });

        // Inicializar modal de eliminación si existe
        const deleteModalEl = document.getElementById('deleteAusenciaModal');
        if (deleteModalEl) {
            this.ausenciaDeleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
            this.ausenciaDeleteForm = document.getElementById('ausenciaDeleteForm');
            this.ausenciaDeleteBtn = document.getElementById('ausenciaDeleteBtn');
            this.ausenciaDeleteIdInput = document.getElementById('ausencia_delete_id');

            if (this.ausenciaDeleteForm) {
                this.ausenciaDeleteForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.submitAusenciaDelete();
                });
            }
        }
    },

    resetAusenciaForm() {
        if (!this.ausenciaForm) return;

        this.ausenciaForm.reset();
        this.clearAusenciaErrors();

        // Borrar errores de validación
        this.ausenciaForm.querySelectorAll('.is-invalid').forEach(field => {
            this.clearFieldError(field);
        });

        if (this.ausenciaArchivoActual) {
            this.ausenciaArchivoActual.classList.add('d-none');
            this.ausenciaArchivoActual.textContent = '';
        }

        if (this.ausenciaDocenteDisplay) {
            this.ausenciaDocenteDisplay.value = '';
        }

        if (this.ausenciaDocenteId) {
            this.ausenciaDocenteId.value = '';
        }

        if (this.ausenciaSelect) {
            this.ausenciaSelect.value = '';
            // Eliminar opciones temporales
            this.ausenciaSelect.querySelectorAll('option[data-temp="true"]').forEach(option => option.remove());
        }
    },

    openAusenciaModal(data = null) {
        console.log('Abriendo modal de ausencia...', data);
        
        // Inicializar modal si no está inicializado
        const modalEl = document.getElementById('ausenciaModal');
        if (!modalEl) {
            console.error('Modal de ausencia no encontrado en el DOM');
            return;
        }

        console.log('Modal encontrado, inicializando...');
        if (!this.ausenciaModal) {
            this.ausenciaModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            console.log('Modal de Bootstrap inicializado');
        }

        // Inicializar elementos del formulario si no están inicializados
        if (!this.ausenciaForm) {
            this.ausenciaForm = document.getElementById('ausenciaForm');
            this.ausenciaModalLabel = document.getElementById('ausenciaModalLabel');
            this.ausenciaError = document.getElementById('ausenciaError');
            this.ausenciaSubmitBtn = document.getElementById('ausenciaSubmitBtn');
            this.ausenciaArchivoActual = document.getElementById('ausenciaArchivoActual');
            this.ausenciaSelect = document.getElementById('ausencia_asistencia');
            this.ausenciaFecha = document.getElementById('ausencia_fecha');
            this.ausenciaJustificacion = document.getElementById('ausencia_justificacion');
            this.ausenciaEstado = document.getElementById('ausencia_estado');
            this.ausenciaDocenteId = document.getElementById('ausencia_docente_id');
            this.ausenciaDocenteDisplay = document.getElementById('ausencia_docente_display');
            this.ausenciaIdInput = document.getElementById('ausencia_id');

            if (this.ausenciaForm) {
                this.ausenciaForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.submitAusenciaForm();
                });
            }

            if (this.ausenciaSelect) {
                this.ausenciaSelect.addEventListener('change', () => this.handleAusenciaAsistenciaChange(this.ausenciaSelect));
            }
        }

        if (!this.ausenciaForm) {
            console.error('Formulario de ausencia no encontrado');
            return;
        }

        const isEdit = data && data.id;
        this.resetAusenciaForm();

        if (this.ausenciaModalLabel) {
            const icon = isEdit ? 'bi-pencil' : 'bi-plus-circle';
            const title = isEdit ? 'Editar justificación' : 'Registrar justificación';
            this.ausenciaModalLabel.innerHTML = `<i class="bi ${icon} me-2"></i>${title}`;
        }

        console.log('Mostrando modal...');
        this.ausenciaModal.show();
        console.log('Modal mostrado');

        if (isEdit) {
            this.ausenciaForm.setAttribute('action', `/ausencias/update/${data.id}`);
            if (this.ausenciaIdInput) {
                this.ausenciaIdInput.value = data.id;
            }

            if (this.ausenciaFecha && data.fecha) {
                this.ausenciaFecha.value = data.fecha;
            }

            if (this.ausenciaJustificacion && typeof data.justificacion !== 'undefined') {
                this.ausenciaJustificacion.value = this.decodeHtml(data.justificacion);
            }

            if (this.ausenciaEstado && data.estado) {
                this.ausenciaEstado.value = data.estado;
            }

            if (this.ausenciaDocenteId && data.docenteId) {
                this.ausenciaDocenteId.value = data.docenteId;
            }
            if (this.ausenciaDocenteDisplay && data.docenteNombre) {
                this.ausenciaDocenteDisplay.value = this.decodeHtml(data.docenteNombre);
            }

            if (this.ausenciaSelect) {
                const asistenciaId = data.asistenciaId || '';
                if (asistenciaId) {
                    let option = this.ausenciaSelect.querySelector(`option[value="${asistenciaId}"]`);
                    if (!option) {
                        option = document.createElement('option');
                        option.value = asistenciaId;
                        option.dataset.temp = 'true';
                        option.dataset.docenteId = data.docenteId || '';
                        option.dataset.docenteNombre = data.docenteNombre || '';
                        option.dataset.fecha = data.fecha || '';
                        option.textContent = `Registro asociado (${data.fecha ? new Date(data.fecha).toLocaleDateString() : 'Asignado'})`;
                        this.ausenciaSelect.insertBefore(option, this.ausenciaSelect.firstChild);
                    }
                    this.ausenciaSelect.value = asistenciaId;
                }
            }

            if (this.ausenciaArchivoActual && data.archivo) {
                this.ausenciaArchivoActual.innerHTML = `Archivo actual: <a href="/ausencias/download/${data.id}" target="_blank" rel="noopener">Descargar soporte</a>`;
                this.ausenciaArchivoActual.classList.remove('d-none');
            }
        } else {
            this.ausenciaForm.setAttribute('action', '/ausencias/store');
        }
    },

    handleAusenciaAsistenciaChange(select) {
        if (!select) return;
        const option = select.options[select.selectedIndex];
        if (!option) {
            return;
        }

        const fecha = option.dataset.fecha || '';
        const docenteId = option.dataset.docenteId || '';
        const docenteNombre = option.dataset.docenteNombre || '';

        if (this.ausenciaFecha && fecha) {
            this.ausenciaFecha.value = fecha;
        }

        if (this.ausenciaDocenteId && docenteId) {
            this.ausenciaDocenteId.value = docenteId;
        }

        if (this.ausenciaDocenteDisplay && docenteNombre) {
            this.ausenciaDocenteDisplay.value = this.decodeHtml(docenteNombre);
        }
    },

    async submitAusenciaForm() {
        if (!this.ausenciaForm) {
            return;
        }

        // Validación básica del formulario
        if (!this.ausenciaForm.checkValidity()) {
            this.ausenciaForm.reportValidity();
            return;
        }

        const action = this.ausenciaForm.getAttribute('action');
        const formData = new FormData(this.ausenciaForm);
        const submitBtn = this.ausenciaSubmitBtn;

        this.clearAusenciaErrors();

        // Validar que haya fecha o asistencia_id
        const asistenciaId = formData.get('asistencia_id');
        const fecha = formData.get('fecha');
        
        if (!asistenciaId && !fecha) {
            this.showAusenciaError('Debes seleccionar un registro de asistencia o especificar una fecha.');
            if (this.ausenciaFecha) {
                this.showFieldError(this.ausenciaFecha, 'Este campo es obligatorio si no seleccionas un registro');
            }
            return;
        }

        // Validar que haya justificación
        const justificacion = formData.get('justificacion');
        if (!justificacion || justificacion.trim() === '') {
            this.showAusenciaError('La justificación es obligatoria.');
            if (this.ausenciaJustificacion) {
                this.showFieldError(this.ausenciaJustificacion, 'Este campo es obligatorio');
            }
            return;
        }

        if (submitBtn) {
            this.showLoading(submitBtn);
        }

        try {
            const response = await fetch(action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (submitBtn) {
                this.hideLoading(submitBtn);
            }

            if (result.success) {
                this.showNotification(result.message || 'Ausencia guardada correctamente', 'success');
                this.ausenciaModal.hide();
                setTimeout(() => window.location.reload(), 800);
            } else {
                this.showAusenciaError(result.message || 'No se pudo guardar la ausencia');
                if (result.errors) {
                    Object.entries(result.errors).forEach(([field, message]) => {
                        const input = this.ausenciaForm.querySelector(`[name="${field}"]`);
                        if (input) {
                            this.showFieldError(input, message);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error guardando ausencia:', error);
            if (submitBtn) {
                this.hideLoading(submitBtn);
            }
            this.showAusenciaError('Ocurrió un error al guardar la ausencia. Intenta nuevamente.');
        }
    },

    openDeleteAusenciaModal(ausenciaId) {
        // Inicializar modal si no está inicializado
        const deleteModalEl = document.getElementById('deleteAusenciaModal');
        if (!deleteModalEl) {
            console.error('Modal de eliminación no encontrado');
            return;
        }

        if (!this.ausenciaDeleteModal) {
            this.ausenciaDeleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
        }

        if (!this.ausenciaDeleteForm) {
            this.ausenciaDeleteForm = document.getElementById('ausenciaDeleteForm');
            this.ausenciaDeleteBtn = document.getElementById('ausenciaDeleteBtn');
            this.ausenciaDeleteIdInput = document.getElementById('ausencia_delete_id');

            if (this.ausenciaDeleteForm) {
                this.ausenciaDeleteForm.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.submitAusenciaDelete();
                });
            }
        }

        if (!this.ausenciaDeleteForm) {
            console.error('Formulario de eliminación no encontrado');
            return;
        }

        this.ausenciaDeleteForm.setAttribute('action', `/ausencias/delete/${ausenciaId}`);
        if (this.ausenciaDeleteIdInput) {
            this.ausenciaDeleteIdInput.value = ausenciaId;
        }
        this.ausenciaDeleteModal.show();
    },

    async submitAusenciaDelete() {
        if (!this.ausenciaDeleteForm) {
            return;
        }

        const action = this.ausenciaDeleteForm.getAttribute('action');
        const formData = new FormData(this.ausenciaDeleteForm);
        const deleteBtn = this.ausenciaDeleteBtn;

        try {
            const response = await fetch(action, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (deleteBtn) {
                this.hideLoading(deleteBtn);
            }

            if (result.success) {
                this.showNotification(result.message || 'Ausencia eliminada correctamente', 'success');
                this.ausenciaDeleteModal.hide();
                setTimeout(() => window.location.reload(), 800);
            } else {
                this.showNotification(result.message || 'No se pudo eliminar la ausencia', 'error');
            }
        } catch (error) {
            console.error('Error eliminando ausencia:', error);
            if (deleteBtn) {
                this.hideLoading(deleteBtn);
            }
            this.showNotification('Ocurrió un error al eliminar la ausencia.', 'error');
        }
    },

    showAusenciaError(message) {
        if (!this.ausenciaError) {
            return;
        }
        this.ausenciaError.textContent = message;
        this.ausenciaError.classList.remove('d-none');
    },

    clearAusenciaErrors() {
        if (this.ausenciaError) {
            this.ausenciaError.classList.add('d-none');
            this.ausenciaError.textContent = '';
        }
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
    console.log('Iniciando carga de gráficos...');
    console.log('Canvas asistenciaChart:', document.getElementById('asistenciaChart'));
    console.log('Canvas asistenciaBarrasChart:', document.getElementById('asistenciaBarrasChart'));
    console.log('Canvas horariosChart:', document.getElementById('horariosChart'));
    
    if (document.getElementById('asistenciaChart')) {
        loadAsistenciaChart();
    }
    if (document.getElementById('asistenciaBarrasChart')) {
        loadAsistenciaBarrasChart();
    }
    if (document.getElementById('horariosChart')) {
        loadHorariosChart();
    }
}

let asistenciaChart = null;
let asistenciaBarrasChart = null;
let horariosChart = null;

function loadAsistenciaChart() {
    const ctx = document.getElementById('asistenciaChart');
    if (!ctx) {
        console.log('Canvas asistenciaChart no encontrado');
        return;
    }
    
    // Destruir gráfico anterior si existe
    if (asistenciaChart) {
        asistenciaChart.destroy();
        asistenciaChart = null;
    }
    
    fetch('/dashboard/chart-data?type=asistencia_mensual')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar datos: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos de asistencia recibidos:', data);
            console.log('Resumen completo:', JSON.stringify(data.resumen, null, 2));
            
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
            } else if (data.datasets && (data.datasets.total || data.datasets.presentes)) {
                // Datos con estructura datasets pero puede tener solo uno
                datasets = [{
                    label: 'Mis Asistencias',
                    data: data.datasets.total || data.datasets.presentes || [],
                    borderColor: App.config.chartColors?.primary || 'rgb(54, 162, 235)',
                    backgroundColor: (App.config.chartColors?.primary || 'rgb(54, 162, 235)') + '20',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }];
            } else {
                // Datos simples (docente view) - fallback
                datasets = [{
                    label: 'Mis Asistencias',
                    data: data.data || [],
                    borderColor: App.config.chartColors?.primary || 'rgb(54, 162, 235)',
                    backgroundColor: (App.config.chartColors?.primary || 'rgb(54, 162, 235)') + '20',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }];
            }
            
            // Asegurar que hay labels y datos
            let labels = data.labels || [];
            if (labels.length === 0 && datasets[0] && datasets[0].data.length > 0) {
                // Si hay datos pero no labels, crear labels genéricos
                for (let i = 0; i < datasets[0].data.length; i++) {
                    labels.push('Día ' + (i + 1));
                }
            }
            
            // Si no hay datos, mostrar un mensaje o gráfico vacío
            if (labels.length === 0 || (datasets[0] && datasets[0].data.length === 0)) {
                labels = ['No hay datos'];
                datasets = [{
                    label: 'Sin asistencias',
                    data: [0],
                    borderColor: 'rgb(200, 200, 200)',
                    backgroundColor: 'rgba(200, 200, 200, 0.2)',
                    borderWidth: 2,
                    fill: true
                }];
            }
            
            console.log('Creando gráfico de línea con labels:', labels, 'datasets:', datasets);
            console.log('Chart disponible:', typeof Chart !== 'undefined');
            console.log('Context del canvas:', ctx);
            console.log('Ancho del canvas:', ctx.width, 'Alto del canvas:', ctx.height);
            
            if (typeof Chart === 'undefined') {
                console.error('Chart.js no está disponible');
                if (ctx && ctx.parentElement) {
                    ctx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-exclamation-triangle me-2"></i>Chart.js no está disponible</div>';
                }
                return;
            }
            
            // Asegurar que el canvas tenga dimensiones
            if (!ctx.width || !ctx.height) {
                console.warn('Canvas sin dimensiones, estableciendo valores por defecto');
                ctx.width = ctx.parentElement ? ctx.parentElement.clientWidth : 400;
                ctx.height = 250;
            }
            
            try {
                // Destruir gráfico anterior si existe
                if (asistenciaChart) {
                    asistenciaChart.destroy();
                    asistenciaChart = null;
                }
                
                asistenciaChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1000
                        },
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
                console.log('Gráfico creado:', asistenciaChart);
            } catch (error) {
                console.error('Error al crear el gráfico de línea:', error);
                console.error('Stack trace:', error.stack);
                if (ctx && ctx.parentElement) {
                    ctx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-exclamation-triangle me-2"></i>Error al crear el gráfico: ' + error.message + '</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading chart:', error);
            // Crear gráfico con datos vacíos en caso de error
            const labels = ['No hay datos'];
            const datasets = [{
                label: 'Sin asistencias',
                data: [0],
                borderColor: 'rgb(200, 200, 200)',
                backgroundColor: 'rgba(200, 200, 200, 0.2)',
                borderWidth: 2,
                fill: true
            }];
            
            try {
                asistenciaChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } catch (chartError) {
                console.error('Error al crear gráfico de línea con datos vacíos:', chartError);
                if (ctx && ctx.parentElement) {
                    ctx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-exclamation-triangle me-2"></i>No se pudieron cargar los datos de asistencia</div>';
                }
            }
        });
}

function loadAsistenciaBarrasChart() {
    const ctx = document.getElementById('asistenciaBarrasChart');
    if (!ctx) {
        console.log('Canvas asistenciaBarrasChart no encontrado');
        return;
    }
    
    // Destruir gráfico anterior si existe
    if (asistenciaBarrasChart) {
        asistenciaBarrasChart.destroy();
        asistenciaBarrasChart = null;
    }
    
    fetch('/dashboard/chart-data?type=asistencia_mensual')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar datos: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos para gráfico de barras:', data);
            
            // Preparar datos para el gráfico de barras
            const resumen = data.resumen || {};
            const porcentajes = resumen.porcentajes || {
                presente: 0,
                tardanza: 0,
                asistido_tarde: 0,
                incumplido: 0
            };
            
            const labels = ['Presentes', 'Tardanzas', 'Asistido Tarde', 'Incumplidos'];
            const datos = [
                parseFloat(porcentajes.presente) || 0,
                parseFloat(porcentajes.tardanza) || 0,
                parseFloat(porcentajes.asistido_tarde) || 0,
                parseFloat(porcentajes.incumplido) || 0
            ];
            
            const colores = [
                'rgba(40, 167, 69, 0.8)',    // Verde para Presentes
                'rgba(255, 193, 7, 0.8)',     // Amarillo para Tardanzas
                'rgba(255, 152, 0, 0.8)',     // Naranja para Asistido Tarde
                'rgba(220, 53, 69, 0.8)'      // Rojo para Incumplidos
            ];
            
            const coloresBorde = [
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(255, 152, 0, 1)',
                'rgba(220, 53, 69, 1)'
            ];
            
            console.log('Creando gráfico de barras con datos:', datos);
            console.log('Chart disponible:', typeof Chart !== 'undefined');
            console.log('Context del canvas:', ctx);
            console.log('Ancho del canvas:', ctx.width, 'Alto del canvas:', ctx.height);
            
            if (typeof Chart === 'undefined') {
                console.error('Chart.js no está disponible');
                if (ctx && ctx.parentElement) {
                    ctx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-exclamation-triangle me-2"></i>Chart.js no está disponible</div>';
                }
                return;
            }
            
            // Asegurar que el canvas tenga dimensiones
            if (!ctx.width || !ctx.height) {
                console.warn('Canvas sin dimensiones, estableciendo valores por defecto');
                ctx.width = ctx.parentElement ? ctx.parentElement.clientWidth : 400;
                ctx.height = 250;
            }
            
            try {
                // Destruir gráfico anterior si existe
                if (asistenciaBarrasChart) {
                    asistenciaBarrasChart.destroy();
                    asistenciaBarrasChart = null;
                }
                
                asistenciaBarrasChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Porcentaje (%)',
                            data: datos,
                            backgroundColor: colores,
                            borderColor: coloresBorde,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1000
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed.y || 0;
                                        const total = resumen.total || 0;
                                        const cantidad = Math.round((value / 100) * total);
                                        return label + ': ' + value.toFixed(1) + '% (' + cantidad + ' de ' + total + ')';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                },
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
                console.log('Gráfico de barras de asistencia cargado correctamente');
                console.log('Gráfico creado:', asistenciaBarrasChart);
            } catch (error) {
                console.error('Error al crear el gráfico de barras:', error);
                console.error('Stack trace:', error.stack);
                if (ctx && ctx.parentElement) {
                    ctx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-exclamation-triangle me-2"></i>Error al crear el gráfico: ' + error.message + '</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading bar chart:', error);
            // Crear gráfico con datos vacíos en caso de error
            const labels = ['Presentes', 'Tardanzas', 'Asistido Tarde', 'Incumplidos'];
            const datos = [0, 0, 0, 0];
            
            try {
                asistenciaBarrasChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Porcentaje (%)',
                            data: datos,
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(255, 152, 0, 0.8)',
                                'rgba(220, 53, 69, 0.8)'
                            ],
                            borderColor: [
                                'rgba(40, 167, 69, 1)',
                                'rgba(255, 193, 7, 1)',
                                'rgba(255, 152, 0, 1)',
                                'rgba(220, 53, 69, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (chartError) {
                console.error('Error al crear gráfico de barras con datos vacíos:', chartError);
                if (ctx && ctx.parentElement) {
                    ctx.parentElement.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-exclamation-triangle me-2"></i>No se pudieron cargar los datos de asistencia</div>';
                }
            }
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
        loadAsistenciaBarrasChart();
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

// Verificar que Chart.js esté disponible
function waitForChartJS(callback, maxAttempts = 50) {
    let attempts = 0;
    const checkChart = setInterval(function() {
        attempts++;
        if (typeof Chart !== 'undefined') {
            clearInterval(checkChart);
            console.log('Chart.js cargado correctamente');
            callback();
        } else if (attempts >= maxAttempts) {
            clearInterval(checkChart);
            console.error('Chart.js no se pudo cargar después de ' + maxAttempts + ' intentos');
        }
    }, 100);
}

// Inicializar aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    App.init();
    
    // Inicializar ausencias después de que todo esté cargado
    setTimeout(function() {
        if (typeof App.initAusencias === 'function') {
            App.initAusencias();
        }
    }, 100);
    
    // Cargar componentes específicos del dashboard
    if (document.getElementById('asistenciaChart') || document.getElementById('asistenciaBarrasChart')) {
        waitForChartJS(function() {
            // Esperar un poco más para asegurar que todo esté listo
            setTimeout(function() {
                console.log('Ejecutando loadCharts después de esperar Chart.js...');
                loadCharts();
                if (typeof loadRecentActivities === 'function') {
                    loadRecentActivities();
                }
            }, 200);
        });
    }
});