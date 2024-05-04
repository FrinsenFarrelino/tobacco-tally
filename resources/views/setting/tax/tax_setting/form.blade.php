@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="/{{ $menu }}" method="post" id="myForm">
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
                                <h4>{{ __('setting_tax_tax_setting')[$mode] }} {{ $title }} - {{ $subtitle }}</h4>
                            </div>
                            <div class="d-flex justify-content-end align-items-center pr-3">
                                @if (isset($list_nav_button['save']))
                                    {!! $list_nav_button['save'] !!}
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                            <div class="alert alert-success alert-dismissible show fade" id="alert">
                                <div class="alert-body">
                                    <button class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    {{ session()->get('success') }}
                                </div>
                            </div>
                            @endif
                            @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible show fade" id="alert">
                                <div class="alert-body">
                                    <button class="close" data-dismiss="alert">
                                        <span>&times;</span>
                                    </button>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            <div class="row">
                                <div class="col-md-8">
                                    {!! renderInput('mb-3 row', 'col-md-4 col-form-label', 'col-md-8', 'text', 'ppn', __('setting_tax_tax_setting')['col_ppn'], $setting_tax_tax_setting['ppn'] ?? '', $mode, 'required') !!}
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