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
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-money"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('dashboard')['total_sale_this_month'] }}</h4>
                        </div>
                        <div class="card-body text-success">
                            {{ $total_sales_this_month }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('dashboard')['total_purchase_this_month'] }}</h4>
                        </div>
                        <div class="card-body text-danger">
                            {{ $total_purchase_this_month }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-usd"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>{{ __('dashboard')['profit'] }}</h4>
                        </div>
                        <div class="card-body {{ $is_profit_minus == true ? 'text-danger' : 'text-success' }}">
                            {{ $total_profit }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    {{-- <div class="card-header">
                        <h4>{{ __('dashboard')['sale_buy'] }}</h4>
                    </div> --}}
                    <div class="card-body">
                        <canvas id="salesPurchaseChart" width="800" height="400"></canvas>
                        {{-- <div class="statistic-details mt-sm-4">
                            <div class="statistic-details-item">
                                <span class="text-muted"><span class="text-primary"><i class="fas fa-caret-up"></i></span> 7%</span>
                                <div class="detail-value">$243</div>
                                <div class="detail-name">Today's Sales</div>
                            </div>
                            <div class="statistic-details-item">
                                <span class="text-muted"><span class="text-danger"><i class="fas fa-caret-down"></i></span> 23%</span>
                                <div class="detail-value">$2,902</div>
                                <div class="detail-name">This Week's Sales</div>
                            </div>
                            <div class="statistic-details-item">
                                <span class="text-muted"><span class="text-primary"><i class="fas fa-caret-up"></i></span>9%</span>
                                <div class="detail-value">$12,821</div>
                                <div class="detail-name">This Month's Sales</div>
                            </div>
                            <div class="statistic-details-item">
                                <span class="text-muted"><span class="text-primary"><i class="fas fa-caret-up"></i></span> 19%</span>
                                <div class="detail-value">$92,142</div>
                                <div class="detail-name">This Year's Sales</div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('dashboard')['top_selling'] }}</h4>
                        <div>(KG)</div>
                    </div>
                    <div class="card-body">
                        @foreach($topSellings as $topSelling)
                        <div class="mb-4">
                            <div class="text-small float-right font-weight-bold text-muted">{{ $topSelling['amount'] }}</div>
                            <div class="mb-1 font-weight-bold">{{ $topSelling['item_name'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('dashboard')['stock_summary'] }}</h4>
                        <div>(KG)</div>
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

        var ctx = document.getElementById('salesPurchaseChart').getContext('2d');
        var monthlyData = @json($monthlyData); // Convert PHP array to JavaScript object

        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(monthlyData), // Array of month/year labels
                datasets: [
                    {
                        label: "{{ __('dashboard')['total_sale'] }}",
                        data: Object.values(monthlyData).map(data => data.total_sales), // Array of total sales data
                        borderColor: 'blue',
                        borderWidth: 2,
                        fill: false
                    },
                    {
                        label: "{{ __('dashboard')['total_purchase'] }}",
                        data: Object.values(monthlyData).map(data => data.total_purchases), // Array of total purchases data
                        borderColor: 'green',
                        borderWidth: 2,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: "{{ __('dashboard')['monthly_sale_purchase'] }}"
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: "{{ __('dashboard')['month_year'] }}"
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: "{{ __('dashboard')['amount'] }}"
                        },
                        ticks: {
                            suggestedMin: 0 // Set minimum y-axis value to 0
                        }
                    }]
                }
            }
        });
    });
</script>
@endsection