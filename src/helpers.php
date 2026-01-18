<?php

declare(strict_types=1);

function basePath(string $path = ''): string
{
    $root = __DIR__ . '/..';
    return $path === '' ? $root : $root . '/' . ltrim($path, '/');
}

function assetPath(string $path): string
{
    return ltrim($path, '/');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'));
        exit();
    }
}

function currentUser(PDO $pdo): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function countryCatalog(): array
{
    return [
        ['code' => 'ID', 'name' => 'Indonesia'],
        ['code' => 'SG', 'name' => 'Singapura'],
        ['code' => 'MY', 'name' => 'Malaysia'],
        ['code' => 'TH', 'name' => 'Thailand'],
        ['code' => 'VN', 'name' => 'Vietnam'],
        ['code' => 'PH', 'name' => 'Filipina'],
        ['code' => 'JP', 'name' => 'Jepang'],
        ['code' => 'KR', 'name' => 'Korea Selatan'],
        ['code' => 'CN', 'name' => 'Tiongkok'],
        ['code' => 'HK', 'name' => 'Hong Kong'],
        ['code' => 'IN', 'name' => 'India'],
        ['code' => 'SA', 'name' => 'Arab Saudi'],
        ['code' => 'AE', 'name' => 'Uni Emirat Arab'],
        ['code' => 'TR', 'name' => 'Turki'],
        ['code' => 'US', 'name' => 'Amerika Serikat'],
        ['code' => 'GB', 'name' => 'Inggris'],
        ['code' => 'DE', 'name' => 'Jerman'],
        ['code' => 'FR', 'name' => 'Prancis'],
        ['code' => 'IT', 'name' => 'Italia'],
        ['code' => 'ES', 'name' => 'Spanyol'],
        ['code' => 'AU', 'name' => 'Australia'],
        ['code' => 'NZ', 'name' => 'Selandia Baru'],
    ];
}

function countryName(string $code): string
{
    $code = strtoupper(trim($code));
    foreach (countryCatalog() as $country) {
        if ($country['code'] === $code) {
            return $country['name'];
        }
    }

    return $code;
}

function countryFlag(string $code): string
{
    $code = strtoupper(trim($code));
    if (!preg_match('/^[A-Z]{2}$/', $code)) {
        return '';
    }

    $offset = 0x1F1E6;
    $first = $offset + (ord($code[0]) - ord('A'));
    $second = $offset + (ord($code[1]) - ord('A'));

    return html_entity_decode(
        sprintf('&#x%X;&#x%X;', $first, $second),
        ENT_NOQUOTES,
        'UTF-8'
    );
}

function visaTypeCatalog(): array
{
    return [
        ['name' => 'Visa Turis', 'code' => 'TRS', 'multiplier' => 1.0, 'min_days' => 5, 'max_days' => 12],
        ['name' => 'Visa Bisnis', 'code' => 'BUS', 'multiplier' => 1.2, 'min_days' => 4, 'max_days' => 10],
        ['name' => 'Visa Kunjungan Keluarga', 'code' => 'KLG', 'multiplier' => 1.1, 'min_days' => 7, 'max_days' => 14],
        ['name' => 'Visa Pelajar', 'code' => 'STU', 'multiplier' => 1.4, 'min_days' => 14, 'max_days' => 30],
        ['name' => 'Visa Kerja', 'code' => 'WRK', 'multiplier' => 1.6, 'min_days' => 14, 'max_days' => 30],
        ['name' => 'Visa Transit', 'code' => 'TRN', 'multiplier' => 0.85, 'min_days' => 2, 'max_days' => 5],
        ['name' => 'eVisa / Visa on Arrival (VoA)', 'code' => 'EVO', 'multiplier' => 0.9, 'min_days' => 1, 'max_days' => 3],
    ];
}

