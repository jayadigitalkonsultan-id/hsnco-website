<?php

namespace App\Services\Mail;

/** Only application-authored messages without provider payloads or credentials. */
class GmailDeliveryException extends \RuntimeException
{
}
