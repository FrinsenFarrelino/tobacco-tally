@extends('template.app')

@section('styles')
<style>
    .dataTables_wrapper .dataTables_filter {
        text-align: right;
        margin-right: 10px;
        /* Add margin for spacing */
    }

    .dataTables_wrapper .dataTables_paginate {
        float: right;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $title }} - {{ $subtitle }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">{{ $title }}</div>
            @if($submodule !== '')
            <div class="breadcrumb-item">{{ $submodule }}</div>
            @endif
            <div class="breadcrumb-item">{{ $subtitle }}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between">
                        <div class="card-header">
                            <h4>{{ __('report_stock_balance')['list'] }} {{ $title }} - {{ $subtitle }}</h4>
                        </div>
                        <div class="d-flex justify-content-end align-items-center pr-3">
                            {!! $list_nav_button['reload'] !!}
                            @if (isset($list_nav_button['add']))
                            {!! $list_nav_button['add'] !!}
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="myTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('report_stock_balance')['col_warehouse'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_item_code'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_item_name'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_category'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_stock'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_unit'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_branch'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_overstapled_at'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_is_overstapled'] }}</th>
                                        <th>{{ __('report_stock_balance')['col_action'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        setMenuActive();

        $('#myTable').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            ajax: {
                url: "{{ route('ajax-data-table', ['action' => $action]) }}",
                type: "GET",
                data: function(d) {
                    d.route = "{{ $menu_route }}";
                }
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'item_code',
                    name: 'items.code'
                },
                {
                    data: 'item_name',
                    name: 'items.name'
                },
                {
                    data: 'category_name',
                    name: 'categories.name'
                },
                {
                    data: 'stock',
                    name: 'stock'
                },
                {
                    data: 'unit_name',
                    name: 'units.name'
                },
                {
                    data: 'branch_name',
                    name: 'branches.name'
                },
                {
                    data: 'overstapled_at',
                    name: 'overstapled_at'
                },
                {
                    data: 'is_overstapled',
                    name: 'is_overstapled',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (row.is_overstapled === 'Overstapled') {
                            var statusOverstapled = '<p class="text-success">Overstapled</p>';
                        } else {
                            var statusOverstapled = '<p class="text-danger">Not Overstapled</p>';
                        }

                        return statusOverstapled;
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var overstapledButton = '<button id="btnOverstaple" class="btn btn-primary btn-sm rounded" data-id="' + row.id + '">Overstaple Now</button>';
                        
                        return overstapledButton;
                    }
                },
            ],
            language: {
                paginate: {
                    next: '<i class="fas fa-angle-double-right" aria-hidden="true"></i>',
                    previous: '<i class="fas fa-angle-double-left" aria-hidden="true"></i>'
                },
            }
        });
    });

    function updateOverstapleStatus(id, title, text) {
        swal({
            title: title,
            text: text,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#C7232B',
            cancelButtonColor: '#6E6E6E',
            confirmButtonText: "{{ __('script_modal')['button_confirm'] }}",
            cancelButtonText: "{{ __('script_modal')['button_cancel'] }}"
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: "/overstaple/" + id,
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        showLoadingOverlay();
                    },
                    success: function (response) {
                        hideLoadingOverlay();
                        if (response.success) {
                            swal({
                                title: "{{ __('success') }}", 
                                text: "{{ __('script_modal')['success_overstaple'] }}",
                                type: 'success',
                                // timer: 1500,
                                allowOutsideClick: false,
                                confirmButtonText: "{{ __('script_modal')['button_confirm'] }}",
                                confirmButtonColor: '#C7232B', 
                                onClose:() => {        
                                    window.location.reload();
                                }
                            });
                        } else {
                            swal({
                                title: "{{ __('fail') }}", 
                                text: response.message,
                                type: 'error',
                                // timer: 1500,
                                allowOutsideClick: false,
                                confirmButtonText: "{{ __('script_modal')['button_confirm'] }}",
                                confirmButtonColor: '#C7232B', 
                                onClose:() => {        
                                    window.location.reload();
                                }
                            });
                        }
                    },
                    error: function (error) {
                        hideLoadingOverlay();
                        console.error('Error update status:', error);
                    }
                });
            }
        })
    }

    // Function to overstaple
    $(document).on('click', '#btnOverstaple', function() {
        var id = $(this).data('id');
        updateOverstapleStatus(id, "{{ __('script_modal')['title_overstaple'] }}", "{{ __('script_modal')['content_overstaple'] }}");
    });
</script>
@endsection