<?php
/**
 * Instagram Scraper Class
 * @author Azizbek Qozoqov (@azizbekqozoqov)
 * @version 1.0
 * @date 2025-03-18
 */

class InstagramScraper {
    // Asosiy so'rovlar uchun umumiy headerlar
    private $commonHeaders = [
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'sec-gpc' => '1',
        'sec-fetch-site' => 'same-origin',
        'x-ig-app-id' => '936619743392459'
    ];

    // Mobil qurilmalar uchun headerlar
    private $mobileHeaders = [
        'x-ig-app-locale' => 'en_US',
        'x-ig-device-locale' => 'en_US',
        'x-ig-mapped-locale' => 'en_US',
        'user-agent' => 'Instagram 275.0.0.27.98 Android (33/13; 280dpi; 720x1423; Xiaomi; Redmi 7; onclite; qcom; en_US; 458229237)',
        'accept-language' => 'en-US',
        'x-fb-http-engine' => 'Liger',
        'x-fb-client-ip' => 'True',
        'x-fb-server-cluster' => 'True',
        'content-length' => '0'
    ];

    // Embed so'rovlar uchun headerlar
    private $embedHeaders = [
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'en-GB,en;q=0.9',
        'Cache-Control' => 'max-age=0',
        'Dnt' => '1',
        'Sec-Fetch-Dest' => 'document',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-Site' => 'none',
        'Sec-Fetch-User' => '?1',
        'Upgrade-Insecure-Requests' => '1',
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    ];

    // DTSG token uchun kesh
    private $cachedDtsg = [
        'value' => '',
        'expiry' => 0
    ];

    private $cookies = [];

