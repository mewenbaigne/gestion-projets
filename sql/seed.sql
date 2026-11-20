-- Données de démonstration (mot de passe de tous les comptes : password123)
USE gestion_projets;

INSERT INTO roles (nom, description) VALUES
 ('Chef de projet', 'Gère le projet, ses membres et ses tâches'),
 ('Développeur', 'Travaille sur les tâches attribuées'),
 ('Designer', 'Travaille sur les tâches de design');

INSERT INTO utilisateurs (nom, prenom, email, motDePasse, isAdmin) VALUES
 ('Admin', 'Alice', 'admin@example.com', '$2y$12$AeBstXCBFKMdT3HRtblBPeoG7FISLl65a9kmC75RYMPvQB7BPF0XG', TRUE),
 ('Martin', 'Chloé', 'chef@example.com', '$2y$12$AeBstXCBFKMdT3HRtblBPeoG7FISLl65a9kmC75RYMPvQB7BPF0XG', FALSE),
 ('Durand', 'David', 'dev@example.com', '$2y$12$AeBstXCBFKMdT3HRtblBPeoG7FISLl65a9kmC75RYMPvQB7BPF0XG', FALSE),
 ('Petit', 'Emma', 'designer@example.com', '$2y$12$AeBstXCBFKMdT3HRtblBPeoG7FISLl65a9kmC75RYMPvQB7BPF0XG', FALSE);

INSERT INTO projets (nom, description, etat, dateDebut, dateFin) VALUES
 ('Site e-commerce', 'Refonte complète de la boutique en ligne', 'en_cours', '2026-09-01', '2026-12-20'),
 ('Application mobile', 'Première version de l\'application client', 'a_faire', '2026-11-15', '2027-02-28');

INSERT INTO utilisateur_projet (id_utilisateur, id_projet, id_role) VALUES
 (2, 1, 1), (3, 1, 2), (4, 1, 3),
 (2, 2, 2), (3, 2, 1);

INSERT INTO taches (id_projet, nom, dateDebut, dateFin, description, etat) VALUES
 (1, 'Maquettes de la page produit', '2026-09-05', '2026-10-10', 'Réaliser les maquettes desktop et mobile', 'termine'),
 (1, 'Panier et paiement', '2026-10-01', '2026-11-30', 'Intégrer le tunnel de commande', 'en_cours'),
 (1, 'Tests de charge', '2026-11-20', '2026-12-10', 'Valider la tenue en charge avant mise en ligne', 'a_faire'),
 (2, 'Choix de la techno', '2026-11-15', '2026-11-30', 'Comparer natif / hybride', 'a_faire');

INSERT INTO tache_utilisateur (id_tache, id_utilisateur) VALUES (1, 4), (2, 3), (2, 2), (3, 3), (4, 2);

INSERT INTO commentaires (id_utilisateur, id_tache, message) VALUES
 (2, 2, 'Pensez à gérer le paiement refusé.'),
 (3, 2, 'OK, je m\'en occupe cette semaine.');

-- Réponse au commentaire n°1 (id_parent)
INSERT INTO commentaires (id_utilisateur, id_tache, id_parent, message) VALUES
 (3, 2, 1, 'Bien noté, je prévois un écran d\'erreur dédié.');
