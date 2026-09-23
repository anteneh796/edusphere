<?php

namespace App\Support\Enums;

enum CommunicationType: string
{
    case Email = 'email';
    case PhoneCall = 'phone_call';
    case Sms = 'sms';
    case InPerson = 'in_person';
    case Letter = 'letter';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::PhoneCall => 'Phone call',
            self::Sms => 'SMS',
            self::InPerson => 'In person',
            self::Letter => 'Letter',
        };
    }
}
