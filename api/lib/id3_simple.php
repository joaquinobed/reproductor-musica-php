<?php
/**
 * Simple ID3 Reader for PHP
 * Extracts basic metadata (Title, Artist) from MP3 files
 */
class SimpleID3 {
    public static function read($filename) {
        $tags = ['title' => '', 'artist' => ''];
        $file = fopen($filename, 'rb');
        if (!$file) return $tags;

        // Try ID3v2 (at the beginning of file)
        $header = fread($file, 10);
        if (substr($header, 0, 3) === 'ID3') {
            $version = ord($header[3]);
            $size = ((ord($header[6]) & 0x7f) << 21) | ((ord($header[7]) & 0x7f) << 14) | ((ord($header[8]) & 0x7f) << 7) | (ord($header[9]) & 0x7f);
            
            $data = fread($file, $size);
            $pos = 0;
            while ($pos < $size - 10) {
                $frameID = substr($data, $pos, 4);
                $frameSize = (ord($data[$pos+4]) << 24) | (ord($data[$pos+5]) << 16) | (ord($data[$pos+6]) << 8) | ord($data[$pos+7]);
                
                // Compatibility for ID3v2.4 and unsync
                if ($frameSize <= 0 || $pos + 10 + $frameSize > $size) break;

                $frameData = substr($data, $pos + 10, $frameSize);
                
                // TIT2 = Title, TPE1 = Artist
                if ($frameID === 'TIT2') {
                    $tags['title'] = self::cleanText($frameData);
                } elseif ($frameID === 'TPE1') {
                    $tags['artist'] = self::cleanText($frameData);
                }
                
                $pos += 10 + $frameSize;
            }
        }

        // Try ID3v1 (last 128 bytes) if ID3v2 failed to find tags
        if (empty($tags['title']) || empty($tags['artist'])) {
            fseek($file, -128, SEEK_END);
            $tag1 = fread($file, 128);
            if (substr($tag1, 0, 3) === 'TAG') {
                if (empty($tags['title'])) $tags['title'] = trim(substr($tag1, 3, 30));
                if (empty($tags['artist'])) $tags['artist'] = trim(substr($tag1, 33, 30));
            }
        }

        fclose($file);
        return $tags;
    }

    private static function cleanText($data) {
        $encoding = ord($data[0]);
        $text = substr($data, 1);
        
        if ($encoding === 1) { // UTF-16
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-16');
        }
        
        return trim(preg_replace('/[[:cntrl:]]/', '', $text));
    }
}
?>
