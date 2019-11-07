ALTER TABLE users 
ADD COLUMN research_allowed TINYINT(2) DEFAULT -1 AFTER phone;
--1 = research allowed
--0 = research disallowed
--(-1) = no answer
