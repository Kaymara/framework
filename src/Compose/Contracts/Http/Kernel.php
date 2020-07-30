<?php

namespace Compose\Contracts\Http;

use Compose\Http\Request;
use Compose\Http\Response;

interface Kernel
{
    /**
     * Bootstrap the application for handling Http requests
     *
     * @return void
     */
    public function bootstrap();

    /**
     * Consume an Http request and return an Http response
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request);

    /**
     * Perform any final actions during the request/response cycle
     *
     * @param Request $request
     * @param Response $response
     *
     * @return void
     */
    public function terminate(Request $request, Response $response);

    /**
     * Get middleware collection by key
     *
     * @param string $collection
     *
     * @return mixed
     */
    public function middlewareCollection(string $collection);
}