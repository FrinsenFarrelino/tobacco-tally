<?php
namespace App\Handlers;

class StatusMessageCode{
    private $statusMessageText;
    private $statusErrorText;

    public static $statusMessageTexts = [
        0 => '',
        600 => 'No Account Found with that username',
        601 => 'If you enter your password incorrect 3 times, then your account will be blocked',
        700 => 'after entering your password incorrectly 3 times',
    ];

    public static $statusErrorTexts = [
        0 => '',
        601 => 'Either your username or password is incorrect',
        700 => 'Your account has been blocked',
    ];

    public function setCustomMessageStatusCode(int $code, string $text = null)
    {
        $this->statusMessageText = self::$statusMessageTexts[$code] ?? 'unknown status code';

        return $this->statusMessageText;
    }

    public function setCustomMessageErrorCode(int $code, string $text = null)
    {
        $this->statusErrorText = self::$statusErrorTexts[$code] ?? '';

        return $this->statusErrorText;
    }
}
