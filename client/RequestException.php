<?php

namespace SMSCenter;

class RequestException extends \Exception
{
    /**
     * @var Response|null
     */
    protected $response;

    public function __construct($message = "", $code = 0, $response = null, \Exception $previous = null)
    {
        $this->response = $response;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

}