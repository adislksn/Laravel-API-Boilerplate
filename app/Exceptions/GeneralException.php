<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GeneralException extends Exception
{
    public function __construct($code, $message)
    {
        if($code === 0) $code = 500;
        parent::__construct($code, $message);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        return response([
            'status'    => 'fail',
            'message'   => $this->message,
            'data'      => [],
        ])->setStatusCode($this->code);
    }
}
