<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ModalButton
{
    public string $target;
    public ?string $href = null;
}
