<?php
namespace Volsu\Url;
class Uri{
    public static function createUrl($downloader, $id, $version, $extension)
    {
        return $downloader.'?id='.trim($id).'-'.trim($version).'.'.trim($extension);
    }
}