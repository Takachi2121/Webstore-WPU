<?php
declare(strict_types=1);

namespace App\States\SalesOrder;

use App\States\SalesOrder\Transition\PendingtoCancel;
use App\States\SalesOrder\Transition\PendingtoProgress;
use App\States\SalesOrder\Transition\ProgresstoCompleted;
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
            ->AllowTransition(Pending::class, Progress::class, PendingtoProgress::class)
            ->allowTransition(Pending::class, Cancel::class, PendingtoCancel::class)
            ->AllowTransition(Progress::class, Completed::class, ProgresstoCompleted::class);
    }
}
?>
