<?php

declare(strict_types=1);

class VisaGenerator
{
    public function __construct(private PDO $pdo)
    {
    }

    public function ensureScheduleIsFresh(): void
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $lastGenerated = $this->pdo->query('SELECT MAX(generated_for_date) FROM flight_generations')->fetchColumn();
        if ($lastGenerated === $today && $this->hasVisaDataForDate($today)) {
            return;
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM flights WHERE date(departure_time) >= :today')->execute(['today' => $today]);
            $this->generateVisasForRange($today, 7);
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

    private function generateVisasForRange(string $startDate, int $days): void
    {
        $countries = countryCatalog();
        $start = new DateTimeImmutable($startDate);

        for ($i = 0; $i < $days; $i++) {
            $date = $start->modify("+{$i} days");
            foreach ($countries as $origin) {
                foreach ($countries as $destination) {
                    if ($origin['code'] === $destination['code']) {
                        continue;
                    }

                    $visaTypes = $destination['code'] === 'SA' ? saudiVisaTypeCatalog() : visaTypeCatalog();
                    foreach ($visaTypes as $visaType) {
                        $visa = $this->buildVisa($origin, $destination, $visaType, $date);
                        $this->insertVisa($visa);
                    }
                }
            }
        }
    }

    private function hasVisaDataForDate(string $date): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM flights WHERE date(departure_time) >= :date AND length(origin_code) = 2 AND length(destination_code) = 2'
        );
        $stmt->execute(['date' => $date]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function buildVisa(array $origin, array $destination, array $visaType, DateTimeImmutable $date): array
    {
        $submissionTime = $this->randomSubmissionTime($date);
        $processingDays = random_int($visaType['min_days'], $visaType['max_days']);
        $completionTime = $submissionTime->modify('+' . $processingDays . ' days');
        $basePrice = random_int(350000, 2000000);
        $price = (int) min(6000000, max(350000, $basePrice * $visaType['multiplier']));

        return [
            'origin_code' => $origin['code'],
            'origin_city' => $origin['name'],
            'destination_code' => $destination['code'],
            'destination_city' => $destination['name'],
            'airline' => $visaType['name'],
            'flight_code' => $this->composeVisaCode($visaType['code']),
            'departure_time' => $submissionTime->format(DateTimeInterface::ATOM),
            'arrival_time' => $completionTime->format(DateTimeInterface::ATOM),
            'price' => $price,
            'created_at' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ];
    }

    private function insertVisa(array $visa): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO flights (origin_code, origin_city, destination_code, destination_city, airline, flight_code, departure_time, arrival_time, price, created_at)
            VALUES (:origin_code, :origin_city, :destination_code, :destination_city, :airline, :flight_code, :departure_time, :arrival_time, :price, :created_at)'
        );
        $stmt->execute($visa);
    }

    private function composeVisaCode(string $typeCode): string
    {
        return sprintf('V-%s-%d', $typeCode, random_int(100, 9999));
    }

    private function randomSubmissionTime(DateTimeImmutable $date): DateTimeImmutable
    {
        $hour = random_int(8, 17);
        $minute = [0, 15, 30, 45][array_rand([0, 1, 2, 3])];
        return $date->setTime($hour, $minute);
    }
}
