@extends('error.template')

@section('title')
<title>403 &mdash; Tobacco Tally</title>
@endsection

@section('page-error')
<div class="page-error">
    <div class="page-inner">
        <h1>403</h1>
        <div class="page-description">
            You do not have access to this page.
        </div>
        <div class="page-search">
            <div class="mt-3">
                <a href="{{ route('dashboard') }}">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection