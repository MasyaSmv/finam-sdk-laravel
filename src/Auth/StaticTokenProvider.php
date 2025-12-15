<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Auth;

use MasyaSmv\FinamSdk\Exceptions\TokenNotConfiguredException;

/**
 * Провайдер, возвращающий заранее заданный токен.
 *
 * Подходит для сценариев, где токен выдан вручную или через отдельную
 * out-of-band авторизацию и не требует динамического обновления.
 */
final class StaticTokenProvider implements TokenProviderInterface
{
    /**
     * @param string $token Предоставленный пользователем токен.
     */
    public function __construct(private string $token)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getToken(): string
    {
        if ($this->token === '') {
            // Явно сигнализируем о неправильно настроенном токене, чтобы потребитель
            // мог различить ошибку конфигурации ещё до сетевых вызовов.
            throw new TokenNotConfiguredException('Finam token is not configured.');
        }

        return $this->token;
    }
}
