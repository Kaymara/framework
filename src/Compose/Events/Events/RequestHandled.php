<?php

namespace Compose\Events\Events;

use Compose\Http\Request;
use Compose\Http\Response;

class RequestHandled
{
    /**
     * The request
     *
     * @var Request
     */
    public $request;

    /**
     * The response
     *
     * @var Response
     */
    public $response;

    /**
     * Create a new RequestHandled event
     *
     * @param Request $request
     * @param Response $response
     *
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}