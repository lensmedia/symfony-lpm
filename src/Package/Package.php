<?php

namespace Lens\Bundle\LpmBundle\Package;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

abstract class Package
{
    public Uuid $id;
    public string $file;
    public int $filesize = 0;
    public DateTimeImmutable $modifiedAt;
}
