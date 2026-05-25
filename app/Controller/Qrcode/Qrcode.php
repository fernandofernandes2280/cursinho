<?php
/**
 * @title            QR Code
 * @desc             Compatible to vCard 4.0 or higher.
 *
 * @author           Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright        (c) 2012-2021, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License <http://www.gnu.org/licenses/gpl.html>
 * @version          1.2
 */
namespace App\Controller\Qrcode;
class Qrcode
{
    const DEFAULT_QR_SIZE = 150;
    const ALPHANUMERIC_CHARSET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';

    private $sData;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sData = 'BEGIN:VCARD' . "\n";
        $this->sData .= 'VERSION:4.0' . "\n";
    }

    /**
     * The name of the person.
     *
     * @param string $sName
     *
     * @return self
     */
    public function name($sName)
    {
        $this->sData .= 'N:' . $sName . "\n";
        return $this;
    }

    /**
     * The full name of the person.
     *
     * @param string $sFullName
     *
     * @return self this
     */
    public function fullName($sFullName)
    {
        $this->sData = $sFullName;
        $this->sData = urlencode($this->sData);
        return $this;
    }

    /**
     * @param string $sAddress
     *
     * @return self
     */
    public function address($sAddress)
    {
        $this->sData .= 'ADR:' . $sAddress . "\n";

        return $this;
    }

    /**
     * @param string $sNickname
     *
     * @return self
     */
    public function nickName($sNickname)
    {
        $this->sData .= 'NICKNAME:' . $sNickname . "\n";
        return $this;
    }

    /**
     * @param string $sMail
     *
     * @return self
     */
    public function email($sMail)
    {
        $this->sData .= 'EMAIL;TYPE=PREF,INTERNET:' . $sMail . "\n";
        return $this;
    }

    /**
     * @param string $sVal
     *
     * @return self
     */
    public function workPhone($sVal)
    {
        $this->sData .= 'TEL;TYPE=WORK:' . $sVal . "\n";
        return $this;
    }

    /**
     * @param string $sVal
     *
     * @return self
     */
    public function homePhone($sVal)
    {
        $this->sData .= 'TEL;TYPE=HOME:' . $sVal . "\n";
        return $this;
    }

    /**
     * @param string $sUrl
     *
     * @return self
     */
    public function url($sUrl)
    {
        $sUrl = (substr($sUrl, 0, 4) != 'http') ? 'http://' . $sUrl : $sUrl;
        $this->sData .= 'URL:' . $sUrl . "\n";
        return $this;
    }

    /**
     * @param string $sPhone
     * @param string $sText
     *
     * @return self
     */
    public function sms($sPhone, $sText)
    {
        $this->sData .= 'SMSTO:' . $sPhone . ':' . $sText . "\n";
        return $this;
    }

    /**
     * @param string $sBirthday Date in the format YYYY-MM-DD or ISO 8601
     *
     * @return self
     */
    public function birthday($sBirthday)
    {
        $this->sData .= 'BDAY:' . $sBirthday . "\n";
        return $this;
    }

    /**
     * @param string $sBirthDate Date in the format YYYY-MM-DD or ISO 8601
     *
     * @return self
     */
    public function anniversary($sBirthDate)
    {
        $this->sData .= 'ANNIVERSARY:' . $sBirthDate . "\n";
        return $this;
    }

    /**
     * @param string $sSex F = Female. M = Male
     *
     * @return self
     */
    public function gender($sSex)
    {
        $this->sData .= 'GENDER:' . $sSex . "\n";
        return $this;
    }

    /**
     * A list of "tags" that can be used to describe the object represented by this vCard.
     *
     * @param string $sCategories
     *
     * @return self
     */
    public function categories($sCategories)
    {
        $this->sData .= 'CATEGORIES:' . $sCategories . "\n";
        return $this;
    }

    /**
     * The instant messenger (Instant Messaging and Presence Protocol).
     *
     * @param string $sVal
     *
     * @return self
     */
    public function impp($sVal)
    {
        $this->sData .= 'IMPP:' . $sVal . "\n";
        return $this;
    }

    /**
     * Photo (avatar).
     *
     * @param string $sImgUrl URL of the image.
     *
     * @return self
     *
     * @throws \InvalidArgumentException:: If the image format is invalid.
     */
    public function photo($sImgUrl)
    {
        $bIsImgExt = strtolower(substr(strrchr($sImgUrl, '.'), 1)); // Get the file extension.

        if ($bIsImgExt == 'jpeg' || $bIsImgExt == 'jpg' || $bIsImgExt == 'png' || $bIsImgExt == 'gif') {
            $sExt = strtoupper($bIsImgExt);
        } else {
            throw new \InvalidArgumentException('Invalid format Image!');
        }

        $this->sData .= 'PHOTO;VALUE=URL;TYPE=' . $sExt . ':' . $sImgUrl . "\n";

        return $this;
    }

    /**
     * The role, occupation, or business category of the vCard object within an organization.
     *
     * @param string $sRole e.g., Executive
     *
     * @return self
     */
    public function role($sRole)
    {
        $this->sData .= 'ROLE:' . $sRole . "\n";
        return $this;
    }

    /**
     * The organization / company.
     *
     * The name and optionally the unit(s) of the organization
     * associated with the vCard object. This property is based on the X.520 Organization Name
     * attribute and the X.520 Organization Unit attribute.
     *
     * @param string $sOrg e.g., Google;GMail Team;Spam Detection Squad
     *
     * @return self
     */
    public function organization($sOrg)
    {
        $this->sData .= 'ORG:' . $sOrg . "\n";
        return $this;
    }

    /**
     * The supplemental information or a comment that is associated with the vCard.
     *
     * @param string $sText
     *
     * @return self
     */
    public function note($sText)
    {
        $this->sData .= 'NOTE:' . $sText . "\n";
        return $this;
    }

    /**
     * @param string $sTitle
     * @param string $sUrl
     *
     * @return self
     */
    public function bookmark($sTitle, $sUrl)
    {
        $this->sData .= 'MEBKM:TITLE:' . $sTitle . ';URL:' . $sUrl . "\n";
        return $this;
    }

    /**
     * Geo location.
     *
     * @param string $sLat Latitude
     * @param string $sLon Longitude
     * @param int $iHeight Height
     *
     * @return self
     */
    public function geo($sLat, $sLon, $iHeight)
    {
        $this->sData .= 'GEO:' . $sLat . ',' . $sLon . ',' . $iHeight . "\n";
        return $this;
    }

    /**
     * The language that the person speaks.
     *
     * @param string $sLang e.g., en-US
     *
     * @return self
     */
    public function lang($sLang)
    {
        $this->sData .= 'LANG:' . $sLang . "\n";
        return $this;
    }

    /**
     * @param string $sType
     * @param string $sSsid
     * @param string $sPwd
     *
     * @return self
     */
    public function wifi($sType, $sSsid, $sPwd)
    {
        $this->sData .= 'WIFI:T:' . $sType . ';S' . $sSsid . ';' . $sPwd . "\n";
        return $this;
    }

    /**
     * Generate the QR code.
     *
     * @return self
     */
    public function finish()
    {
        $this->sData .= 'END:VCARD';
        $this->sData = urlencode($this->sData);
        return $this;
    }

    /**
     * Get the URL of QR Code.
     *
     * @param int $iSize Default 150
     * @param string $sECLevel Default L
     * @param integer $iMargin Default 1
     *
     * @return string The API URL configure.
     */
    public function get($iSize = self::DEFAULT_QR_SIZE, $sECLevel = 'L', $iMargin = 1)
    {
        return 'data:image/png;base64,'.base64_encode($this->png($iSize, $iMargin));
    }

    public function png($iSize = self::DEFAULT_QR_SIZE, $iMargin = 4)
    {
        $payload = urldecode($this->sData);
        $matrix = self::generateVersionOneMatrix($payload);
        $moduleCount = count($matrix);
        $scale = max(1, (int)floor($iSize / ($moduleCount + ($iMargin * 2))));
        $imageSize = $iSize;
        $qrSize = ($moduleCount + ($iMargin * 2)) * $scale;
        $offset = (int)floor(($imageSize - $qrSize) / 2);

        $image = imagecreatetruecolor($imageSize, $imageSize);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $imageSize, $imageSize, $white);

        for($y = 0; $y < $moduleCount; $y++){
            for($x = 0; $x < $moduleCount; $x++){
                if(!$matrix[$y][$x]) continue;
                $left = $offset + (($x + $iMargin) * $scale);
                $top = $offset + (($y + $iMargin) * $scale);
                imagefilledrectangle($image, $left, $top, $left + $scale - 1, $top + $scale - 1, $black);
            }
        }

        ob_start();
        imagepng($image);
        imagedestroy($image);
        return ob_get_clean();
    }

    public function savePng($path, $iSize = self::DEFAULT_QR_SIZE, $iMargin = 4)
    {
        return file_put_contents($path, $this->png($iSize, $iMargin));
    }

    private static function generateVersionOneMatrix($payload)
    {
        $payload = strtoupper($payload);
        $bits = self::encodeAlphanumeric($payload);
        $dataCodewords = self::bitsToCodewords($bits, 19);
        $ecCodewords = self::reedSolomonComputeRemainder($dataCodewords, self::reedSolomonComputeDivisor(7));
        $codewords = array_merge($dataCodewords, $ecCodewords);

        $size = 21;
        $modules = array_fill(0, $size, array_fill(0, $size, false));
        $isFunction = array_fill(0, $size, array_fill(0, $size, false));

        self::drawFunctionPatterns($modules, $isFunction, $size);
        // Reserve format modules before data placement so the QR bit stream stays aligned.
        self::drawFormatBits($modules, $isFunction, $size, 0);
        self::drawCodewords($modules, $isFunction, $size, $codewords, 0);

        return $modules;
    }

    private static function encodeAlphanumeric($payload)
    {
        $bits = [];
        self::appendBits($bits, 0x2, 4);
        self::appendBits($bits, strlen($payload), 9);

        for($i = 0; $i < strlen($payload); $i += 2){
            $first = strpos(self::ALPHANUMERIC_CHARSET, $payload[$i]);
            if($first === false) throw new \InvalidArgumentException('Unsupported QR character: '.$payload[$i]);

            if($i + 1 < strlen($payload)){
                $second = strpos(self::ALPHANUMERIC_CHARSET, $payload[$i + 1]);
                if($second === false) throw new \InvalidArgumentException('Unsupported QR character: '.$payload[$i + 1]);
                self::appendBits($bits, ($first * 45) + $second, 11);
            }else{
                self::appendBits($bits, $first, 6);
            }
        }

        return $bits;
    }

    private static function bitsToCodewords($bits, $dataCodewordCount)
    {
        $capacity = $dataCodewordCount * 8;
        self::appendBits($bits, 0, min(4, $capacity - count($bits)));
        while(count($bits) % 8 !== 0) $bits[] = 0;

        $padBytes = [0xEC, 0x11];
        $padIndex = 0;
        while(count($bits) < $capacity){
            self::appendBits($bits, $padBytes[$padIndex], 8);
            $padIndex = 1 - $padIndex;
        }

        $codewords = [];
        for($i = 0; $i < count($bits); $i += 8){
            $byte = 0;
            for($j = 0; $j < 8; $j++) $byte = ($byte << 1) | $bits[$i + $j];
            $codewords[] = $byte;
        }

        return $codewords;
    }

    private static function appendBits(&$bits, $value, $length)
    {
        for($i = $length - 1; $i >= 0; $i--) $bits[] = ($value >> $i) & 1;
    }

    private static function drawFunctionPatterns(&$modules, &$isFunction, $size)
    {
        self::drawFinderPattern($modules, $isFunction, 3, 3, $size);
        self::drawFinderPattern($modules, $isFunction, $size - 4, 3, $size);
        self::drawFinderPattern($modules, $isFunction, 3, $size - 4, $size);

        for($i = 8; $i < $size - 8; $i++){
            $dark = $i % 2 === 0;
            self::setFunctionModule($modules, $isFunction, 6, $i, $dark);
            self::setFunctionModule($modules, $isFunction, $i, 6, $dark);
        }

        self::setFunctionModule($modules, $isFunction, 8, $size - 8, true);
    }

    private static function drawFinderPattern(&$modules, &$isFunction, $centerX, $centerY, $size)
    {
        for($dy = -4; $dy <= 4; $dy++){
            for($dx = -4; $dx <= 4; $dx++){
                $x = $centerX + $dx;
                $y = $centerY + $dy;
                if($x < 0 || $x >= $size || $y < 0 || $y >= $size) continue;
                $distance = max(abs($dx), abs($dy));
                self::setFunctionModule($modules, $isFunction, $x, $y, $distance !== 2 && $distance !== 4);
            }
        }
    }

    private static function drawCodewords(&$modules, $isFunction, $size, $codewords, $mask)
    {
        $bits = [];
        foreach($codewords as $codeword) self::appendBits($bits, $codeword, 8);

        $bitIndex = 0;
        $upward = true;
        for($right = $size - 1; $right >= 1; $right -= 2){
            if($right === 6) $right--;

            for($vert = 0; $vert < $size; $vert++){
                $y = $upward ? $size - 1 - $vert : $vert;
                for($j = 0; $j < 2; $j++){
                    $x = $right - $j;
                    if($isFunction[$y][$x]) continue;

                    $dark = $bitIndex < count($bits) ? (bool)$bits[$bitIndex] : false;
                    $bitIndex++;
                    if(self::mask($mask, $x, $y)) $dark = !$dark;
                    $modules[$y][$x] = $dark;
                }
            }

            $upward = !$upward;
        }
    }

    private static function drawFormatBits(&$modules, &$isFunction, $size, $mask)
    {
        $formatBits = self::getFormatBits($mask);

        for($i = 0; $i <= 5; $i++) self::setFunctionModule($modules, $isFunction, 8, $i, self::getBit($formatBits, $i));
        self::setFunctionModule($modules, $isFunction, 8, 7, self::getBit($formatBits, 6));
        self::setFunctionModule($modules, $isFunction, 8, 8, self::getBit($formatBits, 7));
        self::setFunctionModule($modules, $isFunction, 7, 8, self::getBit($formatBits, 8));
        for($i = 9; $i < 15; $i++) self::setFunctionModule($modules, $isFunction, 14 - $i, 8, self::getBit($formatBits, $i));

        for($i = 0; $i < 8; $i++) self::setFunctionModule($modules, $isFunction, $size - 1 - $i, 8, self::getBit($formatBits, $i));
        for($i = 8; $i < 15; $i++) self::setFunctionModule($modules, $isFunction, 8, $size - 15 + $i, self::getBit($formatBits, $i));
        self::setFunctionModule($modules, $isFunction, 8, $size - 8, true);
    }

    private static function getFormatBits($mask)
    {
        $data = (1 << 3) | $mask;
        $remainder = $data << 10;
        for($i = 14; $i >= 10; $i--){
            if((($remainder >> $i) & 1) !== 0) $remainder ^= 0x537 << ($i - 10);
        }
        return (($data << 10) | $remainder) ^ 0x5412;
    }

    private static function getBit($value, $index)
    {
        return (($value >> $index) & 1) !== 0;
    }

    private static function setFunctionModule(&$modules, &$isFunction, $x, $y, $dark)
    {
        $modules[$y][$x] = $dark;
        $isFunction[$y][$x] = true;
    }

    private static function mask($mask, $x, $y)
    {
        return ($x + $y) % 2 === 0;
    }

    private static function reedSolomonComputeDivisor($degree)
    {
        $result = array_fill(0, $degree, 0);
        $result[$degree - 1] = 1;
        $root = 1;

        for($i = 0; $i < $degree; $i++){
            for($j = 0; $j < $degree; $j++){
                $result[$j] = self::reedSolomonMultiply($result[$j], $root);
                if($j + 1 < $degree) $result[$j] ^= $result[$j + 1];
            }
            $root = self::reedSolomonMultiply($root, 0x02);
        }

        return $result;
    }

    private static function reedSolomonComputeRemainder($data, $divisor)
    {
        $result = array_fill(0, count($divisor), 0);

        foreach($data as $byte){
            $factor = $byte ^ $result[0];
            array_shift($result);
            $result[] = 0;

            for($i = 0; $i < count($result); $i++){
                $result[$i] ^= self::reedSolomonMultiply($divisor[$i], $factor);
            }
        }

        return $result;
    }

    private static function reedSolomonMultiply($x, $y)
    {
        $z = 0;
        for($i = 7; $i >= 0; $i--){
            $z = ($z << 1) ^ (($z >> 7) * 0x11D);
            $z ^= (($y >> $i) & 1) * $x;
        }
        return $z & 0xFF;
    }

    /**
     * The HTML code for displaying the QR Code.
     *
     * @param int $iSize Default 150
     * @return void
     */
    public function display($iSize = self::DEFAULT_QR_SIZE)
    {
        echo '<p class="center"><img src="' . $this->_cleanUrl($this->get($iSize)) . '" alt="QR Code" /></p>';
    }

    /**
     * Clean URL.
     *
     * @param string $sUrl
     *
     * @return string
     */
    private function _cleanUrl($sUrl)
    {
        return str_replace('&', '&amp;', $sUrl);
    }
}
