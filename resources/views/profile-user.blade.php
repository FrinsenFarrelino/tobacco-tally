@extends('template.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $title }} - {{ __('profile_user')['subtitle'] }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">{{ $title }}</div>
            <div class="breadcrumb-item">{{ __('profile_user')['subtitle'] }}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="settings-form">
                    <div class="card">
                        <form action="{{ url('/profile') }}" method="post">
                            {{ csrf_field() }}
                            <div class="d-flex justify-content-between">
                                <div class="card-header">
                                    <h4>{{ __('profile_user')['title'] }}</h4>
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

                                <input type="hidden" class="form-control" name="id" value="{{ $user->id }}">
                                <div class="row">
                                    <div class="col-xl-5">
                                        <div class="mb-3 row">
                                            <label class="col-lg-3 col-form-label">{{ __('profile_user')['name'] }}</label>
                                            <div class="col-lg-8">
                                                <input type="text" placeholder="Nama" class="form-control" name="name" value="{{ $user->name }}" disabled>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-lg-3 col-form-label">{{ __('profile_user')['username'] }}</label>
                                            <div class="col-lg-8">
                                                <input type="text" placeholder="Username" class="form-control" name="username" value="{{ $user->username }}" disabled>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-lg-3 col-form-label">{{ __('profile_user')['email'] }}</label>
                                            <div class="col-lg-8">
                                                <input type="text" placeholder="Email" class="form-control" name="email" value="{{ $user->email }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-7">
                                        <div class="mb-3 row">
                                            <label class="col-lg-4 col-form-label">{{ __('profile_user')['old_password'] }} <span class="text-danger">*</span> </label>
                                            <div class="col-lg-7">
                                                <input type="password" class="form-control" name="current_password" required>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-lg-4 col-form-label">{{ __('profile_user')['new_password'] }} <span class="text-danger">*</span> </label>
                                            <div class="col-lg-7">
                                                <input type="password" class="form-control" name="password" required>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-lg-4 col-form-label">{{ __('profile_user')['confirm_password'] }} <span class="text-danger">*</span> </label>
                                            <div class="col-lg-7">
                                                <input type="password" class="form-control" name="password_confirmation" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $('#alert').delay(3000).fadeOut('slow');
</script>
@endsection