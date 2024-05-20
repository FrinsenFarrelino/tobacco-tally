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
                            <h4>{{ __('report_stock_report')['list'] }} {{ $title }} - {{ $subtitle }}</h4>
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
                                        <th>{{ __('report_stock_report')['col_transaction_code'] }}</th>
                                        <th>{{ __('report_stock_report')['col_warehouse'] }}</th>
                                        <th>{{ __('report_stock_report')['col_branch'] }}</th>
                                        <th>{{ __('report_stock_report')['col_item'] }}</th>
                                        <th>{{ __('report_stock_report')['col_amount'] }}</th>
                                        <th>{{ __('report_stock_report')['col_unit'] }}</th>
                                        <th>{{ __('report_stock_report')['col_remark'] }}</th>
                                        <th>{{ __('report_stock_report')['col_date'] }}</th>
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
    @if(session('success'))
        {!! renderSweetAlert('Success',session('success'),'success') !!}
    @endif
</script>
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
                    data: 'transaction_code',
                    name: 'transaction_code'
                },
                {
                    data: 'warehouse_name',
                    name: 'warehouses.name'
                },
                {
                    data: 'branch_name',
                    name: 'branches.name'
                },
                {
                    data: 'item_name',
                    name: 'items.name'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'unit_name',
                    name: 'units.name'
                },
                {
                    data: 'remark',
                    name: 'remark'
                },
                {
                    data: 'date',
                    name: 'date'
                }
            ],
            language: {
                paginate: {
                    next: '<i class="fas fa-angle-double-right" aria-hidden="true"></i>',
                    previous: '<i class="fas fa-angle-double-left" aria-hidden="true"></i>'
                },
            }
        });
    });
</script>
@endsection