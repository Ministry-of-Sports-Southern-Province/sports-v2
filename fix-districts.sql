-- Fix district Sinhala letters with proper UTF-8 encoding
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELETE FROM districts;

INSERT INTO districts (name, sinhala_letter) VALUES 
('Galle', 'ගා'),
('Matara', 'මා'),
('Hambantota', 'හ');

SELECT id, name, sinhala_letter, 
       HEX(sinhala_letter) as hex_value,
       CHAR_LENGTH(sinhala_letter) as char_len,
       LENGTH(sinhala_letter) as byte_len
FROM districts;
