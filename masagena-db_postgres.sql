--
-- PostgreSQL database dump
--

\restrict edm66TTprf8Afcn9q4OnIhgLFNNxVHDASjXejMnh0AvlKAZBfJONtEJPxRRVGdQ

-- Dumped from database version 18.0
-- Dumped by pg_dump version 18.0

-- Started on 2026-06-16 09:48:30

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 868 (class 1247 OID 91012)
-- Name: level_pengurus_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.level_pengurus_type AS ENUM (
    'inti',
    'biasa'
);


ALTER TYPE public.level_pengurus_type OWNER TO postgres;

--
-- TOC entry 865 (class 1247 OID 91005)
-- Name: peran_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.peran_type AS ENUM (
    'mahasiswa',
    'pengurus',
    'admin'
);


ALTER TYPE public.peran_type OWNER TO postgres;

--
-- TOC entry 877 (class 1247 OID 91032)
-- Name: status_aspirasi_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.status_aspirasi_type AS ENUM (
    'terkirim',
    'dibaca',
    'direspons'
);


ALTER TYPE public.status_aspirasi_type OWNER TO postgres;

--
-- TOC entry 874 (class 1247 OID 91024)
-- Name: status_pendaftaran_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.status_pendaftaran_type AS ENUM (
    'menunggu',
    'diterima',
    'ditolak'
);


ALTER TYPE public.status_pendaftaran_type OWNER TO postgres;

--
-- TOC entry 871 (class 1247 OID 91018)
-- Name: status_verifikasi_type; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.status_verifikasi_type AS ENUM (
    'pending',
    'verified'
);


ALTER TYPE public.status_verifikasi_type OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 232 (class 1259 OID 91188)
-- Name: aspirasi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.aspirasi (
                                 id_aspirasi integer NOT NULL,
                                 isi_aspirasi text NOT NULL,
                                 id_user integer,
                                 id_organisasi_tujuan integer NOT NULL,
                                 status public.status_aspirasi_type DEFAULT 'terkirim'::public.status_aspirasi_type,
                                 tanggapan text,
                                 created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.aspirasi OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 91187)
-- Name: aspirasi_id_aspirasi_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.aspirasi_id_aspirasi_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.aspirasi_id_aspirasi_seq OWNER TO postgres;

--
-- TOC entry 5123 (class 0 OID 0)
-- Dependencies: 231
-- Name: aspirasi_id_aspirasi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.aspirasi_id_aspirasi_seq OWNED BY public.aspirasi.id_aspirasi;


--
-- TOC entry 226 (class 1259 OID 91111)
-- Name: komentar; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.komentar (
                                 id_komentar integer NOT NULL,
                                 isi_komentar text NOT NULL,
                                 id_user integer NOT NULL,
                                 id_konten integer NOT NULL,
                                 id_komentar_parent integer,
                                 created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.komentar OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 91110)
-- Name: komentar_id_komentar_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.komentar_id_komentar_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.komentar_id_komentar_seq OWNER TO postgres;

--
-- TOC entry 5124 (class 0 OID 0)
-- Dependencies: 225
-- Name: komentar_id_komentar_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.komentar_id_komentar_seq OWNED BY public.komentar.id_komentar;


--
-- TOC entry 224 (class 1259 OID 91085)
-- Name: konten_kegiatan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.konten_kegiatan (
                                        id_konten integer NOT NULL,
                                        judul character varying(255) NOT NULL,
                                        deskripsi text NOT NULL,
                                        tanggal_kegiatan date,
                                        kategori character varying(50),
                                        lampiran character varying(255),
                                        status_publikasi character varying(20) DEFAULT 'publish'::character varying,
                                        id_organisasi integer NOT NULL,
                                        id_user_pembuat integer NOT NULL,
                                        created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.konten_kegiatan OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 91084)
-- Name: konten_kegiatan_id_konten_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.konten_kegiatan_id_konten_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.konten_kegiatan_id_konten_seq OWNER TO postgres;

--
-- TOC entry 5125 (class 0 OID 0)
-- Dependencies: 223
-- Name: konten_kegiatan_id_konten_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.konten_kegiatan_id_konten_seq OWNED BY public.konten_kegiatan.id_konten;


--
-- TOC entry 228 (class 1259 OID 91140)
-- Name: likes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.likes (
                              id_like integer NOT NULL,
                              id_user integer NOT NULL,
                              id_konten integer NOT NULL,
                              created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.likes OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 91139)
-- Name: likes_id_like_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.likes_id_like_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.likes_id_like_seq OWNER TO postgres;

--
-- TOC entry 5126 (class 0 OID 0)
-- Dependencies: 227
-- Name: likes_id_like_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.likes_id_like_seq OWNED BY public.likes.id_like;


