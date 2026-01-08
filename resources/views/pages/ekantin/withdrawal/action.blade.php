<div class="d-flex gap-1 justify-content-center">
    {{-- Detail --}}
    <a
        href="{{ route("merchant_withdrawal.show", $wd->id) }}"
        class="btn btn-sm btn-primary rounded-pill"
        title="Detail Penarikan"
    >
        <i class="ri-eye-line"></i>
    </a>

    {{-- Print PDF --}}
    @if ($wd->status === "approved")
        <a
            href="{{ route("merchant_withdrawal.print", $wd->id) }}"
            target="_blank"
            class="btn btn-sm btn-warning rounded-pill"
            title="Print Struk"
        >
            <i class="ri-printer-line"></i>
        </a>
    @endif
</div>
