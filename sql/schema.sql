CREATE DATABASE IF NOT EXISTS gestion_projets
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gestion_projets;

-- 1. Tables sans clé étrangère
CREATE TABLE utilisateurs (
id INT AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(100) NOT NULL,
prenom VARCHAR(100) NOT NULL,
email VARCHAR(255) NOT NULL UNIQUE,
motDePasse VARCHAR(255) NOT NULL,
isAdmin BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB;

CREATE TABLE projets (
id INT AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(150) NOT NULL,
description TEXT,
etat ENUM('a_faire','en_cours','termine','annule') NOT NULL DEFAULT 'a_faire',
dateDebut DATE,
dateFin DATE
) ENGINE=InnoDB;

CREATE TABLE roles (
id INT AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(50) NOT NULL UNIQUE,
description VARCHAR(255)
) ENGINE=InnoDB;

-- Tables avec clés étrangères
CREATE TABLE taches (
id INT AUTO_INCREMENT PRIMARY KEY,
id_projet INT NOT NULL,
nom VARCHAR(150) NOT NULL,
dateDebut DATE,
dateFin DATE,
description TEXT,
etat ENUM('a_faire','en_cours','termine','annule') NOT NULL DEFAULT 'a_faire',
FOREIGN KEY (id_projet) REFERENCES projets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. Tables de liaison et commentaires
CREATE TABLE utilisateur_projet (
id INT AUTO_INCREMENT PRIMARY KEY,
id_utilisateur INT NOT NULL,
id_projet INT NOT NULL,
id_role INT NOT NULL,
UNIQUE (id_utilisateur, id_projet),
FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
FOREIGN KEY (id_projet) REFERENCES projets(id) ON DELETE CASCADE,
FOREIGN KEY (id_role) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE tache_utilisateur (
id INT AUTO_INCREMENT PRIMARY KEY,
id_tache INT NOT NULL,
id_utilisateur INT NOT NULL,
UNIQUE (id_tache, id_utilisateur),
FOREIGN KEY (id_tache) REFERENCES taches(id) ON DELETE CASCADE,
FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE commentaires (
id INT AUTO_INCREMENT PRIMARY KEY,
id_utilisateur INT NULL,
id_tache INT NOT NULL,
id_parent INT NULL, -- NULL = commentaire de premier niveau, sinon réponse au commentaire id_parent
date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
message TEXT NOT NULL,
supprime BOOLEAN NOT NULL DEFAULT FALSE, -- TRUE = « Commentaire supprimé » (conservé car il a des réponses)
FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL,
FOREIGN KEY (id_tache) REFERENCES taches(id) ON DELETE CASCADE,
FOREIGN KEY (id_parent) REFERENCES commentaires(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Historiques
CREATE TABLE historique_projets (
id INT AUTO_INCREMENT PRIMARY KEY,
id_projet INT NOT NULL,
id_utilisateur INT NULL,
date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
descriptionModification TEXT NOT NULL,
FOREIGN KEY (id_projet) REFERENCES projets(id) ON DELETE CASCADE,
FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE historique_taches (
id INT AUTO_INCREMENT PRIMARY KEY,
id_tache INT NOT NULL,
id_utilisateur INT NULL,
date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
descriptionModification TEXT NOT NULL,
FOREIGN KEY (id_tache) REFERENCES taches(id) ON DELETE CASCADE,
FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE historique_commentaires (
id INT AUTO_INCREMENT PRIMARY KEY,
id_commentaire INT NOT NULL,
id_utilisateur INT NULL,
date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
descriptionModification TEXT NOT NULL,
FOREIGN KEY (id_commentaire) REFERENCES commentaires(id) ON DELETE CASCADE,
FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;
