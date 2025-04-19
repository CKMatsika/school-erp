<?php

namespace App\Policies;

use App\Models\Accounting\SmsGateway;
use App\Models\User; // Or your specific User model
use Illuminate\Auth\Access\Response;

class SmsGatewayPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allow any logged-in user to view the list for now
        return true;

        // Replace with your actual logic later, e.g.:
        // return $user->hasPermissionTo('view sms gateways');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SmsGateway $smsGateway): bool
    {
         // Allow viewing for now, add logic later
         return true; // Example: return $user->school_id === $smsGateway->school_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Allow creating for now, add logic later
         return true; // Example: return $user->hasPermissionTo('create sms gateways');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SmsGateway $smsGateway): bool
    {
         // Allow updating for now, add logic later
         return true; // Example: return $user->school_id === $smsGateway->school_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SmsGateway $smsGateway): bool
    {
         // Allow deleting for now, add logic later
         return true; // Example: return $user->school_id === $smsGateway->school_id;
    }

    /**
     * Determine whether the user can restore the model. (If using SoftDeletes)
     */
    // public function restore(User $user, SmsGateway $smsGateway): bool
    // {
    //     return true; // Add logic if needed
    // }

    /**
     * Determine whether the user can permanently delete the model. (If using SoftDeletes)
     */
    // public function forceDelete(User $user, SmsGateway $smsGateway): bool
    // {
    //     return true; // Add logic if needed
    // }
}