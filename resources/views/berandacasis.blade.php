@extends('layout.tampilancasis')

@section('page-title', 'Dashboard')
@section('title', 'Beranda Calon Siswa')

@push('styles')
<style>
/* =========================================================
   CSS VARIABLES — Skala ukuran, warna, radius, dan jarak
   ========================================================= */
:root {
    /* Identitas Warna */
    --dc-blue:          #2563eb;
    --dc-blue-hover:    #1d4ed8;
    --dc-blue-light:    #eff6ff;
    --dc-blue-mid:      #bfdbfe;
    --dc-blue-dark:     #1e40af;
    --dc-green:         #059669;
    --dc-green-light:   #ecfdf5;
    --dc-green-mid:     #a7f3d0;
    --dc-green-dark:    #065f46;
    --dc-red-light:     #fef2f2;
    --dc-red:           #dc2626;
    --dc-purple-light:  #faf5ff;
    --dc-purple:        #7c3aed;

    /* Warna Teks (WCAG AA kontras >= 4.5:1 terhadap latar putih) */
    --dc-text-head:     #0f172a;
    --dc-text-body:     #1e293b;
    --dc-text-muted:    #334155;
    --dc-text-faint:    #475569;

    /* Permukaan & Batas */
    --dc-surface:       #ffffff;
    --dc-surface-alt:   #f8fafc;
    --dc-border:        #e2e8f0;
    --dc-border-soft:   #f1f5f9;

    /* Tipografi */
    --dc-fs-title:      1.1875rem; /* ~19px */
    --dc-fs-body:       0.9375rem; /* 15px */
    --dc-fs-label:      0.84375rem;/* 13.5px */
    --dc-fs-value:      1.0625rem; /* 17px */
    --dc-fs-badge:      0.84375rem;/* 13.5px */

    /* Dimensi Komponen */
    --dc-radius:        1rem;
    --dc-radius-sm:     0.625rem;
    --dc-shadow:        0 1px 3px 0 rgb(0 0 0/.08), 0 1px 2px -1px rgb(0 0 0/.06);
    --dc-shadow-md:     0 4px 6px -1px rgb(0 0 0/.1), 0 2px 4px -2px rgb(0 0 0/.1);
    --dc-gap:           24px;
    --dc-badge-size:    38px;
    --dc-line-w:        2px;
}

/* =========================================================
   BANNER UTAMA (Pusat Aksi & Sapaan Tunggal)
   ========================================================= */
.dc-banner {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 55%, #3b82f6 100%);
    border-radius: 1.25rem;
    padding: 24px 28px;
    box-shadow: 0 10px 25px -5px rgb(29 78 216 / .25);
    display: flex;
    flex-direction: column;
    gap: 18px;
}
@media (min-width: 900px) {
    .dc-banner {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        padding: 26px 32px;
    }
}
.dc-banner-content {
    position: relative;
    z-index: 2;
    min-width: 0;
}
.dc-banner-title {
    font-size: 1.625rem; /* 26px */
    font-weight: 700;
    color: #ffffff;
    line-height: 1.25;
    margin: 0 0 6px;
}
.dc-banner-step {
    font-size: 0.9375rem; /* 15px */
    color: #dbeafe;
    line-height: 1.45;
    margin: 0 0 4px;
}
.dc-banner-step-name {
    font-weight: 700;
    color: #ffffff;
    background: rgba(255, 255, 255, 0.18);
    padding: 2px 8px;
    border-radius: 6px;
}
.dc-banner-time {
    font-size: 0.875rem; /* 14px */
    color: #eff6ff;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 6px 0 0;
    line-height: 1.4;
}

