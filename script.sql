-- Création de la base de données
CREATE DATABASE ITThink;

-- Utilisation de la base de données
USE ITThink;

-- Table : Utilisateurs
CREATE TABLE Utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(255) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    autres_informations TEXT
);

-- Table : Catégories
CREATE TABLE Categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(255) NOT NULL
);

-- Table : Sous-Catégories
CREATE TABLE SousCategories (
    id_sous_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_sous_categorie VARCHAR(255) NOT NULL,
    id_categorie INT NOT NULL,
    FOREIGN KEY (id_categorie) REFERENCES Categories(id_categorie)
);

-- Table : Projets
CREATE TABLE Projets (
    id_projet INT AUTO_INCREMENT PRIMARY KEY,
    titre_projet VARCHAR(255) NOT NULL,
    description TEXT,
    id_categorie INT NOT NULL,
    id_sous_categorie INT NOT NULL,
    id_utilisateur INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES Categories(id_categorie),
    FOREIGN KEY (id_sous_categorie) REFERENCES SousCategories(id_sous_categorie),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur)
);

-- Table : Freelances
CREATE TABLE Freelances (
    id_freelance INT AUTO_INCREMENT PRIMARY KEY,
    nom_freelance VARCHAR(255) NOT NULL,
    competences TEXT,
    id_utilisateur INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur)
);

-- Table : Offres
CREATE TABLE Offres (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(10, 2) NOT NULL,
    delai INT NOT NULL,
    id_freelance INT NOT NULL,
    id_projet INT NOT NULL,
    FOREIGN KEY (id_freelance) REFERENCES Freelances(id_freelance),
    FOREIGN KEY (id_projet) REFERENCES Projets(id_projet)
);

-- Table : Témoignages
CREATE TABLE Temoignages (
    id_temoignage INT AUTO_INCREMENT PRIMARY KEY,
    commentaire TEXT NOT NULL,
    id_utilisateur INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur)
);


SHOW TABLES;


USE itthink;

-- Insertion d’un utilisateur
INSERT INTO Utilisateurs (nom_utilisateur, mot_de_passe, email, autres_informations)
VALUES ('Mohamed Zouhairi', 'password_hashed', 'mohamed@zouhairi.com', 'Informations additionnelles');

-- Insertion d’une catégorie
INSERT INTO Categories (nom_categorie)
VALUES ('Développement Web');

-- Insertion d’une sous-catégorie
INSERT INTO SousCategories (nom_sous_categorie, id_categorie)
VALUES ('Frontend', 1);

-- Insertion d’un projet
INSERT INTO Projets (titre_projet, description, id_categorie, id_sous_categorie, id_utilisateur)
VALUES ('Site E-commerce', 'Création d’un site e-commerce complet', 1, 1, 1);

-- Insertion d’un freelance
INSERT INTO Freelances (nom_freelance, competences, id_utilisateur)
VALUES ('Jane Smith', 'HTML, CSS, JavaScript', 1);

-- Insertion d’une offre
INSERT INTO Offres (montant, delai, id_freelance, id_projet)
VALUES (500.00, 30, 1, 1);

-- Insertion d’un témoignage
INSERT INTO Temoignages (commentaire, id_utilisateur)
VALUES ('Service excellent!', 1);


SELECT * FROM offres


USE itthink;
-- Mise à jour des détails d’un projet
UPDATE Projets
SET titre_projet = 'Site E-commerce Pro', description = 'Site complet avec système de paiement'
WHERE id_projet = 1;


SELECT * FROM projets;

-- Suppression d’un témoignage
DELETE FROM Temoignages
WHERE id_temoignage = 1;


-- Récupérer les détails des projets liés à une catégorie spécifique
SELECT Projets.titre_projet, Projets.description, Categories.nom_categorie
FROM Projets
INNER JOIN Categories ON Projets.id_categorie = Categories.id_categorie
WHERE Categories.nom_categorie = 'Développement Web';

-- Récupérer les offres et les freelances associés à un projet
SELECT Offres.montant, Offres.delai, Freelances.nom_freelance
FROM Offres
INNER JOIN Freelances ON Offres.id_freelance = Freelances.id_freelance
WHERE Offres.id_projet = 1;


mysqldump -u root -p ITThink > backup.sql
