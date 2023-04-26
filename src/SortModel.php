<?php

namespace Thoss\GapSort;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Summary of SortModel.
 */
class SortModel
{
    protected $model = null;
    protected $orderColumn = null;
    protected $gap = null;
    protected $main = null;
    protected $next = null;
    protected $previous = null;
    protected $initTable = false;

    /**
     * Summary of handle.
     */
    public function handle(Request $request = null): ?Model
    {
        if ($this->initTable) {
            // Die Tabelle wird nur initial neu sortiert
            $this->initSortTable();

            return null;
        }

        $this->main = $this->main ?? $request->get('main');
        $this->next = $this->next ?? $request->get('next');
        $this->previous = $this->previous ?? $request->get('previous');

        $mainItem = $this->model->findOrFail($this->main);
        $newOrder = $this->getNewOrder();

        if (null === $newOrder) {
            // Die Tabelle muss neu sortiert werden
            $this->initSortTable();

            // order nochmal neu berechnen
            $newOrder = $this->getNewOrder();
        }

        return $this->updateOrder($mainItem, $newOrder);
    }

    /**
     * Aktualisiert den Wert der order Column ohne die save Methode zu verwenden.
     */
    protected function updateOrder(object $model, $value): ?Model
    {
        $obj = $this->model->find($model->id);

        if ($obj) {
            $obj->{$this->orderColumn} = $value;
            $obj->saveQuietly();
        }

        return $obj;
    }

    /**
     * Die Tabelle wird mit dem Gap neu aufgebaut.
     */
    protected function initSortTable(): void
    {
        $this->model
        ->select($this->model->getKeyName())
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
     */
    protected function getNewOrder(): ?int
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

    /**
     * Summary of __construct.
     *
     * @param mixed $modelString
     * @param mixed $main
     * @param mixed $next
     * @param mixed $previous
     * @param mixed $initTable
     */
    public function __construct(string $modelString, $main = null, $next = null, $previous = null, bool $initTable = false)
    {
        $this->model = new $modelString();
        $this->gap = config('gap-sort.order_gap');
        $this->orderColumn = config('gap-sort.order_column');
        $this->main = $main;
        $this->next = $next;
        $this->previous = $previous;
        $this->initTable = $initTable;
    }
}
