<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private const CACHE_TTL = 900; // 15 minutes in seconds
    private const API_URL = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private string $apiKey
    ) {
    }

    /**
     * Get weather data for a city
     *
     * @return array{success: bool, data?: array, error?: string, code?: int}
     */
    public function getWeather(string $city): array
    {
        if (empty($city)) {
            return [
                'success' => false,
                'error' => 'City is required',
                'code' => Response::HTTP_BAD_REQUEST
            ];
        }

        $cacheKey = 'weather_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($city));

        try {
            $weatherData = $this->cache->get($cacheKey, function (ItemInterface $item) use ($city) {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->fetchWeatherFromApi($city);
            });

            if (isset($weatherData['error'])) {
                $this->cache->delete($cacheKey);
                return [
                    'success' => false,
                    'error' => $weatherData['error'],
                    'code' => $weatherData['code'] ?? Response::HTTP_INTERNAL_SERVER_ERROR
                ];
            }

            return [
                'success' => true,
                'data' => $weatherData
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error fetching weather data: ' . $e->getMessage(),
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * Fetch weather data from OpenWeatherMap API
     */
    private function fetchWeatherFromApi(string $city): array
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => [
                'q' => $city,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang' => 'en'
            ]
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 404) {
            return [
                'error' => 'City not found: ' . $city,
                'code' => Response::HTTP_NOT_FOUND
            ];
        }

        if ($statusCode !== 200) {
            return [
                'error' => 'Weather API error (code: ' . $statusCode . ')',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }

        $data = $response->toArray();

        return [
            'city' => $data['name'] ?? $city,
            'country' => $data['sys']['country'] ?? null,
            'temperature' => round($data['main']['temp'] ?? 0, 1),
            'feels_like' => round($data['main']['feels_like'] ?? 0, 1),
            'humidity' => $data['main']['humidity'] ?? null,
            'description' => $data['weather'][0]['description'] ?? null,
            'icon' => $data['weather'][0]['icon'] ?? null,
            'wind_speed' => round(($data['wind']['speed'] ?? 0) * 3.6, 1),
            'cached_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ];
    }
}
