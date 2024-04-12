@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $transaction_warehouse_incoming_item["id"] }}@endif" method="post" id="myForm">
        @if($mode != 'add')
            @method('put')
        @else
            @method('post')
        @endif
            @csrf
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
                                <h4>{{ __('transaction_warehouse_incoming_item')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
                            </div>
                            <div class="d-flex justify-content-end align-items-center pr-3">
                                <?php
                                    $is_approve = $transaction_warehouse_incoming_item['is_approve_2'] ?? 0;
                                ?>
                                @if ($mode == 'edit')
                                    {!! $list_nav_button['cancel'] !!}
                                    @if (isset($list_nav_button['save']))
                                        {!! $list_nav_button['save'] !!}
                                    @endif
                                @elseif ($mode == 'view')
                                    {!! $list_nav_button['back'] !!}
                                    @if (isset($list_nav_button['edit']))
                                        @if ($is_approve == false && $show_button == '')
                                            {!! $list_nav_button['edit'] !!}
                                        @endif
                                    @endif
                                    @if (isset($list_nav_button['approve']))
                                        @if ($show_button == 'approve' && $is_approve == false)
                                            {!! $list_nav_button['approve'] !!}
                                        @endif
                                    @endif
                                    @if (isset($list_nav_button['disapprove']))
                                        @if ($show_button == 'disapprove' && $is_approve == true)
                                            {!! $list_nav_button['disapprove'] !!}
                                        @endif
                                    @endif
                                @else
                                    {!! $list_nav_button['back'] !!}
                                    @if (isset($list_nav_button['save']))
                                        {!! $list_nav_button['save'] !!}
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                        <script language="javascript" type="text/javascript">
                                var vgrid_comp = [];
                                var vgrid_load = [];
                                var vgrid_last = [];
                                var vgrid_real = [];
                            </script>
                            <?php
                                $gridFormData = gridSetup($mode, "getIncomingItemItemGrid", $transaction_warehouse_incoming_item["id"] ?? '', "transaction_warehouse_incoming_item_item", "'".__('transaction_warehouse_incoming_item')['col_item_detail']."'",
                                "'Id', 'Stock Transfer Id', 'Item Id', '".__('transaction_warehouse_incoming_item')['col_item_code']."', '".__('transaction_warehouse_incoming_item')['col_item_name']."', '".__('transaction_warehouse_incoming_item')['col_amount']."', '".__('transaction_warehouse_incoming_item')['col_unit']."'",
                                [
                                    "id", "stock_transfer_id", "item_id", "item_code", "item_name", "amount", "unit"
                                ],
                                [
                                    [
                                        'name' => "'id'",
                                        'index' => "'id'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'true',
                                        'hidden' => 'true'
                                    ],
                                    [
                                        'name' => "'stock_transfer_id'",
                                        'index' => "'stock_transfer_id'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'true',
                                        'hidden' => 'true'
                                    ],
                                    [
                                        'name' => "'item_id'",
                                        'index' => "'item_id'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'true',
                                        'hidden' => 'true'
                                    ],
                                    [
                                        'name' => "'item_code'",
                                        'index' => "'item_code'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'true',
                                        'hidden' => 'false',
                                        'editoptions' => '{ dataInit: autocomplete_transaction_warehouse_incoming_item_item }'
                                    ],
                                    [
                                        'name' => "'item_name'",
                                        'index' => "'item_name'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'false',
                                        'hidden' => 'false',
                                    ],
                                    [
                                        'name' => "'amount'",
                                        'index' => "'amount'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'true',
                                        'hidden' => 'false'
                                    ],
                                    [
                                        'name' => "'unit'",
                                        'index' => "'unit'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'false',
                                        'hidden' => 'false'
                                    ]
                                ]);
                            ?>
                            <div class="row">
                                <div class="col-md-8">
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'code', __('transaction_warehouse_incoming_item')['col_code'], $transaction_warehouse_incoming_item['code'] ?? '', $mode, 'disabled placeholder="Auto Generated"') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'date', 'date', __('transaction_warehouse_incoming_item')['col_date'], $transaction_warehouse_incoming_item['date'] ?? $today, $mode, 'required') !!}
                                </div>
                            </div>
                            <div class="form-data row">
                                {!! $gridFormData !!}
                            </div>
                            <input type="hidden" name="detail" id="hiddenInput" value="" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@php
    $grid_id = 'transaction_warehouse_incoming_item_item';
@endphp
<?php echo renderLargeModel(); ?>
@endsection

@section('scripts')
<script>
    <?php echo autocomplete_render("transaction_warehouse_incoming_item_item",["items.name|like"],"getItem", ['code','name','category_name'], ['code']) ?>

    function updateIncomingItemStatus(url, is_approve, title, text) {
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
                    url: url,
                    method: "POST",
                    data: { 
                        is_approve_2: is_approve,
                        branch_id: "{{ Session::get('user_group')['branch_id']; }}",
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
                                text: "{{ __('script_modal')['success_update_incoming_item'] }}",
                                type: 'success',
                                // timer: 1500,
                                allowOutsideClick: false,
                                confirmButtonText: "{{ __('script_modal')['button_confirm'] }}",
                                confirmButtonColor: '#C7232B', 
                                onClose:() => {        
                                    window.location.href = "{{ route('incoming-item.index') }}";
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
                                    window.location.href = "{{ route('incoming-item.index') }}";
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

    // Function to approve sales order
    $(document).on('click', '#btnApprove', function() {
        updateIncomingItemStatus("{{ route('update-status-incoming-item', $transaction_warehouse_incoming_item['id'] ?? '') }}", 1, "{{ __('script_modal')['title_approve_incoming_item'] }}", "{{ __('script_modal')['content_approve_incoming_item'] }}");
    });

    // Function to disapprove sales order
    $(document).on('click', '#btnDisapprove', function() {
        updateIncomingItemStatus("{{ route('update-status-incoming-item', $transaction_warehouse_incoming_item['id'] ?? '') }}", 0, "{{ __('script_modal')['title_disapprove_incoming_item'] }}", "{{ __('script_modal')['content_disapprove_incoming_item'] }}");
    });

    $(document).ready(function() {
        setMenuActive();
        // set the jqgrid to 100%
        $(".ui-jqgrid").parents(".form-data").find("div").css("width", "100%");
    });

    const myForm = document.getElementById('myForm');
    myForm.addEventListener('submit', function(event) {
        event.preventDefault();

        <?php echo addToArray(['transaction_warehouse_incoming_item_item']) ?>
    });
</script>
@endsection