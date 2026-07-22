-- =========================================================
-- Script de creation de la base de donnees "bibliotheque"
-- =========================================================

CREATE DATABASE IF NOT EXISTS bibliotheque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bibliotheque;

-- ---------------------------------------------------------
-- Table utilisateurs (comptes administrateurs)
-- ---------------------------------------------------------
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'gestionnaire') NOT NULL DEFAULT 'gestionnaire',
    mot_de_passe VARCHAR(255) NOT NULL
);

-- ---------------------------------------------------------
-- Table categories de livres
-- ---------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
);

-- ---------------------------------------------------------
-- Table livres
-- ---------------------------------------------------------
CREATE TABLE livres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    isbn VARCHAR(20),
    annee INT,
    quantite INT NOT NULL DEFAULT 0,
    id_categorie INT NULL,
    couverture VARCHAR(255) NULL,
    FOREIGN KEY (id_categorie) REFERENCES categories(id)
);

-- ---------------------------------------------------------
-- Table etudiants
-- ---------------------------------------------------------
CREATE TABLE etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    telephone VARCHAR(30),
    filiere VARCHAR(100)
);

-- ---------------------------------------------------------
-- Table emprunts
-- ---------------------------------------------------------
CREATE TABLE emprunts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_livre INT NOT NULL,
    id_etudiant INT NOT NULL,
    date_emprunt DATE NOT NULL,
    date_retour_prevue DATE NOT NULL,
    date_retour DATE NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'En cours',
    FOREIGN KEY (id_livre) REFERENCES livres(id),
    FOREIGN KEY (id_etudiant) REFERENCES etudiants(id)
);

-- ---------------------------------------------------------
-- Table logs (journalisation)
-- ---------------------------------------------------------
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    table_cible VARCHAR(50) NOT NULL,
    id_cible INT NULL,
    details TEXT NULL,
    ip VARCHAR(45) NULL,
    utilisateur_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (created_at),
    INDEX idx_action (action)
);

-- =========================================================
-- Comptes utilisateurs par defaut
-- =========================================================
-- Admin : admin@bibliotheque.local / admin123
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Administrateur', 'admin@bibliotheque.local', '$2y$10$/UcNGIvOR1cRf34xw0Ac4.395DqIxOYwKpY.F0YRoA2nTaw.w8tVO', 'admin');

-- Gestionnaire : gestionnaire@bibliotheque.local / gestionnaire123
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Jean Dupont', 'gestionnaire@bibliotheque.local', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gestionnaire');

-- =========================================================
-- Jeu de donnees de demonstration : categories
-- =========================================================
INSERT INTO categories (nom) VALUES
('Roman'),
('Science'),
('Histoire'),
('Philosophie'),
('Informatique'),
('Littérature'),
('Science-Fiction'),
('Fantasy'),
('Pédagogie'),
('Biographie');

-- =========================================================
-- Jeu de donnees de demonstration : livres
-- =========================================================
INSERT INTO livres (titre, auteur, isbn, annee, quantite, id_categorie) VALUES
('Le Petit Prince', 'Antoine de Saint-Exupery', '9782070408504', 1943, 5, 6),
('1984', 'George Orwell', '9780451524935', 1949, 3, 7),
('Les Miserables', 'Victor Hugo', '9782253096344', 1862, 4, 6),
('L\'etranger', 'Albert Camus', '9782070360024', 1942, 2, 6),
('Le Comte de Monte-Cristo', 'Alexandre Dumas', '9782253097661', 1844, 3, 1),
('Germinal', 'emile Zola', '9782253004268', 1885, 2, 6),
('Candide', 'Voltaire', '9782070413714', 1759, 6, 4),
('Madame Bovary', 'Gustave Flaubert', '9782253006389', 1857, 3, 1),
('Notre-Dame de Paris', 'Victor Hugo', '9782253009880', 1831, 2, 1),
('Le Rouge et le Noir', 'Stendhal', '9782253004909', 1830, 3, 1),
('Introduction à l\'algorithmique', 'Thomas Cormen', '9782100545261', 2010, 4, 5),
('Structures de donnees en Java', 'Robert Lafore', '9782744072052', 2003, 2, 5),
('Reseaux informatiques', 'Andrew Tanenbaum', '9782744077009', 2011, 5, 5),
('Bases de donnees', 'Georges Gardarin', '9782212120952', 2003, 3, 5),
('PHP et MySQL', 'Luke Welling', '9782744066068', 2017, 6, 5),
('Clean Code', 'Robert C. Martin', '9780132350884', 2008, 4, 5),
('Design Patterns', 'Erich Gamma', '9780201633610', 1994, 2, 5),
('Le Seigneur des Anneaux', 'J.R.R. Tolkien', '9782266154167', 1954, 3, 8),
('Harry Potter à l\'ecole des sorciers', 'J.K. Rowling', '9782070518379', 1997, 7, 8),
('Fondation', 'Isaac Asimov', '9782070415799', 1951, 3, 7),
('Dune', 'Frank Herbert', '9782221191104', 1965, 4, 7),
('La Peste', 'Albert Camus', '9782070360420', 1947, 2, 6),
('Vingt mille lieues sous les mers', 'Jules Verne', '9782253005715', 1870, 3, 7),
('Le Tour du monde en 80 jours', 'Jules Verne', '9782253005531', 1873, 2, 1),
('L\'Alchimiste', 'Paulo Coelho', '9782290314048', 1988, 5, 4),
('Sapiens', 'Yuval Noah Harari', '9782226257017', 2011, 4, 3),
('Homo Deus', 'Yuval Noah Harari', '9782226393895', 2015, 3, 3),
('Le Petit Nicolas', 'Rene Goscinny', '9782070613970', 1960, 6, 6),
('Asterix le Gaulois', 'Rene Goscinny', '9782012101333', 1961, 8, 1),
('Tintin au Congo', 'Herge', '9782203001152', 1931, 2, 1);

