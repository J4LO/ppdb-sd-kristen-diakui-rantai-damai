<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Aturan Seleksi PPDB SD Kristen Rantai Damai
    |--------------------------------------------------------------------------
    |
    | Seluruh parameter ambang batas kelulusan seleksi dan rentang nilai
    | diatur secara terpusat di sini (Single Source of Truth).
    |
    */
    'seleksi' => [
        'nilai_minimal_lulus' => 150, // Ambang batas kelulusan: total >= 150 -> Lulus
        'skala_nilai_min'     => 0,   // Nilai minimum per mata uji (Membaca, Menulis, Berhitung)
        'skala_nilai_max'     => 100, // Nilai maksimum per mata uji
        'total_nilai_max'     => 300, // Total nilai maksimal akumulasi 3 mata uji
    ],
];

