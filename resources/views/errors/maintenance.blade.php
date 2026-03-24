<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-stone-100 flex items-center justify-center p-6">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-sm border border-stone-200 p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 text-2xl">
            ⚙
        </div>
        <h1 class="text-2xl font-bold text-stone-900 mb-2">Website Sedang Maintenance</h1>
        <p class="text-stone-600 leading-relaxed">
            {{ $message ?? 'Kami sedang melakukan pemeliharaan sistem. Silakan cek kembali beberapa saat lagi.' }}
        </p>
    </div>
</body>

</html>
