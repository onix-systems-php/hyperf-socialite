<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfSocialite\Contracts;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface Provider
{
    /**
     * Redirect the user to the authentication page for the provider.
     */
    public function redirect(): PsrResponseInterface;

    /**
     * Get the User instance for the authenticated user.
     */
    public function user(): User;
}
