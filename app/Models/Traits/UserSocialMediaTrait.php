<?php

namespace App\Models\Traits;

use Illuminate\Support\Arr;

trait UserSocialMediaTrait
{
    private array $socialBaseUrl = [
        'facebook' => 'https://web.facebook.com/%s',
        'tokopedia' => 'https://tokopedia.com/%s',
        'tiktok' => 'https://www.tiktok.com/%s', // tambahkan @ di depan jika tdk ada
        'instagram' => 'https://www.instagram.com/%s',
        'shopee' => 'https://shopee.co.id/%s',
    ];

    private array $socialMaps = [
        'whatsapp' => 'phone',
    ];

    private function fixWhatsappPhone(string $phone = null): string
    {
        $phone = $phone ?? $this->phone;
        $f1 = substr($phone, 0, 1);

        if ($f1 == '0') {
            $phone = substr($phone, 1);
            $phone = "62{$phone}";
        } elseif ($f1 == '+') {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    // public
    public function socialMediaUrl(string $key, $default = null)
    {
        $field = Arr::get($this->socialMaps, $key, $key);
        $socialId = $this->$field;

        if (!empty($url = Arr::get($this->socialBaseUrl, $key, '')) && !empty($socialId)) {
            if ($key == 'tiktok') {
                if (substr($socialId, 0, 1) != '@') $socialId = "@{$socialId}";
            }

            return sprintf($url, $socialId);
        }

        return $default;
    }

    public function whatsappUrl(bool $includePhone, string $message = null): string
    {
        $params = [];

        if ($includePhone) {
            $params['phone'] = $this->fixWhatsappPhone();
        }

        if (!empty($message)) {
            $params['text'] = $message;
        }

        $paramUrl = !empty($params)
            ? implode('&', array_map(function ($k, $v) {
                return "{$k}={$v}";
            }, array_keys($params), array_values($params)))
            : '';

        return 'https://api.whatsapp.com/send?' . $paramUrl;
    }

    public function hasSocialMedia(string $key): bool
    {
        $field = Arr::get($this->socialMaps, $key, $key);

        return !empty($this->$field);
    }
    // public:end

    // accessor
    public function getHasSosmedAttribute()
    {
        return ($this->hasSocialMedia('facebook') ||
            $this->hasSocialMedia('tokopedia') ||
            $this->hasSocialMedia('tiktok') ||
            $this->hasSocialMedia('instagram') ||
            $this->hasSocialMedia('shopee')
        );
    }

    public function getFacebookUrlAttribute()
    {
        return $this->socialMediaUrl('facebook');
    }

    public function getTokopediaUrlAttribute()
    {
        return $this->socialMediaUrl('tokopedia');
    }

    public function getTiktokUrlAttribute()
    {
        return $this->socialMediaUrl('tiktok');
    }

    public function getInstagramUrlAttribute()
    {
        return $this->socialMediaUrl('instagram');
    }

    public function getShopeeUrlAttribute()
    {
        return $this->socialMediaUrl('shopee');
    }

    public function getSocialMediaListAttribute()
    {
        $list = [
            [
                'text' => 'Whatsapp',
                'icon' => 'bi bi-whatsapp',
                'url' => $this->whatsappUrl(true),
                'color' => 'success',
                'style' => null,
                'button' => 'success',
            ],
        ];

        if ($this->hasSocialMedia('facebook')) {
            $list[] = [
                'text' => 'Facebook',
                'icon' => 'bi bi-facebook',
                'url' => $this->facebook_url,
                'color' => 'primary',
                'style' => null,
                'button' => 'primary',
            ];
        }

        if ($this->hasSocialMedia('tokopedia')) {
            $list[] = [
                'text' => 'Tokopedia',
                'icon' => 'bi bi-tokopedia',
                'url' => $this->tokopedia_url,
                'color' => 'primary',
                'style' => null,
                'button' => 'primary',
            ];
        }

        if ($this->hasSocialMedia('tiktok')) {
            $list[] = [
                'text' => 'Tiktok',
                'icon' => 'bi bi-tiktok',
                'url' => $this->tiktok_url,
                'color' => 'dark',
                'style' => null,
                'button' => 'secondary',
            ];
        }

        if ($this->hasSocialMedia('instagram')) {
            $list[] = [
                'text' => 'Instagram',
                'icon' => 'bi bi-instagram',
                'url' => $this->instagram_url,
                'color' => 'danger',
                'style' => null,
                'button' => 'danger',
            ];
        }

        if ($this->hasSocialMedia('shopee')) {
            $list[] = [
                'text' => 'Shopee',
                'icon' => 'bi bi-bag-fill',
                'url' => $this->shopee_url,
                'color' => 'danger',
                // 'style' => 'color:#fa5330;',
                'style' => null,
                'button' => 'danger',
            ];
        }

        return $list;
    }
    // accessor:end
}
