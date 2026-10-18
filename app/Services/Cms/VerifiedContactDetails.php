<?php

namespace App\Services\Cms;

class VerifiedContactDetails
{
    public function from(?array $settings): array
    {
        $verified = is_array($settings) && filter_var($settings['verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $emailCandidate = is_string($settings['email'] ?? null) ? trim($settings['email']) : '';
        $email = $verified && filter_var($emailCandidate, FILTER_VALIDATE_EMAIL) ? $emailCandidate : null;

        $phoneCandidate = is_string($settings['phone'] ?? null) ? trim($settings['phone']) : '';
        $phoneDigits = preg_replace('/\D/', '', $phoneCandidate);
        $phone = $verified && preg_match('/^\+?[0-9().\-\s]{5,40}$/', $phoneCandidate)
            && strlen($phoneDigits) >= 5 ? $phoneCandidate : null;

        $addressCandidate = is_string($settings['office_address'] ?? null) ? trim($settings['office_address']) : '';
        $address = $verified && $addressCandidate !== '' ? $addressCandidate : null;

        return [
            'verified' => $verified,
            'address' => $address,
            'email' => $email,
            'phone' => $phone,
            'phone_href' => $phone ? preg_replace('/[^0-9+]/', '', $phone) : null,
        ];
    }
}