--
-- TOC entry 222 (class 1259 OID 91061)
-- Name: organisasi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.organisasi (
                                   id_organisasi integer NOT NULL,
                                   nama_organisasi character varying(100) NOT NULL,
                                   jenis character varying(20) NOT NULL,
                                   deskripsi text,
                                   logo character varying(255),
                                   id_user_ketua integer,
                                   created_at timestamp without time zone DEFAULT now(),
                                   CONSTRAINT organisasi_jenis_check CHECK (((jenis)::text = ANY ((ARRAY['BEM'::character varying, 'UKM'::character varying, 'SC'::character varying, 'Himpunan'::character varying])::text[])))
);


ALTER TABLE public.organisasi OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 91060)
-- Name: organisasi_id_organisasi_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.organisasi_id_organisasi_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.organisasi_id_organisasi_seq OWNER TO postgres;

--
-- TOC entry 5127 (class 0 OID 0)
-- Dependencies: 221
-- Name: organisasi_id_organisasi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.organisasi_id_organisasi_seq OWNED BY public.organisasi.id_organisasi;


--
-- TOC entry 230 (class 1259 OID 91163)
-- Name: pendaftaran; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pendaftaran (
                                    id_pendaftaran integer NOT NULL,
                                    id_user integer NOT NULL,
                                    id_konten integer NOT NULL,
                                    tanggal_daftar timestamp without time zone DEFAULT now(),
                                    status_pendaftaran public.status_pendaftaran_type DEFAULT 'menunggu'::public.status_pendaftaran_type,
                                    kuota_maks integer DEFAULT 0
);


ALTER TABLE public.pendaftaran OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 91162)
-- Name: pendaftaran_id_pendaftaran_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pendaftaran_id_pendaftaran_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pendaftaran_id_pendaftaran_seq OWNER TO postgres;

--
-- TOC entry 5128 (class 0 OID 0)
-- Dependencies: 229
-- Name: pendaftaran_id_pendaftaran_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pendaftaran_id_pendaftaran_seq OWNED BY public.pendaftaran.id_pendaftaran;


--
-- TOC entry 220 (class 1259 OID 91040)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
                              id_user integer NOT NULL,
                              nama character varying(100) NOT NULL,
                              email character varying(100) NOT NULL,
                              password character varying(255) NOT NULL,
                              peran public.peran_type NOT NULL,
                              level public.level_pengurus_type DEFAULT 'biasa'::public.level_pengurus_type,
                              id_organisasi integer,
                              nim character varying(20),
                              prodi character varying(50),
                              angkatan character varying(4),
                              status_verifikasi public.status_verifikasi_type DEFAULT 'pending'::public.status_verifikasi_type,
                              verification_token character varying(64),
                              remember_token character varying(100),
                              created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 91039)
-- Name: users_id_user_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_user_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_user_seq OWNER TO postgres;

--
-- TOC entry 5129 (class 0 OID 0)
-- Dependencies: 219
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- TOC entry 4918 (class 2604 OID 91191)
-- Name: aspirasi id_aspirasi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.aspirasi ALTER COLUMN id_aspirasi SET DEFAULT nextval('public.aspirasi_id_aspirasi_seq'::regclass);


--
-- TOC entry 4910 (class 2604 OID 91114)
-- Name: komentar id_komentar; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.komentar ALTER COLUMN id_komentar SET DEFAULT nextval('public.komentar_id_komentar_seq'::regclass);


--
-- TOC entry 4907 (class 2604 OID 91088)
-- Name: konten_kegiatan id_konten; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten_kegiatan ALTER COLUMN id_konten SET DEFAULT nextval('public.konten_kegiatan_id_konten_seq'::regclass);


--
-- TOC entry 4912 (class 2604 OID 91143)
-- Name: likes id_like; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.likes ALTER COLUMN id_like SET DEFAULT nextval('public.likes_id_like_seq'::regclass);


--
-- TOC entry 4905 (class 2604 OID 91064)
-- Name: organisasi id_organisasi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisasi ALTER COLUMN id_organisasi SET DEFAULT nextval('public.organisasi_id_organisasi_seq'::regclass);


--
-- TOC entry 4914 (class 2604 OID 91166)
-- Name: pendaftaran id_pendaftaran; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pendaftaran ALTER COLUMN id_pendaftaran SET DEFAULT nextval('public.pendaftaran_id_pendaftaran_seq'::regclass);


--
-- TOC entry 4901 (class 2604 OID 91043)
-- Name: users id_user; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id_user SET DEFAULT nextval('public.users_id_user_seq'::regclass);


