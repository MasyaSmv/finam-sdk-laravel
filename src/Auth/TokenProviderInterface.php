<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Auth;

/**
 * Контракт провайдера токенов Finam.
 *
 * Благодаря интерфейсу можно подменять способ получения токена
 * (статический токен, OAuth, иные кастомные реализации) без изменения
 * клиентского кода, что соответствует принципу DIP.
 */
interface TokenProviderInterface
{
    /**
     * Возвращает актуальный access token для авторизации в Finam API.
     *
     * @return string Невалидное или пустое значение считается ошибкой
     *                конкретной реализации провайдера.
     */
    public function getToken(): string;
}
