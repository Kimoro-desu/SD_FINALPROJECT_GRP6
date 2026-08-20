<?php
namespace Utils;

/**
 * Delete an asset and all related borrow records so FK constraints (transactions → assets) succeed.
 * Order: penalties → transaction_ratings → transactions (return_photos CASCADE) → asset.
 *
 * @param \PDO $db
 * @param int $assetId
 * @param int|null $requiredLenderId If set, asset must belong to this user; null = no ownership check (admin).
 * @throws \RuntimeException With user-facing message and optional HTTP-ish code in previous exception
 */
function deleteAssetWithDependents(\PDO $db, int $assetId, ?int $requiredLenderId): void
{
    if ($assetId <= 0) {
        throw new \InvalidArgumentException('Invalid asset id.');
    }

    $db->beginTransaction();
    try {
        $ownStmt = $db->prepare('SELECT Asset_ID, Lender_ID FROM assets WHERE Asset_ID = :id FOR UPDATE');
        $ownStmt->execute([':id' => $assetId]);
        $row = $ownStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            $db->rollBack();
            throw new \RuntimeException('Asset not found or already deleted.', 404);
        }
        if ($requiredLenderId !== null && (int)($row['Lender_ID'] ?? 0) !== $requiredLenderId) {
            $db->rollBack();
            throw new \RuntimeException('Forbidden. You can only delete your own assets.', 403);
        }

        $txnStmt = $db->prepare('SELECT transaction_id FROM transactions WHERE asset_id = :aid');
        $txnStmt->execute([':aid' => $assetId]);
        $txnIds = array_map('intval', $txnStmt->fetchAll(\PDO::FETCH_COLUMN, 0));

        if ($txnIds !== []) {
            $inList = implode(',', array_fill(0, count($txnIds), '?'));

            $penTables = $db->query("SHOW TABLES LIKE 'penalties'")->fetch(\PDO::FETCH_NUM);
            if ($penTables) {
                $db->prepare("DELETE FROM penalties WHERE transaction_id IN ($inList)")->execute($txnIds);
            }

            $rateTables = $db->query("SHOW TABLES LIKE 'transaction_ratings'")->fetch(\PDO::FETCH_NUM);
            if ($rateTables) {
                $db->prepare("DELETE FROM transaction_ratings WHERE transaction_id IN ($inList)")->execute($txnIds);
            }

            $db->prepare('DELETE FROM transactions WHERE asset_id = :aid')->execute([':aid' => $assetId]);
        }

        $delAsset = $db->prepare('DELETE FROM assets WHERE Asset_ID = :id');
        $delAsset->execute([':id' => $assetId]);
        if ($delAsset->rowCount() <= 0) {
            $db->rollBack();
            throw new \RuntimeException('Asset not found or already deleted.', 404);
        }

        $db->commit();
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
