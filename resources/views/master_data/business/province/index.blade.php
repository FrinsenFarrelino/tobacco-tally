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
        <h1>Master Data - Province</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Master Data</a></div>
            <div class="breadcrumb-item"><a href="#">Business</a></div>
            <div class="breadcrumb-item">Province</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="d-flex justify-content-between">
                        <div class="card-header">
                            <h4>List Master Data - Province</h4>
                        </div>
                        <div class="d-flex justify-content-end align-items-center pr-3">
                            <div class="col-auto">
                                {!! $list_nav_button['reload'] !!}
                            </div>
                            <div class="col-auto">
                                @if (isset($list_nav_button['add']))
                                {!! $list_nav_button['add'] !!}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="myTable">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>{{ __('master_data_business_province')['col_code'] }}</th>
                                        <th>{{ __('master_data_business_province')['col_name'] }}</th>
                                        <th>{{ __('master_data_business_province')['col_country'] }}</th>
                                        <th>{{ __('master_data_business_province')['col_is_active'] }}</th>
                                        <th>{{ __('master_data_business_province')['col_action'] }}</th>
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
        $('#myTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('ajax-data-table', ['action' => 'getProvince']) }}",
                type: "GET",
                data: function(d) {
                    d.route = "province"; // Add extra parameters here
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'country',
                    name: 'country'
                },
                {
                    data: 'is_active',
                    name: 'is_active'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        new $.fn.dataTable.FixedColumns(table, {
            rightColumns: 1
        });
    });
</script>
@endsection