--
-- TOC entry 5117 (class 0 OID 91188)
-- Dependencies: 232
-- Data for Name: aspirasi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.aspirasi (id_aspirasi, isi_aspirasi, id_user, id_organisasi_tujuan, status, tanggapan, created_at) FROM stdin;
\.


--
-- TOC entry 5111 (class 0 OID 91111)
-- Dependencies: 226
-- Data for Name: komentar; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.komentar (id_komentar, isi_komentar, id_user, id_konten, id_komentar_parent, created_at) FROM stdin;
1	tes	3	2	\N	2026-06-16 08:23:37.221497
\.


--
-- TOC entry 5109 (class 0 OID 91085)
-- Dependencies: 224
-- Data for Name: konten_kegiatan; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.konten_kegiatan (id_konten, judul, deskripsi, tanggal_kegiatan, kategori, lampiran, status_publikasi, id_organisasi, id_user_pembuat, created_at) FROM stdin;
1	Dies Natalis ITH ke-4	Perayaan Dies Natalis kampus dengan berbagai lomba, seminar, dan malam puncak seni.	2026-06-25	Acara Kampus		publish	1	2	2026-06-16 07:42:40.887182
2	Sosialisasi Beasiswa 2026	Informasi lengkap beasiswa internal dan eksternal untuk mahasiswa ITH.	2026-07-02	Pendidikan		publish	1	2	2026-06-16 07:42:40.887182
3	Kongres Mahasiswa Tahunan	Pemilihan ketua BEM dan pembahasan program kerja tahunan.	2026-08-10	Acara Kampus		publish	1	2	2026-06-16 07:42:40.887182
4	Workshop Robotika Dasar	Belajar merakit dan memprogram robot sederhana menggunakan Arduino.	2026-07-10	Workshop		publish	5	5	2026-06-16 07:42:40.887182
5	Lomba Robot Line Follower	Kompetisi robot pengikut garis tingkat institut.	2026-08-20	Lomba		publish	5	5	2026-06-16 07:42:40.887182
6	Bootcamp Web Development	Pelatihan intensif membangun website modern dengan HTML, CSS, JavaScript, dan PHP.	2026-07-15	Workshop		publish	6	6	2026-06-16 07:42:40.887182
7	Hackathon ITH 2026	Kompetisi coding 24 jam membangun aplikasi inovatif.	2026-08-25	Lomba		publish	6	6	2026-06-16 07:42:40.887182
8	English Debate Competition	Kompetisi debat bahasa Inggris antar mahasiswa ITH.	2026-08-15	Lomba		publish	11	11	2026-06-16 07:42:40.887182
9	TOEFL Preparation Class	Kelas persiapan TOEFL gratis untuk mahasiswa.	2026-07-20	Pendidikan		publish	11	11	2026-06-16 07:42:40.887182
10	Turnamen Basket 3x3	Turnamen bola basket tiga lawan tiga untuk seluruh mahasiswa.	2026-07-05	Olahraga		publish	13	13	2026-06-16 07:42:40.887182
11	Futsal Championship	Kejuaraan futsal antar program studi ITH.	2026-07-18	Olahraga		publish	9	9	2026-06-16 07:42:40.887182
12	Pentas Seni Tradisional	Pagelaran seni tari dan musik tradisional oleh anggota ARATTA.	2026-08-05	Seni		publish	8	8	2026-06-16 07:42:40.887182
13	Workshop Melukis Kanvas	Belajar teknik melukis di atas kanvas untuk pemula.	2026-07-12	Workshop		publish	8	8	2026-06-16 07:42:40.887182
14	Konser Amal "Suara Hati"	Konser paduan suara untuk penggalangan dana beasiswa.	2026-08-30	Seni		publish	16	16	2026-06-16 07:42:40.887182
15	Kemah Bakti Sosial	Kegiatan bakti sosial dan perkemahan di desa binaan.	2026-07-25	Sosial		publish	21	21	2026-06-16 07:42:40.887182
16	Seminar Start-Up Mahasiswa	Kiat sukses membangun start-up dari nol bersama founder ternama.	2026-08-12	Seminar		publish	20	20	2026-06-16 07:42:40.887182
17	Kajian Ramadhan & Buka Puasa Bersama	Kajian Islam mingguan selama Ramadhan dan buka puasa gratis.	2026-06-20	Keagamaan		publish	23	23	2026-06-16 07:42:40.887182
18	Pelatihan Public Speaking	Meningkatkan kemampuan berbicara di depan umum untuk mahasiswa.	2026-07-28	Workshop		publish	22	22	2026-06-16 07:42:40.887182
19	Seminar AI & Machine Learning	Pengenalan kecerdasan buatan dan implementasinya di dunia industri.	2026-08-22	Seminar		publish	44	44	2026-06-16 07:42:40.887182
\.


