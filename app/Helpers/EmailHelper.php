<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class EmailHelper
{
    public static function isStudentEmail(string $email): bool
    {
        $emailDomain = Str::after($email, '@');
        return Str::endsWith($emailDomain, ['.edu', '.ac.id'])
          && !empty(dns_get_record($emailDomain, DNS_MX));
    }
}
