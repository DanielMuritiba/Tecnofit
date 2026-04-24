<?php

namespace Src\Ranking;

class RankingRepository
{
    public function __construct(private \PDO $pdo) {}

    public function getByMovement(string $movementParam): ?array
    {

        if (ctype_digit($movementParam)) {
            $movementId = (int) $movementParam;
            $where = 'm.id = :movementId';
            $params[':movementId'] = $movementId;
        }else{
            $movementName = trim($movementParam);
            $where = 'm.name LIKE :movementName';
            $params[':movementName'] = '%' . $movementName . '%';
        }

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
                WHERE $where
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
        foreach ($movementsRanking as $movement) {
            $movementName = $movement['movementName'];

            if (!isset($result[$movementName])) {
                $result[$movementName] = [];
            }

            $result[$movementName][] = [
                'userName' => $movement['userName'],
                'personalRecord' => (float) $movement['personalRecord'],
                'rankPosition' => (int) $movement['rankPosition'],
                'recordDate' => $movement['recordDate'],
            ];
        }

        return $result;
    }
}
