<?php

namespace App\Twig;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface //ServiceSubscriberInterface,
{
    public function __construct()
    {
    }

//    public function getFunctions(): array
//    {
//        return [
//
//        ];
//    }

    public function getGlobals(): array
    {
        return [
            'cssVersion' => $this->getCssVersion(),
        ];
    }

//    public function getFilters()
//    {
//        return [
//        ];
//    }

    public function getCssVersion()
    {
        $path = '../public/style/';
        $filename = 'version.txt';

        $file = new SplFileInfo($path.$filename, '', '');
        if (false == $file->isFile()) return null;

        return $file->getContents();
    }
}
