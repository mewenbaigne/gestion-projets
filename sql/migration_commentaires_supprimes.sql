-- Suppression « douce » d'un commentaire qui a des réponses : il reste affiché comme « Commentaire supprimé »
USE gestion_projets;

ALTER TABLE commentaires
  ADD COLUMN supprime BOOLEAN NOT NULL DEFAULT FALSE AFTER message;
