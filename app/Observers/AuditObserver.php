<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    // Kolom yang tidak pernah dicatat (rahasia atau tidak informatif)
    private const IGNORED = ['password', 'remember_token', 'updated_at', 'created_at'];

    public function created(Model $model): void
    {
        $this->log('created', $model, null, $this->clean($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $this->clean($model->getChanges());

        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getRawOriginal($key);
        }

        $this->log('updated', $model, $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, $this->clean($model->getAttributes()), null);
    }

    private function clean(array $values): array
    {
        return array_diff_key($values, array_flip(self::IGNORED));
    }

    private function log(string $action, Model $model, ?array $old, ?array $new): void
    {
        $userId = Auth::id();

        // Perubahan tanpa user yang login (seeder, artisan, pendaftaran mandiri) tidak dicatat
        if ($userId === null) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => class_basename($model),
                'entity_id' => $model->getKey(),
                'description' => $this->describe($model),
                'old_values' => $old,
                'new_values' => $new,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Kegagalan mencatat audit tidak boleh menggagalkan aksi utama
            report($e);
        }
    }

    private function describe(Model $model): string
    {
        if (method_exists($model, 'shipment') && $model->shipment) {
            return 'Shipment '.$model->shipment->shipment_number;
        }

        foreach (['shipment_number', 'code', 'email', 'name', 'key'] as $field) {
            $value = $model->getAttribute($field);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '#'.$model->getKey();
    }
}