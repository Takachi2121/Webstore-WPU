<?php
declare(strict_types=1);

namespace App\States\SalesOrder;

use Spatie\ModelStates\Attributes\AllowTransition;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class SalesOrderState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->AllowTransition(Pending::class, Progress::class)
            ->allowTransition(Pending::class, Cancel::class)
            ->AllowTransition(Progress::class, Completed::class);
    }
}
?>
