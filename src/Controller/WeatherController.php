<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class WeatherController extends AbstractController
{
    public function __construct(
        private WeatherService $weatherService
    ) {
    }

    #[Route('/weather', name: 'api_weather_user', methods: ['GET'])]
    public function getUserWeather(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $city = $user->getCity();

        if (!$city) {
            return $this->json(
                ['error' => 'No city configured for your account'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->getWeatherForCity($city);
    }

    #[Route('/weather/{city}', name: 'api_weather_city', methods: ['GET'])]
    public function getCityWeather(string $city): JsonResponse
    {
        if (empty($city)) {
            return $this->json(
                ['error' => 'City is required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->getWeatherForCity($city);
    }

    private function getWeatherForCity(string $city): JsonResponse
    {
        $result = $this->weatherService->getWeather($city);

        if (!$result['success']) {
            return $this->json(
                ['error' => $result['error']],
                $result['code'] ?? Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json([
            'weather' => $result['data']
        ]);
    }
}
