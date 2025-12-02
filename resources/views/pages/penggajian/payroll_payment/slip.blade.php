<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Slip Gaji - {{ $payment->officer->name }}</title>
        <style>
            /* Gaya Dasar dan Font - Dibuat lebih kompak untuk A5 Landscape */
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 9pt; /* Ukuran font umum dikecilkan */
                color: #333;
                margin: 0;
                padding: 10px;
            }

            /* Container Utama - Memanfaatkan lebar penuh Landscape */
            .payslip-container {
                width: 98%;
                max-width: 900px;
                margin: 0 auto;
                border: 1px solid #eee;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Shadow lebih tipis */
                padding: 15px; /* Padding dikurangi */
                background-color: #fff;
            }

            /* Header Perusahaan/Slip */
            .header {
                text-align: center;
                padding-bottom: 10px;
                margin-bottom: 10px; /* Margin dikurangi */
                border-bottom: 2px solid #28a745; /* Garis lebih tipis */
            }
            .header h3 {
                margin: 0;
                color: #28a745;
                font-size: 15pt; /* Dikecilkan */
                font-weight: 600;
            }
            .header p {
                margin: 4px 0 0;
                color: #666;
                font-size: 8.5pt;
            }

            /* Detail Karyawan */
            .employee-details {
                width: 100%;
                margin-bottom: 0px;
                font-size: 9pt;
            }
            .employee-details td {
                padding: 2px 0; /* Padding vertikal sangat dikurangi */
                vertical-align: top;
            }
            .employee-details td:first-child {
                width: 20%;
                font-weight: bold;
            }
            .employee-details td:nth-child(2) {
                width: 3%;
            }

            /* Tabel Gaji dan Potongan */
            .earnings-deductions {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0 0px; /* Jarak antar baris dikurangi */
                margin-bottom: 0px;
            }
            .earnings-deductions th,
            .earnings-deductions td {
                padding: 5px 10px;
                text-align: left;
                border-bottom: 1px solid #f0f0f0;
                font-size: 8.5pt; /* Ukuran font di rincian dikecilkan */
            }
            .earnings-deductions th {
                background-color: #f8f9fa;
                font-weight: 600;
                color: #495057;
                text-transform: uppercase;
                font-size: 8pt;
            }
            .earnings-deductions .amount {
                text-align: right;
                font-weight: bold;
                width: 25%;
            }

            /* Bagian Total Akhir */
            .net-payment-box {
                background-color: #e6ffed;
                border: 1px solid #28a745;
                padding: 10px; /* Padding dikurangi */
                margin-top: 2px;
                text-align: right;
                border-radius: 4px;
            }
            .net-payment-box strong {
                display: block;
                font-size: 10pt;
                color: #28a745;
                margin-bottom: 2px;
            }
            .net-payment-box h2 {
                margin: 0;
                font-size: 17pt; /* Dikecilkan */
                color: #28a745;
                font-weight: 700;
            }

            /* Catatan dan Tanda Tangan */
            .notes-section {
                margin-top: 0px;
                padding: 8px;
                border-left: 4px solid #ffc107;
                background-color: #fffbe6;
                font-size: 8pt;
            }
            .signatures {
                width: 100%;
                margin-top: 5px; /* Margin dikurangi */
            }
            .signatures td {
                width: 33.33%;
                text-align: center;
                padding-top: 5px; /* Jarak untuk tanda tangan dikurangi */
                font-size: 8.5pt;
            }
            .signatures .name-line {
                border-bottom: 1px solid #333;
                display: inline-block;
                width: 90%;
                margin-top: 3px;
                margin-bottom: 2px;
            }
        </style>
    </head>
    <body>
        <div class="payslip-container">
            <div class="header">
                <h3>SLIP GAJI BULANAN</h3>
                <p>
                    Periode:
                    **{{ \Carbon\Carbon::createFromDate($payment->payment_year, $payment->payment_month, 1)->format("F Y") }}**
                </p>
            </div>

            <table class="employee-details">
                <tr>
                    <td>Nama Karyawan</td>
                    <td>:</td>
                    <td>**{{ $payment->officer->name }}**</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>
                        {{ $payment->officer->position->positions_name ?? "-" }}
                    </td>
                </tr>
                <tr>
                    <td>Tanggal Pembayaran</td>
                    <td>:</td>
                    <td>{{ date("d F Y") }}</td>
                </tr>
            </table>

            <table class="earnings-deductions">
                <thead>
                    <tr>
                        <th colspan="2">Penerimaan</th>
                        <th>Jumlah (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">Gaji Pokok</td>
                        <td class="amount">
                            Rp
                            {{ number_format($payment->base_salary, 0, ",", ".") }}
                        </td>
                    </tr>

                    {{-- Pengecekan allowances --}}
                    @if (is_countable($payment->allowances) && count($payment->allowances) > 0)
                        @foreach ($payment->allowances as $allowance)
                            <tr>
                                <td colspan="2">{{ $allowance->name }}</td>
                                <td class="amount">
                                    Rp
                                    {{ number_format($allowance->amount, 0, ",", ".") }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <tr style="font-weight: bold; background-color: #f0f8ff">
                        <td colspan="2">TOTAL PENERIMAAN BRUTO</td>
                        <td class="amount">
                            Rp
                            {{ number_format($payment->total_earnings, 0, ",", ".") }}
                        </td>
                    </tr>
                </tbody>
                <thead>
                    <tr>
                        <th colspan="2" style="padding-top: 10px">Potongan</th>
                        <th style="padding-top: 10px">Jumlah (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Pengecekan deductions --}}
                    @if (is_countable($payment->deductions) && count($payment->deductions) > 0)
                        @foreach ($payment->deductions as $deduction)
                            <tr>
                                <td colspan="2">{{ $deduction->name }}</td>
                                <td class="amount">
                                    (-) Rp
                                    {{ number_format($deduction->amount, 0, ",", ".") }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <tr style="font-weight: bold; background-color: #fff0f0">
                        <td colspan="2">TOTAL POTONGAN</td>
                        <td class="amount">
                            (-) Rp
                            {{ number_format($payment->total_deductions, 0, ",", ".") }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="net-payment-box">
                <strong>TOTAL GAJI BERSIH DIBAYARKAN</strong>
                <h3>
                    Rp {{ number_format($payment->net_payment, 0, ",", ".") }}
                </h3>
            </div>

            {{--
                @if ($payment->notes)
                <div class="notes-section">
                **Catatan:**
                <p style="margin: 3px 0 0">{{ $payment->notes }}</p>
                </div>
                @endif
            --}}
            <table class="signatures">
                <tr>
                    <td>
                        Disetujui Oleh,
                        <br />
                        <br />
                        <br />
                        <br />
                        <span class="name-line">
                            (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                        </span>
                        <br />
                        HRD / Manajer
                    </td>
                    <td>
                        Dibuat Oleh,
                        <br />
                        <br />
                        <br />
                        <br />
                        <span class="name-line">
                            (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                        </span>
                        <br />
                        Bendahara
                    </td>
                    <td>
                        Penerima,
                        <br />
                        <br />
                        <br />
                        <br />
                        <span class="name-line">
                            ({{ $payment->officer->name }})
                        </span>
                        <br />
                        Karyawan
                    </td>
                </tr>
            </table>

            <div
                style="
                    text-align: center;
                    margin-top: 15px; /* Margin dikurangi */
                    font-size: 7.5pt; /* Font dikecilkan agar hemat tempat */
                    color: #aaa;
                "
            >
                Slip ini dicetak secara otomatis dan berlaku sah tanpa tanda
                tangan basah.
            </div>
        </div>
    </body>
</html>
