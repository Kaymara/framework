<?php

namespace Compose\Contracts\Http\Middleware;

use Compose\Http\Request;
use Compose\Http\Response;

interface Middleware
{
    /**
     * Append middleware to the stack
     *
     * @param mixed $middleware
     *
     * @return $this
     */
    public function append($middleware);

    /**
     * Prepend middleware to the stack
     *
     * @param mixed $middleware
     *
     * @return $this
     */
    public function prepend($middleware);

    /**
     * Terminate any terminable middleware on the stack
     *
     * @return void
     */
    public function terminate();

    /**
     * Is the given middleware on the stack?
     *
     * @param string $middleware
     *
     * @return bool
     */
    public function has(string $middleware) : bool;

    /**
     * Get middleware at given index from the stack
     *
     * @param int $index
     *
     * @return mixed
     */
    public function get(int $index);

    /**
     * Pass request through middleware stack
     *
     * @param Request $request
     * @param Response $response
     * @param array $params
     *
     * @return Response
     */
    public function handle(Request $request, Response $response, $params = []);

    /**
     * Whether or not to skip middleware
     *
     * @param bool $value
     *
     * @return void
     */
    public function skip($value = true);

    /**
     * Should middleware be skipped?
     *
     * @return bool
     */
    public function shouldSkip();

    /**
     * Get middleware stack
     *
     * @return array
     */
    public function stack();

    /**
     * Remove middleware from stack
     *
     * @param mixed
     *
     * @return self
     */
    public function without($middleware);

    /**
     * Apply middleware collection(s)
     *
     * @param string|array $middleware
     *
     * @return self
     */
    public function collection($middleware);
}