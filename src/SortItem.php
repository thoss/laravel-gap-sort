<?php

namespace Thoss\GapSort;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SortItem
{
    protected $model = null;
    protected $orderColumn = null;
    protected $gap = null;
    protected $main = null;
    protected $next = null;
    protected $previous = null;
    protected $initTable = false;
    protected $table = null;

    public function handle(Request $request = null)
    {
        if ($this->initTable) {
            // Die Tabelle wird nur initial neu sortiert
            $this->initSortTable();

            return;
        }

        $this->main = $this->main ?? $request->get('main');
        $this->next = $this->next ?? $request->get('next');
        $this->previous = $this->previous ?? $request->get('previous');

        $mainItem = $this->model->findOrFail($this->main);
        $newOrder = $this->getNewOrder($request);

        if (null === $newOrder) {
            // Die Tabelle muss neu sortiert werden
            $this->initSortTable();

            // order nochmal neu berechnen
            $newOrder = $this->getNewOrder($request);
        }

        $this->updateOrder($mainItem, $newOrder);
    }

    /**
     * Aktualisiert den Wert der order Column ohne die save Methode zu verwenden.
     */
    protected function updateOrder($model, $value)
    {
        DB::table($this->table)
        ->where('id', $model->id)
        ->update([
            $this->orderColumn => $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Die Tabelle wird mit dem Gap neu aufgebaut.
     *
     * @return void
     */
    protected function initSortTable()
    {
        DB::table($this->table)
        ->select('id')
        ->orderBy($this->orderColumn)
        ->get()
        ->each(function ($item, $index) {
            $newOrder = ($index + 1) * $this->gap;

            $this->updateOrder($item, $newOrder);
        });
    }

    /**
     * Berechnet die neue order für das main Item (Mitte zwischen prev und next)
     * null = Es gibt kein Gap mehr, die Tabelle muss neu initial sortiert werden.
     *
     * @param [type] $request
     *
     * @return int|null
     */
    protected function getNewOrder($request)
    {
        $previous = $this->previous;

        if (null === $previous) {
            // Es gibt kein prev, das neue Item soll nach ganz vorne
            $prevOrder = 0;
        } else {
            $prevItem = $this->model->findOrFail($this->previous);
            $prevOrder = $prevItem->{$this->orderColumn};
        }

        $next = $this->next;

        if (null === $next) {
            // Es gibt kein next, das neue Item soll nach ganz hinten
            $next = $this->model->orderBy($this->orderColumn, 'DESC')->first();
            $nextOrder = null !== $next ? $next->{$this->orderColumn} : 0; // Falls es noch kein Item geben sollte in der Tabelle
            $nextOrder += $this->gap;
        } else {
            $nextItem = $this->model->findOrFail($this->next);
            $nextOrder = $nextItem->{$this->orderColumn};
        }

        $diff = $nextOrder - $prevOrder;

        if (0 === $diff || 1 === $diff) {
            // Bei einem diff von 1 funktioniert die nachfolgende Rechnung nicht, da wir runden
            // Beispiel next:2, prev:1 => da würde 1.5 rauskommen
            // Bei 0 gibt es keinen Platz mehr
            return null;
        }

        $newOrder = $prevOrder + (($diff) / 2);
        $hasGap = $newOrder >= 1;

        return $hasGap ? (int) $newOrder : null;
    }

    public function __construct($modelString, $main = null, $next = null, $previous = null, $initTable = false)
    {
        $this->model = new $modelString();
        $this->table = $this->model->getTable();
        $this->gap = config('laravel-gap-sort.order_gap');
        $this->orderColumn = config('laravel-gap-sort.order_column');
        $this->main = $main;
        $this->next = $next;
        $this->previous = $previous;
        $this->initTable = $initTable;
    }
}
