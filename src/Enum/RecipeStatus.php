<?php

declare(strict_types=1);

namespace App\Enum;

enum RecipeStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending review',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
        };
    }
}
