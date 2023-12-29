<?php

declare(strict_types=1);

namespace App\Common\Util;

class BarcodeEan13 extends BarcodeEanBase
{
    protected function encode(): array
    {
        $barcode[] = self::$GUARD['start'];
        
        for ($i = 1; $i <= strlen($this->number) - 1; $i++) {
            if ($i < 7) {
                $barcode[] = self::$LEFT_PARITY[$this->key[$i - 1]][substr($this->number, $i, 1)];
            } else {
                $barcode[] = self::$RIGHT_PARITY[substr($this->number, $i, 1)];
            }
            
            if ($i == 6) {
                $barcode[] = self::$GUARD['middle'];
            }
        }
        $barcode[] = self::$GUARD['end'];

        return $barcode;
    }

    protected function drawText(): void
    {
        $x = (int) ceil($this->leftQuietZone * 0.33);
        $y = $this->barcodeHeight + $this->barcodeYMargin * 1.33;

        $textColor = imagecolorallocate($this->gdImage, 0x00, 0x00, 0x00);
        
        // y = ax + b
        $fontsize = $this->convertMmToPx(($this->scale * 0.15 + (2.22 - 0.15 * $this->scale)) * $this->scale);
        $kerning = $fontsize * 1;

        for ($i = 0; $i < strlen($this->number); $i++) {
            imagettftext($this->gdImage, $fontsize, 0, (int) ceil($x), (int) ceil($y), $textColor, $this->font, $this->number[$i]);
            
            if (0 == $i || 6 == $i) {
                $x += $kerning * 0.9;
            }
            $x += $kerning;
        }
    }
}