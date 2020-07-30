<?php

namespace Compose\Contracts\Http\Debug;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\HttpFoundation\Request;

interface ExceptionHandler
{
    /**
     * Report an exception
     *
     * @param \Exception $e
     *
     * @return void
     */
    public function report(\Exception $e);

    /**
     * Render an Http exception
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render(Request $request, \Exception $e);

    /**
     * Render an exception in the console
     *
     * @param \Symfony\Component\Console\Output\ConsoleOutputInterface $output
     * @param \Exception $e
     *
     * @return void
     */
    public function renderConsole(ConsoleOutputInterface $output, \Exception $e);
}