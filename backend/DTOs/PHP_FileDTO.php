<?php

namespace DTOs;
use stdClass;
use IDTO;

class PHP_FileDTO implements IDTO
{
    public string $name;
    public string $type;
    public int $size;
    public string $tmp_name;

    public function __construct(array | stdClass $data)
    {
         if ($data instanceof stdClass) {
            $data = (array)$data;
        }

       if(array_key_exists('name', $data)) {
            $this->name = (string)$data['name'];
        }
        if(array_key_exists('type', $data)) {
            $this->type = (string)$data['type'];
        }

        if(array_key_exists('size', $data)) {
            $this->size = (int)$data['size'];
        }

        if(array_key_exists('tmp_name', $data)) {
            $this->tmp_name = (string)$data['tmp_name'];
        }
    }
}