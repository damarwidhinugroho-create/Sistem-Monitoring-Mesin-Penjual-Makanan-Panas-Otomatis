<?php

namespace App\Auth;

use App\Models\Operator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class DemoOperatorProvider implements UserProvider
{
    private static ?string $demoPasswordHash = null;

    public function retrieveById($identifier): ?Authenticatable
    {
        return $identifier === 'operator-demo' ? $this->demoOperator() : null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $operator = $this->retrieveById($identifier);

        return $operator !== null && hash_equals((string) $operator->getRememberToken(), (string) $token)
            ? $operator
            : null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $user->setRememberToken($token);
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return mb_strtolower(trim((string) ($credentials['username'] ?? ''))) === 'budi'
            ? $this->demoOperator()
            : null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return isset($credentials['password'])
            && Hash::check((string) $credentials['password'], $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // The demo password belongs to the provider and is not persisted.
    }

    private function demoOperator(): Operator
    {
        self::$demoPasswordHash ??= Hash::make('budi123');

        $operator = new Operator();
        $operator->forceFill([
            'id' => 'operator-demo',
            'name' => 'Budi',
            'username' => 'Budi',
            'password' => self::$demoPasswordHash,
        ]);

        return $operator;
    }
}
