-- HariBorrow Asset Lifecycle migration
-- Run this once against hariborrow_db.

-- 1) Add lifecycle status column to assets
ALTER TABLE assets
  ADD COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending';

-- 2) Ensure lender index for ownership filtering
ALTER TABLE assets
  ADD INDEX idx_assets_lender (Lender_ID);

-- 3) Ensure status + availability index for catalog reads
ALTER TABLE assets
  ADD INDEX idx_assets_status_availability (status, availability);

-- 4) Normalize availability values for lifecycle workflow
-- Existing values like Borrowed/Pending/Maintenance are treated as unavailable.
UPDATE assets
SET availability = CASE
  WHEN LOWER(COALESCE(availability, '')) = 'available' THEN 'available'
  ELSE 'unavailable'
END;

-- 5) Optional: mark all existing assets as approved so they appear in catalog
-- Comment this out if you want all existing assets to start in pending.
UPDATE assets SET status = 'approved' WHERE status IS NULL OR status = 'pending';

