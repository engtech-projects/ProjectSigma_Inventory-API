<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WarehouseTransaction;
use Illuminate\Auth\Access\Response;

class WarehouseTransactionPolicy
{
    public function isEvaluator(User $user, WarehouseTransaction $transaction): Response
    {
        $metadata = $transaction->metadata ?? [];

        if (isset($metadata['evaluated_by']) && $metadata['evaluated_by'] !== $user->id) {
            return Response::deny('You are not authorized to evaluate this transaction. Another evaluator has already started processing this transaction.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WarehouseTransaction $warehouseTransaction): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WarehouseTransaction $warehouseTransaction): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WarehouseTransaction $warehouseTransaction): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WarehouseTransaction $warehouseTransaction): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WarehouseTransaction $warehouseTransaction): bool
    {
        //
    }
}
