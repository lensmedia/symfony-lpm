<?php

namespace Lens\Bundle\LpmBundle\Package;

class Document extends Package
{
    public const TYPE = 'document';

    public string $name;
    public string $title;
    public string $theme;
}
