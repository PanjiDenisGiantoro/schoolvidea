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
                                    <th>{{ $header }}</th>
                        @else
                            <tr>
                                <th>No data</th>
                            </tr>
                            <tr class="align-middle">
                                <td class="text-center">{{ $user->firstItem() + $index }}</td>
                                <td>{{ $item->units->nama_unit ?? '-' }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                            Edit
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data ditemukan</td>
                            </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
