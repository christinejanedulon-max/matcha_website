<?php

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFilter;

/**
 * Maps a product image path (e.g. "images/latte.png") to its normalized,
 * uniformly-framed product shot in "images/drinks/latte.jpg".
 * Falls back to the original path when no normalized version exists.
 */
final class DrinkImageExtension
{
    private const FALLBACK = 'images/latte.png';

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
    ) {
    }

    #[AsTwigFilter('drink_image')]
    public function drinkImage(?string $imageUrl): string
    {
        $imageUrl = $imageUrl ?: self::FALLBACK;
        $normalized = 'images/drinks/'.pathinfo($imageUrl, \PATHINFO_FILENAME).'.jpg';

        return is_file($this->publicDir.'/'.$normalized) ? $normalized : $imageUrl;
    }
}

