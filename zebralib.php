<?PHP
// Parts from https://github.com/robgridley/zebra
// Class to convert Image to ZPL
// joembedded@gmail.com
class ZImage{
    public function __construct($image)
    {
        if (!$this->isGdResource($image)) {
            throw new InvalidArgumentException('Invalid resource');
        }
        if (!imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        $this->image = $image;
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }
    public function __destruct()
    {
        imagedestroy($this->image);
    }
    public function isGdResource($image)
    {
        if (is_resource($image)) {
            return get_resource_type($image) === 'gd';
        }
        return false;
    }
    public function height(){
		return $this->height;
	}
    public function width(){
		return $this->width;
	}
	
    public function toAscii()
    {
        $bytesPerRow = (int)ceil($this->width / 8);
        $byteCount = $fieldCount = $bytesPerRow * $this->height;
		return "^GFA,$byteCount,$fieldCount,$bytesPerRow,".$this->encode();
    }
    protected function encode()
    {
        $bitmap = null;
        $lastRow = null;
        for ($y = 0; $y < $this->height; $y++) {
            $bits = null;
            for ($x = 0; $x < $this->width; $x++) {
                $bits .= (imagecolorat($this->image, $x, $y) & 0xFF) < 127 ? 1 : 0;
            }
            $bytes = str_split($bits, 8);
            $bytes[] = str_pad(array_pop($bytes), 8, '0');
            $row = null;
            foreach ($bytes as $byte) {
                $row .= sprintf('%02X', bindec($byte));
            }
            $bitmap .= $this->compress($row, $lastRow);
            $lastRow = $row;
        }
        return $bitmap;
    }
    protected function compress(string $row, ?string $lastRow): string
    {
        if ($row === $lastRow) {
            return ':';
        }
        $row = $this->compressTrailingZerosOrOnes($row);
        $row = $this->compressRepeatingCharacters($row);
        return $row;
    }
    protected function compressTrailingZerosOrOnes(string $row): string
    {
        return preg_replace(['/0+$/', '/F+$/'], [',', '!'], $row);
    }
    protected function compressRepeatingCharacters(string $row): string
    {
        $callback = function ($matches) {
            $original = $matches[0];
            $repeat = strlen($original);
            $count = null;
            if ($repeat > 400) {
                $count .= str_repeat('z', floor($repeat / 400));
                $repeat %= 400;
            }
            if ($repeat > 19) {
                $count .= chr(ord('f') + floor($repeat / 20));
                $repeat %= 20;
            }
            if ($repeat > 0) {
                $count .= chr(ord('F') + $repeat);
            }
            return $count . substr($original, 1, 1);
        };
        return preg_replace_callback('/(.)(\1{2,})/', $callback, $row);
    }
}

// Send Image to TCP-RAW, Timeout 30 secs
function zpl_sendto($host, $port, $zpl){
	$fp = @fsockopen($host, $port, $errno, $errstr, 30);
	if (!$fp) {
		return "ERROR: $errstr ($errno)";
	} else {
		fwrite($fp, $zpl);
		fclose($fp);
		return '';	// OK
	}
}

/*
//----------- TEST -----------
// Es ist empfehlenswertz bei den 38x23 Etiketten (450 x 270 Pixel) ca. 5 Pixel Rand zu lassen
// Aussenrumzeichen, XA, XZ, FS, .. Fehlen noch
$dbg = 0;
header('Content-Type: text/plain');
echo "Test IMG_to_ZPL\n\n";

$srcimage = @imagecreatefrompng('qr155.png'); // oder 'bt32x32.png', 'testbild440x260.png' oder ..
if($srcimage === false ) die("ERROR: File not Found");

echo "SourceImage H,W:".imagesy($srcimage).",".imagesx($srcimage)."\n";
$scaledimage=imagescale ($srcimage , 100,100);

$zimage = new ZImage($scaledimage); // GD Image Loader
echo "ZImage H,W:".$zimage->height().",".$zimage->width()."\n";

$data = $zimage->toAscii();

$etikett = "^XA\n^FO5,5\n$data\n^XZ\n";
if(!$dbg){
	$res = zpl_sendto('192.168.178.241',6101,$etikett); // 6101 Default RAW Port ZD420
}else{
	echo $etikett;
	$res = null;
}
echo $res ? $res : "OK";
*/
?>
