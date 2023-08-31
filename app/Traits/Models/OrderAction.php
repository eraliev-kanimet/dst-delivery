<?php

namespace App\Traits\Models;

use App\Enums\OrderStatus;

trait OrderAction
{
    public function actionTotalCostRecalculation(array $data = []): void
    {
        $total = 0;

        foreach ($this->orderItems as $item) {
            $total += $item->quantity * $item->price;
        }

        $data['total'] = $total;

        $this->update($data);
    }

    public function actionCancel(): void
    {
        $this->update([
            'status' => OrderStatus::canceled->value
        ]);
    }

    public function actionConfirmed(): void
    {
        $this->update([
            'status' => OrderStatus::confirmed->value
        ]);
    }
}
