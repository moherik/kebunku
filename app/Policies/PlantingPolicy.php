<?php

namespace App\Policies;

use App\Models\Planting;
use App\Models\User;

class PlantingPolicy
{
    /**
     * Determine whether the user can view the planting.
     * Farmers can view their own; buyers can view any non-harvested.
     */
    public function view(User $user, Planting $planting): bool
    {
        if ($user->isFarmer()) {
            return $planting->user_id === $user->id;
        }

        // Buyers can view plantings that are available
        return in_array($planting->status, ['growing', 'pre-order', 'ready']);
    }

    /**
     * Determine whether the user can create plantings.
     * Only farmers can create.
     */
    public function create(User $user): bool
    {
        return $user->isFarmer();
    }

    /**
     * Determine whether the user can update the planting.
     * Only the farmer who owns it can update.
     */
    public function update(User $user, Planting $planting): bool
    {
        return $user->isFarmer() && $planting->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the planting.
     * Only the farmer who owns it can delete.
     */
    public function delete(User $user, Planting $planting): bool
    {
        return $user->isFarmer() && $planting->user_id === $user->id;
    }
}