.dc-banner-action-wrap {
    position: relative;
    z-index: 2;
    flex-shrink: 0;
}
.dc-banner-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 48px;
    padding: 13px 24px;
    background: #ffffff;
    color: #1d4ed8;
    font-size: 0.9375rem;
    font-weight: 700;
    border-radius: var(--dc-radius-sm);
    box-shadow: 0 4px 14px rgb(0 0 0 / .15);
    text-decoration: none;
    transition: background .15s ease, color .15s ease, transform .1s ease, box-shadow .15s ease;
    border: none;
    outline: none;
    width: 100%;
}
@media (min-width: 900px) {
    .dc-banner-btn {
        width: auto;
        min-width: 220px;
    }
}
.dc-banner-btn:hover {
    background: #f8fafc;
    color: #1e40af;
    box-shadow: 0 6px 20px rgb(0 0 0 / .2);
}
.dc-banner-btn:active {
    transform: scale(.98);
}
.dc-banner-btn:focus-visible {
    outline: 3px solid #ffffff;
    outline-offset: 3px;
}

.dc-banner-decor {
    position: absolute;
    right: -20px;
    bottom: -30px;
    width: 180px;
    height: 180px;
    pointer-events: none;
    z-index: 1;
}

/* =========================================================
   GRID DUA KOLOM DENGAN URUTAN RESPONSIF
   ========================================================= */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--dc-gap);
    align-items: stretch;
}
/* Di layar < 900px: Alur tampil duluan (order: 1), lalu Jadwal (order: 2) */
.dash-col-jadwal    { order: 2; }
.dash-col-alur      { order: 1; }
.dashboard-full-col { order: 3; }

@media (min-width: 900px) {
    .dashboard-grid {
        grid-template-columns: 5fr 6fr;
    }
    .dash-col-jadwal { order: 1; }
    .dash-col-alur   { order: 2; }
    .dashboard-full-col {
        grid-column: 1 / -1;
        order: 3;
    }
}

/* =========================================================
   DASH CARD — Pembungkus utama kedua kartu
   ========================================================= */
.dash-card {
    background: var(--dc-surface);
    border: 1px solid var(--dc-border);
    border-radius: var(--dc-radius);
    box-shadow: var(--dc-shadow);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    overflow: hidden;
    transition: box-shadow .2s ease;
}
.dash-card:hover {
    box-shadow: var(--dc-shadow-md);
}

/* =========================================================
   HEADER KARTU
   ========================================================= */
.dc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 22px;
    border-bottom: 1px solid var(--dc-border-soft);
    background: var(--dc-surface);
}
.dc-title {
    font-size: var(--dc-fs-title);
    font-weight: 700;
    color: var(--dc-text-head);
    line-height: 1.3;
    margin: 0;
}
.dc-subtitle {
    font-size: var(--dc-fs-label);
    color: var(--dc-text-muted);
    font-weight: 500;
    display: block;
    margin-top: 3px;
    line-height: 1.4;
}

/* Wadah ikon header: 44×44px, ikon 24px */
.dc-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.dc-icon-wrap.dc-icon-emerald { background: var(--dc-green-light); color: var(--dc-green); }
.dc-icon-wrap.dc-icon-blue    { background: var(--dc-blue-light);  color: var(--dc-blue); }

/* =========================================================
   BADGE STATUS & TAHAP
   ========================================================= */
.dc-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: var(--dc-fs-badge);
    font-weight: 600;
    line-height: 1;
    border: none;
    outline: none;
}
.dc-badge-emerald { background: var(--dc-green-light); color: var(--dc-green-dark); }
.dc-badge-blue    { background: var(--dc-blue-light);  color: var(--dc-blue-dark); }

.dc-badge-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    animation: pulse-dot 2s ease-in-out infinite;
}
.dc-badge-emerald .dc-badge-dot { background: var(--dc-green); }
.dc-badge-blue    .dc-badge-dot { background: var(--dc-blue); }

@keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50%      { opacity: .4; }
}
@media (prefers-reduced-motion: reduce) {
    .dc-badge-dot { animation: none; }
}

