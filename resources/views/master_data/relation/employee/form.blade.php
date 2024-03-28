@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $master_data_relation_employee["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('master_data_relation_employee')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
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
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'code', __('master_data_relation_employee')['col_code'], $master_data_relation_employee['code'] ?? '', $mode, 'disabled placeholder="Auto Generated"') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'name', __('master_data_relation_employee')['col_name'], $master_data_relation_employee['name'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'nik', __('master_data_relation_employee')['col_nik'], $master_data_relation_employee['nik'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'address', __('master_data_relation_employee')['col_address'], $master_data_relation_employee['address'] ?? '', $mode, 'required') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_subdistrict_name', '', __('master_data_relation_employee')['col_subdistrict'], $master_data_relation_employee['subdistrict_name'] ?? '', $mode, 'required', $action_subdistrict, ["subdistricts.code|like","subdistricts.name|like","cities.name|like","provinces.name|like"], [], array('browse_subdistrict_id|id','browse_subdistrict_name|name','browse_subdistrict_city_name|city_name','browse_subdistrict_province_name|province_name'), ['code','name','city_name','province_name'], 'subdistrict', ['Code','Name','City','Province'], [['field'=>'code', 'name' => 'subdistricts.code'], ['field'=>'name', 'name' => 'subdistricts.name'], ['field'=>'city_name', 'name' => 'cities.name'], ['field'=>'province_name', 'name' => 'provinces.name']], title_modal:'Get Subdistrict', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'subdistrict_id', '', $master_data_relation_employee['subdistrict_id'] ?? '', 'add', '', 'browse_subdistrict_id') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'city_name', __('master_data_relation_employee')['col_city'], $master_data_relation_employee['city_name'] ?? '', $mode, 'disabled', 'browse_subdistrict_city_name') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'province_name', __('master_data_relation_employee')['col_province'], $master_data_relation_employee['province_name'] ?? '', $mode, 'disabled', 'browse_subdistrict_province_name') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'postal_code', __('master_data_relation_employee')['col_postal_code'], $master_data_relation_employee['postal_code'] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'phone_number', __('master_data_relation_employee')['col_phone_number'], $master_data_relation_employee['phone_number'] ?? '', $mode, 'required') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'mobile_phone_number', __('master_data_relation_employee')['col_mobile_phone_number'], $master_data_relation_employee['mobile_phone_number'] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'number', 'whatsapp', __('master_data_relation_employee')['col_whatsapp'], $master_data_relation_employee['whatsapp'] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'email', 'email', __('master_data_relation_employee')['col_email'], $master_data_relation_employee['email'] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'telegram', __('master_data_relation_employee')['col_telegram'], $master_data_relation_employee['telegram'] ?? '', $mode, '') !!}
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'skype', __('master_data_relation_employee')['col_skype'], $master_data_relation_employee['skype'] ?? '', $mode, '') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_division_name', '', __('master_data_relation_employee')['col_division'], $master_data_relation_employee['division_name'] ?? '', $mode, 'required', $action_division, ["divisions.code|like","divisions.name|like"], [], array('browse_division_id|id','browse_division_name|name'), ['code','name'], 'division', ['Code','Name'], [['field'=>'code', 'name' => 'divisions.code'], ['field'=>'name', 'name' => 'divisions.name']], title_modal:'Get Division', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'division_id', '', $master_data_relation_employee['division_id'] ?? '', 'add', '', 'browse_division_id') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_position_name', '', __('master_data_relation_employee')['col_position'], $master_data_relation_employee['position_name'] ?? '', $mode, 'required', $action_position, ["positions.code|like","positions.name|like"], [], array('browse_position_id|id','browse_position_name|name'), ['code','name'], 'position', ['Code','Name'], [['field'=>'code', 'name' => 'positions.code'], ['field'=>'name', 'name' => 'positions.name']], title_modal:'Get Position', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'position_id', '', $master_data_relation_employee['position_id'] ?? '', 'add', '', 'browse_position_id') !!}
                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('master_data_relation_employee')['col_remark'], 5, $master_data_relation_employee["remark"] ?? '', $mode, '') !!}
                                    {!! renderSelect('mb-3 row', 'col-md-4 mt-2', 'col-md-8 mt-2 mt-lg-0', 'is_active', __('master_data_relation_employee')['col_is_active'], 'js-data-example-ajax w-100 form-control mb-3', $master_data_relation_employee['is_active'] ?? '', $selectActive ?? [], $mode) !!}
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