<?php

declare(strict_types=1);

class FlightGenerator
{
    public function __construct(private PDO $pdo)
    {
    }

    public function ensureScheduleIsFresh(): void
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $lastGenerated = $this->pdo->query('SELECT MAX(generated_for_date) FROM flight_generations')->fetchColumn();
        if ($lastGenerated === $today) {
            return;
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM flights WHERE date(departure_time) >= :today')->execute(['today' => $today]);
            $this->generateFlightsForRange($today, 7);
            $this->pdo->prepare('INSERT OR REPLACE INTO flight_generations (generated_for_date, created_at) VALUES (:date, :created_at)')
                ->execute([
                    'date' => $today,
                    'created_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
                ]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    private function generateFlightsForRange(string $startDate, int $days): void
    {
        $airports = airportCatalog();
        $airlines = airlinesCatalog();
        $start = new DateTimeImmutable($startDate);

        for ($i = 0; $i < $days; $i++) {
            $date = $start->modify("+{$i} days");
            foreach ($airports as $origin) {
                foreach ($airports as $destination) {
                    if ($origin['code'] === $destination['code']) {
                        continue;
                    }

                    foreach ($airlines as $airline) {
                        $flight = $this->buildFlight($origin, $destination, $airline, $date);
                        $this->insertFlight($flight);
                    }
                }
            }
        }
    }

    private function buildFlight(array $origin, array $destination, array $airline, DateTimeImmutable $date): array
    {
        $departureTime = $this->randomDepartureTime($date);
        $arrivalTime = $departureTime->modify('+' . random_int(90, 180) . ' minutes');
        $basePrice = random_int(600000, 1400000);
        $price = (int) min(4000000, max(600000, $basePrice * $airline['multiplier']));

        return [
            'origin_code' => $origin['code'],
            'origin_city' => $origin['city'],
            'destination_code' => $destination['code'],
            'destination_city' => $destination['city'],
            'airline' => $airline['name'],
            'flight_code' => $this->composeFlightCode($airline['code']),
            'departure_time' => $departureTime->format(DateTimeInterface::ATOM),
            'arrival_time' => $arrivalTime->format(DateTimeInterface::ATOM),
            'price' => $price,
            'created_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];
    }

    private function insertFlight(array $flight): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO flights (origin_code, origin_city, destination_code, destination_city, airline, flight_code, departure_time, arrival_time, price, created_at)
            VALUES (:origin_code, :origin_city, :destination_code, :destination_city, :airline, :flight_code, :departure_time, :arrival_time, :price, :created_at)'
        );
        $stmt->execute($flight);
    }

    private function composeFlightCode(string $airlineCode): string
    {
        return sprintf('%s-%d', $airlineCode, random_int(100, 9999));
    }

    private function randomDepartureTime(DateTimeImmutable $date): DateTimeImmutable
    {
        $hour = random_int(5, 22);
        $minute = [0, 15, 30, 45][array_rand([0, 1, 2, 3])];
        return $date->setTime($hour, $minute);
    }
}