/* Badge keterangan relatif pada Jadwal */
.dc-badge-tag {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.3;
}
.dc-tag-done    { background: var(--dc-green-light); color: var(--dc-green-dark); }
.dc-tag-active  { background: var(--dc-blue-light);  color: var(--dc-blue-dark); }
.dc-tag-pending { background: #f1f5f9;               color: #475569; }

/* =========================================================
   BODY KARTU
   ========================================================= */
.dc-body {
    padding: 22px;
}

/* =========================================================
   KOTAK INFO
   ========================================================= */
.dc-infobox {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: var(--dc-blue-light);
    border: 1px solid var(--dc-blue-mid);
    border-radius: 12px;
    padding: 14px 16px;
    margin-top: 20px;
}
.dc-infobox-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #dbeafe;
    color: var(--dc-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    align-self: flex-start;
    margin-top: 1px;
}
.dc-infobox-text {
    font-size: 0.875rem; /* 14px */
    color: #0c4a6e;
    line-height: 1.55;
    margin: 0;
}

/* =========================================================
   TIMELINE (JADWAL & ALUR)
   ========================================================= */
.dc-timeline {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.dc-tl-item {
    display: flex;
    align-items: stretch;
    gap: 14px;
    flex: 1;
}

/* Kolom kiri: rail vertikal untuk lingkaran dan garis */
.dc-tl-rail {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    width: var(--dc-badge-size);
}

/* Lingkaran nomor / centang (38×38px) */
.dc-tl-badge {
    width: var(--dc-badge-size);
    height: var(--dc-badge-size);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dc-tl-done {
    background: var(--dc-green);
    color: #ffffff;
    box-shadow: 0 0 0 4px var(--dc-green-light);
}
.dc-tl-active {
    background: var(--dc-blue);
    color: #ffffff;
    box-shadow: 0 0 0 4px var(--dc-blue-light);
}
.dc-tl-pending {
    background: #ffffff;
    color: var(--dc-text-muted);
    border: 2px solid var(--dc-border);
}
.dc-tl-icon {
    font-size: 16px;
    line-height: 1;
}
.dc-tl-num {
    font-size: 15px;
    font-weight: 700;
    line-height: 1;
}

/* Garis penghubung vertikal */
.dc-tl-line {
    width: var(--dc-line-w);
    flex: 1;
    min-height: 20px;
    margin: 2px 0;
    border-radius: 1px;
}
.dc-tl-line-done    { background: var(--dc-green-mid); }
.dc-tl-line-pending { background: var(--dc-border); }

/* Kolom kanan: konten teks */
.dc-tl-content {
    flex: 1;
    min-width: 0;
    padding-top: 6px;
    padding-bottom: 16px;
}
.dc-tl-item-last .dc-tl-content {
    padding-bottom: 0;
}

.dc-tl-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.dc-tl-title {
    font-size: var(--dc-fs-body);
    line-height: 1.35;
    margin: 0;
}
.dc-tl-title-done    { color: var(--dc-text-body); font-weight: 600; }
.dc-tl-title-active  { color: var(--dc-blue);      font-weight: 700; }
.dc-tl-title-pending { color: var(--dc-text-faint); font-weight: 500; }

.dc-tl-done-icon {
    color: var(--dc-green);
    font-size: 16px;
}
.dc-tl-desc {
    font-size: var(--dc-fs-label);
    color: var(--dc-text-faint);
    line-height: 1.5;
    margin: 4px 0 0;
}

@media (prefers-reduced-motion: reduce) {
    .dc-banner-btn { transition: none; }
}

/* =========================================================
   KARTU INFORMASI PEMBAYARAN & DAFTAR ULANG (STATIS)
   ========================================================= */
.info-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: 4px solid #f59e0b; /* Garis aksen oranye tipis di atas */
    border-radius: var(--dc-radius);
    box-shadow: var(--dc-shadow);
    overflow: hidden;
    margin-bottom: 24px;
}
/* Menghilangkan semua transform, transition, bayangan dinamis, dan animasi pada kartu ini */
.info-card.info-card--static,
.info-card.info-card--static:hover,
.info-card.info-card--static:focus,
.info-card.info-card--static:active {
    transform: none !important;
    transition: none !important;
    box-shadow: var(--dc-shadow) !important;
    animation: none !important;
}
.info-card.info-card--static *,
.info-card.info-card--static *::before,
.info-card.info-card--static *::after {
    animation: none !important;
}
.info-card.info-card--static *:not(.info-wa-btn) {
    transform: none !important;
    transition: none !important;
}
.info-card.info-card--static .info-total-card,
.info-card.info-card--static .info-total-card:hover {
    transform: none !important;
    transition: none !important;
}
.info-card.info-card--static .info-chevron-mobile {
    transition: none !important;
}

.info-card-header {
    background: #ffffff;
    padding: 16px 22px;
    border-bottom: 1px solid var(--dc-border-soft);
}
.info-header-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #fef3c7; /* Oranye muda lembut */
    color: #d97706;      /* Oranye tegas */
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.info-header-title {
    font-size: 1.25rem; /* 20px */
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin: 0;
}
.info-header-subtitle {
    font-size: 0.875rem; /* 14px */
    color: #475569;
    font-weight: 400;
    margin-top: 2px;
    display: block;
    line-height: 1.4;
}

/* Grid Dua Kolom Berdampingan */
.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}
@media (min-width: 900px) {
    .info-grid {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        align-items: stretch;
    }
}

.info-col {
    display: flex;
    flex-direction: column;
}

/* Urutan responsif mobile sesuai tahap */
@media (max-width: 899px) {
    .info-order-first  { order: 1; }
    .info-order-second { order: 2; }
}

/* Section Header Button (< 700px bisa di-tap, >= 700px statis tanpa chevron) */
.info-section-header-wrap {
    padding-bottom: 8px;
    border-bottom: 1px solid #e2e8f0;
}
.info-section-header-btn {
    width: 100%;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: none;
    border: none;
    padding: 0;
    text-align: left;
    cursor: default;
}
.info-chevron-mobile {
    display: none;
    color: #64748b;
    font-size: 13px;
}
@media (max-width: 699px) {
    .info-section-header-btn {
        cursor: pointer;
    }
    .info-section-header-btn:focus-visible {
        outline: 2px solid var(--dc-blue);
        outline-offset: 4px;
        border-radius: 6px;
    }
    .info-chevron-mobile {
        display: block;
    }
}

.info-sec-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.info-sec-emerald { background: var(--dc-green-light); color: var(--dc-green); }
.info-sec-blue    { background: var(--dc-blue-light);  color: var(--dc-blue); }
.info-sec-indigo  { background: #e0e7ff; color: #4338ca; }
.info-sec-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}

/* Wadah Ikon Poin Seragam (Lingkaran 34px) */
.info-point-wrap {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #ecfdf5; /* Hijau muda */
    color: #059669;      /* Hijau tua */
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    margin-top: 1px;
}
.info-point-wrap.info-point-warning {
    background: #fef3c7; /* Oranye muda */
    color: #d97706;      /* Oranye tua */
}

/* Baris Grid Khusus Dua Kotak Info */
.info-notes-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-top: 24px;
}
@media (min-width: 900px) {
    .info-notes-grid {
        grid-template-columns: 1fr 1fr;
        gap: 32px; /* Gap sama persis dengan .info-grid kolom di atasnya */
        align-items: stretch; /* Kedua kotak otomatis sama tinggi */
    }
}

