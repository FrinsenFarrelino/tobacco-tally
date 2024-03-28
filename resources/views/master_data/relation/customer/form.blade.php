@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $master_data_relation_customer["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('master_data_relation_customer')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
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
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'code', __('master_data_relation_customer')['col_code'], $master_data_relation_customer['code'] ?? '', $mode, 'disabled placeholder="Auto Generated"') !!}
                                    {!! renderSelect('row', 'col-md-4 mt-2', 'col-md-8 mt-2 mt-lg-0', 'title', __('master_data_relation_customer')['col_title'], 'js-data-example-ajax w-100 form-control mb-3', $master_data_relation_customer['title'] ?? '', $selectTitle ?? [], $mode) !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'name', __('master_data_relation_customer')['col_name'], $master_data_relation_customer['name'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'address', __('master_data_relation_customer')['col_address'], $master_data_relation_customer['address'] ?? '', $mode, 'required') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_subdistrict_name', '', __('master_data_relation_customer')['col_subdistrict'], $master_data_relation_customer['subdistrict_name'] ?? '', $mode, 'required', $action_subdistrict, ["subdistricts.code|like","subdistricts.name|like","cities.name|like","provinces.name|like"], [], array('browse_subdistrict_id|id','browse_subdistrict_name|name','browse_subdistrict_city_name|city_name','browse_subdistrict_province_name|province_name'), ['code','name','city_name','province_name'], 'subdistrict', ['Code','Name','City','Province'], [['field'=>'code', 'name' => 'subdistricts.code'], ['field'=>'name', 'name' => 'subdistricts.name'], ['field'=>'city_name', 'name' => 'cities.name'], ['field'=>'province_name', 'name' => 'provinces.name']], title_modal:'Get Subdistrict', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'subdistrict_id', '', $master_data_relation_customer['subdistrict_id'] ?? '', 'add', '', 'browse_subdistrict_id') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'city_name', __('master_data_relation_customer')['col_city'], $master_data_relation_customer['city_name'] ?? '', $mode, 'disabled', 'browse_subdistrict_city_name') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'province_name', __('master_data_relation_customer')['col_province'], $master_data_relation_customer['province_name'] ?? '', $mode, 'disabled', 'browse_subdistrict_province_name') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'office_phone', __('master_data_relation_customer')['col_office_phone'], $master_data_relation_customer['office_phone'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'fax', __('master_data_relation_customer')['col_fax'], $master_data_relation_customer['fax'] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'email', 'email', __('master_data_relation_customer')['col_email'], $master_data_relation_customer['email'] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'contact_person', __('master_data_relation_customer')['col_contact_person'], $master_data_relation_customer['contact_person'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'phone_number', __('master_data_relation_customer')['col_phone_number'], $master_data_relation_customer['phone_number'] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'send_name', __('master_data_relation_customer')['col_send_name'], $master_data_relation_customer['send_name'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'send_address', __('master_data_relation_customer')['col_send_address'], $master_data_relation_customer['send_address'] ?? '', $mode, 'required') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_send_city_name', '', __('master_data_relation_customer')['col_send_city'], $master_data_relation_customer['send_city_name'] ?? '', $mode, 'required', $action_send_city, ["cities.code|like","cities.name|like"], [], array('browse_send_city_id|id','browse_send_city_name|name'), ['code','name'], 'city', ['Code','Name'], [['field'=>'code', 'name' => 'cities.code'], ['field'=>'name', 'name' => 'cities.name']], title_modal:'Get City', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'send_city_id', '', $master_data_relation_customer['send_city_id'] ?? '', 'add', '', 'browse_send_city_id') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'send_phone', __('master_data_relation_customer')['col_send_phone'], $master_data_relation_customer['send_phone'] ?? '', $mode, 'required') !!}
                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('master_data_relation_customer')['col_remark'], 5, $master_data_relation_customer["remark"] ?? '', $mode, '') !!}
                                    {!! renderSelect('mb-3 row', 'col-md-4 mt-2', 'col-md-8 mt-2 mt-lg-0', 'is_active', __('master_data_relation_customer')['col_is_active'], 'js-data-example-ajax w-100 form-control mb-3', $master_data_relation_customer['is_active'] ?? '', $selectActive ?? [], $mode) !!}
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