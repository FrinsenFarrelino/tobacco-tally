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
                    <div class="card-icon bg-danger">
                        <i class="fas fa-info"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('dashboard')['purchase_not_approved'] }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalPurchaseNotApproved }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('dashboard')['purchase_approved'] }}</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalPurchaseApproved }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <a href="/transaction/purchase" class="mx-auto col-12 col-sm-9 col-md-6"><button class="btn btn-rounded btn-primary mx-auto col-12">{{ __('redirect_purchase') }}</button></a>
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