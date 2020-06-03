CREATE TABLE IF NOT EXISTS git (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) NOT NULL,
  repository varchar(100) NOT NULL,
  owner varchar(40) NOT NULL,
  PRIMARY KEY (id));