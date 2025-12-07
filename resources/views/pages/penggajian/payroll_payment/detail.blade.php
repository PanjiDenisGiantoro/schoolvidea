@extends("layouts.app")
@section("title", "Detail Pembayaran Gaji")

@section("content")
    @include(
        "partials.page-title",
        [
            "title" => "Detail Pembayaran Gaji",
            "subTitle" => "Penggajian",
        ]
    )

    <style>
        .min-h-screen {
            min-height: 100vh;
        }
        .bg-gray-50 {
            background-color: transparent;
        }
        .py-8 {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .max-w-4xl {
            max-width: 56rem;
        }
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .sm\:px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        .lg\:px-8 {
            padding-left: 2rem;
            padding-right: 2rem;
        }
        .text-3xl {
            font-size: 1.875rem;
            line-height: 2.25rem;
        }
        .font-bold {
            font-weight: 700;
        }
        .text-gray-900 {
            color: #111827;
        }
        .mb-8 {
            margin-bottom: 2rem;
        }
        .grid {
            display: grid;
        }
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        .lg\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .gap-8 {
            gap: 2rem;
        }
        .bg-white {
            background-color: #ffffff;
        }
        .shadow-lg {
            box-shadow:
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .rounded-lg {
            border-radius: 0.5rem;
        }
        .p-6 {
            padding: 1.5rem;
        }
        .text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }
        .font-semibold {
            font-weight: 600;
        }
        .text-gray-800 {
            color: #1f2937;
        }
        .mb-4 {
            margin-bottom: 1rem;
        }
        .flex {
            display: flex;
        }
        .items-center {
            align-items: center;
        }
        .w-16 {
            width: 4rem;
        }
        .h-16 {
            height: 4rem;
        }
        .rounded-full {
            border-radius: 9999px;
        }
        .mr-4 {
            margin-right: 1rem;
        }
        .text-lg {
            font-size: 1.125rem;
            line-height: 1.75rem;
        }
        .font-medium {
            font-weight: 500;
        }
        .text-gray-600 {
            color: #4b5563;
        }
        .space-y-2 > * + * {
            margin-top: 0.5rem;
        }
        .space-y-4 > * + * {
            margin-top: 1rem;
        }
        .justify-between {
            justify-content: space-between;
        }
        .text-green-600 {
            color: #059669;
        }
        .px-3 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        .py-1 {
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        .bg-green-100 {
            background-color: #dcfce7;
        }
        .text-green-800 {
            color: #166534;
        }
        .bg-yellow-100 {
            background-color: #fef3c7;
        }
        .text-yellow-800 {
            color: #92400e;
        }
        .mt-6 {
            margin-top: 1.5rem;
        }
        .w-full {
            width: 100%;
        }
        .bg-blue-600 {
            background-color: #2563eb;
        }
        .text-white {
            color: #ffffff;
        }
        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .hover\:bg-blue-700:hover {
            background-color: #1d4ed8;
        }
        .transition {
            transition-property:
                color, background-color, border-color, text-decoration-color,
                fill, stroke, opacity, box-shadow, transform, filter,
                backdrop-filter;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
        .mt-8 {
            margin-top: 2rem;
        }
        .mb-2 {
            margin-bottom: 0.5rem;
        }
    </style>

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Profile Section -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        User Profile
                    </h2>
                    <div class="flex items-center mb-4">
                        <img
                            src="{{ $payment->officer->image ? asset($payment->officer->image) : "https://via.placeholder.com/100" }}"
                            alt="Profile Picture"
                            class="w-16 h-16 rounded-full mr-4"
                        />
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $payment->officer->name }}
                            </h3>
                            <p class="text-gray-600">
                                {{ $payment->officer->user->email }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p>
                            <strong>Phone:</strong>
                            {{ $payment->officer->no_hp ?? "N/A" }}
                        </p>
                        <p>
                            <strong>Address:</strong>
                            {{ $payment->officer->alamat ?? "N/A" }}
                        </p>
                        <p>
                            <strong>Member Since:</strong>
                            {{ $payment->officer->created_at->format("M Y") }}
                        </p>
                    </div>
                </div>

                <!-- Payment Details Section -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Payment Information
                    </h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction ID:</span>
                            <span class="font-medium">
                                {{ $payment->transaction_id ?? "TXN123456" }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-medium text-green-600">
                                Rp
                                {{ number_format($payment->net_payment ?? 99.99, 0, ",", ".") }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-medium">
                                {{ $payment->method ?? "Credit Card" }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date:</span>
                            <span class="font-medium">
                                {{ $payment->month ?? "2023-10-01" }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status:</span>
                            <span
                                class="px-3 py-1 rounded-full text-sm font-medium {{ $payment->status == "completed" ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800" }}"
                            >
                                {{ ucfirst($payment->status ?? "pending") }}
                            </span>
                        </div>
                    </div>
                    @if ($payment->status == "pending")
                        <button
                            onclick="history.back()"
                            class="mt-6 w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition"
                        >
                            Complete Payment
                        </button>
                    @endif
                </div>
            </div>

            <!-- Optional: Additional Actions or Notes -->
            <div class="mt-8 bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Notes</h3>
                <p class="text-gray-600">
                    {{ $payment->notes ?? "No additional notes for this payment." }}
                    - Rp.
                    {{ number_format($payment->salary_note, 0, ",", ".") ?? 0 }}
                </p>
            </div>
        </div>
    </div>
@endsection

@push("script")
    @if ($payment)
        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    searching: false,
                    scrollX: true,
                    language: {
                        url: '{{ asset("assets/datatables/id.json") }}',
                    },
                });
            });
        </script>
    @endif
@endpush
