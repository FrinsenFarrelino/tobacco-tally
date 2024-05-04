@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $transaction_sale["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('transaction_sale')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
                            </div>
                            <div class="d-flex justify-content-end align-items-center pr-3">
                                <?php
                                    $is_approve = $transaction_sale['is_approve'] ?? 0;
                                ?>
                                {!! $list_nav_button['back'] !!}
                                @if ($is_approve == true)
                                    {!! $list_nav_button['print'] !!}
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
                                $gridFormData = gridSetup($mode, "getSaleItemGrid", $transaction_sale["id"] ?? '', "transaction_sale_item", "'".__('transaction_sale')['col_item_detail']."'",
                                "'Id', 'Sale Id', 'Item Id', '".__('transaction_sale')['col_item_code']."', '".__('transaction_sale')['col_item_name']."', '".__('transaction_sale')['col_amount']."', '".__('transaction_sale')['col_unit']."', '".__('transaction_sale')['col_price']."', '".__('transaction_sale')['col_subtotal']."'",
                                [
                                    "id", "sale_id", "item_id", "item_code", "item_name", "amount", "unit", "sell_price", "subtotal"
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
                                        'name' => "'sale_id'",
                                        'index' => "'sale_id'",
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
                                        'editoptions' => '{ dataInit: autocomplete_transaction_sale_item }'
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
                                    ],
                                    [
                                        'name' => "'sell_price'",
                                        'index' => "'sell_price'",
                                        'template' => 'coltemplate_number',
                                        'editable' => 'false',
                                        'hidden' => 'false'
                                    ],
                                    [
                                        'name' => "'subtotal'",
                                        'index' => "'subtotal'",
                                        'template' => 'coltemplate_number',
                                        'editable' => 'false',
                                        'hidden' => 'false'
                                    ],
                                ]);
                            ?>
                            <div class="row">
                                <div class="col-md-8">
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'code', __('transaction_sale')['col_code'], $transaction_sale['code'] ?? '', $mode, 'disabled placeholder="Auto Generated"') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'date', 'date', __('transaction_sale')['col_date'], $transaction_sale['date'] ?? $today, $mode, 'required') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_customer_name', '', __('transaction_sale')['col_customer'], $transaction_sale['customer_name'] ?? '', $mode, 'required', $action_customer, ["customers.code|like","customers.name|like"], [], array('browse_customer_id|id','browse_customer_name|name'), ['code','name'], 'customer', ['Code','Name'], [['field'=>'code', 'name' => 'customers.code'], ['field'=>'name', 'name' => 'customers.name']], title_modal:'Get Customer', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'customer_id', '', $transaction_sale['customer_id'] ?? '', 'add', '', 'browse_customer_id') !!}
                                </div>
                            </div>
                            <div class="form-data row">
                                {!! $gridFormData !!}
                            </div>
                            <input type="hidden" name="detail" id="hiddenInput" value="" />
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('transaction_sale')['col_remark'], 5, $transaction_sale["remark"] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'subtotal', __('transaction_sale')['col_subtotal'], $transaction_sale['subtotal'] ?? '', $mode, 'onkeypress="return false;"') !!}
                                    {!! renderFieldCombineInput('mb-3 row', 'col-md-4', 'col-md-8', __('transaction_sale')['col_ppn'], 'col-3 col-lg-2', 'col-3 col-lg-2', 'col-6 col-lg-8', 'ppn', $transaction_sale["ppn"] ?? '10', 'ppn_percent', $transaction_sale["ppn_percent"] ?? '%', 'ppn_price', $transaction_sale["ppn_price"] ?? '', 'required min="0" max="100"', 'disabled', 'onkeypress="return false;"', 'ppn', 'ppn_percent', 'ppn_price', $mode, type_input1: 'number') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'total', __('transaction_sale')['col_total'], $transaction_sale['total'] ?? '', $mode, 'onkeypress="return false;"') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@php
    $grid_id = 'transaction_sale_item';
