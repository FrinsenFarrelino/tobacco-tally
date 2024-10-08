@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}@if($mode != 'add')/{{ $setting_group_user["id"] }}@endif" method="post" id="myForm">
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
                                <h4>{{ __('setting_group_user')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
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
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'name', __('setting_group_user')['col_name'], $setting_group_user['name'] ?? '', $mode, 'required') !!}
                                    {!! renderBrowserInput('mb-3 row', 'col-md-4', 'col-8 col-md-5', 'browse_branch_name', '', __('setting_group_user')['col_branch'], $setting_group_user['branch_name'] ?? '', $mode, '', $action_branch, ["branches.code|like","branches.name|like"], [], array('browse_branch_id|id','browse_branch_name|name'), ['code','name'], '', ['Code','Name'], [['field'=>'code', 'name' => 'branches.code'], ['field'=>'name', 'name' => 'branches.name']], title_modal:'Get Branch', id_ajax: 'example') !!}
                                    {!! renderInput('row', 'col-md-4 col-form-label', 'col-md-8', 'hidden', 'branch_id', '', $setting_group_user['branch_id'] ?? '', 'add', '', 'browse_branch_id') !!}
                                    {!! renderTextArea('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'remark', __('setting_group_user')['col_remark'], 5, $setting_group_user["remark"] ?? '', $mode, '') !!}
                                    {!! renderSelect('mb-3 row', 'col-md-4 mt-2', 'col-md-8 mt-2 mt-lg-0', 'is_active', __('setting_group_user')['col_is_active'], 'js-data-example-ajax w-100 form-control mb-3', $setting_group_user['is_active'] ?? '', $selectActive ?? [], $mode) !!}
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