<?php

namespace CaioMarcatti12\WebSocketServer;

use CaioMarcatti12\Data\ObjectMapper;

class RouterResponse
{
    private mixed $_body;
    private int $_status = 200;

    public function __construct(mixed $body, int $status = 200)
    {
        $this->_body = $body;
        $this->_status = $status;
    }

    public function response(): string
    {
        if(is_array($this->_body)) return $this->arrayResponse();
        if(is_object($this->_body)) return $this->objectResponse();

        return $this->stringResponse();
    }

    private function stringResponse(): string{
        return $this->_body ?? '';
    }

    private function arrayResponse(): string{
        return json_encode($this->_body);
    }

    private function objectResponse(): string{
        $this->_body = ObjectMapper::toArray($this->_body);
        return $this->arrayResponse();
    }
}