@endphp
<?php echo renderLargeModel(); ?>
@endsection

@section('scripts')
<script>
    <?php echo autocomplete_render("transaction_sale_item",["items.name|like"],"getItem", ['code','name','category_name'], ['code']) ?>

    function setSubtotalPerItem(rowid) {
        var rowData = $(<?= $grid_id ?>_element).jqGrid('getRowData', rowid);
        if (!isNaN(rowData.amount)) {
            rowData.subtotal = rowData.amount * rowData.sell_price
            $(<?=$grid_id?>_element).jqGrid('setRowData', rowid, rowData);
        } else if (rowData.amount !== undefined && isNaN(rowData.amount)) {
            alert('"'+ rowData.amount + '" is not a valid number, please fill a valid number')
            rowData.amount = '0';
            $(<?=$grid_id?>_element).jqGrid('setRowData', rowid, rowData);
        }
    }

    function setSubtotal() {
        var subtotal = parseFloat($("#transaction_sale_item_grid").jqGrid('getCol', 'subtotal', false, 'sum'));
        if (!isNaN(subtotal)) {
            $('#subtotal').val(formatRupiah(subtotal, 'Rp.')).trigger('change');
        }
    }

    $("#ppn").on("keyup change", function() {
        var subtotal = $("#subtotal").val()
        var subtotal = parseFloat(subtotal.replace(/Rp |Rp\.|\.|,/g, ''))
        var ppn = $("#ppn").val()
        var ppnPrice = 0;
        var total = 0;

        if (!isNaN(subtotal)) {
            ppnPrice = subtotal * (ppn/100)
            $("#ppn_price").val(formatRupiah(ppnPrice, 'Rp. '))
            total = subtotal + ppnPrice
            $("#total").val(formatRupiah(total, 'Rp. '))
        }
    });

    $("#subtotal").on("change", function() {
        var subtotal = $("#subtotal").val()
        var subtotal = parseFloat(subtotal.replace(/Rp |Rp\.|\.|,/g, ''))
        var ppn = $("#ppn").val()
        var ppnPrice = 0;
        var total = 0;

        if (!isNaN(subtotal) && !isNaN(ppn)) {
            ppnPrice = subtotal * (ppn/100)
            $("#ppn_price").val(formatRupiah(ppnPrice, 'Rp. '))
            total = subtotal + ppnPrice
            $("#total").val(formatRupiah(total, 'Rp. '))
        }
    });

    function updateSaleStatus(url, is_approve, title, text) {
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
                        is_approve: is_approve,
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
                                text: "{{ __('script_modal')['success_update_sale'] }}",
                                type: 'success',
                                // timer: 1500,
                                allowOutsideClick: false,
                                confirmButtonText: "{{ __('script_modal')['button_confirm'] }}",
                                confirmButtonColor: '#C7232B', 
                                onClose:() => {        
                                    window.location.href = "{{ route('sale.index') }}";
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
                                    window.location.href = "{{ route('sale.index') }}";
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
        updateSaleStatus("{{ route('status-sale', $transaction_sale['id'] ?? '') }}", 1, "{{ __('script_modal')['title_approve_sale'] }}", "{{ __('script_modal')['content_approve_sale'] }}");
    });

    // Function to disapprove sales order
    $(document).on('click', '#btnDisapprove', function() {
        updateSaleStatus("{{ route('status-sale', $transaction_sale['id'] ?? '') }}", 0, "{{ __('script_modal')['title_disapprove_sale'] }}", "{{ __('script_modal')['content_disapprove_sale'] }}");
    });

    $(document).ready(function() {
        setMenuActive();
        // set the jqgrid to 100%
        $(".ui-jqgrid").parents(".form-data").find("div").css("width", "100%");
    });

    const myForm = document.getElementById('myForm');
    myForm.addEventListener('submit', function(event) {
        event.preventDefault();

        <?php echo addToArray(['transaction_sale_item']) ?>
    });
</script>
@endsection