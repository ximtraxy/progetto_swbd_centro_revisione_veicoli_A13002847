CREATE DATABASE IF NOT EXISTS centro_revisioni;
USE centro_revisioni;

CREATE TABLE UTENTE (
    Id_User INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(50) NOT NULL,
    Cognome VARCHAR(50) NOT NULL,
    `E-mail` VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Ruolo ENUM('Cliente', 'Tecnico', 'Logista', 'Admin') NOT NULL
);

CREATE TABLE VEICOLO (
    Targa VARCHAR(10) PRIMARY KEY,
    Marca VARCHAR(50) NOT NULL,
    Modello VARCHAR(50) NOT NULL,
    Anno_Immatricolazione INT NOT NULL,
    Categoria ENUM('Auto', 'Moto', 'Commerciale') NOT NULL,
    Id_Utente INT NOT NULL,
    Scadenza_Revisione DATE NULL,
    FOREIGN KEY (Id_Utente) REFERENCES UTENTE(Id_User) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE REVISIONE (
    Id_Revisione INT AUTO_INCREMENT PRIMARY KEY,
    Data_ora DATETIME NOT NULL,
    Esito ENUM('Da effettuare', 'Regolare', 'Ripetere', 'Sospeso') DEFAULT 'Da effettuare',
    Note TEXT,
    Firma_Tecnico VARCHAR(255),
    Targa VARCHAR(10) NOT NULL,
    Id_Utente_Tecnico INT NULL,
    FOREIGN KEY (Targa) REFERENCES VEICOLO(Targa) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Id_Utente_Tecnico) REFERENCES UTENTE(Id_User) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE ABBONAMENTO (
    Id_Abbonamento INT AUTO_INCREMENT PRIMARY KEY,
    Data_Sottoscrizione DATE NOT NULL,
    Data_Scadenza DATE NOT NULL,
    Stato ENUM('Attivo', 'Scaduto') DEFAULT 'Attivo',
    Id_Utente INT NOT NULL,
    Targa varchar(10) NOT NULL,
    FOREIGN KEY (Id_Utente) REFERENCES UTENTE(Id_User) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Targa) REFERENCES VEICOLO(Targa) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE PAGAMENTO (
    Id_Pagamento INT AUTO_INCREMENT PRIMARY KEY,
    Data DATETIME NOT NULL,
    Importo DECIMAL(10, 2) NOT NULL,
    Causale VARCHAR(255) NOT NULL,
    Id_Utente INT NOT NULL,
    FOREIGN KEY (Id_Utente) REFERENCES UTENTE(Id_User) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE AUTO_SOSTITUTIVA (
    Targa_Auto VARCHAR(10) PRIMARY KEY,
    Marca VARCHAR(50) NOT NULL,
    Modello VARCHAR(50) NOT NULL,
    Stato ENUM('Libera', 'In uso', 'Manutenzione') DEFAULT 'Libera'
);

CREATE TABLE INCARICO_LOGISTICO (
    Id_Incarico INT AUTO_INCREMENT PRIMARY KEY,
    Data_Ora DATETIME NOT NULL,
    Indirizzo VARCHAR(255) NOT NULL,
    Stato ENUM('In attesa', 'In transito', 'Veicolo Ritirato', 'Veicolo Riconsegnato', 'Completato') DEFAULT 'In attesa',
    Id_Utente_Autista INT NULL,
    Targa_Veicolo_Cliente VARCHAR(10) NOT NULL,
    Targa_Auto_Sost VARCHAR(10),
    FOREIGN KEY (Id_Utente_Autista) REFERENCES UTENTE(Id_User) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (Targa_Veicolo_Cliente) REFERENCES VEICOLO(Targa) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Targa_Auto_Sost) REFERENCES AUTO_SOSTITUTIVA(Targa_Auto) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE NOTIFICA (
    Id_Notifica INT AUTO_INCREMENT PRIMARY KEY,
    Testo TEXT NOT NULL,
    Data DATETIME NOT NULL,
    Letta BOOLEAN DEFAULT FALSE,
    Id_Utente INT NOT NULL,
    FOREIGN KEY (Id_Utente) REFERENCES UTENTE(Id_User) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE REPORT_CONDIZIONI (
    Id_Report INT AUTO_INCREMENT PRIMARY KEY,
    Fase ENUM('Check-in', 'Check-out') NOT NULL,
    Descrizione_Danni TEXT,
    URL_Media VARCHAR(255),
    Data_Ora DATETIME NOT NULL,
    Firma_Accettazione VARCHAR(255),
    Id_Incarico INT NOT NULL,
    FOREIGN KEY (Id_Incarico) REFERENCES INCARICO_LOGISTICO(Id_Incarico) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO UTENTE (Nome, Cognome, `E-mail`, Password, Ruolo) VALUES 
('Cliente1', 'Prova', 'cliente1@email.it', 'cliente123', 'Cliente'),
('Cliente2', 'Prova', 'cliente2@email.it', 'cliente123', 'Cliente'),
('Tecnico1', 'Try', 'tecnico1@centro.it', 'tecnico123', 'Tecnico'),
('Tecnico2', 'Try', 'tecnico2@centro.it', 'tecnico123', 'Tecnico'),
('Logista1', 'Proba', 'logista1@centro.it', 'logista123', 'Logista'),
('Logista2', 'Proba', 'logista2@centro.it', 'logista123', 'Logista'),
('Admin', 'Principale', 'admin@centro.it', 'admin123', 'Admin');

INSERT INTO VEICOLO (Targa, Marca, Modello, Anno_Immatricolazione, Categoria, Id_Utente, Scadenza_Revisione) VALUES 
('AB123CD', 'Fiat', 'Panda', 2001, 'Auto', 1, '2026-10-15'),
('AA12345', 'Yamaha', 'MT-07', 2021, 'Moto', 1, '2026-03-04'),
('FZ888KX', 'Ford', 'Transit', 2019, 'Commerciale', 2, '2026-01-20'),
('FN192SE', 'Autobianchi', 'Y10', 1990, 'Auto', 2, '2027-11-12');

INSERT INTO AUTO_SOSTITUTIVA (Targa_Auto, Marca, Modello, Stato) VALUES 
('ZA999ZA', 'Ferrari', 'F40', 'Libera'),
('ZA998ZA', 'Bugatti', 'Chiron', 'Libera');

INSERT INTO ABBONAMENTO (Data_Sottoscrizione, Data_Scadenza, Stato, Id_Utente, Targa) VALUES 
('2024-01-01', '2025-01-01', 'Scaduto', 1, 'AB123CD'),
('2026-01-01', '2027-01-01', 'Attivo', 1, 'AA12345'),
('2026-01-01', '2027-01-01', 'Attivo', 2, 'FZ888KX'),
('2025-01-01', '2026-01-01', 'Attivo', 2, 'FN192SE'); 

INSERT INTO PAGAMENTO (Data, Importo, Causale, Id_Utente) VALUES 
('2024-01-01 10:00:00', 150.00, 'Sottoscrizione Abbonamento Premium', 1),
('2026-01-01 17:00:00', 150.00, 'Sottoscrizione Abbonamento Premium', 1),
('2026-01-01 19:00:00', 150.00, 'Sottoscrizione Abbonamento Premium', 2),
('2025-01-01 21:00:00', 150.00, 'Sottoscrizione Abbonamento Premium', 2);

INSERT INTO INCARICO_LOGISTICO (Data_Ora, Indirizzo, Stato, Id_Utente_Autista, Targa_Veicolo_Cliente, Targa_Auto_Sost) VALUES 
('2026-04-01 08:30:00', 'Via Roma 10, Aversa', 'In attesa', 6, 'AB123CD', 'ZA999ZA'),
('2026-03-28 12:30:00', 'Viale Michelangelo 10, Aversa', 'In attesa', 5, 'FN192SE', 'ZA998ZA');

INSERT INTO NOTIFICA (Testo, Data, Letta, Id_Utente) VALUES 
('Il tuo abbonamento è attivo. Abbiamo fissato il ritiro del veicolo per il 15 Ottobre.', '2024-10-10 12:00:00', FALSE, 1),
('Il tuo abbonamento è attivo. Abbiamo fissato il ritiro del veicolo per il 21 Aprile.', '2025-04-15 12:00:00', FALSE, 2);

INSERT INTO REPORT_CONDIZIONI (Fase, Descrizione_Danni, URL_Media, Data_Ora, Firma_Accettazione, Id_Incarico) VALUES 
('Check-in', 'Lieve graffio paraurti anteriore destro', '/upload/1.jpg', '2024-10-15 08:35:00', 'Firma_Cliente1', 1),
('Check-in', 'Nessun danno', '/upload/2.jpg', '2024-04-21 08:35:00', 'Firma_Cliente2', 2);

INSERT INTO REVISIONE (Data_ora, Esito, Note, Targa, Id_Utente_Tecnico) VALUES 
('2024-10-15 11:00:00', 'Da effettuare', 'Revisione periodica ordinaria', 'AB123CD', NULL),
('2024-10-15 11:00:00', 'Da effettuare', 'Revisione periodica ordinaria', 'FN192SE', NULL);