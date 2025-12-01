<?php 

namespace App\Enums;

enum BookGenre: string 
{
    case FICTION = 'Fiction';
    case NON_FICTION = 'Non-Fiction';
    case FANTASY = 'Fantasy';
    case SCIENCE_FICTION = 'Science Fiction';
    case MYSTERY = 'Mystery';
    case ROMANCE = 'Romance';
    case BIOGRAPHY = 'Biography';
    case HISTORY = 'History';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}