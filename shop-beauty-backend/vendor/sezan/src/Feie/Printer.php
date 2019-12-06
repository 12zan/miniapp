<?php

namespace SeZan\Feie;

use SeZan\Kernel\Http\HttpClient;
use SeZan\Kernel\Exceptions\CustomeException;

class Printer extends Feie
{
    protected $privateParams = [];

    public function setParames($data)
    {
        $this->privateParams['printerContent'] = implode("#", $data);

        return $this;
    }

    public function getApiName()
    {
        return 'Open_printerAddlist';
    }


}

?>