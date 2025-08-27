@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row g-5">

                <div class="col-lg-12">
                    <h5 class="card-title mb-4">
                        Loading State
                    </h5>
                    <div id="table-loading-state"></div>

                </div>

            </div> <!-- end row -->
        </div>
    </div>

    @include('partials.vendor-scripts')
    <!-- Gridjs Plugin js -->
    <script src="{{ asset('assets/vendor/gridjs/gridjs.umd.js') }}"></script>

    <!-- Gridjs Demo js -->
    <script src="{{ asset('assets/js/components/table-gridjs.js') }}"></script>
@endsection
