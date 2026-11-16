<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terjadi Kesalahan - 500</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-slate-50 font-sans antialiased flex items-center justify-center p-4">

    <div class="max-w-md w-full text-center">
        <div class="mb-8 relative">
            <div class="text-[120px] font-bold text-slate-200 leading-none font-heading select-none">
                500
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center shadow-lg shadow-red-500/30 text-white text-3xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2 font-heading">
            Sistem Sedang Bermasalah
        </h1>
        <p class="text-slate-500 mb-8">
            Maaf, terjadi kesalahan internal pada sistem kami. Silakan coba beberapa saat lagi atau hubungi administrator.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="javascript:history.back()" class="w-full sm:w-auto px-6 py-3 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                Kembali ke Sebelumnya
            </a>
            <a href="{{ url('/') }}" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 shadow-md shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                Ke Beranda Utama
            </a>
        </div>
    </div>

</body>
</html>
