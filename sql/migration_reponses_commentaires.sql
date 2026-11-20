-- Réponses aux commentaires : à exécuter une fois sur une base gestion_projets existante
USE gestion_projets;

ALTER TABLE commentaires
  ADD COLUMN id_parent INT NULL AFTER id_tache,
  ADD CONSTRAINT fk_commentaires_parent FOREIGN KEY (id_parent) REFERENCES commentaires(id) ON DELETE CASCADE;