/* Kotak Info Biru Muda Rapi (Struktur Sama Persis Kiri & Kanan) */
.info-note-box {
    background: #f0f7ff;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    height: 100%;
    box-sizing: border-box;
}
.info-note-icon-wrap {
    width: 36px;
    height: 36px;
    min-width: 36px;
    min-height: 36px;
    border-radius: 50%;
    background: #dbeafe;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    align-self: flex-start;
    margin-top: 1px;
}
.info-note-content {
    min-width: 0;
    flex: 1;
}
.info-note-title {
    font-size: 0.9375rem; /* 15px */
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    margin: 0 0 4px 0;
}
.info-note-text {
    font-size: 0.875rem;  /* 14px */
    color: #0c4a6e;       /* Biru tua WCAG AA (> 7:1) */
    line-height: 1.5;
    margin: 0;
}
.info-note-text a {
    color: #1d4ed8;
    font-weight: 600;
    text-decoration: underline;
}
.info-note-text a:hover {
    color: #1e40af;
}

/* Bagian Selebar Penuh Rincian Biaya */
.info-full-biaya-wrap {
    border-top: 1px solid #e2e8f0;
    padding-top: 24px;
    margin-top: 24px;
}
.info-biaya-table {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
}
.info-biaya-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
}
.info-biaya-row:last-child {
    border-bottom: none;
}
.info-tabular {
    font-variant-numeric: tabular-nums;
}

/* Kartu Total Biaya */
.info-total-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 18px;
}
.info-total-card.info-total-active {
    background: #eff6ff;
    border: 2px solid #3b82f6;
    box-shadow: 0 4px 12px rgb(59 130 246 / .15);
}
.info-active-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    background: #dbeafe;
    color: #1d4ed8;
}

/* =========================================================
   BAR BANTUAN SELEBAR PENUH (Kontak Panitia Resmi di Bawah)
   ========================================================= */
.info-help-bar-wrap {
    border-top: 1px solid #e2e8f0;
    padding-top: 24px;
    margin-top: 24px;
}
.info-help-bar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
}
@media (min-width: 700px) {
    .info-help-bar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
}
.info-help-bar-content {
    display: flex;
    align-items: center;
    gap: 14px;
    flex: 1 1 auto;
    min-width: 0;
}
.info-help-bar-action {
    flex-shrink: 0;
}
@media (max-width: 699px) {
    .info-help-bar-action {
        width: 100%;
    }
    .info-help-bar-action .info-wa-btn {
        width: 100%;
    }
}
.info-help-bar-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ecfdf5;
    color: #059669;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.info-help-bar-title {
    font-size: 1rem; /* 16px */
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin: 0;
}
.info-help-bar-desc {
    font-size: 0.875rem; /* 14px */
    color: #475569;
    line-height: 1.45;
    margin: 2px 0 0;
}

/* Tombol WhatsApp Resmi */
.info-wa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;     /* minimal 44px */
    padding: 10px 20px;   /* padding horizontal nyaman */
    background: #16a34a;
    color: #ffffff;
    font-size: 0.875rem;  /* 14px */
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: background-color 0.15s ease;
    border: none;
    cursor: pointer;
    white-space: nowrap;
}
.info-wa-btn:hover {
    background: #15803d;
}
.info-wa-btn:focus-visible {
    outline: 2px solid #16a34a;
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    .info-card.info-card--static,
    .info-card.info-card--static * {
        transition: none !important;
        animation: none !important;
        transform: none !important;
    }
}
</style>
@endpush

@php
    $bulanId = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $isArchived = !empty($isArchived);
    $hasCasis = !empty($casis);
    $pembayaran = $casis?->pembayaran;
    $rawStatusBayar = strtolower($pembayaran?->status_pembayaran ?? '');
    $isLunasBayar = $pembayaran && in_array($rawStatusBayar, ['lunas', 'diterima', 'settlement', 'success', 'capture']);
    $seleksi = $casis?->seleksi;
    $hasSeleksi = $seleksi && !empty($seleksi->hasil_seleksi) && $seleksi->isDiumumkan();
    $isLulus = $hasSeleksi && in_array($seleksi->hasil_seleksi, ['Lulus', 'Lolos']);
    $daftarUlang = $seleksi?->daftarUlang ?? $casis?->daftarUlang;
    $totalLunasDu = $daftarUlang ? $daftarUlang->pembayaranDaftarUlang()->whereIn('status_du', ['Lunas', 'Diterima'])->sum('jml_bayar_du') : 0;
    $totalBiayaDu = (float) ($tahunajar?->biaya_daftar_ulang ?? 1000000);
    $isDaftarUlangSelesai = $daftarUlang && ($daftarUlang->status_daftar_ulang === 'Selesai' || $totalLunasDu >= $totalBiayaDu);

    // Tentukan tahap aktif, nama tahap, info waktu, dan tombol aksi untuk banner (tanpa "Tahap N")
    if ($isArchived) {
        $tahapAktif = 1;
        $tahapNama = 'Pendaftaran Diarsipkan';
        $btnText = 'Hubungi Panitia PPDB';
        $btnRoute = 'https://wa.me/6281234567890?text=' . urlencode('Halo Panitia PPDB SD Kristen Rantai Damai, saya ingin menanyakan perihal status arsip pendaftaran calon siswa: ' . ($archivedCasis?->nama_calon_siswa ?? ''));
        $btnIcon = 'fab fa-whatsapp';
        $infoWaktu = 'Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia.';
    } elseif (!$hasCasis) {
        $tahapAktif = 2;
        $tahapNama = 'Isi Formulir & Dokumen';
        $btnText = 'Isi Formulir Pendaftaran';
        $btnRoute = route('formcasis');
        $btnIcon = 'fas fa-file-alt';
        $infoWaktu = 'Batas pendaftaran: ' . ($tahunajar->batas_pendaftaran ? \Carbon\Carbon::parse($tahunajar->batas_pendaftaran)->format('d') . ' ' . $bulanId[(int)\Carbon\Carbon::parse($tahunajar->batas_pendaftaran)->format('n')] . ' ' . \Carbon\Carbon::parse($tahunajar->batas_pendaftaran)->format('Y') : '-');
    } elseif (!$isLunasBayar) {
        $tahapAktif = 3;
        $tahapNama = 'Pembayaran Pendaftaran';
        $btnText = 'Bayar Biaya Pendaftaran';
        $btnRoute = route('pembayaran');
        $btnIcon = 'fas fa-credit-card';
        $infoWaktu = 'Selesaikan pembayaran formulir secara online via Midtrans.';
    } elseif (!$hasSeleksi) {
        $tahapAktif = 4;
        $tahapNama = 'Seleksi Observasi';
        $btnText = 'Cek Jadwal & Pengumuman';
        $btnRoute = route('pengumuman');
        $btnIcon = 'fas fa-calendar-check';
        $infoWaktu = ($tahunajar->tgl_seleksi ? \Carbon\Carbon::parse($tahunajar->tgl_seleksi)->format('d') . ' ' . $bulanId[(int)\Carbon\Carbon::parse($tahunajar->tgl_seleksi)->format('n')] . ' ' . \Carbon\Carbon::parse($tahunajar->tgl_seleksi)->format('Y') : '-') . ' · 08:00 – 10:00 ' . school_timezone_label();
    } elseif ($isLulus && $isDaftarUlangSelesai) {
        $tahapAktif = 5;
        $tahapNama = 'Daftar Ulang Selesai';
        $btnText = 'Lihat Bukti Daftar Ulang';
        $btnRoute = route('calon_siswa.daftar_ulang.show');
        $btnIcon = 'fas fa-receipt';
        $infoWaktu = 'Selamat! Administrasi pendaftaran dan daftar ulang Anda telah selesai.';
    } elseif ($isLulus) {
        $tahapAktif = 5;
        $tahapNama = 'Daftar Ulang';
        $btnText = $totalLunasDu > 0 ? 'Lanjutkan Daftar Ulang' : 'Lakukan Daftar Ulang';
        $btnRoute = route('calon_siswa.daftar_ulang.show');
        $btnIcon = $totalLunasDu > 0 ? 'fas fa-arrow-right' : 'fas fa-redo-alt';
        $infoWaktu = $totalLunasDu > 0 ? 'Cicilan pertama telah dibayar. Segera serahkan berkas ke sekolah.' : 'Selamat Anda dinyatakan lolos! Segera selesaikan administrasi daftar ulang.';
    } else {
        $tahapAktif = 5;
        $tahapNama = 'Pengumuman Seleksi';
        $btnText = 'Lihat Pengumuman Seleksi';
        $btnRoute = route('pengumuman');
        $btnIcon = 'fas fa-bullhorn';
        $infoWaktu = 'Pantau hasil observasi seleksi siswa baru.';
    }