-- =========================================================
-- Jeu de donnees de demonstration : etudiants
-- =========================================================
INSERT INTO etudiants (nom, prenom, email, telephone, filiere) VALUES
('Obame', 'Jean', 'jean.obame@etu.iai.ga', '074123456', 'Genie Logiciel'),
('Nzue', 'Marie', 'marie.nzue@etu.iai.ga', '074123457', 'Reseaux et Securite'),
('Mba', 'Paul', 'paul.mba@etu.iai.ga', '074123458', 'Systèmes et Reseaux'),
('Ndong', 'Sylvie', 'sylvie.ndong@etu.iai.ga', '074123459', 'Genie Logiciel'),
('Ella', 'David', 'david.ella@etu.iai.ga', '074123460', 'Cybersecurite'),
('Meye', 'Claire', 'claire.meye@etu.iai.ga', '074123461', 'Base de Donnees'),
('Owono', 'Franck', 'franck.owono@etu.iai.ga', '074123462', 'Reseaux et Securite'),
('Assoumou', 'Nadia', 'nadia.assoumou@etu.iai.ga', '074123463', 'Genie Logiciel'),
('Biyoghe', 'Steve', 'steve.biyoghe@etu.iai.ga', '074123464', 'Cybersecurite'),
('Moussavou', 'Alice', 'alice.moussavou@etu.iai.ga', '074123465', 'Systèmes et Reseaux'),
('Ondo', 'Kevin', 'kevin.ondo@etu.iai.ga', '074123466', 'Genie Logiciel'),
('Bongo', 'Laura', 'laura.bongo@etu.iai.ga', '074123467', 'Base de Donnees'),
('Mihindou', 'Eric', 'eric.mihindou@etu.iai.ga', '074123468', 'Reseaux et Securite'),
('Ntoutoume', 'Sarah', 'sarah.ntoutoume@etu.iai.ga', '074123469', 'Cybersecurite'),
('Rekangalt', 'Junior', 'junior.rekangalt@etu.iai.ga', '074123470', 'Genie Logiciel');

-- =========================================================
-- Jeu de donnees de demonstration : emprunts
-- =========================================================
INSERT INTO emprunts (id_livre, id_etudiant, date_emprunt, date_retour_prevue, date_retour, statut) VALUES
-- Emprunts retournes
(1, 1, '2026-06-01', '2026-06-15', '2026-06-14', 'Retourne'),
(3, 3, '2026-06-05', '2026-06-19', '2026-06-20', 'Retourne'),
(5, 5, '2026-05-20', '2026-06-03', '2026-06-02', 'Retourne'),
(12, 7, '2026-05-25', '2026-06-08', '2026-06-10', 'Retourne'),
(20, 11, '2026-05-15', '2026-05-29', '2026-05-28', 'Retourne'),
(26, 13, '2026-06-02', '2026-06-16', '2026-06-18', 'Retourne'),
(29, 15, '2026-05-10', '2026-05-24', '2026-05-23', 'Retourne'),
-- Emprunts en cours EN RETARD (dates depassees)
(2, 2, '2026-06-03', '2026-06-17', NULL, 'En cours'),
(4, 4, '2026-06-10', '2026-06-24', NULL, 'En cours'),
(15, 8, '2026-06-12', '2026-06-26', NULL, 'En cours'),
(18, 9, '2026-06-01', '2026-06-15', NULL, 'En cours'),
(19, 10, '2026-06-08', '2026-06-22', NULL, 'En cours'),
(25, 12, '2026-06-14', '2026-06-28', NULL, 'En cours'),
(28, 14, '2026-06-16', '2026-06-30', NULL, 'En cours'),
-- Emprunts en cours NON en retard (dates futures)
(1, 3, '2026-07-01', '2026-07-15', NULL, 'En cours'),
(5, 7, '2026-07-05', '2026-07-19', NULL, 'En cours'),
(11, 9, '2026-07-10', '2026-07-24', NULL, 'En cours');
