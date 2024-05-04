<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta name="generator" content="Aspose.Words for .NET 24.3.0" />
    <title></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <style>
        @page {
            margin: 50px 25px;
        }

        header {
            position: fixed;
            top: -60px;
            left: 0px;
            right: 0px;
            height: 20px;
            font-size: 20px !important;
            color: white;
            text-align: center;
            line-height: 35px;
        }

        .pagenum:before {
            content: counter(page);
        }

        footer {
            /* for make border on top */
            /* border-top: 1px solid #000; */
            position: fixed;
            bottom: -25px;
            left: 0px;
            right: 0px;
            height: 30px;
            font-size: 16px !important;
            color: black;
            line-height: 35px;
        }

        body {
            font-family: 'Times New Roman';
            font-size: 12pt
        }

        p {
            margin: 0pt
        }

        .content {
            /* Set a fixed height for your content area */
            height: calc(100vh - 100px);
            /* Adjust based on your footer and header heights */
            /* Add additional styling as needed */
        }

        .table-bordered {
            border: 1px solid #000000 !important;
            border-color: #000000 !important;
        }

        .no-text-underline {
            height: 20px;
            /* adjust the height to your liking */
            width: 100px;
            /* adjust the width to your liking */
            display: inline-block;
            position: relative;
            border-bottom: 1px solid transparent;
            /* create a transparent border at the bottom */
        }

        .no-text-underline:after {
            content: '';
            position: absolute;
            left: -20;
            right: 0;
            bottom: -1px;
            /* adjust the position of the underline */
            height: 1px;
            /* adjust the height of the underline */
            background-color: black;
            /* set the color of the underline to black */
        }

        .page-break {
            page-break-before: always;
        }

        /* .bottom {
            bottom: 0%;
            position: fixed;
            width: 100%;
        } */
    </style>
</head>

<body>
    <!-- Define Footer Block -->
    <footer>
        Tgl Cetak : {{ date('d M Y, H:i') }}
    </footer>
    <main>
        <div class="content">

            <table class="table table-sm table-borderless">
                <thead>
                    <tr>
                        <td style="text-align: left">
                            <p style="padding-bottom: 15%;"><span style="font-family:Calibri; font-size:11pt;">TY</span>
                            </p>
                        </td>
                        <td scope="col" style="text-align: center">
                            <p>
                            <h3 style="font-family:Calibri;">CV. YES AGRO</h3>
                            </p>
                            <h6><small>FAKTUR PENJUALAN</small>
                            </h6>
                        </td>
                        <td scope="col" style="text-align: right;">
                            <p>
                            <h6 style="font-family:Calibri;">TGL. ORDER &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-weight: normal;">{{ date_format(date_create($data['date']),"d M Y") }}</span></h6>
                            </p>
                        </td>
                    </tr>
                </thead>
            </table>
            <table class="table table-borderless table-sm">
                <tbody>
                    <tr>
                        <td style="font-weight: bold; width: 10%;">No Penjualan</td>
                        <td>:&nbsp;{{ $data['code'] }}</td>
                        <td style="font-weight: bold; width: 10%;">Supplier</td>
                        <td>:&nbsp;{{ $data['customer_name'] }} ({{ $data['customer_code'] }})</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; width: 10%;">Tanggal</td>
                        <td>:&nbsp;{{ $data['date'] }}</td>
                        <td style="font-weight: bold; width: 10%;">Kirim Ke</td>
                        <td>:&nbsp;{{ $data['customer_send_address'] }} - {{ $data['customer_send_city'] }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold; width: 10%;">Telp</td>
                        <td>:&nbsp;{{ $data['customer_phone_number'] }}</td>
                    </tr>
                </tbody>
            </table>

            <br />

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th scope="col" style="text-align: center">No</th>
                        <th scope="col" style="text-align: center">Nama Barang</th>
                        <th scope="col">QTY</th>
                        <th scope="col">Harga</th>
                        <th scope="col">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $number = 1;
                    @endphp
                    @foreach($data_grid as $res)
                    <tr>
                        <th scope="row" style="text-align: center">{{ $number }}</th>
                        <td style="text-align: center">{{ $res['item_name'] }}</td>
                        <td>{{ $res['amount'] }} {{ $res['unit_name'] }}</td>
                        <td>{{ $res['sell_price'] }}</td>
                        <td>{{ $res['subtotal'] }}</td>
                    </tr>
                    {{ $number++ }}
                    @endforeach
                </tbody>
            </table>

            <br>
            <br>
            <br>

        </div>
        <div class="bottom">
            <table class="table table-bordered">
            </table>
            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <td style="font-weight: bold; ">Keterangan</td>
                        <td>{{ $data['remark'] }}</td>
                        <td></td>
                        <td style="font-weight: bold;">Subtotal</td>
                        <td>{{ $data['subtotal'] }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold;">PPN&nbsp;&nbsp;<span style="font-weight: normal;">{{ $data['ppn'] }}</span></td>
                        <td>{{ $data['ppn_price'] }}</td>
                    </tr>
                    <tr>
                        @php
                            $total = strtok($data['total'], ',');
                            $grandtotal = str_replace(['Rp ','Rp.', '.', ','], '', $total);
                        @endphp
                        <td style="font-weight: bold;">Terbilang</td>
                        <td>{{ generateTerbilang($grandtotal) }} rupiah</td>
                        <td></td>
                        <td style="font-weight: bold;">TOTAL</td>
                        <td>{{ $data['total'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>