--
-- TOC entry 5113 (class 0 OID 91140)
-- Dependencies: 228
-- Data for Name: likes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.likes (id_like, id_user, id_konten, created_at) FROM stdin;
12	3	2	2026-06-16 08:41:02.299151
\.


--
-- TOC entry 5107 (class 0 OID 91061)
-- Dependencies: 222
-- Data for Name: organisasi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.organisasi (id_organisasi, nama_organisasi, jenis, deskripsi, logo, id_user_ketua, created_at) FROM stdin;
3	Himpunan Ilmu Komputer	Himpunan	Himpunan Mahasiswa Program Studi Ilmu Komputer	\N	\N	2026-06-15 14:47:10.061814
1	BEM ITH	BEM	Badan Eksekutif Mahasiswa Institut Teknologi Habibie	\N	2	2026-06-15 14:47:10.061814
2	UKM Robotik	UKM	Unit Kegiatan Mahasiswa Robotika	\N	4	2026-06-15 14:47:10.061814
5	UKM Robotika	UKM	Unit Kegiatan Mahasiswa Robotika	\N	5	2026-06-16 07:34:36.022486
6	Habibie Coding Club	SC	Klub pemrograman dan pengembangan perangkat lunak	\N	6	2026-06-16 07:34:36.022486
7	PKM Center	UKM	Pusat Pengembangan Kreativitas Mahasiswa (PKM)	\N	7	2026-06-16 07:34:36.022486
8	ARATTA	UKM	Organisasi Kesenian Mahasiswa ITH	\N	8	2026-06-16 07:34:36.022486
9	Futsal ITH	UKM	Unit Kegiatan Mahasiswa Futsal	\N	9	2026-06-16 07:34:36.022486
10	Catur Club ITH	UKM	Klub Catur Institut Teknologi Habibie	\N	10	2026-06-16 07:34:36.022486
11	English Club ITH	UKM	Klub Bahasa Inggris Mahasiswa	\N	11	2026-06-16 07:34:36.022486
12	Aljazari	UKM	Klub Robotika dan Mekatronika Aljazari	\N	12	2026-06-16 07:34:36.022486
13	Basket ITH	UKM	Unit Kegiatan Mahasiswa Bola Basket	\N	13	2026-06-16 07:34:36.022486
14	Voli ITH	UKM	Unit Kegiatan Mahasiswa Bola Voli	\N	14	2026-06-16 07:34:36.022486
15	Bulu Tangkis ITH	UKM	Unit Kegiatan Mahasiswa Bulu Tangkis	\N	15	2026-06-16 07:34:36.022486
16	Paduan Suara Mahasiswa ITH	UKM	Unit Kegiatan Mahasiswa Paduan Suara	\N	16	2026-06-16 07:34:36.022486
17	Teater ITH	UKM	Unit Kegiatan Mahasiswa Teater	\N	17	2026-06-16 07:34:36.022486
18	Fotografi ITH	UKM	Klub Fotografi Mahasiswa	\N	18	2026-06-16 07:34:36.022486
19	Jurnalistik ITH	UKM	Klub Jurnalistik dan Media Kampus	\N	19	2026-06-16 07:34:36.022486
20	Kewirausahaan ITH	UKM	Unit Kegiatan Mahasiswa Kewirausahaan	\N	20	2026-06-16 07:34:36.022486
21	Pramuka ITH	UKM	Gugus Depan Pramuka Institut Teknologi Habibie	\N	21	2026-06-16 07:34:36.022486
22	Debat ITH	UKM	Klub Debat Bahasa Indonesia dan Inggris	\N	22	2026-06-16 07:34:36.022486
23	Kajian Islam ITH	UKM	Unit Kegiatan Mahasiswa Kerohanian Islam	\N	23	2026-06-16 07:34:36.022486
24	Himpunan Mahasiswa Teknologi Produksi dan Industri	Himpunan	Himpunan Mahasiswa Jurusan Teknologi Produksi dan Industri	\N	24	2026-06-16 07:34:36.022486
25	Himpunan Mahasiswa Sains	Himpunan	Himpunan Mahasiswa Jurusan Sains	\N	25	2026-06-16 07:34:36.022486
26	Himpunan Mahasiswa Matematika	Himpunan	Himpunan Mahasiswa Program Studi S1 Matematika / Sains Matematika	\N	26	2026-06-16 07:34:36.022486
27	Himpunan Mahasiswa Sistem Informasi	Himpunan	Himpunan Mahasiswa Program Studi S1 Sistem Informasi	\N	27	2026-06-16 07:34:36.022486
28	Himpunan Mahasiswa Aktuaria	Himpunan	Himpunan Mahasiswa Program Studi S1 Sains Aktuaria	\N	28	2026-06-16 07:34:36.022486
29	Himpunan Mahasiswa Sains Data	Himpunan	Himpunan Mahasiswa Program Studi S1 Sains Data	\N	29	2026-06-16 07:34:36.022486
30	Himpunan Mahasiswa Bisnis Digital	Himpunan	Himpunan Mahasiswa Program Studi S1 Bisnis Digital	\N	30	2026-06-16 07:34:36.022486
31	Himpunan Mahasiswa Bioteknologi	Himpunan	Himpunan Mahasiswa Program Studi S1 Bioteknologi	\N	31	2026-06-16 07:34:36.022486
32	Himpunan Mahasiswa Teknik Robotika dan AI	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Robotika dan Kecerdasan Buatan	\N	32	2026-06-16 07:34:36.022486
33	Himpunan Mahasiswa Teknologi Pangan	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknologi Pangan	\N	33	2026-06-16 07:34:36.022486
34	Himpunan Mahasiswa Teknik Metalurgi	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Metalurgi	\N	34	2026-06-16 07:34:36.022486
35	Himpunan Mahasiswa Teknik Sistem Energi	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Sistem Energi	\N	35	2026-06-16 07:34:36.022486
36	Himpunan Mahasiswa Teknik Elektro	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Elektro	\N	36	2026-06-16 07:34:36.022486
37	Himpunan Mahasiswa Teknik Industri	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Industri	\N	37	2026-06-16 07:34:36.022486
38	Himpunan Mahasiswa Teknik Mesin	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Mesin	\N	38	2026-06-16 07:34:36.022486
39	Himpunan Mahasiswa Teknik Sipil	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Sipil	\N	39	2026-06-16 07:34:36.022486
40	Himpunan Mahasiswa Arsitektur	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Arsitektur / Arsitektur	\N	40	2026-06-16 07:34:36.022486
41	Himpunan Mahasiswa Teknik Perkapalan	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Perkapalan	\N	41	2026-06-16 07:34:36.022486
42	Himpunan Mahasiswa Teknik Lingkungan	Himpunan	Himpunan Mahasiswa Program Studi S1 Teknik Lingkungan	\N	42	2026-06-16 07:34:36.022486
43	Himpunan Mahasiswa PWK	Himpunan	Himpunan Mahasiswa Program Studi S1 Perencanaan Wilayah dan Kota	\N	43	2026-06-16 07:34:36.022486
44	Himpunan Mahasiswa Ilmu Komputer	Himpunan	Himpunan Mahasiswa Program Studi S1 Ilmu Komputer / Informatika	\N	44	2026-06-16 07:34:36.022486
\.


