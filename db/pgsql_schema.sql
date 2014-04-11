CREATE TABLE activations (
  id SERIAL PRIMARY KEY,
  email varchar(50) NOT NULL,
  code varchar(64) NOT NULL,
  timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  used boolean NOT NULL DEFAULT false
) WITH (OIDS=FALSE);
CREATE UNIQUE INDEX idx_email_code ON activations USING btree ("email", "code", "timestamp");


CREATE TABLE reports (
  id SERIAL PRIMARY KEY,
  sql text NOT NULL
) WITH (OIDS=FALSE);

CREATE TABLE sessions (
  id SERIAL PRIMARY KEY,
  session_id varchar(32) NOT NULL,
  access TIMESTAMP NOT NULL,
  user_id int NOT NULL,
  UNIQUE(session_id)
) WITH (OIDS=FALSE);
CREATE INDEX access ON sessions USING btree(access);
CREATE INDEX user_id ON sessions USING hash(user_id);


CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  social_token varchar(128) DEFAULT NULL,
  email varchar(50) DEFAULT NULL,
  password varchar(60) DEFAULT NULL,
  date_register TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  chat_id varchar(32) NOT NULL DEFAULT '1'
) WITH (OIDS=FALSE);
CREATE INDEX email ON users USING hash(email);
CREATE INDEX chat_id ON users USING hash(chat_id);
CREATE INDEX social_token ON users USING hash(social_token);


CREATE TABLE user_blacklist (
  id SERIAL PRIMARY KEY,
  user_id int NOT NULL,
  ignored_user_id int NOT NULL
) WITH (OIDS=FALSE);
CREATE INDEX user_blacklist_id ON user_blacklist USING btree(user_id, ignored_user_id);


CREATE TABLE user_properties (
  id SERIAL PRIMARY KEY,
  user_id int NOT NULL,
  name varchar(20) NOT NULL,
  sex int NOT NULL,
  tim int NOT NULL,
  notifications text,
  UNIQUE (user_id),
  UNIQUE (name)
) WITH (OIDS=FALSE);
