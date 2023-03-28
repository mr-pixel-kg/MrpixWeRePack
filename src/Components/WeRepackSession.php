<?php

namespace Mrpix\WeRepack\Components;

use Symfony\Component\HttpFoundation\Session\Session;

class WeRepackSession
{
    public const SESSION_WEREPACK_ENABLED = 'MrpixWeRepack_enabled';

    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * Set the selected store.
     */
    public function setWeRepackEnabled(bool $werepackEnabled): void
    {
        $this->session->set(self::SESSION_WEREPACK_ENABLED, $werepackEnabled);
    }

    /**
     * Returns true if the user has enabled WeRepack.
     */
    public function isWeRepackEnabled(): bool
    {
        return $this->session->get(self::SESSION_WEREPACK_ENABLED, false);
    }


    /**
     * Resets and clears all data in the session.
     */
    public function clear(): void
    {
        $this->session->set(self::SESSION_WEREPACK_ENABLED, false);
    }

    /**
     * Returns the Symfony Session.
     */
    public function getSession(): Session
    {
        return $this->session;
    }
}