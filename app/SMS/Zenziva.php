<?php

namespace App\SMS;

use App\Models\SmsError;
use App\Models\SmsSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Zenziva
{
    // protected SmsSetting|null $setting;
    protected $setting;

    public function __construct()
    {
        // $this->setting = SmsSetting::query()->byVendor('zenziva')->byVendorType(VENDOR_TYPE_SMS_REGULAR)->first();
        // sementara manual dlu;

        $entryPoints = [
            // 'endpoint' => 'https://console.zenziva.net/wareguler/api/sendWA/',
            'endpoint' => 'https://gsm.zenziva.net/api/sendsms/',
            'vendor' => 'zenziva',
            'vendor_type' => VENDOR_TYPE_SMS_REGULAR,
            'user_key' => '7a5a7cff8bd8',
            'pass_key' => '27f0e7b28e89f74921ddf5e9',
            'sms_token' => null,
            'is_token' => false,
        ];

        $this->setting = (object) array_merge($entryPoints, ['entry_points' => $entryPoints]);
    }

    // public function updateSetting(array $values): bool
    // {
    //     try {
    //         $this->setting = SmsSetting::updateSetting($values, $this->setting);
    //         return true;
    //     } catch (\Throwable $th) {
    //         return false;
    //     }
    // }

    public function send(int $userId, string $phoneNumber, string $message): bool
    {
        $phone = $phoneNumber;

        if (substr($phone, 0, 3) == '+62') {
            $phone = '0' . substr($phone, 3);
        } elseif (substr($phone, 0, 2) == '62') {
            $phone = '0' . substr($phone, 2);
        }

        $setting = optional($this->setting);
        $url = $setting->endpoint ?? env('APP_URL');

        $data = [
            'userkey' => $setting->user_key,
            'passkey' => $setting->pass_key,
            'nohp' => $phone,
            'pesan' => $message
        ];

        // // pake native curl
        // $curlHandle = curl_init();
        // curl_setopt($curlHandle, CURLOPT_URL, $url);
        // curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        // curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        // curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        // curl_setopt($curlHandle, CURLOPT_POST, 1);
        // curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);

        // $results = json_decode(curl_exec($curlHandle), true);
        // curl_close($curlHandle);

        // $status = Arr::get($results, 'status', 1);
        // $error = ($status != 1);

        // if ($error && !empty($this->setting)) {
        //     $errMessage = Arr::get($results, 'text', '-');
        //     $errValues = $this->setting->entry_points;
        //     $errValues['user_id'] = $userId;
        //     $errValues['phone'] = $phoneNumber;
        //     $errValues['send_message'] = $message;
        //     $errValues['error_message'] = $errMessage;

        //     SmsError::create($errValues);
        // }

        // pake Http (laravel package)
        $response = Http::post($url, $data);
        $status = $response->json('status', 1);
        $error = ($status != 1);

        if ($error && !empty($this->setting)) {
            $errMessage = $response->json('text', '-');
            $errValues = $this->setting->entry_points;
            $errValues['user_id'] = $userId;
            $errValues['phone'] = $phoneNumber;
            $errValues['send_message'] = $message;
            $errValues['error_message'] = $errMessage;

            SmsError::create($errValues);
        }

        return !$error;
    }
}