@endphp

@section('content')
<div class="space-y-6">
    
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold font-heading">Portal Calon Siswa</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola data pendaftaran dan pantau status PPDB Anda di sini.</p>
    </div>

    @if(!empty($isArchived))
        {{-- Banner Arsip Ramah --}}
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-6 py-5 rounded-2xl flex items-start gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-700">
                <i class="fas fa-archive text-lg" aria-hidden="true"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-base mb-1 text-amber-950">Data Pendaftaran Sedang Diarsipkan</h4>
                <p class="text-sm leading-relaxed text-amber-900">
                    Data pendaftaran Anda sedang diarsipkan. Silakan hubungi panitia PPDB untuk informasi dan pemulihan data pendaftaran Anda.
                </p>
                <div class="mt-3 flex items-center gap-3">
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Panitia PPDB SD Kristen Rantai Damai, saya ingin menanyakan perihal status arsip data pendaftaran calon siswa: ' . ($archivedCasis?->nama_calon_siswa ?? '')) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-colors shadow-xs">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        <span>Hubungi Panitia PPDB</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Banner Utama: Pusat Aksi & Sapaan Tunggal --}}
    <div class="dc-banner">
        <div class="dc-banner-content">
            <h2 class="dc-banner-title font-heading">
                Halo, {{ Auth::user()->name }}! 👋
            </h2>
            <p class="dc-banner-step">
                Langkah Anda sekarang: <span class="dc-banner-step-name">{{ $tahapNama }}</span>
            </p>
            @if(!empty($infoWaktu))
                <p class="dc-banner-time">
                    <i class="fas fa-calendar-day text-blue-200" aria-hidden="true"></i>
                    <span>{{ $infoWaktu }}</span>
                </p>
            @endif
        </div>

        <div class="dc-banner-action-wrap">
            <a href="{{ $btnRoute }}" @if(str_starts_with($btnRoute, 'https://wa.me')) target="_blank" rel="noopener noreferrer" @endif class="dc-banner-btn" aria-label="{{ $btnText }}">
                <i class="{{ $btnIcon }}" aria-hidden="true"></i>
                <span>{{ $btnText }}</span>
                <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
            </a>
        </div>

        {{-- Dekorasi Lingkaran Halus (tidak menutupi teks) --}}
        <div class="dc-banner-decor" aria-hidden="true">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="80" stroke="white" stroke-width="20" stroke-dasharray="10 10" opacity="0.12"/>
                <circle cx="100" cy="100" r="50" stroke="white" stroke-width="12" opacity="0.15"/>
            </svg>
        </div>
    </div>

    {{-- Main Content Grid: Proporsi 5fr : 6fr di layar >= 900px, 1 kolom di layar < 900px --}}
    <div class="dashboard-grid">
        
        {{-- Jadwal PPDB (5fr di desktop, posisi bawah di HP) --}}
        @if(isset($tahunajar))
            <div class="dash-col-jadwal flex flex-col h-full">
                @include('components.casis.pengumuman-card')
            </div>
        @endif

        {{-- Alur Pendaftaran Online (6fr di desktop, posisi atas di HP) --}}
        <div class="dash-col-alur flex flex-col h-full">
            @include('components.casis.alur-card')
        </div>

        {{-- Full Width: Informasi --}}
        <div class="dashboard-full-col mt-2">
            @include('components.casis.informasi-card')
        </div>

    </div>
</div>
@endsection
