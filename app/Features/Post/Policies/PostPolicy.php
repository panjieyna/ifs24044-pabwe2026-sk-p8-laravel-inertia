<?php

namespace App\Features\Post\Policies;

use App\Features\Post\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Admin dapat melihat artikel siapa pun (read-only); penulis hanya artikelnya sendiri.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->isAdmin() || $this->owns($user, $post);
    }

    /**
     * Hanya pemilik artikel yang dapat mengubahnya.
     */
    public function update(User $user, Post $post): bool
    {
        return $this->owns($user, $post);
    }

    /**
     * Hanya pemilik artikel yang dapat menghapusnya.
     */
    public function delete(User $user, Post $post): bool
    {
        return $this->owns($user, $post);
    }

    protected function owns(User $user, Post $post): bool
    {
        return (int) $user->id === (int) $post->user_id;
    }
}
