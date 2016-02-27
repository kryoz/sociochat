<?php

namespace Front\Loader;

use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Loader\FileLoader;
use Symfony\Component\Yaml\Exception\ParseException;

class IniFileLoader extends FileLoader
{
    protected function loadResource($resource)
    {
        try {
            $messages = parse_ini_file($resource, true, INI_SCANNER_TYPED);
        } catch (ParseException $e) {
            throw new InvalidResourceException(sprintf('Error parsing INI, invalid file "%s"', $resource), 0, $e);
        }

        return $messages;
    }
}