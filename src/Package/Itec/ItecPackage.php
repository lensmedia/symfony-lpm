<?php

namespace Lens\Bundle\LpmBundle\Package\Itec;

use Lens\Bundle\LpmBundle\Package\Package;

abstract class ItecPackage extends Package
{
    public int $originalId;
    public string $title;
    public string $locale;
    public string $course;
    public Features $features;
    public array $topics = [];
    public array $levels = [];
    public array $parts = [];
}
