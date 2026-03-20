SET @fallback_user := (SELECT id FROM users ORDER BY id LIMIT 1);
SET @fallback_service := (SELECT id FROM services ORDER BY id LIMIT 1);

INSERT INTO maintenance_reports (
    report_number,
    intervention_type,
    intervention_scope,
    status,
    intervention_date,
    started_at,
    ended_at,
    duration_minutes,
    equipment_id,
    service_id,
    user_id,
    engineer_user_id,
    hospital_name,
    unit_code,
    equipment_designation,
    equipment_serial_number,
    equipment_inventory_number,
    supplier_name,
    brand_name,
    model_name,
    problem_description,
    operations_performed,
    created_at,
    updated_at
)
SELECT
    CONCAT('OTDM-', REPLACE(i.code, ' ', '-')),
    CASE WHEN i.type = 'Préventive' THEN 'preventive' ELSE 'curative' END,
    'interne',
    CASE WHEN i.status = 'termine' THEN 'closed' WHEN i.status = 'en_cours' THEN 'submitted' ELSE 'draft' END,
    COALESCE(i.date_start, DATE(i.created_at), CURDATE()),
    CASE WHEN i.date_start IS NOT NULL THEN CONCAT(i.date_start, ' 08:00:00') ELSE NULL END,
    CASE WHEN i.date_end IS NOT NULL THEN CONCAT(i.date_end, ' 17:00:00') ELSE NULL END,
    CASE
        WHEN i.date_start IS NOT NULL AND i.date_end IS NOT NULL
            THEN GREATEST(1, TIMESTAMPDIFF(MINUTE, CONCAT(i.date_start, ' 08:00:00'), CONCAT(i.date_end, ' 17:00:00')))
        ELSE NULL
    END,
    i.equipment_id,
    COALESCE(eq.service_id, @fallback_service),
    @fallback_user,
    NULL,
    h.name,
    NULL,
    eq.designation,
    eq.serial_number,
    eq.inventory_number_current,
    NULL,
    eq.brand_name,
    eq.model_name,
    CONCAT('AUTO-RESTORE-INTERVENTION ', i.code),
    COALESCE(i.closure_note, i.failure_cause, 'Import historique OT/DM'),
    COALESCE(i.created_at, NOW()),
    COALESCE(i.updated_at, NOW())
FROM interventions i
LEFT JOIN equipments eq ON eq.id = i.equipment_id
LEFT JOIN hospitals h ON h.id = eq.hospital_id
WHERE NOT EXISTS (
    SELECT 1
    FROM maintenance_reports mr
    WHERE mr.report_number = CONCAT('OTDM-', REPLACE(i.code, ' ', '-'))
);

SELECT
    'after_otdm_build' AS src,
    (SELECT COUNT(*) FROM interventions) AS interventions,
    (SELECT COUNT(*) FROM complaints) AS complaints,
    (SELECT COUNT(*) FROM maintenance_reports) AS maintenance_reports;
