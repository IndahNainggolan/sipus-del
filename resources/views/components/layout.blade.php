
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SIPUS-Del' }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f3ff;
            color: #1f2937;
        }

        header {
            background: #4C1D95;
            color: white;
            padding: 20px 8%;
        }

        header h1 {
            margin: 0 0 8px;
        }

        nav {
            margin-top: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            font-weight: bold;
        }

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0000000d;
        }

        h2 {
            color: #4C1D95;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            background: #4C1D95;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .button-secondary {
            background: #6b7280;
        }

        .button-danger {
            background: #dc2626;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #ede9fe;
            color: #4C1D95;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font: inherit;
        }

        label {
            display: block;
            margin-top: 16px;
            font-weight: bold;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            background: #dcfce7;
            color: #166534;
        }

        .error {
            color: #dc2626;
            font-size: 14px;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #6b7280;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        @media (max-width: 700px) {
            main {
                padding: 0 12px;
            }

            .card {
                overflow-x: auto;
            }

            header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>SIPUS-Del</h1>
        <p>Sistem Informasi Perpustakaan Institut Teknologi Del</p>

        <nav>
            <a href="{{ route('bukus.index') }}">Data Buku</a>
            <a href="{{ route('kategoris.index') }}">Kategori</a>
        </nav>
    </header>

    <main>
        @if (session('success'))
            <div class="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert" style="background: #fee2e2; color: #991b1b;">
                <strong>Periksa kembali data yang kamu masukkan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer>
        &copy; {{ date('Y') }} SIPUS-Del | Institut Teknologi Del
    </footer>
</body>
</html>
