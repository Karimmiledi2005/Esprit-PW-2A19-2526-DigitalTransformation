-- Migration: normalize statuses + harden reponse/reclamation schema for BackOffice module.

-- 1) Normalize reclamation status values used by UI/API.
UPDATE reclamation SET statut = 'closed' WHERE statut IN ('resolue', 'résolue');
UPDATE reclamation
SET statut = 'open'
WHERE statut IS NULL OR statut = '' OR statut NOT IN ('open', 'closed', 'rejected');

-- 2) Normalize reponse status values (kept separate on purpose from reclamation.statut).
UPDATE reponse SET statut = 'envoyee' WHERE statut IS NULL OR statut = '';
UPDATE reponse SET statut = 'rejetee' WHERE statut IN ('rejected', 'rejetee', 'rejetée');
UPDATE reponse SET statut = 'envoyee' WHERE statut NOT IN ('envoyee', 'rejetee');

-- 3) Support longer messages.
ALTER TABLE reclamation MODIFY COLUMN description TEXT NULL;
ALTER TABLE reponse MODIFY COLUMN contenu TEXT NOT NULL;

-- 4) Remove historical duplicates, keep latest response by highest id_re.
DELETE r_old
FROM reponse r_old
INNER JOIN reponse r_new
	ON r_old.reclamation_id = r_new.reclamation_id
   AND r_old.id_re < r_new.id_re;

-- 5) Enforce one response row per reclamation.
ALTER TABLE reponse
	ADD UNIQUE KEY uq_reponse_reclamation (reclamation_id);
