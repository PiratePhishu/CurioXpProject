<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class EncryptedEloquentUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * The default implementation matches credentials with a database-level
     * WHERE clause, which can't work against an encrypted column: the same
     * plaintext never encrypts to the same ciphertext twice. Instead, this
     * decrypts and compares in PHP. That's only viable because the table
     * this is used for (students) is small; it is not a general-purpose
     * replacement for querying encrypted columns at scale.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $credentials = array_filter(
            $credentials,
            fn (string $key) => ! str_contains($key, 'password'),
            ARRAY_FILTER_USE_KEY,
        );

        if (empty($credentials)) {
            return null;
        }

        return $this->newModelQuery()->get()->first(function (Authenticatable $model) use ($credentials) {
            foreach ($credentials as $key => $value) {
                if ($model->{$key} !== $value) {
                    return false;
                }
            }

            return true;
        });
    }
}
