<?php

namespace SeZan\Feie;

use SeZan\Kernel\Http\HttpClient;
use SeZan\Kernel\Exceptions\CustomeException;

class PrinterStatus extends Feie
{
    protected $privateParams = [];

    public function setParames($private)
    {
        $this->privateParams = $private;

        return $this;
    }

    public function getApiName()
    {
        return 'Open_queryPrinterStatus';
    }


}

?>