# Gastgeber-Felder für EXT:news
CREATE TABLE tx_news_domain_model_news (
  tx_gastgeber_street varchar(255) DEFAULT '' NOT NULL,
  tx_gastgeber_address_addition varchar(255) DEFAULT '' NOT NULL,
  tx_gastgeber_zip varchar(20) DEFAULT '' NOT NULL,
  tx_gastgeber_city varchar(255) DEFAULT '' NOT NULL,
  tx_gastgeber_country varchar(80) DEFAULT 'Deutschland' NOT NULL,
  tx_gastgeber_phone varchar(80) DEFAULT '' NOT NULL,
  tx_gastgeber_email varchar(255) DEFAULT '' NOT NULL,
  tx_gastgeber_website varchar(2048) DEFAULT '' NOT NULL,
  tx_gastgeber_booking_url varchar(2048) DEFAULT '' NOT NULL,
  tx_gastgeber_booking_note text,
  tx_gastgeber_price_from decimal(10,2) DEFAULT '0.00' NOT NULL,
  tx_gastgeber_price_note text,
  tx_gastgeber_capacity_people int(11) DEFAULT '0' NOT NULL,
  tx_gastgeber_rooms int(11) DEFAULT '0' NOT NULL,
  tx_gastgeber_beds int(11) DEFAULT '0' NOT NULL,
  tx_gastgeber_latitude decimal(10,7) DEFAULT '0.0000000' NOT NULL,
  tx_gastgeber_longitude decimal(10,7) DEFAULT '0.0000000' NOT NULL,
  tx_gastgeber_show_on_map tinyint(1) unsigned DEFAULT '1' NOT NULL,
  tx_gastgeber_opening_times text,
  tx_gastgeber_equipment text,
  tx_gastgeber_certifications text,
  tx_gastgeber_seo_title varchar(255) DEFAULT '' NOT NULL,
  tx_gastgeber_seo_description text,
  tx_gastgeber_focus_keyword varchar(255) DEFAULT '' NOT NULL,
  tx_gastgeber_og_title varchar(255) DEFAULT '' NOT NULL,
  tx_gastgeber_og_description text,
  tx_gastgeber_seo_noindex tinyint(1) unsigned DEFAULT '0' NOT NULL
);

# Zusatzfelder für Merkmal-Kategorien / Filterdarstellung
CREATE TABLE sys_category (
  tx_gastgeber_icon int(11) unsigned DEFAULT '0' NOT NULL,
  tx_gastgeber_icon_css_class varchar(120) DEFAULT '' NOT NULL,
  tx_gastgeber_filter_hidden tinyint(1) unsigned DEFAULT '0' NOT NULL
);
