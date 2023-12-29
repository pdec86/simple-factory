<?php

declare(strict_types=1);

namespace App\Common\Util;

use GdImage;

abstract class BarcodeEanBase
{
    const SIZE_100 = '100';
    const SIZE_125 = '125';
    const SIZE_150 = '150';
    const SIZE_175 = '175';
    const SIZE_200 = '200';

    const AVAILABLE_SIZES = [
        self::SIZE_100,
        self::SIZE_125,
        self::SIZE_150,
        self::SIZE_175,
        self::SIZE_200,
    ];

    const DIMENSTIONS = [
        self::SIZE_100 => [1, 0.33, 31.35, 22.85, 3.63, 2.31],
        self::SIZE_125 => [1.25, 0.41, 39.19, 28.56, 4.54, 2.89],
        self::SIZE_150 => [1.5, 0.5, 47.03, 34.28, 5.45, 3.47],
        self::SIZE_175 => [1.75, 0.58, 54.86, 39.99, 6.35, 4.04],
        self::SIZE_200 => [2, 0.66, 62.70, 45.70, 7.26, 4.62],
    ];

    protected string $font;
    protected string $number;
    protected array $sizes;
    protected float $scale;

    protected $key;
    protected $bars;

    protected GdImage $gdImage;
    protected int $width;
    protected int $height;

    protected int $dpi = 300;
    protected bool $excludingNumber = false;
    protected int $leftQuietZone;
    protected int $rightQuietZone;
    protected int $barcodeWidth;
    protected int $barcodeHeight;
    protected int $barcodeYMargin;

    protected static $PARITY_KEY = array(
        0 => "000000", 1 => "001011", 2 => "001101", 3 => "001110",
        4 => "010011", 5 => "011001", 6 => "011100", 7 => "010101",
        8 => "010110", 9 => "011010"
    );

    protected static $LEFT_PARITY = array(
        // Odd Encoding
        0 => array(
            0 => "0001101", 1 => "0011001", 2 => "0010011", 3 => "0111101",
            4 => "0100011", 5 => "0110001", 6 => "0101111", 7 => "0111011",
            8 => "0110111", 9 => "0001011"
        ),
        // Even Encoding
        1 => array ( 
            0 => "0100111", 1 => "0110011", 2 => "0011011", 3 => "0100001", 
            4 => "0011101", 5 => "0111001", 6 => "0000101", 7 => "0010001", 
            8 => "0001001", 9 => "0010111"
        )
    );

    protected static $RIGHT_PARITY = array(
        0 => "1110010", 1 => "1100110", 2 => "1101100", 3 => "1000010", 
        4 => "1011100", 5 => "1001110", 6 => "1010000", 7 => "1000100", 
        8 => "1001000", 9 => "1110100"
    );

    protected static $GUARD = array(
        'start' => "101", 'middle' => "01010", 'end' => "101"
    );

    public function __construct(string $number, $size, $fontpath)
    {
        /* Get the parity key, which is based on the first digit. */
        $this->key = self::$PARITY_KEY[substr($number, 0, 1)];
        $this->font = $fontpath;

        if (!in_array($size, self::AVAILABLE_SIZES)) {
            throw new \InvalidArgumentException('Invalid barcode size provided.');
        }
        $this->sizes = self::DIMENSTIONS[$size];
        $this->scale = $this->sizes[0];

        if (strlen($number) != 13 && strlen($number) != 8) {
            throw new \InvalidArgumentException('Barcode must have 13 or 8 digits.');
        }

        $this->number = $number;

        $this->bars = $this->encode();
        $this->createImage();
        $this->drawBars();

        if (!$this->excludingNumber) {
            $this->drawText();
        }
    }

    public function &image(): GdImage
    {
        return $this->gdImage;
    }

    public function finalize(): void
    {
        imagedestroy($this->gdImage);
    }

    public function __destruct()
    {
        $this->finalize();
    }

    abstract protected function encode(): array;

    abstract protected function drawText(): void;

    protected function createImage()
    {
        $this->leftQuietZone = $this->convertMmToPx($this->sizes[4]);
        $this->rightQuietZone = $this->convertMmToPx($this->sizes[5]);
        
        $this->width = $this->convertMmToPx($this->sizes[2] + $this->sizes[4] + $this->sizes[5]);
        $this->barcodeWidth = $this->convertMmToPx($this->sizes[2]);
        $this->barcodeHeight = $this->convertMmToPx($this->sizes[3]);
        $this->barcodeYMargin = $this->convertMmToPx(2.31 * $this->scale);
        
        $this->height = $this->convertMmToPx($this->sizes[3] + $this->sizes[1] * 9.3) + $this->barcodeYMargin;
        
        $this->gdImage = imagecreate((int) $this->width, (int) $this->height);
        imagecolorallocate($this->gdImage, 0xFF, 0xFF, 0xFF);
    }

    protected function drawBars()
    {
        $barColor = imagecolorallocate($this->gdImage, 0x00, 0x00, 0x00);

        $barTop = $this->barcodeYMargin;
        $barBottom = $this->barcodeHeight;
        $barWidth = (int) ceil(($this->barcodeWidth) / (count($this->bars) * 2 * 4));
        $barWidth = (int) ceil($this->convertMmToPx(0.33 * $this->scale));
        
        $x = $this->leftQuietZone;

        foreach($this->bars as $bar) {
            $tall = 0;

            if (strlen($bar) == 3 || strlen($bar) == 5) {
                $tall = $this->barcodeYMargin;
            }

            for ($i = 1; $i <= strlen($bar); $i++) {
                if ('1' === substr($bar, $i - 1, 1)) {
                    imagefilledrectangle($this->gdImage, $x, $barTop, $x + $barWidth, $barBottom + $tall, $barColor);
                }

                $x += $barWidth;
            }
        }
    }

    protected function convertMmToPx(float $mm): int
    {
        $pixels = ($mm * $this->dpi) / 25.4;
        
        return (int) ceil($pixels);
    }
}