    /**
     * cURL yordamida HTTP so'rov yuborish
     * @param string $url So'rov manzili
     * @param array $headers HTTP headerlari
     * @param string $method So'rov usuli (GET/POST)
     * @param mixed $postData POST ma'lumotlari
     * @return array Javob ma'lumotlari
     */
    private function curlRequest($url, $headers = [], $method = 'GET', $postData = null) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postData) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        }

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

        return [
            'body' => $body,
            'headers' => $this->parseHeaders($headers)
        ];
    }

    /**
     * Headerlarni parsing qilish
     * @param string $headers Xom header ma'lumotlari
     * @return array Parsing qilingan headerlar
     */
    private function parseHeaders($headers) {
        $result = [];
        foreach (explode("\r\n", $headers) as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(': ', $line, 2);
                $result[strtolower($key)] = $value;
            }
        }
        return $result;
    }

    /**
     * DTSG tokenni topish
     * @param string $cookie Cookie ma'lumotlari
     * @return string|bool Token yoki false
     */
    private function findDtsgId($cookie) {
        if ($this->cachedDtsg['expiry'] > time()) {
            return $this->cachedDtsg['value'];
        }

        $headers = array_merge($this->commonHeaders, ['cookie' => $cookie]);
        $response = $this->curlRequest('https://www.instagram.com/',
            array_map(function($k, $v) { return "$k: $v"; }, array_keys($headers), $headers));

        preg_match('/"dtsg":{"token":"(.*?)"/', $response['body'], $matches);
        $token = $matches[1] ?? false;

        if ($token) {
            $this->cachedDtsg['value'] = $token;
            $this->cachedDtsg['expiry'] = time() + 86390;
            return $token;
        }
        return false;
    }

    /**
     * Umumiy Instagram API so'rovi
     * @param string $url So'rov URL
     * @param array $cookie Cookie ma'lumotlari
     * @param string $method So'rov usuli
     * @param array $requestData So'rov ma'lumotlari
     * @return array API javobi
     */
    private function request($url, $cookie, $method = 'GET', $requestData = null) {
        $headers = array_merge($this->commonHeaders, [
            'x-ig-www-claim' => $cookie['_wwwClaim'] ?? '0',
            'x-csrftoken' => $cookie['csrftoken'] ?? '',
            'cookie' => http_build_query($cookie, '', '; ')
        ]);

        if ($method === 'POST') {
            $headers['content-type'] = 'application/x-www-form-urlencoded';
        }

        $response = $this->curlRequest($url,
            array_map(function($k, $v) { return "$k: $v"; }, array_keys($headers), $headers),
            $method,
            $requestData ? http_build_query($requestData) : null);

        if (isset($response['headers']['x-ig-set-www-claim']) && $cookie) {
            $cookie['_wwwClaim'] = $response['headers']['x-ig-set-www-claim'];
        }

        return json_decode($response['body'], true);
    }

    /**
     * Post uchun media ID olish
     * @param string $id Post shortcode
     * @param array $options Qo'shimcha parametrlari
     * @return string|bool Media ID yoki false
     */
    private function getMediaId($id, $options = []) {
        $url = "https://i.instagram.com/api/v1/oembed/?url=https://www.instagram.com/p/{$id}/";
        $headers = $this->mobileHeaders;

        if (!empty($options['cookie'])) {
            $headers['cookie'] = http_build_query($options['cookie'], '', '; ');
        }
        if (!empty($options['token'])) {
            $headers['authorization'] = "Bearer {$options['token']}";
        }

        $response = $this->curlRequest($url,
            array_map(function($k, $v) { return "$k: $v"; }, array_keys($headers), $headers));
        $data = json_decode($response['body'], true);

        return $data['media_id'] ?? false;
    }

    /**
     * Post ma'lumotlarini olish
     * @param string $id Post shortcode
     * @param bool $alwaysProxy Proxy ishlatish
     * @return array Post ma'lumotlari
     */
    public function getPost($id, $alwaysProxy = false) {
        $cookie = $this->getCookie('instagram');
        $bearer = $this->getCookie('instagram_bearer');
        $token = $bearer['token'] ?? null;

        $data = null;

        // Media ID ni turli usullar bilan olishga urinish
        $media_id = $this->getMediaId($id);
        if (!$media_id && $token) $media_id = $this->getMediaId($id, ['token' => $token]);
        if (!$media_id && $cookie) $media_id = $this->getMediaId($id, ['cookie' => $cookie]);

        // Mobil API orqali ma'lumot olish
        if ($media_id && $token) $data = $this->requestMobileApi($media_id, ['token' => $token]);
        if ($media_id && !$data) $data = $this->requestMobileApi($media_id);
        if ($media_id && $cookie && !$data) $data = $this->requestMobileApi($media_id, ['cookie' => $cookie]);

        // HTML embed orqali ma'lumot olish
        if (!$data) $data = $this->requestHTML($id);
        if (!$data && $cookie) $data = $this->requestHTML($id, $cookie);

        // GraphQL orqali ma'lumot olish
        if (!$data) $data = $this->requestGQL($id);
        if (!$data && $cookie) $data = $this->requestGQL($id, $cookie);

        if (!$data) return ['error' => 'fetch.fail'];

        // Ma'lumotlarni qayta ishlash logikasi bu yerga qo'shilishi mumkin

        return ['error' => 'fetch.empty'];
    }

    /**
     * Cookie ma'lumotlarini olish
     * @param string $name Cookie nomi
     * @return array Cookie ma'lumotlari
     */
    private function getCookie($name) {
        return $this->cookies[$name] ?? [];
    }

    /**
     * Asosiy ishlov berish metodi
     * @param array $params Kiruvchi parametrlari
     * @return array Natija
     */
    public function process($params) {
        if (isset($params['postId'])) 
        {
            return $this->getPost($params['postId'], $params['alwaysProxy'] ?? false);
        }

        return ['error' => 'fetch.empty'];
    }

    // Qo'shimcha yordamchi metodlar...
    private function requestMobileApi($mediaId, $options = []) {/* Implementation */}
    private function requestHTML($id, $cookie = null) {/* Implementation */}
    private function requestGQL($id, $cookie = null) {/* Implementation */}
}


?>