function saudiVisaTypeCatalog(): array
{
    return [
        ['name' => 'Visa Umrah (Tasyira Umrah)', 'code' => 'UMR', 'multiplier' => 1.3, 'min_days' => 7, 'max_days' => 14],
        ['name' => 'Visa Haji (Tasyira Haj)', 'code' => 'HAJ', 'multiplier' => 1.6, 'min_days' => 14, 'max_days' => 30],
        ['name' => 'Visa Turis (Tasyira Siyaha)', 'code' => 'TRS', 'multiplier' => 1.0, 'min_days' => 5, 'max_days' => 10],
        ['name' => 'eVisa & Visa on Arrival (VoA)', 'code' => 'EVO', 'multiplier' => 0.9, 'min_days' => 2, 'max_days' => 5],
        ['name' => 'Visa Kunjungan Bisnis (Tasyira Ziyarah Tijariyah)', 'code' => 'KBZ', 'multiplier' => 1.2, 'min_days' => 5, 'max_days' => 10],
        ['name' => 'Visa Kunjungan Pribadi (Tasyirah Ziyarah al-Syakhsiyah)', 'code' => 'KPR', 'multiplier' => 1.1, 'min_days' => 7, 'max_days' => 14],
        ['name' => 'Visa Kerja (Tasyira Amal)', 'code' => 'WRK', 'multiplier' => 1.6, 'min_days' => 14, 'max_days' => 30],
        ['name' => 'Visa Residen (Tasyirat al-Iqamah)', 'code' => 'RES', 'multiplier' => 1.5, 'min_days' => 14, 'max_days' => 30],
        ['name' => "Visa Kunjungan Keluarga (Tasyira Ziyarah 'Aailiyah)", 'code' => 'KLF', 'multiplier' => 1.2, 'min_days' => 7, 'max_days' => 14],
        ['name' => 'Visa Pendamping', 'code' => 'PDM', 'multiplier' => 1.1, 'min_days' => 7, 'max_days' => 14],
        ['name' => 'Visa Pelajar (Tasyira Dirasiyah)', 'code' => 'STU', 'multiplier' => 1.4, 'min_days' => 14, 'max_days' => 30],
        ['name' => 'Visa Transit (Tasyirah Murur)', 'code' => 'TRN', 'multiplier' => 0.85, 'min_days' => 2, 'max_days' => 5],
        ['name' => "Visa Berobat (Tasyira al-'Ilaj)", 'code' => 'MED', 'multiplier' => 1.3, 'min_days' => 7, 'max_days' => 14],
        ['name' => "Visa Kunjungan Kerja (Tasyira Ziarah al-'Amal)", 'code' => 'WKV', 'multiplier' => 1.2, 'min_days' => 5, 'max_days' => 10],
    ];
}

function saudiVisaGuide(): array
{
    return [
        [
            'title' => 'Untuk Ibadah & Kunjungan Keagamaan',
            'items' => [
                'Visa Umrah (Tasyira Umrah): Untuk ibadah umrah, seringkali melalui agen travel resmi.',
                'Visa Haji (Tasyira Haj): Khusus untuk ibadah haji, ada juga yang disebut Haji Furoda/Mujamalah (undangan khusus).',
            ],
        ],
        [
            'title' => 'Untuk Wisata & Bisnis',
            'items' => [
                'Visa Turis (Tasyira Siyaha): Untuk tujuan wisata umum.',
                'eVisa & Visa on Arrival (VoA): Kemudahan bagi WNI yang punya visa AS/Schengen/UK aktif, bisa diajukan online atau saat tiba di bandara untuk turis/bisnis.',
                'Visa Kunjungan Bisnis (Tasyira Ziyarah Tijariyah): Untuk rapat, konferensi, atau negosiasi.',
                'Visa Kunjungan Pribadi (Tasyirah Ziyarah al-Syakhsiyah): Untuk kunjungan personal.',
            ],
        ],
        [
            'title' => 'Untuk Tinggal & Kerja (Ekspatriat)',
            'items' => [
                'Visa Kerja (Tasyira Amal): Wajib bagi pekerja asing.',
                'Visa Residen (Tasyirat al-Iqamah): Untuk tinggal permanen, namun biasanya tidak boleh bekerja.',
                "Visa Kunjungan Keluarga (Tasyira Ziyarah 'Aailiyah): Untuk keluarga dekat ekspatriat.",
                'Visa Pendamping: Untuk mendampingi anggota keluarga.',
            ],
        ],
        [
            'title' => 'Untuk Tujuan Lain',
            'items' => [
                'Visa Pelajar (Tasyira Dirasiyah): Untuk studi di Arab Saudi.',
                'Visa Transit (Tasyirah Murur): Untuk transit di Arab Saudi dalam perjalanan ke negara lain.',
                "Visa Berobat (Tasyira al-'Ilaj): Untuk tujuan medis.",
                "Visa Kunjungan Kerja (Tasyira Ziarah al-'Amal): Kunjungan kerja singkat.",
            ],
        ],
    ];
}

function formatRupiah(int $value): string
{
    return 'Rp ' . number_format($value, 0, ',', '.');
}
