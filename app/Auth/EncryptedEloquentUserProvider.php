<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

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
                $modelValue = $model->{$key};

                // Emails are entered by hand and imported from a spreadsheet, so
                // compare them case-insensitively rather than requiring an exact
                // byte-for-byte match.
                $matches = is_string($modelValue) && is_string($value)
                    ? Str::lower($modelValue) === Str::lower($value)
                    : $modelValue === $value;

                if (! $matches) {
                    return false;
                }
            }

            return true;
        });
    }
}
