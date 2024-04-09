@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $transaction_purchase["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('transaction_purchase')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
                            </div>
                            <div class="d-flex justify-content-end align-items-center pr-3">
                                @if ($mode == 'edit')
                                    {!! $list_nav_button['cancel'] !!}
                                    @if (isset($list_nav_button['save']))
                                        {!! $list_nav_button['save'] !!}
                                    @endif
                                @elseif ($mode == 'view')
                                    {!! $list_nav_button['back'] !!}
                                    @if (isset($list_nav_button['edit']))
                                        {!! $list_nav_button['edit'] !!}
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
                                $gridFormData = gridSetup($mode, "getPurchaseItemGrid", $transaction_purchase["id"] ?? '', "transaction_purchase_item", "'".__('transaction_purchase')['col_item_detail']."'",
                                "'Id', 'Purchase Id', 'Item Id', '".__('transaction_purchase')['col_item_code']."', '".__('transaction_purchase')['col_item_name']."', '".__('transaction_purchase')['col_amount']."', '".__('transaction_purchase')['col_unit']."', '".__('transaction_purchase')['col_price']."', '".__('transaction_purchase')['col_subtotal']."'",
                                [
                                    "id", "purchase_id", "item_id", "item_code", "item_name", "amount", "unit", "price", "subtotal"
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
                                        'name' => "'purchase_id'",
                                        'index' => "'purchase_id'",
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
                                        'editoptions' => '{ dataInit: autocomplete_transaction_purchase_item }'
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
                                        'name' => "'price'",
                                        'index' => "'price'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'false',
                                        'hidden' => 'false'
                                    ],
                                    [
                                        'name' => "'subtotal'",
                                        'index' => "'subtotal'",
                                        'template' => 'coltemplate_general',
                                        'editable' => 'false',
                                        'hidden' => 'false'
                                    ],
                                ]);
                            ?>
                            <div class="row">
                                <div class="col-md-8">
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'code', __('transaction_purchase')['col_code'], $transaction_purchase['code'] ?? '', $mode, 'disabled placeholder="Auto Generated"') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'date', 'date', __('transaction_purchase')['col_date'], $transaction_purchase['date'] ?? '', $mode, 'required') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_supplier_name', '', __('transaction_purchase')['col_supplier'], $transaction_purchase['supplier_name'] ?? '', $mode, 'required', $action_supplier, ["suppliers.code|like","suppliers.name|like"], [], array('browse_supplier_id|id','browse_supplier_name|name'), ['code','name'], 'supplier', ['Code','Name'], [['field'=>'code', 'name' => 'suppliers.code'], ['field'=>'name', 'name' => 'suppliers.name']], title_modal:'Get Supplier', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'supplier_id', '', $transaction_purchase['supplier_id'] ?? '', 'add', '', 'browse_supplier_id') !!}
                                </div>
                            </div>
                            <div class="form-data row">
                                {!! $gridFormData !!}
                            </div>
                            <input type="hidden" name="detail" id="hiddenInput" value="" />
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('transaction_purchase')['col_remark'], 5, $transaction_purchase["remark"] ?? '', $mode, '') !!}
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'subtotal', __('transaction_purchase')['col_subtotal'], $transaction_purchase['subtotal'] ?? '', $mode, 'disabled') !!}
                                    {!! renderFieldCombineInput('mb-3 row', 'col-md-4', 'col-md-8', __('transaction_purchase')['col_ppn'], 'col-3 col-lg-2', 'col-3 col-lg-2', 'col-6 col-lg-8', 'ppn', $transaction_purchase["ppn"] ?? '10', 'ppn_percent', $transaction_purchase["ppn_percent"] ?? '%', 'ppn_price', $transaction_purchase["ppn_price"] ?? '', '', 'disabled', 'disabled', 'ppn', 'ppn_percent', 'ppn_price', $mode, type_input1: 'number') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'total', __('transaction_purchase')['col_total'], $transaction_purchase['total'] ?? '', $mode, 'disabled') !!}
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
    $grid_id = 'transaction_purchase_item';
@endphp
<?php echo renderLargeModel(); ?>
@endsection

@section('scripts')
<script>
    <?php echo autocomplete_render("transaction_purchase_item",["banks.name|like"],"getBank", ['code','name'], ['name']) ?>

    $(document).ready(function() {
        setMenuActive();
        // set the jqgrid to 100%
        $(".ui-jqgrid").parents(".form-data").find("div").css("width", "100%");
    });

    const myForm = document.getElementById('myForm');
    myForm.addEventListener('submit', function(event) {
        event.preventDefault();

        <?php echo addToArray(['transaction_purchase_item']) ?>
    });
</script>
@endsection