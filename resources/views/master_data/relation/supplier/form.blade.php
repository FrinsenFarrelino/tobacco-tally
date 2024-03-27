@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $master_data_relation_supplier["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('master_data_relation_supplier')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
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
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'code', __('master_data_relation_supplier')['col_code'], $master_data_relation_supplier['code'] ?? '', $mode, 'disabled placeholder="Auto Generated"') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'name', __('master_data_relation_supplier')['col_name'], $master_data_relation_supplier['name'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'address', __('master_data_relation_supplier')['col_address'], $master_data_relation_supplier['address'] ?? '', $mode, '') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_subdistrict_name', '', __('master_data_relation_supplier')['col_subdistrict'], $master_data_relation_supplier['subdistrict_name'] ?? '', $mode, 'required', $action_subdistrict, ["subdistricts.code|like","subdistricts.name|like","cities.name|like","provinces.name|like"], [], array('browse_subdistrict_id|id','browse_subdistrict_name|name','browse_subdistrict_city_name|city_name','browse_subdistrict_province_name|province_name'), ['code','name','city_name','province_name'], 'subdistrict', ['Code','Name','City','Province'], [['field'=>'code', 'name' => 'subdistricts.code'], ['field'=>'name', 'name' => 'subdistricts.name'], ['field'=>'city_name', 'name' => 'cities.name'], ['field'=>'province_name', 'name' => 'provinces.name']], title_modal:'Get Subdistrict', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'subdistrict_id', '', $master_data_relation_supplier['subdistrict_id'] ?? '', 'add', '', 'browse_subdistrict_id') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'city_name', __('master_data_relation_supplier')['col_city'], $master_data_relation_supplier['city_name'] ?? '', $mode, 'disabled', 'browse_subdistrict_city_name') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'province_name', __('master_data_relation_supplier')['col_province'], $master_data_relation_supplier['province_name'] ?? '', $mode, 'disabled', 'browse_subdistrict_province_name') !!}
                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('master_data_relation_supplier')['col_remark'], 5, $master_data_relation_supplier["remark"] ?? '', $mode, '') !!}
                                    {!! renderSelect('mb-3 row', 'col-md-4 mt-2', 'col-md-8 mt-2 mt-lg-0', 'is_active', __('master_data_relation_supplier')['col_is_active'], 'js-data-example-ajax w-100 form-control mb-3', $master_data_relation_supplier['is_active'] ?? '', $selectActive ?? [], $mode) !!}
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