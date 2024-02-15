@extends(Auth::user()->layout_blade)

@section('content')
<div class="d-block" id="alert-container">
    @include('partials.alert')
</div>

<form method="POST" action="{{ route('password.update') }}" id="myForm" data-alert-container="#alert-container">
    @csrf
    <div class="row g-0 mb-2">
        <div class="col-sm-6 col-md-5 col-lg-4">
            <div class="p-3 border rounded-3 bg-gray-100">
                <label class="d-block required">Password Lama</label>
                <input type="password" name="old_password" class="form-control" placeholder="Password Lama" autocomplete="off" required autofocus>
            </div>
        </div>
    </div>
    <div class="row g-0 mb-2">
        <div class="col-sm-6 col-md-5 col-lg-4">
            <div class="p-3 border rounded-3 bg-gray-100">
                <div class="mb-3">
                    <label class="d-block required">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Password Baru" autocomplete="off" required>
                </div>
                <div class="mb-0">
                    <label class="d-block required">Ketik Ulang Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ketik Ulang Password" autocomplete="off" required>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-0">
        <div class="col-sm-6 col-md-5 col-lg-4">
            <button type="submit" class="d-block w-100 btn btn-sm btn-primary">
                <i class="fa-solid fa-save me-1"></i>
                Ganti Password
            </button>
        </div>
    </div>
</form>

@endsection
