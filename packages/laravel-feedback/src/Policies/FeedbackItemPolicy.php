<?php

namespace BeegoodIT\LaravelFeedback\Policies;

use BeegoodIT\LaravelFeedback\Models\FeedbackItem;
use Illuminate\Foundation\Auth\User;

class FeedbackItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view feedback items
        return $user->is_admin ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FeedbackItem $feedbackItem): bool
    {
        return $user->is_admin ?? false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create feedback
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FeedbackItem $feedbackItem): bool
    {
        return $user->is_admin ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FeedbackItem $feedbackItem): bool
    {
        return $user->is_admin ?? false;
    }
}