--
-- TOC entry 5115 (class 0 OID 91163)
-- Dependencies: 230
-- Data for Name: pendaftaran; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pendaftaran (id_pendaftaran, id_user, id_konten, tanggal_daftar, status_pendaftaran, kuota_maks) FROM stdin;
\.


--
-- TOC entry 5105 (class 0 OID 91040)
-- Dependencies: 220
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id_user, nama, email, password, peran, level, id_organisasi, nim, prodi, angkatan, status_verifikasi, verification_token, remember_token, created_at) FROM stdin;
2	Budi Santoso	budi@bem.ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	1	\N	\N	\N	verified	\N	\N	2026-06-15 14:47:10.061814
3	Andi Prasetyo	andi@mahasiswa.ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011001	Ilmu Komputer	2024	verified	\N	\N	2026-06-15 14:47:10.061814
45	Rina Melati	rina.melati@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011002	Sistem Informasi	2024	verified	\N	\N	2026-06-16 07:39:09.72185
46	Dimas Saputra	dimas.saputra@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011003	Bisnis Digital	2024	verified	\N	\N	2026-06-16 07:39:09.72185
47	Siska Aulia	siska.aulia@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011004	Sains Data	2024	verified	\N	\N	2026-06-16 07:39:09.72185
48	Arif Rahman	arif.rahman@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011005	Matematika	2024	verified	\N	\N	2026-06-16 07:39:09.72185
1	Administrator	admin@ith.ac.id	$2y$10$qu8S96J2sRC6WfI3Nb2VsupZ1.X.h05ORFfiBOed8zwNYRAGuDz6.	admin	biasa	\N	\N	\N	\N	verified	\N	\N	2026-06-15 14:47:10.061814
4	Dewi Robotik	dewi.robotik@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	2	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
5	Eko Robotika	eko.robotika@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	5	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
6	Fajar Coding	fajar.coding@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	6	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
7	Gita PKM	gita.pkm@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	7	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
8	Hana Seni	hana.seni@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	8	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
9	Irfan Futsal	irfan.futsal@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	9	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
10	Joko Catur	joko.catur@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	10	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
11	Kiki English	kiki.english@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	11	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
12	Lutfi Aljazari	lutfi.aljazari@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	12	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
13	Mira Basket	mira.basket@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	13	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
14	Nando Voli	nando.voli@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	14	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
15	Oki Bulutangkis	oki.bulutangkis@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	15	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
16	Putri Padus	putri.padus@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	16	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
17	Qori Teater	qori.teater@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	17	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
18	Rian Foto	rian.foto@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	18	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
19	Sari Jurnal	sari.jurnal@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	19	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
20	Tio Wirausaha	tio.wirausaha@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	20	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
21	Umar Pramuka	umar.pramuka@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	21	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
22	Vina Debat	vina.debat@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	22	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
23	Wahyu Islam	wahyu.islam@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	23	\N	\N	\N	verified	\N	\N	2026-06-16 07:36:48.005614
24	Ketua HMTI	ketua.hmti@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	24	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
25	Ketua HIMSAINS	ketua.himsains@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	25	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
26	Ketua Matematika	ketua.matematika@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	26	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
27	Ketua SI	ketua.si@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	27	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
28	Ketua Aktuaria	ketua.aktuaria@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	28	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
29	Ketua Sains Data	ketua.sainsdata@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	29	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
30	Ketua Bisnis Digital	ketua.bisnisdigital@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	30	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
31	Ketua Bioteknologi	ketua.biotek@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	31	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
32	Ketua Robotika AI	ketua.robotikaai@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	32	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
33	Ketua Tekpang	ketua.tekpang@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	33	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
34	Ketua Metalurgi	ketua.metalurgi@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	34	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
35	Ketua Energi	ketua.energi@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	35	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
36	Ketua Elektro	ketua.elektro@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	36	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
37	Ketua Industri	ketua.industri@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	37	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
38	Ketua Mesin	ketua.mesin@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	38	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
39	Ketua Sipil	ketua.sipil@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	39	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
40	Ketua Arsitektur	ketua.arsitektur@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	40	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
41	Ketua Perkapalan	ketua.perkapalan@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	41	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
42	Ketua Lingkungan	ketua.lingkungan@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	42	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
43	Ketua PWK	ketua.pwk@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	43	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
44	Ketua Ilkom	ketua.ilkom@ith.ac.id	$2y$10$t6Eliq.1g540dJCNHh8zOeq2eXTPDbNLdgsMnqkvrpnhlk.gXSMwG	pengurus	inti	44	\N	\N	\N	verified	\N	\N	2026-06-16 07:37:50.805391
49	Dewi Lestari	dewi.lestari@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011006	Aktuaria	2024	verified	\N	\N	2026-06-16 07:39:09.72185
50	Bayu Prasetya	bayu.prasetya@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011007	Bioteknologi	2024	verified	\N	\N	2026-06-16 07:39:09.72185
51	Cindy Permata	cindy.permata@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011008	Teknik Robotika dan Kecerdasan Buatan	2024	verified	\N	\N	2026-06-16 07:39:09.72185
52	Eko Prasetyo	eko.prasetyo@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011009	Teknologi Pangan	2024	verified	\N	\N	2026-06-16 07:39:09.72185
53	Fitriani	fitriani@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011010	Teknik Metalurgi	2024	verified	\N	\N	2026-06-16 07:39:09.72185
54	Gilang Ramadhan	gilang.ramadhan@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011011	Teknik Sistem Energi	2024	verified	\N	\N	2026-06-16 07:39:09.72185
55	Hana Amalia	hana.amalia@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011012	Teknik Elektro	2024	verified	\N	\N	2026-06-16 07:39:09.72185
56	Irfan Maulana	irfan.maulana@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011013	Teknik Industri	2024	verified	\N	\N	2026-06-16 07:39:09.72185
57	Joko Widodo	joko.widodo@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011014	Teknik Mesin	2024	verified	\N	\N	2026-06-16 07:39:09.72185
58	Kartika Sari	kartika.sari@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011015	Teknik Sipil	2024	verified	\N	\N	2026-06-16 07:39:09.72185
59	Lutfi Hakim	lutfi.hakim@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011016	Arsitektur	2024	verified	\N	\N	2026-06-16 07:39:09.72185
60	Mega Utami	mega.utami@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011017	Teknik Perkapalan	2024	verified	\N	\N	2026-06-16 07:39:09.72185
61	Nanda Pratama	nanda.pratama@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011018	Teknik Lingkungan	2024	verified	\N	\N	2026-06-16 07:39:09.72185
62	Olivia Putri	olivia.putri@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011019	PWK	2024	verified	\N	\N	2026-06-16 07:39:09.72185
63	Pandu Winata	pandu.winata@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	241011020	Ilmu Komputer	2024	verified	\N	\N	2026-06-16 07:39:09.72185
64	Qonita Aulia	qonita.aulia@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011001	Sains Data	2025	verified	\N	\N	2026-06-16 07:39:09.72185
65	Rudi Hartono	rudi.hartono@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011002	Teknik Mesin	2025	verified	\N	\N	2026-06-16 07:39:09.72185
66	Santi Oktaviani	santi.oktaviani@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011003	Bioteknologi	2025	verified	\N	\N	2026-06-16 07:39:09.72185
67	Teguh Setiawan	teguh.setiawan@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011004	Teknik Elektro	2025	verified	\N	\N	2026-06-16 07:39:09.72185
68	Uswatun Hasanah	uswatun.hasanah@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011005	Sistem Informasi	2025	verified	\N	\N	2026-06-16 07:39:09.72185
69	Vera Andriani	vera.andriani@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011006	Matematika	2025	verified	\N	\N	2026-06-16 07:39:09.72185
70	Wahyu Setiawan	wahyu.setiawan@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011007	Teknik Industri	2025	verified	\N	\N	2026-06-16 07:39:09.72185
71	Xena Putri	xena.putri@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011008	Teknik Sipil	2025	verified	\N	\N	2026-06-16 07:39:09.72185
72	Yusuf Kurniawan	yusuf.kurniawan@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011009	Ilmu Komputer	2025	verified	\N	\N	2026-06-16 07:39:09.72185
73	Zahra Amalia	zahra.amalia@ith.ac.id	$2y$10$qPJV5xk3fITtOFFsrszLK.Jy3B2xHHoBLDt9AigLJmD0mn8c0fw7G	mahasiswa	biasa	\N	251011010	Arsitektur	2025	verified	\N	\N	2026-06-16 07:39:09.72185
\.


