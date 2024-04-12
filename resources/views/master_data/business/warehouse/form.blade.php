@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $master_data_business_warehouse["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('master_data_business_warehouse')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
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
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'code', __('master_data_business_warehouse')['col_code'], $master_data_business_warehouse['code'] ?? '', $mode, 'disabled placeholder="Auto Generated"') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'name', __('master_data_business_warehouse')['col_name'], $master_data_business_warehouse['name'] ?? '', $mode, 'required') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_item_name', '', __('master_data_business_warehouse')['col_item'], $master_data_business_warehouse['item_name'] ?? '', $mode, 'required', $action_item, ["items.code|like","items.name|like","categories.name|like"], [], array('browse_item_id|id','browse_item_name|name','browse_item_category_name|category_name'), ['code','name','category_name'], 'item', ['Code','Name','Category'], [['field'=>'code', 'name' => 'items.code'], ['field'=>'name', 'name' => 'items.name'], ['field'=>'category_name', 'name' => 'categories.name']], title_modal:'Get Item', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'item_id', '', $master_data_business_warehouse['item_id'] ?? '', 'add', '', 'browse_item_id') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'category_name', __('master_data_business_warehouse')['col_category'], $master_data_business_warehouse['category_name'] ?? '', $mode, 'disabled', 'browse_item_category_name') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_branch_name', '', __('master_data_business_warehouse')['col_branch'], $master_data_business_warehouse['branch_name'] ?? '', $mode, 'required', $action_branch, ["branches.code|like","branches.name|like"], [], array('browse_branch_id|id','browse_branch_name|name'), ['code','name'], 'branch', ['Code','Name'], [['field'=>'code', 'name' => 'branches.code'], ['field'=>'name', 'name' => 'branches.name']], title_modal:'Get Branch', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'branch_id', '', $master_data_business_warehouse['branch_id'] ?? '', 'add', '', 'browse_branch_id') !!}
                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('master_data_business_warehouse')['col_remark'], 5, $master_data_business_warehouse["remark"] ?? '', $mode, '') !!}
                                    {!! renderSelect('mb-3 row', 'col-md-4 mt-2', 'col-md-8 mt-2 mt-lg-0', 'is_active', __('master_data_business_warehouse')['col_is_active'], 'js-data-example-ajax w-100 form-control mb-3', $master_data_business_warehouse['is_active'] ?? '', $selectActive ?? [], $mode) !!}
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
    function getVal() {
        console.log($('#browse_province_id').val())
    }
    
    $(document).ready(function() {
        setMenuActive();
    });

</script>
@endsection