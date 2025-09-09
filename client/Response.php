<?php

namespace SMSCenter;

class Response
{
    protected $raw;
    protected $data;

    protected $smsId;
    protected $smsCnt;
    protected $cost;
    protected $balance;

    protected $error;
    protected $errorCode;

    protected $throwException = false;

    /**
     * Конструктор класса
     * @param string $response JSON-строка ответа от API
     * @throws RequestException
     */
    public function __construct($response = null, $format = Client::FMT_JSON)
    {
        if ($response !== null) {
            $this->parse($response, $format);
        }
    }

    /**
     * Получить ID SMS
     * @return int
     */
    public function getSmsId()
    {
        return $this->smsId;
    }

    /**
     * Получить количество SMS
     * @return int
     */
    public function getSmsCount()
    {
        return $this->smsCnt;
    }

    /**
     * Получить стоимость
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Получить баланс
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Получить ошибки
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Получить ошибки
     * @return int|string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param $message
     * @param $code
     * @return void
     * @throws RequestException
     */
    protected function error($message, $code = null)
    {
        $this->error     = $message;
        $this->errorCode = $code;

        if ($this->throwException) {
            throw new RequestException($this->error, $this->errorCode, $this);
        }
    }

    /**
     * @param     $response
     * @param int $format
     * @return static
     * @throws RequestException
     */
    public function parse($response, $format = Client::FMT_JSON)
    {
        $this->raw  = $response;
        $this->data = $this->fromRaw($response, $format);

        if (empty($this->data)) {
            $this->error('Empty response');
        }

        if (isset($this->data['error'])) {
            $this->error($this->data['error'], $this->data['error_code']);
        } else {
            $this->smsId   = isset($this->data['id']) ? (int)$this->data['id'] : null;
            $this->smsCnt  = isset($this->data['cnt']) ? (int)$this->data['cnt'] : null;
            $this->cost    = isset($this->data['cost']) ? (float)$this->data['cost'] : null;
            $this->balance = isset($this->data['balance']) ? (float)$this->data['balance'] : null;
        }

        return $this;
    }

    /**
     * @param $raw
     * @param $format
     * @return array|mixed
     * @throws RequestException
     */
    protected function fromRaw($raw, $format = Client::FMT_JSON)
    {
        switch ($format) {
            case Client::FMT_XML:
                $data = json_decode(json_encode(simplexml_load_string($raw)), true);

                return isset($data['result']) ? $data['result'] : $data;
            case Client::FMT_JSON:
                $data = json_decode($raw, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Ошибка декодирования JSON');
                }

                return $data;
            default:
                return [];
        }
    }

    /**
     * @return bool
     */
    public function bad()
    {
        return !$this->ok();
    }

    /**
     * @return bool
     */
    public function ok()
    {
        return empty($this->error);
    }
}
