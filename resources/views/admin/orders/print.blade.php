<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f3f4f6;
            color: #000;
            font-size: 12px;
            line-height: 1.4;
        }

        .receipt-container {
            width: 58mm;
            margin: 20px auto;
            background: #fff;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .font-bold { font-weight: bold; }
        .text-lg { font-size: 16px; }
        .text-xl { font-size: 18px; }
        .text-sm { font-size: 10px; }

        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }

        .border-t { border-top: 1px dashed #000; }
        .border-b { border-bottom: 1px dashed #000; }
        
        .py-2 { padding-top: 8px; padding-bottom: 8px; }

        .flex { display: flex; }
        .justify-between { justify-content: space-between; }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .item-name { width: 60%; }
        .item-qty { width: 10%; text-align: center; }
        .item-price { width: 30%; text-align: right; }

        /* Sembunyikan tombol saat print sungguhan */
        @media print {
            body {
                background: none;
            }
            .receipt-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .no-print {
                display: none !important;
            }
        }

        .print-btn {
            display: block;
            width: 58mm;
            margin: 0 auto 20px;
            background: #2563eb;
            color: white;
            text-align: center;
            padding: 10px;
            text-decoration: none;
            font-family: sans-serif;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-weight: bold;
        }
        .print-btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn no-print">🖨️ Cetak Struk</button>

    <div class="receipt-container">
        {{-- Header --}}
        <div class="text-center mb-4">
            <h1 class="font-bold text-lg mb-1">{{ $order->tenant->name }}</h1>
            <p class="text-sm">Struk Kasir</p>
        </div>

        {{-- Order Info --}}
        <div class="border-b py-2 mb-2">
            <div class="flex justify-between">
                <span>Waktu</span>
                <span>{{ $order->created_at->format('d/m/y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>No. Order</span>
                <span>{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg mt-2">
                <span>Meja</span>
                <span>{{ $order->table->table_number }}</span>
            </div>
            @if($order->customer_name)
            <div class="flex justify-between mt-1">
                <span>Pelanggan</span>
                <span>{{ $order->customer_name }}</span>
            </div>
            @endif
        </div>

        {{-- Items --}}
        <div class="border-b py-2 mb-2">
            @foreach($order->items as $item)
            <div class="item-row">
                <div class="item-name">
                    {{ $item->product->name }}
                    @if($item->notes)
                        <br><span class="text-sm">- {{ $item->notes }}</span>
                    @endif
                </div>
                <div class="item-qty">{{ $item->quantity }}x</div>
                <div class="item-price">{{ number_format($item->subtotal, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="py-2 mb-2">
            <div class="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between mt-2 text-sm">
                <span>Pembayaran</span>
                <span style="text-transform: uppercase;">{{ $order->payment_method }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span>Status</span>
                <span style="text-transform: uppercase;">{{ $order->payment_status }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-4 text-sm border-t py-2">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Silakan datang kembali.</p>
        </div>
    </div>

    {{-- Auto trigger print saat halaman dibuka --}}
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
