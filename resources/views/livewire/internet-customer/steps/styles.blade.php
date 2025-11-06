<style>
    .main-content { 
        background: radial-gradient(circle at center, rgba(219, 39, 41, 0.6) 0%, rgba(219, 39, 41, 0) 80%);
    }
    
    /* Optimasi untuk touch devices */
    * {
        -webkit-tap-highlight-color: rgba(0,0,0,0);
        -webkit-touch-callout: none;
    }

    /* Clean white background untuk content */
    .registration-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 40px;
        margin: 0 auto;
        max-width: 1000px;
    }

    /* Progress Steps */
    .progress-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 35px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e0e0e0;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .step-item {
        flex: 1 1 auto;
        text-align: center;
        color: #999;
        font-size: 14px;
        min-width: 80px;
        white-space: nowrap;
    }

    .step-item.active {
        color: #DB2328;
        font-weight: 600;
    }

    .step-item.completed {
        color: #666;
    }

    .step-number {
        font-size: 11px;
        display: block;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .step-title {
        font-size: 13px;
    }

    /* Section Title */
    .section-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 25px;
        color: #2c3e50;
    }

    /* Form Elements */
    .form-label {
        font-weight: 500;
        color: #555;
        margin-bottom: 6px;
        font-size: 14px;
    }

    .form-control,
    .form-select {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px 14px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #DB2328;
        box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.1);
        outline: none;
    }

    /* Buttons */
    .btn-primary-red {
        background-color: #DB2328;
        border: none;
        color: white;
        padding: 10px 35px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 14px;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-primary-red:hover {
        background-color: #c01f24;
        color: white;
    }

    .btn-secondary-gray {
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        color: #666;
        padding: 10px 30px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s;
    }

    .btn-secondary-gray:hover {
        background-color: #e8e8e8;
        border-color: #ccc;
        color: #333;
    }

    /* Alert Badges */
    .alert-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        margin-top: 10px;
    }

    .alert-badge.success {
        background-color: #d4edda;
        color: #155724;
    }

    .alert-badge.danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    /* Payment Method Cards */
    .payment-method-card {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .payment-method-card:hover {
        border-color: #DB2328;
        box-shadow: 0 4px 12px rgba(219, 35, 40, 0.15);
        transform: translateY(-2px);
    }

    .payment-method-card.border-primary {
        border-color: #007bff !important;
        background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
    }

    .payment-method-card.border-success {
        border-color: #28a745 !important;
        background: linear-gradient(135deg, #f1f8f4 0%, #d4edda 100%);
    }

    /* Custom Month Input */
    .input-group .btn {
        min-width: 45px;
    }

    .input-group .form-control {
        max-width: 100px;
        font-size: 18px;
        font-weight: 600;
    }

    /* Signature Pad */
    .signature-wrapper {
        border: 2px dashed #ddd;
        border-radius: 6px;
        padding: 15px;
        background: #fafafa;
        margin-top: 10px;
    }

    #signature-canvas {
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        cursor: crosshair;
        display: block;
        width: 100%;
    }

    .signature-preview {
        max-width: 100%;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
    }

    #signature-canvas-container, 
    #signature-preview-container {
        transition: all 0.3s ease;
    }

    #signature-preview-container {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        background-color: #f8f9fa;
    }

    /* Agreement Box */
    .agreement-box {
        max-height: 350px;
        overflow-y: auto;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 20px;
        background: #fafafa;
        margin-bottom: 20px;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Select2 Styling */
    .select2-container--default .select2-selection--single {
        border: 1px solid #ddd;
        border-radius: 4px;
        height: 42px;
        padding: 6px 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #DB2328 !important;
        font-weight: 500;
        line-height: 28px;
    }

    .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: #fff5f5 !important;
        color: #DB2328 !important;
        font-weight: 600;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #DB2328 !important;
        color: #fff !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #DB2328 !important;
        box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.15) !important;
        outline: none !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #DB2328 transparent transparent transparent !important;
    }

    .select2-container--default .select2-selection--single:hover {
        border-color: #c01f24 !important;
    }

    /* Progress bar */
    .progress-bar {
        background: #DB2328;
    }

    /* Focus states */
    *:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    input:focus,
    textarea:focus,
    select:focus,
    button:focus {
        border-color: #DB2328 !important;
        box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.15) !important;
        outline: none !important;
        transition: all 0.2s ease-in-out;
    }

    button:focus-visible {
        box-shadow: 0 0 0 3px rgba(219, 35, 40, 0.25) !important;
    }

    /* Info Alert */
    .info-alert {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 6px;
        padding: 14px;
        margin-top: 20px;
        font-size: 13px;
    }

    /* Badge */
    .badge {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 4px;
    }

    .badge-primary {
        background-color: #007bff;
        color: white;
    }

    .badge-success {
        background-color: #28a745;
        color: white;
    }

    .badge-info {
        background-color: #17a2b8;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .registration-card {
            padding: 20px 15px;
            margin: 10px;
        }

        .progress-steps {
            flex-wrap: nowrap;
            gap: 5px;
            margin-bottom: 25px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .progress-steps::-webkit-scrollbar {
            display: none;
        }

        .step-item {
            flex: 0 0 auto;
            min-width: 70px;
            font-size: 10px;
            padding: 0 5px;
        }

        .step-number {
            font-size: 9px;
            margin-bottom: 3px;
        }

        .step-title {
            font-size: 10px;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            max-width: 70px;
            margin: 0 auto;
        }

        .step-item:not(.active):not(.completed) .step-title {
            display: none;
        }

        .section-title {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 13px;
            margin-bottom: 5px;
        }

        .form-control,
        .form-select {
            font-size: 14px;
            padding: 10px 12px;
        }

        .btn-primary-red,
        .btn-secondary-gray {
            font-size: 14px;
            padding: 10px 25px;
            width: 100%;
        }

        .d-flex.justify-content-between {
            flex-direction: column-reverse;
            gap: 10px;
        }

        .d-flex.justify-content-between .btn-primary-red,
        .d-flex.justify-content-between .btn-secondary-gray {
            width: 100%;
            justify-content: center;
        }

        .payment-method-card {
            padding: 15px;
            margin-bottom: 10px;
        }

        .agreement-box {
            max-height: 250px;
            padding: 15px;
            font-size: 13px;
            line-height: 1.5;
        }

        #signature-canvas {
            height: 150px !important;
        }

        .signature-wrapper {
            padding: 12px;
        }

        .input-group .form-control {
            font-size: 16px;
        }

        .row.g-3 {
            row-gap: 15px !important;
        }

        textarea.form-control {
            min-height: 80px;
        }
    }

    @media (max-width: 576px) {
        .registration-card {
            padding: 15px 10px;
            border-radius: 8px;
        }

        .section-title {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .progress-steps {
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .step-item {
            min-width: 60px;
        }

        .step-title {
            max-width: 60px;
        }

        .form-control,
        .form-select {
            font-size: 14px;
            padding: 9px 10px;
        }

        .form-label {
            font-size: 12px;
        }

        .btn-primary-red,
        .btn-secondary-gray {
            font-size: 13px;
            padding: 9px 20px;
        }

        .select2-container--default .select2-selection--single {
            height: 40px;
            padding: 5px 10px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            font-size: 13px;
            line-height: 28px;
        }
    }

    @media (max-width: 768px) and (orientation: landscape) {
        .registration-card {
            padding: 15px;
        }

        .section-title {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .progress-steps {
            margin-bottom: 20px;
        }

        #signature-canvas {
            height: 120px !important;
        }

        .agreement-box {
            max-height: 180px;
        }
    }
</style>