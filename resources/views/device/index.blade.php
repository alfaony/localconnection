@extends('adminlte::page')

@section('content_header')
    <h1>Device Management</h1>
@stop

@section('content')
<div class="row justify-content-end align-items-center mb-4">
    <div class="col-auto">
        <div class="input-group">
            <input type="search" id="deviceSearch" class="form-control" placeholder="Search devices" aria-label="Search">
            <div class="input-group-append">
                <button class="btn btn-outline-primary" type="button" onclick="searchDevices()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Device List</h3>
    </div>
    <div class="card-body p-0">
        <!-- Loading spinner -->
        <div id="loadingSpinner" class="text-center my-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">Fetching devices, please wait...</p>
        </div>

        <table class="table table-bordered table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Status (ON/OFF)</th>
                    <th>Availability</th>
                    <th>Last Connected</th>
                </tr>
            </thead>
            <tbody id="deviceTableBody">
                <!-- Data will be injected here via JavaScript -->
            </tbody>
        </table>

        <!-- Message when no devices are found inside the table -->
        <div id="noDevicesMessage" class="text-center my-4" style="display: none;">
            <p class="text-muted">No devices found.</p>
        </div>

        <div id="paginationLinks" class="mt-3"></div>
    </div>
</div>
@stop

@section('css')
<!-- Add Bootstrap Toggle Switches -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap4-toggle/css/bootstrap4-toggle.min.css" rel="stylesheet">
<style>
    .pagination {
        justify-content: center;
        padding: 15px 0;
    }
    .pagination .page-item .page-link {
        color: #007bff;
    }
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
    }
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    #loadingSpinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
    /* Styling for table hover effects */
    table.table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    /* No devices message in the center of the table */
    #noDevicesMessage {
        display: none;
    }
</style>
@stop

@section('js')
<!-- Add Bootstrap Toggle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap4-toggle/js/bootstrap4-toggle.min.js"></script>
@canAccess('dataJson','devices')
<script>
    function fetchDevices(page = 1) {
        let searchQuery = $('#deviceSearch').val();

        $('#deviceTableBody').empty();
        $('#noDevicesMessage').hide(); // Hide the "No devices found" message initially
        $('#loadingSpinner').show(); // Show loading spinner

        $.ajax({
            url: '{{ route("device.dataJson") }}',
            type: 'GET',
            data: {
                search: searchQuery,
                page: page,
            },
            success: function(response) {
                $('#loadingSpinner').hide(); // Hide loading spinner
                
                // Handle no data scenario
                if (response.devices.length === 0) {
                    $('#noDevicesMessage').show(); // Show "No devices found" message
                    return;
                }

                // Append each device row with toggle switches
                response.devices.forEach(function(device) {
                    $('#deviceTableBody').append(`
                        <tr>
                            <td>${device.name}</td>
                            <td>${device.type}</td>
                            <td>${device.location}</td>
                            <td>
                                <input type="checkbox" ${device.status ? 'checked' : ''} data-toggle="toggle" data-on="ON" data-off="OFF" data-onstyle="success" data-offstyle="danger">
                            </td>
                            <td>
                                <input type="checkbox" ${device.active ? 'checked' : ''} data-toggle="toggle" data-on="Active" data-off="Inactive" data-onstyle="primary" data-offstyle="secondary">
                            </td>
                            <td>${formatTimestampWIB(device.last_connected)}</td>
                        </tr>
                    `);
                });

                // Initialize Bootstrap Toggle Switches
                $('input[data-toggle="toggle"]').bootstrapToggle();

                // Update pagination links
                updatePagination(response.pagination);
            },
            error: function(xhr, status, error) {
                $('#loadingSpinner').hide(); // Hide loading spinner
                console.error('Error fetching devices:', error);
            }
        });
    }

    // Update pagination links
    function updatePagination(pagination) {
        let paginationHtml = '';

        // Previous Page Link
        if (pagination.prev_page_url) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">&laquo; Previous</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>`;
        }

        // Generate dynamic page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === pagination.current_page) {
                paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
        }

        // Next Page Link
        if (pagination.next_page_url) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next &raquo;</a></li>`;
        } else {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>`;
        }

        $('#paginationLinks').html(`<ul class="pagination">${paginationHtml}</ul>`);
    }

    function formatTimestampWIB(timestamp) 
    {
        if (!timestamp) return ''; // Return empty if there's no timestamp

        // If the timestamp is in seconds (10 digits), convert it to milliseconds
        if (String(timestamp).length === 10) {
            timestamp = parseInt(timestamp) * 1000;
        }

        // Create a new Date object from the timestamp (milliseconds)
        let date = new Date(timestamp);

        // Check if the timestamp is valid
        if (isNaN(date.getTime())) {
            return ''; // Invalid timestamp
        }

        // Adjust the time for WIB (UTC+7)
        let wibOffset = 7 * 60 * 60 * 1000; // 7 hours in milliseconds
        let wibDate = new Date(date.getTime() + wibOffset);

        // Get the individual date components
        let day = ("0" + wibDate.getDate()).slice(-2);
        let month = ("0" + (wibDate.getMonth() + 1)).slice(-2); // Months are zero-indexed
        let year = wibDate.getFullYear();

        // Get the time components
        let hours = ("0" + wibDate.getHours()).slice(-2);
        let minutes = ("0" + wibDate.getMinutes()).slice(-2);
        let seconds = ("0" + wibDate.getSeconds()).slice(-2);

        // Return the formatted date and time in MM/DD/YYYY HH:MM:SS WIB format
        return `${month}/${day}/${year} ${hours}:${minutes}:${seconds} WIB`;
    }





    // Handle pagination link clicks
    $(document).on('click', '#paginationLinks a', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        fetchDevices(page);
    });

    // Trigger search on button click
    function searchDevices() {
        fetchDevices(1); // Always go to page 1 when searching
    }

    // Fetch devices when the page is first loaded
    $(document).ready(function() {
        fetchDevices();
    });
</script>
@endcanAccess
@stop