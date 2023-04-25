<?php
declare (strict_types = 1);

namespace tenglin\integrate;

class Common
{
    public static function vendor(string $string = '')
    {
        $vendorPath = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        if (!empty($string)) {
            return $vendorPath . $string;
        } else {
            return $vendorPath;
        }
    }

    public static function file_split(string $file = '', $size = 2048)
    {
        $blockInfo = [];
        $blockSize = $size * 1024;
        $fileSize = filesize($file);
        $i = 0;
        while ($fileSize > 0) {
            $blockInfo[] = [
                'size' => ($fileSize >= $blockSize ? $blockSize : $fileSize),
                'file' => $file . '.' . $i . '.myArchive',
            ];
            $fileSize -= $blockSize;
            $i++;
        }
        $fp = fopen($file, 'rb');
        foreach ($blockInfo as $item) {
            $handle = fopen($item['file'], 'wb');
            fwrite($handle, fread($fp, $item['size']));
            fclose($handle);
            unset($handle);
        }
        fclose($fp);
        unset($fp);
    }

    public static function file_combine($file, $save_file = '', $delete_file = false)
    {
        $blockInfo = [];
        for ($i = 0;; $i++) {
            if (file_exists($file . '.' . $i . '.myArchive') && filesize($file . '.' . $i . '.myArchive') > 0) {
                $blockInfo[] = $file . '.' . $i . '.myArchive';
            } else {
                break;
            }
        }
        if ($save_file) {
            $fp = fopen($save_file, 'wb');
        } else {
            $fp = fopen($file, 'wb');
        }
        foreach ($blockInfo as $item) {
            $handle = fopen($item, 'rb');
            fwrite($fp, fread($handle, filesize($item)));
            fclose($handle);
            unset($handle);
            if ($delete_file) {
                unlink($item);
            }
        }
        fclose($fp);
        unset($fp);
    }
}
