<?php

namespace Src\Ranking;

class RankingRepository
{
    public function __construct(private \PDO $pdo) {}

    /**
     * Returns the ranking for a movement searched by ID (numeric) or partial name (LIKE).
     * Uses ROW_NUMBER to isolate each user's personal record and DENSE_RANK for tie handling.
     */
    public function getByMovement(string $movementParam): ?array
    {
        $params = [
            ':movementId'   => ctype_digit($movementParam) ? (int) $movementParam : null,
            ':movementName' => ctype_digit($movementParam) ? null : '%' . $movementParam . '%',
        ];

        $sql = "
            SELECT
                m.name AS movementName,
                u.name AS userName,
                t.value AS personalRecord,
                DENSE_RANK() OVER (
                    PARTITION BY t.movement_id
                    ORDER BY t.value DESC
                ) AS rankPosition,
                t.date AS recordDate
            FROM (
                SELECT
                    pr.user_id,
                    pr.movement_id,
                    pr.value,
                    pr.date,
                    ROW_NUMBER() OVER (
                        PARTITION BY pr.user_id, pr.movement_id
                        ORDER BY pr.value DESC, pr.date ASC
                    ) AS rn
                FROM personal_record pr
                JOIN movement m ON m.id = pr.movement_id
                WHERE m.id = :movementId OR m.name LIKE :movementName
            ) t
            JOIN user u ON u.id = t.user_id
            JOIN movement m ON m.id = t.movement_id
            WHERE t.rn = 1
            ORDER BY rankPosition ASC
        ";

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        $movementsRanking = $statement->fetchAll();

        if (empty($movementsRanking)) {
            return null;
        }

        $result = [];
        $movementIndex = [];

        foreach ($movementsRanking as $movement) {
            $movementName = $movement['movementName'];

            if (!isset($movementIndex[$movementName])) {
                $movementIndex[$movementName] = count($result);
                $result[] = [
                    'movement' => $movementName,
                    'ranking'  => [],
                ];
            }

            $result[$movementIndex[$movementName]]['ranking'][] = [
                'userName'       => $movement['userName'],
                'personalRecord' => (float) $movement['personalRecord'],
                'rankPosition'   => (int) $movement['rankPosition'],
                'recordDate'     => $movement['recordDate'],
            ];
        }

        return $result;
    }
}
