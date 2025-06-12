<?php

namespace App\Rules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use App\Models\Feed;
use App\Models\Order;

class ValidRssFeed implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Проверка формата URL
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $fail('Неверный URL.');
            return;
        }

        // Проверка домена (дополнительная валидация)
        $host = parse_url($value, PHP_URL_HOST);
        if (!$host || !$this->isValidDomain($host)) {
            $fail('Неверный домен в URL.');
            return;
        }

        // Проверка уникальности в таблицах Feed и Order
        if (Feed::where('url', $value)->exists()) {
            $fail('Этот URL уже используется.');
            return;
        }

        if (Order::where('url', $value)->exists()) {
            $fail('Этот URL уже находится в заявках.');
            return;
        }

        // Проверка доступности и содержимого
        try {
            $client = new Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'verify' => false, /// для разработки. в проде надо true
            ]);

            $response = $client->get($value, [
                'headers' => [
                    'User-Agent' => 'RSS-Validator/1.0',
                ]
            ]);

            if (!$this->isValidRssContent($response->getBody()->getContents())) {
                $fail('Предоставленный адрес не содержит валидный RSS канал.');
            }

        } catch (ConnectException $e) {
            $fail('Не удалось подключиться к серверу. Проверьте URL и доступность сайта.');
        } catch (RequestException $e) {
            $fail('Сервер вернул ошибку: ' . $e->getResponse()?->getStatusCode() ?? 'нет ответа');
        } catch (Exception $e) {
            $fail('Ошибка обработки RSS канала.');
        }
    }

    protected function isValidDomain(string $host): bool
    {
        // Простая проверка формата домена (без реального DNS-запроса)
        return (bool) preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i', $host);
    }

    protected function isValidRssContent(string $content): bool
    {
        // Быстрая проверка по сигнатурам
        if (!str_contains($content, '<rss') && !str_contains($content, '<feed')) {
            return false;
        }

        // Строгая проверка XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        return $xml !== false && (isset($xml->channel) || isset($xml->entry));
    }
}
