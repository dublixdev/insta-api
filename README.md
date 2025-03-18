# Instagram Scraper PHP
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Author](https://img.shields.io/badge/author-%40azizbekqozoqov-orange)

Instagram-dan ma'lumotlarni olish uchun PHP da yozilgan kuchli scraper sinfi.

## Muallif haqida
- **Muallif**: Azizbek Qozoqov (@azizbekqozoqov)
- **GitHub**: [github.com/dublixdev](https://github.com/dublixdev)
- **Yaratilgan sana**: 18 Mart, 2025

## Xususiyatlari
- Instagram postlarini turli API'lardan olish
- Mobil va veb interfeyslarini qo'llab-quvvatlash
- Cookie va token autentifikatsiyasi
- cURL yordamida moslashuvchan so'rovlar
- Keshlangan DTSG token boshqaruvi

## O'rnatish

1. Repozitoriyani klonlash:
```bash
git clone https://github.com/dublixdev/insta-api/instagram-scraper-php.git
```

2. Kerakli bog'liqliklarni o'rnatish:
- PHP 7.4 yoki undan yuqori
- cURL extension

## Foydalanish

```php
<?php
require_once 'InstagramScraper.php';

try {
    $scraper = new InstagramScraper();
    $result = $scraper->process([
        'postId' => 'your_post_id_here',
        'alwaysProxy' => false
    ]);
    
    print_r($result);
} catch (Exception $e) {
    echo "Xatolik: " . $e->getMessage();
}
```

## Asosiy metodlar

| Metod | Tavsif |
|-------|---------|
| `getPost($id, $alwaysProxy)` | Berilgan ID bo'yicha post ma'lumotlarini oladi |
| `process($params)` | Kiruvchi parametrlarga qarab ma'lumotlarni qayta ishlaydi |
| `request($url, $cookie, $method, $data)` | Umumiy HTTP so'rovlarini amalga oshiradi |

## Konfiguratsiya

```php
private $commonHeaders = [
    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    'x-ig-app-id' => '936619743392459'
];

private $mobileHeaders = [
    'user-agent' => 'Instagram 275.0.0.27.98 Android',
    'x-ig-app-locale' => 'en_US'
];
```

## Talablar
- PHP 7.4+
- cURL PHP extension
- Internet aloqasi

## Litsenziya
Ushbu loyiha MIT litsenziyasi ostida tarqatiladi. Batafsil ma'lumot uchun [LICENSE](LICENSE) faylini ko'ring.

## Qo'shimcha ma'lumot
- Hozirda faqat postlarni olishni qo'llab-quvvatlaydi
- Story olish funksionalligi kelajakda qo'shilishi mumkin
- Proxy qo'llab-quvvatlash opsional

## Yordam berish
- Agar xatolik topsangiz, issue oching
- Yangi funksiyalar uchun pull request yuboring
- Takliflar uchun @azizbekqozoqov ga yozing

© 2025 Azizbek Qozoqov. Barcha huquqlar himoyalangan.
```

Bu README.md faylida:

1. Loyiha haqida umumiy ma'lumot
2. Muallif (@azizbekqozoqov) haqida ma'lumot
3. O'rnatish va foydalanish bo'yicha ko'rsatmalar
4. Asosiy xususiyatlar va metodlar jadvali
5. Badge'lar (versiya, litsenziya, muallif)
6. Konfiguratsiya namunasi
7. Talablar va litsenziya ma'lumotlari
8. Professional va chiroyli formatlash

