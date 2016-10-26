CREATE TABLE "sessions" (
	"id" varchar(120) NOT NULL DEFAULT ''::character varying,
	"data" text NOT NULL,
	"expires" int8 NOT NULL
)
WITH (OIDS=FALSE);

ALTER TABLE "sessions" ADD CONSTRAINT "sessions_pkey" PRIMARY KEY ("id") NOT DEFERRABLE INITIALLY IMMEDIATE;

CREATE INDEX "sessions_id" ON "public"."sessions" USING btree("id");
COMMENT ON INDEX "public"."sessions_id" IS NULL;
CREATE INDEX "sessions_expire" ON "public"."sessions" USING btree(expires);
COMMENT ON INDEX "public"."sessions_expire" IS NULL;