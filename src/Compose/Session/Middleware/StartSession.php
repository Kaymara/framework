<?php declare(strict_types=1);

namespace Compose\Session\Middleware;

use Compose\Http\Request;
use Compose\Http\Response;
use Compose\Session\SessionManager as Manager;

class StartSession
{
    /**
     * The Session Manager instance
     *
     * @var Manager $manager;
     */
    protected $manager;

    public function __construct(?Manager $manager  = null)
    {
        $this->manager = $manager ?? Manager::create();
    }

    public function __invoke(Request $request, Response $response, array $params, $next)
    {
        session_start();
    }

    public function getSession(Request $request)
    {
        // get driver
        if (! $this->manager->driverConfigured()) {
            // if driver is not configured, bail
        }

        // create session from cookie if exists
    }
}