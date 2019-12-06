<?php

namespace App\Util;

use Endroid\QrCode\QrCode as SQrCode;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;


/**
 * 二维码生成
 */
class QrCode
{

    public static function basic($str)
    {

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );

        $writer = new Writer($renderer);

        return  $writer->writeString($str);
    }

    public static function getBaseCode($str)
    {
        $qrCode = self::basic($str);

        return "data:image/png;base64,".base64_encode($qrCode);
    }


}