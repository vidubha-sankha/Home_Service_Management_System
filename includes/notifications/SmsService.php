<?php

class SmsService {
    public static function normalizePhone($phone) {
        $phone = preg_replace('/[^0-9\+]/', '', $phone);
        if (strpos($phone, '0') === 0) {
            $phone = '+94' . substr($phone, 1);
        } elseif (strpos($phone, '94') === 0) {
            $phone = '+' . $phone;
        } elseif (strpos($phone, '+') !== 0) {
            $phone = '+' . $phone;
        }
        return $phone;
    }

    public static function sendSms($phone, $message) {
        $mode = getenv('NOTIFICATION_MODE') ?: 'development';
        $phone = self::normalizePhone($phone);
        
        if ($mode === 'development') {
            return ['status' => true, 'provider_id' => 'DEV_SMS_' . uniqid(), 'error' => null];
        }

        // Mock Twilio call or equivalent
        // In a real app we would use curl to POST to Twilio API
        
        $account_sid = getenv('TWILIO_ACCOUNT_SID');
        $auth_token = getenv('TWILIO_AUTH_TOKEN');
        $twilio_number = getenv('TWILIO_PHONE_NUMBER');
        
        if (empty($account_sid) || empty($auth_token)) {
            return ['status' => false, 'provider_id' => null, 'error' => 'SMS credentials not configured'];
        }

        // Simulated success for production mode without real curl
        // To do real curl: 
        // $url = "https://api.twilio.com/2010-04-01/Accounts/$account_sid/Messages.json";
        // curl_setopt...
        
        return ['status' => true, 'provider_id' => 'PROD_SMS_' . uniqid(), 'error' => null];
    }
}
