@extends('error.template')

@section('title')
<title>404 &mdash; Tobacco Tally</title>
@endsection

@section('page-error')
<div class="page-error">
    <div class="page-inner">
        <h1>404</h1>
        <div class="page-description">
            The page you were looking for could not be found.
        </div>
        <div class="page-search">
            <div class="mt-3">
                <a href="{{ route('dashboard') }}">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection