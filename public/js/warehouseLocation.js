/**
 * Warehouse Location Selector
 * Sistem select bertingkat: Warehouse → Zone → Rack
 * 
 * @version 1.0.0
 * @author Your Name
 */

class WarehouseLocationSelector {
    constructor(options = {}) {
        // Default configuration
        this.config = {
            // API endpoint
            apiUrl: options.apiUrl || '/warehouses/get-location',
            
            // Element IDs
            warehouseSelectId: options.warehouseSelectId || 'warehouse_id',
            zoneSelectId: options.zoneSelectId || 'zone_id',
            rackSelectId: options.rackSelectId || 'rack_id',
            
            // Status icon IDs
            warehouseStatusId: options.warehouseStatusId || 'warehouse-status',
            zoneStatusId: options.zoneStatusId || 'zone-status',
            rackStatusId: options.rackStatusId || 'rack-status',
            
            // Helper text IDs
            warehouseHelperId: options.warehouseHelperId || 'warehouse-helper',
            zoneHelperId: options.zoneHelperId || 'zone-helper',
            rackHelperId: options.rackHelperId || 'rack-helper',
            
            // Edit mode
            isEditMode: options.isEditMode || false,
            currentRackId: options.currentRackId || null,
            
            // Initial values (untuk edit mode)
            initialWarehouseId: options.initialWarehouseId || null,
            initialZoneId: options.initialZoneId || null,
            initialRackId: options.initialRackId || null,
            
            // Callbacks
            onWarehouseChange: options.onWarehouseChange || null,
            onZoneChange: options.onZoneChange || null,
            onRackChange: options.onRackChange || null,
            onError: options.onError || null,
            
            // Use Select2
            useSelect2: options.useSelect2 !== undefined ? options.useSelect2 : true,
            
            // Show SweetAlert
            showAlerts: options.showAlerts !== undefined ? options.showAlerts : true,
            
            // Debug mode
            debug: options.debug || false
        };
        
        // Cache jQuery selectors
        this.elements = {
            warehouse: $(`#${this.config.warehouseSelectId}`),
            zone: $(`#${this.config.zoneSelectId}`),
            rack: $(`#${this.config.rackSelectId}`),
            warehouseStatus: $(`#${this.config.warehouseStatusId} i`),
            zoneStatus: $(`#${this.config.zoneStatusId} i`),
            rackStatus: $(`#${this.config.rackStatusId} i`),
            warehouseHelper: $(`#${this.config.warehouseHelperId}`),
            zoneHelper: $(`#${this.config.zoneHelperId}`),
            rackHelper: $(`#${this.config.rackHelperId}`)
        };
        
        this.init();
    }
    
    /**
     * Initialize the selector
     */
    init() {
        this.log('Initializing Warehouse Location Selector...');
        
        // Initialize Select2 if enabled
        if (this.config.useSelect2 && $.fn.select2) {
            this.elements.warehouse.select2({ width: '100%' });
            this.elements.zone.select2({ width: '100%' });
            this.elements.rack.select2({ width: '100%' });
            this.log('Select2 initialized');
        }
        
        // Attach event handlers
        this.attachEvents();
        
        // Load initial warehouses
        this.loadWarehouses();
    }
    
    /**
     * Attach event handlers
     */
    attachEvents() {
        this.elements.warehouse.on('change', () => this.handleWarehouseChange());
        this.elements.zone.on('change', () => this.handleZoneChange());
        this.elements.rack.on('change', () => this.handleRackChange());
    }
    
    /**
     * Load warehouses
     */
    loadWarehouses() {
        this.log('Loading warehouses...');
        
        $.ajax({
            url: this.config.apiUrl,
            method: 'GET',
            success: (response) => {
                this.log('Warehouses loaded:', response);
                
                if (response.success && response.data.length > 0) {
                    this.populateWarehouse(response.data);
                    this.updateStatus('warehouse', 'success', `${response.total} warehouse tersedia`);
                    
                    // Auto-select for edit mode
                    if (this.config.isEditMode && this.config.initialWarehouseId) {
                        this.elements.warehouse.val(this.config.initialWarehouseId).trigger('change');
                    }
                } else {
                    this.populateWarehouse([]);
                    this.updateStatus('warehouse', 'warning', 'Belum ada warehouse');
                    this.elements.warehouse.prop('disabled', true);
                }
            },
            error: (xhr) => {
                this.log('Error loading warehouses:', xhr);
                this.updateStatus('warehouse', 'error', 'Gagal memuat warehouse');
                this.handleError('warehouse', xhr);
            }
        });
    }
    
    /**
     * Handle warehouse change
     */
    handleWarehouseChange() {
        const warehouseId = this.elements.warehouse.val();
        this.log('Warehouse changed:', warehouseId);
        
        // Reset zone and rack
        this.resetSelect('zone');
        this.resetSelect('rack');
        
        if (!warehouseId) {
            this.updateStatus('warehouse', 'default', 'Pilih warehouse terlebih dahulu');
            return;
        }
        
        this.updateStatus('warehouse', 'success', '');
        
        // Load zones
        this.loadZones(warehouseId);
        
        // Callback
        if (this.config.onWarehouseChange) {
            this.config.onWarehouseChange(warehouseId);
        }
    }
    
