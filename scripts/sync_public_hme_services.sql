SET NAMES utf8mb4;

DROP TEMPORARY TABLE IF EXISTS tmp_public_services;
CREATE TEMPORARY TABLE tmp_public_services (
    ord INT NOT NULL,
    code VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (ord)
);

INSERT INTO tmp_public_services (ord, code, name) VALUES
(1, 'RPE', 'Réanimation Pédiatrique'),
(2, 'URP', 'Urgences pédiatriques'),
(3, 'CEFP', 'Consultations et Explorations Fonctionnelles Pédiatriques'),
(4, 'TOP', 'Chirurgie Pédiatrique Traumato-orthopédique'),
(5, 'UVP', 'Chirurgie Pédiatrique Urologique-Viscérale'),
(6, 'NEO', 'Néonatologie (Réanimation néonatale)'),
(7, 'PED', 'Pédiatrie'),
(8, 'UOP', 'Unité d''Oncologie Pédiatrique'),
(9, 'UTA', 'Unité Technique d''Accouchement'),
(10, 'GYN', 'Unité de gynécologie'),
(11, 'OBS', 'Unité d''obstétrique'),
(12, 'PMA', 'Unité de PMA (Procréation Médicalement Assistée)'),
(13, 'BOC M3', 'Bloc Opératoire Central - Module 3 (Chirurgie pédiatrique)'),
(14, 'BOC M4', 'Bloc Opératoire Central - Module 4 (Césarienne)'),
(15, 'BOC RVE', 'Bloc Opératoire Central - Réveil Enfant');

SET @norm_expr := "REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(%s)), ' ', ''), '-', ''), '_', ''), '/', '')";

UPDATE services s
JOIN tmp_public_services t
    ON REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(s.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
     = REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(t.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
SET s.code = t.code,
    s.name = t.name;

UPDATE services s
JOIN tmp_public_services t
    ON REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(s.name)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
     = REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(t.name)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
LEFT JOIN services sx
    ON REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(sx.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
     = REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(t.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
SET s.code = t.code,
    s.name = t.name
WHERE sx.id IS NULL;

INSERT INTO services (code, name, zone_id, floor_id, created_at, updated_at)
SELECT
    t.code,
    t.name,
    (SELECT s0.zone_id FROM services s0 WHERE s0.zone_id IS NOT NULL LIMIT 1),
    (SELECT s1.floor_id FROM services s1 WHERE s1.floor_id IS NOT NULL LIMIT 1),
    NOW(),
    NOW()
FROM tmp_public_services t
LEFT JOIN services s
    ON REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(s.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
     = REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(t.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
WHERE s.id IS NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_public_canonical;
CREATE TEMPORARY TABLE tmp_public_canonical AS
SELECT
    t.ord,
    t.code,
    t.name,
    MIN(s.id) AS service_id
FROM tmp_public_services t
JOIN services s
    ON REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(s.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
     = REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(t.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
GROUP BY t.ord, t.code, t.name;

DROP TEMPORARY TABLE IF EXISTS tmp_public_alias;
CREATE TEMPORARY TABLE tmp_public_alias AS
SELECT DISTINCT
    c.service_id AS canonical_id,
    s.id AS alias_id,
    c.name AS canonical_name,
    c.code AS canonical_code
FROM tmp_public_canonical c
JOIN services s
    ON REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(s.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
        = REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(c.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
    OR REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(s.name)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
        = REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(c.name)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci;

UPDATE equipments e
JOIN tmp_public_alias a ON e.service_id = a.alias_id
SET e.service_id = a.canonical_id,
    e.service_name = a.canonical_name;

UPDATE equipments e
JOIN tmp_public_canonical c
    ON REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(IFNULL(e.service_name, ''))), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
        LIKE CONCAT('%', REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(c.name)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci, '%')
    OR REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(IFNULL(e.unit_name, ''))), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
        LIKE CONCAT('%', REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(c.name)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci, '%')
    OR REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(IFNULL(e.service_name, ''))), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
        LIKE CONCAT('%', REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(c.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci, '%')
    OR REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(IFNULL(e.unit_name, ''))), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci
        LIKE CONCAT('%', REPLACE(REPLACE(REPLACE(REPLACE(UPPER(TRIM(c.code)), ' ', ''), '-', ''), '_', ''), '/', '') COLLATE utf8mb4_unicode_ci, '%')
SET e.service_id = c.service_id,
    e.service_name = c.name;

SELECT c.ord, c.code, c.name, c.service_id
FROM tmp_public_canonical c
ORDER BY c.ord;
