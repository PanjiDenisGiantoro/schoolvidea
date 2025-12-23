@extends("layouts.merchant")
@push("styles")
    <link rel="stylesheet" href="{{ asset("assets/css/merchant.css") }}" />
@endpush

@section("content")

<div class="hero">
    <div class="login-box">
        <h3>
            LOGIN
            <br />
            MERCHANT
        </h3>
        <form>
            <input type="text" placeholder="Username" />
            <input type="password" placeholder="Password" />
            <button type="submit">Login</button>
            <a href="{{ url("/merchant") }}">Kembali</a>
        </form>
    </div>
    <p class="text-muted mb-3 text-center">
        &copy; {{ date("Y") }} VideaClass by PT. Inovasi Dalam Negeri - All
        Rights Reserved.
    </p>
</div>
