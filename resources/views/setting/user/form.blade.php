@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $setting_user["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('setting_user')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
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
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_employee_name', '', __('setting_user')['col_employee'], $setting_user['employee_name'] ?? '', $mode, 'required', $action_employee, ["employees.code|like","employees.name|like"], [], array('browse_employee_id|id','browse_employee_name|name'), ['code','name'], 'employee', ['Code','Name'], [['field'=>'code', 'name' => 'employees.code'], ['field'=>'name', 'name' => 'employees.name']], title_modal:'Get employee', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'employee_id', '', $setting_user['employee_id'] ?? '', 'add', '', 'browse_employee_id') !!}

                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_user_group_name', '', __('setting_user')['col_user_group'], $setting_user['user_group_name'] ?? '', $mode, 'required', $action_user_group, ["user_groups.name|like"], [], array('browse_user_group_id|id','browse_user_group_name|name','browse_user_group_branch_name|branch_name'), ['name','branch_name'], 'group-user', ['Name','Branch Name'], [['field'=>'name', 'name' => 'user_groups.name'], ['field'=>'branch_name', 'name' => 'branches.name']], title_modal:'Get User Group', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'user_group_id', '', $setting_user['user_group_id'] ?? '', 'add', '', 'browse_user_group_id') !!}

                                    @if ($errors->has('email'))
                                        {!! renderFieldError($errors, 'email') !!}
                                    @endif
                                    {!! renderInput('mb-3 row', 'col-lg-4 col-form-label', 'col-lg-8', 'email', 'email', __('setting_user')['col_email'], $setting_user["email"] ?? '', $mode, 'required', $errors->has('email') ?? '') !!}

                                    @if ($errors->has('username'))
                                        {!! renderFieldError($errors, 'username') !!}
                                    @endif
                                    {!! renderInput('mb-3 row', 'col-lg-4 col-form-label', 'col-lg-8', 'text', 'username', __('setting_user')['col_username'], $setting_user["username"] ?? '', $mode, 'required') !!}

                                    @if ($errors->has('name'))
                                        {!! renderFieldError($errors, 'name') !!}
                                    @endif
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'name', __('setting_user')['col_name'], $setting_user['name'] ?? '', $mode, 'required') !!}

                                    @if ($errors->has('password'))
                                        {!! renderFieldError($errors, 'password') !!}
                                    @endif

                                    @if($mode == 'add')
                                        {!! renderInput('mb-3 row', 'col-lg-4 col-form-label', 'col-lg-8', 'password', 'password', __('setting_user')['col_password'], '', $mode, 'required') !!}
                                    @else
                                        {!! renderInput('mb-3 row', 'col-lg-4 col-form-label', 'col-lg-8', 'password', 'password', __('setting_user')['col_password'], '', $mode, '') !!}
                                    @endif

                                    @if ($errors->has('confirm_password'))
                                        {!! renderFieldError($errors, 'confirm_password') !!}
                                    @endif

                                    @if($mode == 'add')
                                        {!! renderInput('mb-3 row', 'col-lg-4 col-form-label', 'col-lg-8', 'password', 'password_confirmation', __('setting_user')['col_confirm_password'], '', $mode, 'required') !!}
                                    @else
                                        {!! renderInput('mb-3 row', 'col-lg-4 col-form-label', 'col-lg-8', 'password', 'password_confirmation', __('setting_user')['col_confirm_password'], '', $mode, '') !!}
                                    @endif

                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('setting_user')['col_remark'], 5, $setting_user["remark"] ?? '', $mode, '') !!}
                                    {!! renderSelect('mb-3 row', 'col-md-4 mt-2', 'col-md-8 mt-2 mt-lg-0', 'is_active', __('setting_user')['col_is_active'], 'js-data-example-ajax w-100 form-control mb-3', $setting_user['is_active'] ?? '', $selectActive ?? [], $mode) !!}

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