--
-- TOC entry 5130 (class 0 OID 0)
-- Dependencies: 231
-- Name: aspirasi_id_aspirasi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.aspirasi_id_aspirasi_seq', 1, false);


--
-- TOC entry 5131 (class 0 OID 0)
-- Dependencies: 225
-- Name: komentar_id_komentar_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.komentar_id_komentar_seq', 1, true);


--
-- TOC entry 5132 (class 0 OID 0)
-- Dependencies: 223
-- Name: konten_kegiatan_id_konten_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.konten_kegiatan_id_konten_seq', 19, true);


--
-- TOC entry 5133 (class 0 OID 0)
-- Dependencies: 227
-- Name: likes_id_like_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.likes_id_like_seq', 12, true);


--
-- TOC entry 5134 (class 0 OID 0)
-- Dependencies: 221
-- Name: organisasi_id_organisasi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.organisasi_id_organisasi_seq', 44, true);


--
-- TOC entry 5135 (class 0 OID 0)
-- Dependencies: 229
-- Name: pendaftaran_id_pendaftaran_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pendaftaran_id_pendaftaran_seq', 1, false);


--
-- TOC entry 5136 (class 0 OID 0)
-- Dependencies: 219
-- Name: users_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_user_seq', 73, true);


--
-- TOC entry 4943 (class 2606 OID 91200)
-- Name: aspirasi aspirasi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.aspirasi
    ADD CONSTRAINT aspirasi_pkey PRIMARY KEY (id_aspirasi);


