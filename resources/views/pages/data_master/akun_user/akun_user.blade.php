    <div class="card">
        <div class="card-body">
            <div class="row g-5">
            </div>
            <!-- Unit Filter untuk Admin -->
        </div>
        <div class="col-md-2">
            </button>
        </div>
        </form>
    </div>

    <table class="table-bordered table-striped table">
        <thead>
            @if (!empty($headers) && is_array($headers))
                <tr class="text-center align-middle">
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            @else
                <tr>
                    <th>No data</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @forelse($user as $index => $item)
                <tr class="align-middle">
                    <td class="text-center">{{ $user->firstItem() + $index }}</td>
                    <td>{{ $item->units->nama_unit ?? '-' }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->email }}</td>
                    <td>{{ $item->yayasan_id ? 'Ya' : 'Tidak' }}</td>
                    <td>
                        <div class="d-flex gap-3"><a href="{{ url('akun-user/show', $item->id ?? '') }}"
                                class="link-primary text-muted"> <i class="ri-eye-line fs-20 align-middle"></i> Show
                            </a> <a href="{{ route('akun-user.edit', $item->id ?? '') }}"
                                class="link-warning text-muted"> <i class="ri-edit-line fs-20 align-middle"></i>
                                Edit
                            </a> {{-- <a href="{{ url('akun-user/delete', $item->id ?? '') }}"                                             class="link-danger text-muted">                                             <i class="ri-delete-bin-5-line fs-20 align-middle"></i>                                             Hapus                                         </a> --}}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    </div>
    </div>
@endsection
