@extends('template.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ __('dashboard')['name'] }}</h1>
    </div>
    <div class="section-body">
        <div class="text-center mb-5">
            <h2>{{ __('welcome') }}, {{ auth()->user()->name }}!</h2>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('dashboard')['outgoing_item'] }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalOutgoing }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('dashboard')['incoming_item'] }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalIncoming }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="mx-auto col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('dashboard')['on_the_way'] }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalOnTheWay }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="mx-auto col-lg-6 col-md-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('dashboard')['stock_summary'] }}</h4>
                    </div>
                    <div class="card-body">
                        @foreach($dataWarehouses as $dataWarehouse)
                        <div class="mb-4">
                            <div class="text-small float-right font-weight-bold text-muted">{{ $dataWarehouse['stock'] }}</div>
                            <div class="mb-1"><span class="font-weight-bold">{{ $dataWarehouse['item_name'] }}</span> - {{ $dataWarehouse['name'] }} ({{ $dataWarehouse['code'] }}) - {{ $dataWarehouse['branch_name'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        setMenuActive();
    });
</script>
@endsection