--
-- TOC entry 4933 (class 2606 OID 91123)
-- Name: komentar komentar_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.komentar
    ADD CONSTRAINT komentar_pkey PRIMARY KEY (id_komentar);


--
-- TOC entry 4931 (class 2606 OID 91099)
-- Name: konten_kegiatan konten_kegiatan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten_kegiatan
    ADD CONSTRAINT konten_kegiatan_pkey PRIMARY KEY (id_konten);


--
-- TOC entry 4935 (class 2606 OID 91151)
-- Name: likes likes_id_user_id_konten_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.likes
    ADD CONSTRAINT likes_id_user_id_konten_key UNIQUE (id_user, id_konten);


--
-- TOC entry 4937 (class 2606 OID 91149)
-- Name: likes likes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.likes
    ADD CONSTRAINT likes_pkey PRIMARY KEY (id_like);


--
-- TOC entry 4927 (class 2606 OID 91073)
-- Name: organisasi organisasi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisasi
    ADD CONSTRAINT organisasi_pkey PRIMARY KEY (id_organisasi);


--
-- TOC entry 4939 (class 2606 OID 91176)
-- Name: pendaftaran pendaftaran_id_user_id_konten_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pendaftaran
    ADD CONSTRAINT pendaftaran_id_user_id_konten_key UNIQUE (id_user, id_konten);


