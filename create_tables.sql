CREATE TABLE leg.MSG
(
  "ID" bigint NOT NULL,
  "Text" text NOT NULL,
  "Subject" character varying(70) NOT NULL,
  "Thread_ID" bigint NOT NULL,
  "Date" bigint NOT NULL,
  CONSTRAINT msg_pkey PRIMARY KEY ("ID")
)

//

CREATE TABLE leg.MSG_BOX
(
  "MSG_ID" bigint NOT NULL,
  "To_User_ID" bigint NOT NULL,
  "From_User_ID" bigint NOT NULL,
  "Status" smallint NOT NULL,
   FOREIGN KEY ("MSG_ID") REFERENCES leg.MSG("ID"),
   FOREIGN KEY ("To_User_ID") REFERENCES leg.player("Player_ID"),
   FOREIGN KEY ("From_User_ID") REFERENCES leg.player("Player_ID")
)

// SEQ Postgres
CREATE SEQUENCE leg.seq_msg_id
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 100000000
  START 1
  CACHE 1;

CREATE SEQUENCE leg.seq_thread_id
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 100000000
  START 1
  CACHE 1;
