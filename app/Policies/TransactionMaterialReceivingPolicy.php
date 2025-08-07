<?php

namespace App\Policies;

use App\Models\TransactionMaterialReceiving;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionMaterialReceivingPolicy
{
    public function isEvaluator(User $user, TransactionMaterialReceiving $transaction): Response
    {
        if ($transaction->evaluated_by_id && $transaction->evaluated_by_id !== $user->id) {
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
    public function view(User $user, TransactionMaterialReceiving $transactionMaterialReceiving): bool
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
    public function update(User $user, TransactionMaterialReceiving $transactionMaterialReceiving): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransactionMaterialReceiving $transactionMaterialReceiving): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TransactionMaterialReceiving $transactionMaterialReceiving): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TransactionMaterialReceiving $transactionMaterialReceiving): bool
    {
        //
    }
}