--
-- TOC entry 4941 (class 2606 OID 91174)
-- Name: pendaftaran pendaftaran_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pendaftaran
    ADD CONSTRAINT pendaftaran_pkey PRIMARY KEY (id_pendaftaran);


--
-- TOC entry 4929 (class 2606 OID 98850)
-- Name: organisasi uq_organisasi_nama_jenis; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisasi
    ADD CONSTRAINT uq_organisasi_nama_jenis UNIQUE (nama_organisasi, jenis);


--
-- TOC entry 4923 (class 2606 OID 91057)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 4925 (class 2606 OID 91055)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id_user);


--
-- TOC entry 4955 (class 2606 OID 91201)
-- Name: aspirasi aspirasi_id_organisasi_tujuan_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.aspirasi
    ADD CONSTRAINT aspirasi_id_organisasi_tujuan_fkey FOREIGN KEY (id_organisasi_tujuan) REFERENCES public.organisasi(id_organisasi) ON DELETE CASCADE;


--
-- TOC entry 4956 (class 2606 OID 91206)
-- Name: aspirasi aspirasi_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.aspirasi
    ADD CONSTRAINT aspirasi_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 4944 (class 2606 OID 91079)
-- Name: users fk_users_organisasi; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT fk_users_organisasi FOREIGN KEY (id_organisasi) REFERENCES public.organisasi(id_organisasi) ON DELETE SET NULL;


--
-- TOC entry 4948 (class 2606 OID 91134)
-- Name: komentar komentar_id_komentar_parent_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.komentar
    ADD CONSTRAINT komentar_id_komentar_parent_fkey FOREIGN KEY (id_komentar_parent) REFERENCES public.komentar(id_komentar) ON DELETE CASCADE;


--
-- TOC entry 4949 (class 2606 OID 91129)
-- Name: komentar komentar_id_konten_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.komentar
    ADD CONSTRAINT komentar_id_konten_fkey FOREIGN KEY (id_konten) REFERENCES public.konten_kegiatan(id_konten) ON DELETE CASCADE;


--
-- TOC entry 4950 (class 2606 OID 91124)
-- Name: komentar komentar_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.komentar
    ADD CONSTRAINT komentar_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 4946 (class 2606 OID 91100)
-- Name: konten_kegiatan konten_kegiatan_id_organisasi_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten_kegiatan
    ADD CONSTRAINT konten_kegiatan_id_organisasi_fkey FOREIGN KEY (id_organisasi) REFERENCES public.organisasi(id_organisasi) ON DELETE CASCADE;


--
-- TOC entry 4947 (class 2606 OID 91105)
-- Name: konten_kegiatan konten_kegiatan_id_user_pembuat_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.konten_kegiatan
    ADD CONSTRAINT konten_kegiatan_id_user_pembuat_fkey FOREIGN KEY (id_user_pembuat) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 4951 (class 2606 OID 91157)
-- Name: likes likes_id_konten_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.likes
    ADD CONSTRAINT likes_id_konten_fkey FOREIGN KEY (id_konten) REFERENCES public.konten_kegiatan(id_konten) ON DELETE CASCADE;


--
-- TOC entry 4952 (class 2606 OID 91152)
-- Name: likes likes_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.likes
    ADD CONSTRAINT likes_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 4945 (class 2606 OID 91074)
-- Name: organisasi organisasi_id_user_ketua_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organisasi
    ADD CONSTRAINT organisasi_id_user_ketua_fkey FOREIGN KEY (id_user_ketua) REFERENCES public.users(id_user) ON DELETE SET NULL;


--
-- TOC entry 4953 (class 2606 OID 91182)
-- Name: pendaftaran pendaftaran_id_konten_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pendaftaran
    ADD CONSTRAINT pendaftaran_id_konten_fkey FOREIGN KEY (id_konten) REFERENCES public.konten_kegiatan(id_konten) ON DELETE CASCADE;


--
-- TOC entry 4954 (class 2606 OID 91177)
-- Name: pendaftaran pendaftaran_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pendaftaran
    ADD CONSTRAINT pendaftaran_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


-- Completed on 2026-06-16 09:48:30

--
-- PostgreSQL database dump complete
--

\unrestrict edm66TTprf8Afcn9q4OnIhgLFNNxVHDASjXejMnh0AvlKAZBfJONtEJPxRRVGdQ

