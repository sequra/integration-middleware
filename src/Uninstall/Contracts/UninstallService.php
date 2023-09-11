<?php

namespace SeQura\Middleware\Uninstall\Contracts;

/**
 * Interface UninstallService
 *
 * @package SeQura\Middleware\Uninstall\Contracts
 */
interface UninstallService
{
    /**
     * Uninstalls an integration for the current user.
     */
    public function uninstall(): void;
}
