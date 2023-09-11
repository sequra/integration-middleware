<?php

namespace SeQura\Middleware\Utility;

use SeQura\Core\BusinessLogic\Utility\EncryptorInterface;

/**
 * Class Encryptor.
 *
 * @package SeQura\Middleware\Utility
 */
class Encryptor implements EncryptorInterface
{
    /**
     * @inheritDoc
     */
    public function encrypt(string $data): string
    {
        return encrypt($data);
    }

    /**
     * @inheritDoc
     */
    public function decrypt(string $encryptedData): string
    {
        return decrypt($encryptedData);
    }
}
