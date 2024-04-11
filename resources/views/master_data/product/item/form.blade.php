@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $master_data_product_item["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('master_data_product_item')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
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
                            <div class="row">
                                <div class="col-md-8">
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'code', __('master_data_product_item')['col_code'], $master_data_product_item['code'] ?? '', $mode, 'disabled placeholder="Auto Generated"') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'name', __('master_data_product_item')['col_name'], $master_data_product_item['name'] ?? '', $mode, 'required') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_category_name', '', __('master_data_product_item')['col_category'], $master_data_product_item['category_name'] ?? '', $mode, 'required', $action_category, ["categories.code|like","categories.name|like"], [], array('browse_category_id|id','browse_category_name|name'), ['code','name'], 'category', ['Code','Name'], [['field'=>'code', 'name' => 'categories.code'], ['field'=>'name', 'name' => 'categories.name']], title_modal:'Get Category', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'category_id', '', $master_data_product_item['category_id'] ?? '', 'add', '', 'browse_category_id') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_unit_name', '', __('master_data_product_item')['col_unit'], $master_data_product_item['unit_name'] ?? '', $mode, 'required', $action_unit, ["units.code|like","units.name|like"], [], array('browse_unit_id|id','browse_unit_name|name'), ['code','name'], 'unit', ['Code','Name'], [['field'=>'code', 'name' => 'units.code'], ['field'=>'name', 'name' => 'units.name']], title_modal:'Get Unit', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'unit_id', '', $master_data_product_item['unit_id'] ?? '', 'add', '', 'browse_unit_id') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'buy_price', __('master_data_product_item')['col_buy_price'], $master_data_product_item['buy_price'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'sell_price', __('master_data_product_item')['col_sell_price'], $master_data_product_item['sell_price'] ?? '', $mode, 'required') !!}
                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('master_data_product_item')['col_remark'], 5, $master_data_product_item["remark"] ?? '', $mode, '') !!}
                                    {!! renderSelect('mb-3 row', 'col-md-4 mt-2', 'col-md-8 mt-2 mt-lg-0', 'is_active', __('master_data_product_item')['col_is_active'], 'js-data-example-ajax w-100 form-control mb-3', $master_data_product_item['is_active'] ?? '', $selectActive ?? [], $mode) !!}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
<?php echo renderLargeModel(); ?>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        setMenuActive();
    });

</script>
@endsection