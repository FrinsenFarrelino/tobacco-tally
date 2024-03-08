@extends('error.template')

@section('title')
<title>500 &mdash; Tobacco Tally</title>
@endsection

@section('page-error')
<div class="page-error">
    <div class="page-inner">
        <h1>503</h1>
        <div class="page-description">
            Be right back.
        </div>
        <div class="page-search">
            <div class="mt-3">
                <a href="{{ route('dashboard') }}">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection