<?php

declare(strict_types=1);

class TrainGenerator
{
    public function __construct(private PDO $pdo)
    {
    }

    public function ensureScheduleIsFresh(): void
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $lastGenerated = $this->pdo->query('SELECT MAX(generated_for_date) FROM train_generations')->fetchColumn();
        if ($lastGenerated === $today) {
            return;
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM trains WHERE date(departure_time) >= :today')->execute(['today' => $today]);
            $this->generateTrainsForRange($today, 7);
            $this->pdo->prepare('INSERT OR REPLACE INTO train_generations (generated_for_date, created_at) VALUES (:date, :created_at)')
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

    private function generateTrainsForRange(string $startDate, int $days): void
    {
        $stations = stationCatalog();
        $operators = trainOperatorsCatalog();
        $start = new DateTimeImmutable($startDate);

        for ($i = 0; $i < $days; $i++) {
            $date = $start->modify("+{$i} days");
            foreach ($stations as $origin) {
                foreach ($stations as $destination) {
                    if ($origin['code'] === $destination['code']) {
                        continue;
                    }

                    foreach ($operators as $operator) {
                        $train = $this->buildTrain($origin, $destination, $operator, $date);
                        $this->insertTrain($train);
                    }
                }
            }
        }
    }

    private function buildTrain(array $origin, array $destination, array $operator, DateTimeImmutable $date): array
    {
        $departureTime = $this->randomDepartureTime($date);
        $arrivalTime = $departureTime->modify('+' . random_int(120, 720) . ' minutes');
        $basePrice = random_int(80000, 350000);
        $price = (int) min(800000, max(60000, $basePrice * $operator['multiplier']));

        return [
            'origin_code' => $origin['code'],
            'origin_city' => $origin['city'],
            'destination_code' => $destination['code'],
            'destination_city' => $destination['city'],
            'operator' => $operator['name'],
            'train_code' => $this->composeTrainCode($operator['code']),
            'departure_time' => $departureTime->format(DateTimeInterface::ATOM),
            'arrival_time' => $arrivalTime->format(DateTimeInterface::ATOM),
            'price' => $price,
            'created_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];
    }

    private function insertTrain(array $train): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO trains (origin_code, origin_city, destination_code, destination_city, operator, train_code, departure_time, arrival_time, price, created_at)
            VALUES (:origin_code, :origin_city, :destination_code, :destination_city, :operator, :train_code, :departure_time, :arrival_time, :price, :created_at)'
        );
        $stmt->execute($train);
    }

    private function composeTrainCode(string $operatorCode): string
    {
        return sprintf('%s-%d', $operatorCode, random_int(100, 9999));
    }

    private function randomDepartureTime(DateTimeImmutable $date): DateTimeImmutable
    {
        $hour = random_int(4, 22);
        $minute = [0, 15, 30, 45][array_rand([0, 1, 2, 3])];
        return $date->setTime($hour, $minute);
    }
}
