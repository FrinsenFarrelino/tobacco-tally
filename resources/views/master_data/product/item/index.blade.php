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
                            <h4>{{ __('master_data_product_item')['list'] }} {{ $title }} - {{ $subtitle }}</h4>
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
                                        <th>{{ __('master_data_product_item')['col_code'] }}</th>
                                        <th>{{ __('master_data_product_item')['col_name'] }}</th>
                                        <th>{{ __('master_data_product_item')['col_category'] }}</th>
                                        <th>{{ __('master_data_product_item')['col_is_active'] }}</th>
                                        <th>{{ __('master_data_product_item')['col_action'] }}</th>
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
<?php echo renderBaseModel(); ?>

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
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'category_name',
                    name: 'categories.name'
                },
                {
                    data: 'is_active',
                    name: 'is_active'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var showButton = '<a href="' + row.showUrl + '" class="btn btn-secondary btn-sm rounded-circle"><i class="fas fa-eye"></i></a>';
                        var editButton = '<a href="' + row.editUrl + '" class="btn btn-warning btn-sm ml-2 rounded-circle"><i class="fas fa-pencil-alt"></i></a>';
                        var deleteButton = '<button class="btn btn-danger btn-sm ml-2 rounded-circle btnDestroy" type="button" data-id="' + row.id + '"><i class="fas fa-trash-alt"></i></button>';
                        
                        return showButton + editButton + deleteButton;
                    }
                }
            ],
            language: {
                paginate: {
                    next: '<i class="fas fa-angle-double-right" aria-hidden="true"></i>',
                    previous: '<i class="fas fa-angle-double-left" aria-hidden="true"></i>'
                },
            }
        });

        // untuk pop up delete modal data
        <?php echo renderOpenModal(); ?>
        <?php echo renderScriptButtonTable('btnDestroy', 'confirmations', 'fa-solid fa-triangle-exclamation fa-beat-fade', '#FF0000', 'title_delete', 'content_delete', $menu_route, $menu_param); ?>
    });
</script>
@endsection