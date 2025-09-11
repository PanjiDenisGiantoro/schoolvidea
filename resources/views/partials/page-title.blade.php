<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            @php
            $tahun_ajaran = \App\Models\Tahun_ajaran::isactive()->first();
            if ($tahun_ajaran){
                if($tahun_ajaran->tahun_ajaran == ''){
                    $tahun_ajaran->tahun_ajaran = 'Tahun Ajaran Belum Diatur';
                }else{
                    $tahun_ajaran->tahun_ajaran = $tahun_ajaran->tahun_ajaran;
                }
                if($tahun_ajaran->semester == ''){
                    $tahun_ajaran->semester = 'Semester Belum Diatur';
                }else{
                    $tahun_ajaran->semester = $tahun_ajaran->semester;
                }
            }
            @endphp
            <h4 class="mb-0 fw-semibold">{{ $subTitle ?? '' }} ({{ $tahun_ajaran->tahun_ajaran }} - {{ $tahun_ajaran->semester }})</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);">{{ $title ?? '' }}</a>
                </li>
                <li class="breadcrumb-item active">{{ $subTitle ?? '' }}</li>
            </ol>
        </div>
    </div>
</div>
