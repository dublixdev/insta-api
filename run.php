<?php



/**
 * Foydalanish namunasi
 * @author Azizbek Qozoqov
 */
try {
    $scraper = new InstagramScraper();
    $result = $scraper->process([
        'postId' => 'your_post_id_here',
        'alwaysProxy' => false
    ]);

    echo '<pre>';
    print_r($result);
    echo '</pre>';
} catch (Exception $e) {
    echo "Xatolik yuz berdi: " . $e->getMessage();
}
