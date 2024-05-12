<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Contribution;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContributionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_contribution');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Contribution $contribution): bool
    {
        return $user->can('view_contribution');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_contribution');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Contribution $contribution): bool
    {
        $isKartar = collect($user->roles->toArray())->contains('name', 'karang_taruna');
        $containUser = collect($contribution->users->toArray())->contains('id', $user->id);

        if (!$contribution->is_calculation_complete) {
            if ($isKartar) {
                return $user->can('update_contribution') && $containUser;
            } else {
                return $user->can('update_contribution');
            }
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Contribution $contribution): bool
    {
        return $user->can('delete_contribution');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_contribution');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Contribution $contribution): bool
    {
        return $user->can('force_delete_contribution');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_contribution');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Contribution $contribution): bool
    {
        return $user->can('restore_contribution');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_contribution');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Contribution $contribution): bool
    {
        return $user->can('replicate_contribution');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_contribution');
    }
}
