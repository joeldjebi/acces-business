-- ============================================
-- Script SQL pour créer les tables d'invitation
-- Base de données: evenement_bd
-- ============================================

-- 0. Ajouter la colonne lien_google_map à la table events si elle n'existe pas
-- Version simple (exécuter seulement si la colonne n'existe pas)
-- ALTER TABLE `events` ADD COLUMN `lien_google_map` TEXT NULL AFTER `longitude`;

-- Version avec vérification (plus sûre)
SET @dbname = DATABASE();
SET @tablename = 'events';
SET @columnname = 'lien_google_map';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT "Colonne lien_google_map existe déjà" as message',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TEXT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 1. Table: event_registrations (Inscriptions aux événements)
CREATE TABLE IF NOT EXISTS `event_registrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `nom` VARCHAR(255) NULL,
  `prenom` VARCHAR(255) NULL,
  `telephone` VARCHAR(255) NULL,
  `entreprise` VARCHAR(255) NULL,
  `statut_reponse` ENUM('en_attente', 'present', 'peut_etre', 'absent') NOT NULL DEFAULT 'en_attente',
  `token_unique` VARCHAR(255) NOT NULL,
  `qr_code_path` VARCHAR(255) NULL,
  `carte_envoyee` TINYINT(1) NOT NULL DEFAULT 0,
  `date_inscription` TIMESTAMP NULL,
  `date_reponse` TIMESTAMP NULL,
  `date_validation_otp` TIMESTAMP NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_registrations_token_unique_unique` (`token_unique`),
  KEY `event_registrations_event_id_email_index` (`event_id`, `email`),
  KEY `event_registrations_token_unique_index` (`token_unique`),
  CONSTRAINT `event_registrations_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_registrations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: event_otp_verifications (Vérifications OTP)
CREATE TABLE IF NOT EXISTS `event_otp_verifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `expires_at` TIMESTAMP NOT NULL,
  `verified_at` TIMESTAMP NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `event_otp_verifications_event_id_email_otp_code_index` (`event_id`, `email`, `otp_code`),
  CONSTRAINT `event_otp_verifications_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: event_access_links (Liens d'accès envoyés par les admins)
CREATE TABLE IF NOT EXISTS `event_access_links` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` BIGINT UNSIGNED NOT NULL,
  `email_destinataire` VARCHAR(255) NOT NULL,
  `token_unique` VARCHAR(255) NOT NULL,
  `envoye_par` BIGINT UNSIGNED NOT NULL,
  `envoye_le` TIMESTAMP NULL,
  `utilise_le` TIMESTAMP NULL,
  `est_utilise` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_access_links_token_unique_unique` (`token_unique`),
  KEY `event_access_links_event_id_email_destinataire_index` (`event_id`, `email_destinataire`),
  KEY `event_access_links_token_unique_index` (`token_unique`),
  CONSTRAINT `event_access_links_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_access_links_envoye_par_foreign` FOREIGN KEY (`envoye_par`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Vérification que les tables ont été créées
-- ============================================
-- SELECT 'event_registrations' as table_name, COUNT(*) as row_count FROM event_registrations
-- UNION ALL
-- SELECT 'event_otp_verifications', COUNT(*) FROM event_otp_verifications
-- UNION ALL
-- SELECT 'event_access_links', COUNT(*) FROM event_access_links;