    /**
     * Load zones
     */
    loadZones(warehouseId) {
        this.log('Loading zones for warehouse:', warehouseId);
        
        this.updateStatus('zone', 'loading', 'Memuat zones...');
        this.elements.zone.prop('disabled', true);
        
        $.ajax({
            url: this.config.apiUrl,
            method: 'GET',
            data: { warehouse_id: warehouseId },
            success: (response) => {
                this.log('Zones loaded:', response);
                
                if (response.success && response.data.length > 0) {
                    this.populateZone(response.data);
                    this.updateStatus('zone', 'success', `${response.total} zone tersedia`);
                    this.elements.zone.prop('disabled', false);
                    
                    // Auto-select for edit mode
                    if (this.config.isEditMode && this.config.initialZoneId) {
                        this.elements.zone.val(this.config.initialZoneId).trigger('change');
                    }
                } else {
                    this.populateZone([]);
                    this.updateStatus('zone', 'warning', response.message || 'Tidak ada zone');
                    this.elements.zone.prop('disabled', true);
                    
                    if (this.config.showAlerts && typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ada Zone',
                            text: response.message,
                            confirmButtonColor: '#007bff'
                        });
                    }
                }
            },
            error: (xhr) => {
                this.log('Error loading zones:', xhr);
                this.updateStatus('zone', 'error', 'Gagal memuat zones');
                this.elements.zone.prop('disabled', true);
                this.handleError('zone', xhr);
            }
        });
    }
    
    /**
     * Handle zone change
     */
    handleZoneChange() {
        const zoneId = this.elements.zone.val();
        const warehouseId = this.elements.warehouse.val();
        
        this.log('Zone changed:', zoneId);
        
        // Reset rack
        this.resetSelect('rack');
        
        if (!zoneId || !warehouseId) {
            if (zoneId) {
                this.updateStatus('zone', 'success', '');
            }
            return;
        }
        
        this.updateStatus('zone', 'success', '');
        
        // Load racks
        this.loadRacks(warehouseId, zoneId);
        
        // Callback
        if (this.config.onZoneChange) {
            this.config.onZoneChange(zoneId);
        }
    }
    
    /**
     * Load racks
     */
    loadRacks(warehouseId, zoneId) {
        this.log('Loading racks for zone:', zoneId);
        
        this.updateStatus('rack', 'loading', 'Memuat racks...');
        this.elements.rack.prop('disabled', true);
        
        // Prepare request data
        let requestData = {
            warehouse_id: warehouseId,
            zone_id: zoneId
        };
        
        // EDIT MODE: Exclude current rack
        if (this.config.isEditMode && this.config.currentRackId) {
            requestData.exclude_rack_id = this.config.currentRackId;
        }
        
        $.ajax({
            url: this.config.apiUrl,
            method: 'GET',
            data: requestData,
            success: (response) => {
                this.log('Racks loaded:', response);
                
                if (response.success && response.data.length > 0) {
                    this.populateRack(response.data);
                    
                    let helperText = `${response.total} rack tersedia`;
                    if (response.is_edit_mode) {
                        helperText += ' (rack saat ini dikecualikan)';
                    }
                    
                    this.updateStatus('rack', 'success', helperText);
                    this.elements.rack.prop('disabled', false);
                    
                } else {
                    this.populateRack([]);
                    this.updateStatus('rack', 'warning', response.message || 'Tidak ada rack');
                    this.elements.rack.prop('disabled', true);
                    
                    // Only show alert if not in edit mode
                    if (this.config.showAlerts && !response.is_edit_mode && typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ada Rack',
                            text: response.message,
                            confirmButtonColor: '#007bff'
                        });
                    }
                }
            },
            error: (xhr) => {
                this.log('Error loading racks:', xhr);
                this.updateStatus('rack', 'error', 'Gagal memuat racks');
                this.elements.rack.prop('disabled', true);
                this.handleError('rack', xhr);
            }
        });
    }
    
    /**
     * Handle rack change
     */
    handleRackChange() {
        const rackId = this.elements.rack.val();
        this.log('Rack changed:', rackId);
        
        if (rackId) {
            this.updateStatus('rack', 'success', '');
        } else {
            this.updateStatus('rack', 'default', 'Pilih rack');
        }
        
        // Callback
        if (this.config.onRackChange) {
            this.config.onRackChange(rackId);
        }
    }
    
    /**
     * Populate warehouse dropdown
     */
    populateWarehouse(data) {
        let options = '<option value="">Pilih Warehouse</option>';
        
        data.forEach(warehouse => {
            const isSelected = this.config.initialWarehouseId == warehouse.id ? 'selected' : '';
            options += `<option value="${warehouse.id}" ${isSelected}>${warehouse.name}</option>`;
        });
        
        this.elements.warehouse.html(options);
        
        if (this.config.useSelect2) {
            this.elements.warehouse.trigger('change.select2');
        }
    }
    
    /**
     * Populate zone dropdown
     */
    populateZone(data) {
        let options = '<option value="">Pilih Zone</option>';
        
        data.forEach(zone => {
            const isSelected = this.config.initialZoneId == zone.id ? 'selected' : '';
            // options += `<option value="${zone.id}" ${isSelected}>${zone.name} (${zone.code})</option>`;
            options += `<option value="${zone.id}" ${isSelected}>${zone.name}</option>`;
        });
        
        this.elements.zone.html(options);
        
        if (this.config.useSelect2) {
            this.elements.zone.trigger('change.select2');
        }
    }
    
    /**
     * Populate rack dropdown
     */
    populateRack(data) 
    {
        let options = '<option value="">Pilih Rack</option>';
        
        data.forEach(rack => {
            const capacity = rack.capacity ? `- Kapasitas: ${rack.capacity}` : '';
            const isSelected = this.config.initialRackId == rack.id ? 'selected' : '';
            options += `<option value="${rack.id}" ${isSelected}>${rack.name}</option>`;
        });
        
        this.elements.rack.html(options);
        
        // Set value untuk memastikan selected
        if (this.config.initialRackId) {
            this.elements.rack.val(this.config.initialRackId);
        }
        
        if (this.config.useSelect2) {
            this.elements.rack.trigger('change.select2');
        }
    }
    
    /**
     * Reset select element
     */
    resetSelect(type) {
        const defaultTexts = {
            warehouse: 'Pilih Warehouse',
            zone: 'Pilih Zone',
            rack: 'Pilih Rack'
        };
        
        const helpers = {
            warehouse: 'Pilih warehouse terlebih dahulu',
            zone: 'Pilih warehouse terlebih dahulu',
            rack: 'Pilih zone terlebih dahulu'
        };
        
        this.elements[type].html(`<option value="">${defaultTexts[type]}</option>`)
            .prop('disabled', true)
            .val('');
        
        if (this.config.useSelect2) {
            this.elements[type].trigger('change.select2');
        }
        
        this.updateStatus(type, 'default', helpers[type]);
    }
    
    /**
     * Update status icon and helper text
     */
    updateStatus(type, status, message) {
        const icons = {
            default: 'fas fa-circle text-muted',
            loading: 'fas fa-spinner fa-spin text-primary',
            success: 'fas fa-check-circle text-success',
            warning: 'fas fa-exclamation-triangle text-warning',
            error: 'fas fa-exclamation-circle text-danger'
        };
        
        const iconClasses = {
            default: '<i class="fas fa-info-circle"></i>',
            loading: '<i class="fas fa-spinner fa-spin"></i>',
            success: '<i class="fas fa-check-circle text-success"></i>',
            warning: '<i class="fas fa-exclamation-triangle text-warning"></i>',
            error: '<i class="fas fa-exclamation-circle text-danger"></i>'
        };
        
        // Update icon
        this.elements[`${type}Status`].removeClass().addClass(icons[status]);
        
        // Update helper text
        if (message) {
            this.elements[`${type}Helper`].html(`${iconClasses[status]} ${message}`);
        }
    }
    
    /**
     * Handle errors
     */
    handleError(type, xhr) {
        const message = xhr.responseJSON?.message || 'Terjadi kesalahan';
        
        this.log(`Error on ${type}:`, xhr);
        
        if (this.config.onError) {
            this.config.onError(type, xhr);
        }
        
        if (this.config.showAlerts && typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#dc3545'
            });
        }
    }
    
    /**
     * Get selected values
     */
    getValues() {
        return {
            warehouse_id: this.elements.warehouse.val(),
            zone_id: this.elements.zone.val(),
            rack_id: this.elements.rack.val()
        };
    }
    
    /**
     * Validate selection
     */
    validate() {
        const values = this.getValues();
        
        if (!values.warehouse_id || !values.zone_id || !values.rack_id) {
            if (this.config.showAlerts && typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Silakan lengkapi lokasi penyimpanan (Warehouse, Zone, dan Rack).',
                    confirmButtonColor: '#dc3545'
                });
            }
            return false;
        }
        
        return true;
    }
    
    /**
     * Reset all selects
     */
    resetAll() {
        this.resetSelect('warehouse');
        this.resetSelect('zone');
        this.resetSelect('rack');
        this.loadWarehouses();
    }
    
    /**
     * Log debug messages
     */
    log(...args) {
        if (this.config.debug) {
            console.log('[WarehouseLocationSelector]', ...args);
        }
    }
    
    /**
     * Destroy instance
     */
    destroy() {
        this.elements.warehouse.off('change');
        this.elements.zone.off('change');
        this.elements.rack.off('change');
        
        if (this.config.useSelect2 && $.fn.select2) {
            this.elements.warehouse.select2('destroy');
            this.elements.zone.select2('destroy');
            this.elements.rack.select2('destroy');
        }
        
        this.log('Destroyed');
    }
}