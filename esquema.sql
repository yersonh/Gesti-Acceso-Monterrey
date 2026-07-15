--
-- PostgreSQL database dump
--

-- Dumped from database version 17.7 (Debian 17.7-3.pgdg13+1)
-- Dumped by pg_dump version 17.10

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
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;

--
-- Name: actualizar_updated_at(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.actualizar_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

BEGIN

    NEW.updated_at := NOW();

    RETURN NEW;

END;

$$;

--
-- Name: calcular_hora_fin(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.calcular_hora_fin() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

DECLARE

    intervalo integer;

BEGIN

    SELECT intervalo_min INTO intervalo FROM configuracion_sistema LIMIT 1;

    NEW.hora_fin := NEW.hora_inicio + (intervalo || ' minutes')::INTERVAL;

    RETURN NEW;

END;

$$;

--
-- Name: trg_ciudadanos_updated_at_fn(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.trg_ciudadanos_updated_at_fn() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

BEGIN

    NEW.updated_at = NOW();

    RETURN NEW;

END;

$$;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: auditoria_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.auditoria_logs (
    id integer NOT NULL,
    usuario_id integer,
    usuario_nombre character varying(200),
    usuario_rol character varying(50),
    accion character varying(100) NOT NULL,
    descripcion text,
    tabla_afectada character varying(100),
    registro_id integer,
    ip character varying(45),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

--
-- Name: auditoria_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.auditoria_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: auditoria_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.auditoria_logs_id_seq OWNED BY public.auditoria_logs.id;

--
-- Name: citas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.citas (
    id_cita integer NOT NULL,
    funcionario_id integer NOT NULL,
    dependencia_id integer NOT NULL,
    fecha date NOT NULL,
    hora_inicio time without time zone NOT NULL,
    hora_fin time without time zone NOT NULL,
    motivo text NOT NULL,
    estado character varying(50) DEFAULT 'pendiente'::character varying NOT NULL,
    gestionado_por integer,
    nota_gestion text,
    cancelado_por_ciudadano boolean DEFAULT false NOT NULL,
    motivo_cancelacion text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    hora_ingreso timestamp without time zone,
    hora_salida timestamp without time zone,
    ciudadano_id integer NOT NULL,
    token_expira timestamp without time zone,
    token_respuesta character varying(64),
    motivo_reprogramacion text,
    hora_propuesta time without time zone,
    fecha_propuesta date,
    CONSTRAINT chk_estado CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'confirmada'::character varying, 'cancelada'::character varying, 'completada'::character varying, 'no_asistio'::character varying, 'en_curso'::character varying, 'finalizada'::character varying, 'propuesta_reprogramacion'::character varying, 'contrapropuesta_ciudadano'::character varying])::text[])))
);

--
-- Name: citas_id_cita_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.citas_id_cita_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: citas_id_cita_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.citas_id_cita_seq OWNED BY public.citas.id_cita;

--
-- Name: ciudadanos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ciudadanos (
    id_ciudadano integer NOT NULL,
    nombres character varying(100) NOT NULL,
    apellidos character varying(100) NOT NULL,
    tipo_identificacion character varying(3) NOT NULL,
    numero_identificacion character varying(20) NOT NULL,
    telefono character varying(20),
    email character varying(150),
    usuario_id integer,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    direccion character varying(250),
    proveniencia character varying(150),
    whatsapp character varying(20),
    CONSTRAINT chk_tipo_id_ciudadano CHECK (((tipo_identificacion)::text = ANY ((ARRAY['CC'::character varying, 'TI'::character varying, 'CE'::character varying, 'PA'::character varying, 'NIT'::character varying, 'RC'::character varying])::text[])))
);

--
-- Name: ciudadanos_id_ciudadano_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.ciudadanos_id_ciudadano_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: ciudadanos_id_ciudadano_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.ciudadanos_id_ciudadano_seq OWNED BY public.ciudadanos.id_ciudadano;

--
-- Name: configuracion_sistema; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.configuracion_sistema (
    id integer NOT NULL,
    manana_inicio time without time zone DEFAULT '07:00:00'::time without time zone NOT NULL,
    manana_fin time without time zone DEFAULT '12:00:00'::time without time zone NOT NULL,
    tarde_inicio time without time zone DEFAULT '14:00:00'::time without time zone NOT NULL,
    tarde_fin time without time zone DEFAULT '17:00:00'::time without time zone NOT NULL,
    intervalo_min integer DEFAULT 15 NOT NULL,
    lunes boolean DEFAULT true NOT NULL,
    martes boolean DEFAULT true NOT NULL,
    miercoles boolean DEFAULT true NOT NULL,
    jueves boolean DEFAULT true NOT NULL,
    viernes boolean DEFAULT true NOT NULL,
    sabado boolean DEFAULT false NOT NULL,
    domingo boolean DEFAULT false NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

--
-- Name: configuracion_sistema_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.configuracion_sistema_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: configuracion_sistema_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.configuracion_sistema_id_seq OWNED BY public.configuracion_sistema.id;

--
-- Name: dependencias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dependencias (
    id_dependencia integer NOT NULL,
    nombre character varying(150) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

--
-- Name: dependencias_id_dependencia_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.dependencias_id_dependencia_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: dependencias_id_dependencia_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.dependencias_id_dependencia_seq OWNED BY public.dependencias.id_dependencia;

--
-- Name: dias_festivos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.dias_festivos (
    id integer NOT NULL,
    fecha date NOT NULL,
    descripcion character varying(100) NOT NULL,
    recurrente boolean DEFAULT false,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT now()
);

--
-- Name: dias_festivos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.dias_festivos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: dias_festivos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.dias_festivos_id_seq OWNED BY public.dias_festivos.id;

--
-- Name: funcionario_dependencia; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.funcionario_dependencia (
    id integer NOT NULL,
    funcionario_id integer NOT NULL,
    dependencia_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

--
-- Name: funcionario_dependencia_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.funcionario_dependencia_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: funcionario_dependencia_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.funcionario_dependencia_id_seq OWNED BY public.funcionario_dependencia.id;

--
-- Name: funcionarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.funcionarios (
    id_funcionario integer NOT NULL,
    nombres character varying(100) NOT NULL,
    apellidos character varying(100) NOT NULL,
    tipo_identificacion character varying(3) NOT NULL,
    numero_identificacion character varying(20) NOT NULL,
    telefono character varying(20),
    email character varying(150),
    cargo character varying(150) NOT NULL,
    usuario_id integer,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_tipo_id_funcionario CHECK (((tipo_identificacion)::text = ANY ((ARRAY['CC'::character varying, 'TI'::character varying, 'CE'::character varying, 'PA'::character varying, 'NIT'::character varying, 'RC'::character varying])::text[])))
);

--
-- Name: funcionarios_id_funcionario_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.funcionarios_id_funcionario_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: funcionarios_id_funcionario_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.funcionarios_id_funcionario_seq OWNED BY public.funcionarios.id_funcionario;

--
-- Name: horarios_bloqueados; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.horarios_bloqueados (
    id_bloqueo integer NOT NULL,
    funcionario_id integer NOT NULL,
    fecha date NOT NULL,
    hora_inicio time without time zone NOT NULL,
    hora_fin time without time zone NOT NULL,
    motivo text NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);

--
-- Name: horarios_bloqueados_id_bloqueo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.horarios_bloqueados_id_bloqueo_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: horarios_bloqueados_id_bloqueo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.horarios_bloqueados_id_bloqueo_seq OWNED BY public.horarios_bloqueados.id_bloqueo;

--
-- Name: login_attempts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.login_attempts (
    id integer NOT NULL,
    ip character varying(45) NOT NULL,
    email character varying(255),
    created_at timestamp with time zone DEFAULT now() NOT NULL
);

--
-- Name: login_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.login_attempts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: login_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.login_attempts_id_seq OWNED BY public.login_attempts.id;

--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_resets (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    token character varying(255) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    used boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

--
-- Name: password_resets_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.password_resets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: password_resets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.password_resets_id_seq OWNED BY public.password_resets.id;

--
-- Name: personal; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal (
    id_personal integer NOT NULL,
    nombres character varying(100) NOT NULL,
    apellidos character varying(100) NOT NULL,
    tipo_identificacion character varying(3) NOT NULL,
    numero_identificacion character varying(20) NOT NULL,
    telefono character varying(20),
    email character varying(150),
    cargo character varying(150),
    usuario_id integer,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT chk_tipo_id_personal CHECK (((tipo_identificacion)::text = ANY ((ARRAY['CC'::character varying, 'TI'::character varying, 'CE'::character varying, 'PA'::character varying, 'NIT'::character varying, 'RC'::character varying])::text[])))
);

--
-- Name: personal_id_personal_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_id_personal_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: personal_id_personal_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_id_personal_seq OWNED BY public.personal.id_personal;

--
-- Name: php_sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.php_sessions (
    id character varying(128) NOT NULL,
    data text DEFAULT ''::text NOT NULL,
    expires_at timestamp with time zone NOT NULL
);

--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuarios (
    id_usuario integer NOT NULL,
    email character varying(150) NOT NULL,
    password_hash character varying(255),
    rol character varying(20) DEFAULT 'ciudadano'::character varying NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    fecha_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    password_temporal boolean DEFAULT false NOT NULL,
    password_temporal_expires_at timestamp with time zone,
    last_login_at timestamp with time zone,
    CONSTRAINT chk_rol CHECK (((rol)::text = ANY (ARRAY[('Superadmin'::character varying)::text, ('Administrador'::character varying)::text, ('Recepcionista'::character varying)::text, ('Funcionario'::character varying)::text, ('Ciudadano'::character varying)::text])))
);

--
-- Name: usuarios_id_usuario_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuarios_id_usuario_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: usuarios_id_usuario_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuarios_id_usuario_seq OWNED BY public.usuarios.id_usuario;

--
-- Name: valoraciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.valoraciones (
    id_valoracion integer NOT NULL,
    visita_id integer,
    tipo_visita character varying(10),
    token character varying(64) NOT NULL,
    estrellas smallint,
    solucionado boolean,
    comentario text,
    respondido boolean DEFAULT false NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT valoraciones_estrellas_check CHECK (((estrellas >= 1) AND (estrellas <= 5)))
);

--
-- Name: valoraciones_id_valoracion_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.valoraciones_id_valoracion_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: valoraciones_id_valoracion_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.valoraciones_id_valoracion_seq OWNED BY public.valoraciones.id_valoracion;

--
-- Name: visitas_espontaneas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.visitas_espontaneas (
    id_visita integer NOT NULL,
    funcionario_id integer,
    dependencia_id integer NOT NULL,
    motivo text NOT NULL,
    hora_ingreso timestamp without time zone DEFAULT now() NOT NULL,
    hora_salida timestamp without time zone,
    estado character varying(20) DEFAULT 'en_curso'::character varying NOT NULL,
    atendido_por integer,
    notas text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    ciudadano_id integer NOT NULL,
    CONSTRAINT visitas_espontaneas_estado_check CHECK (((estado)::text = ANY ((ARRAY['en_curso'::character varying, 'finalizada'::character varying])::text[])))
);

--
-- Name: visitas_espontaneas_id_visita_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.visitas_espontaneas_id_visita_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: visitas_espontaneas_id_visita_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.visitas_espontaneas_id_visita_seq OWNED BY public.visitas_espontaneas.id_visita;

--
-- Name: vista_registro_visitas; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.vista_registro_visitas AS
 SELECT 'cita'::text AS tipo_registro,
    c.id_cita AS id,
    ci.nombres AS nombres_ciudadano,
    ci.apellidos AS apellidos_ciudadano,
    ci.tipo_identificacion,
    ci.numero_identificacion,
    ci.telefono,
    ci.email,
    f.id_funcionario,
    f.nombres AS nombres_funcionario,
    f.apellidos AS apellidos_funcionario,
    d.nombre AS dependencia,
    c.motivo,
    c.fecha AS fecha_cita,
    c.hora_inicio,
    c.hora_fin,
    c.estado,
    c.hora_ingreso,
    c.hora_salida,
    NULL::integer AS atendido_por,
    NULL::text AS nombre_atendido_por,
    c.created_at,
    c.updated_at,
    (((c.fecha)::text || ' '::text) || (c.hora_inicio)::text) AS orden
   FROM (((public.citas c
     JOIN public.ciudadanos ci ON ((ci.id_ciudadano = c.ciudadano_id)))
     JOIN public.funcionarios f ON ((f.id_funcionario = c.funcionario_id)))
     JOIN public.dependencias d ON ((d.id_dependencia = c.dependencia_id)))
  WHERE (((c.estado)::text = ANY (ARRAY['pendiente'::text, 'confirmada'::text, 'en_curso'::text, 'finalizada'::text, 'no_asistio'::text, 'propuesta_reprogramacion'::text, 'contrapropuesta_ciudadano'::text])) AND (c.fecha = CURRENT_DATE))
UNION ALL
 SELECT 'espontanea'::text AS tipo_registro,
    ve.id_visita AS id,
    ci.nombres AS nombres_ciudadano,
    ci.apellidos AS apellidos_ciudadano,
    ci.tipo_identificacion,
    ci.numero_identificacion,
    ci.telefono,
    ci.email,
    ve.funcionario_id AS id_funcionario,
    f.nombres AS nombres_funcionario,
    f.apellidos AS apellidos_funcionario,
    d.nombre AS dependencia,
    ve.motivo,
    NULL::date AS fecha_cita,
    NULL::time without time zone AS hora_inicio,
    NULL::time without time zone AS hora_fin,
    ve.estado,
    ve.hora_ingreso,
    ve.hora_salida,
    ve.atendido_por,
    COALESCE((((p.nombres)::text || ' '::text) || (p.apellidos)::text), (u.email)::text) AS nombre_atendido_por,
    ve.created_at,
    ve.updated_at,
    (ve.hora_ingreso)::text AS orden
   FROM (((((public.visitas_espontaneas ve
     JOIN public.ciudadanos ci ON ((ci.id_ciudadano = ve.ciudadano_id)))
     LEFT JOIN public.funcionarios f ON ((f.id_funcionario = ve.funcionario_id)))
     JOIN public.dependencias d ON ((d.id_dependencia = ve.dependencia_id)))
     LEFT JOIN public.usuarios u ON ((u.id_usuario = ve.atendido_por)))
     LEFT JOIN public.personal p ON ((p.usuario_id = ve.atendido_por)))
  WHERE (date(ve.hora_ingreso) = CURRENT_DATE)
  ORDER BY 24;

--
-- Name: vista_reportes_visitas; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.vista_reportes_visitas AS
 SELECT 'cita'::text AS tipo_registro,
    c.id_cita AS id,
    ci.nombres AS nombres_ciudadano,
    ci.apellidos AS apellidos_ciudadano,
    ci.tipo_identificacion,
    ci.numero_identificacion,
    ci.telefono,
    ci.email,
    f.id_funcionario,
    f.nombres AS nombres_funcionario,
    f.apellidos AS apellidos_funcionario,
    d.nombre AS dependencia,
    d.id_dependencia,
    c.motivo,
    c.fecha AS fecha_cita,
    c.hora_inicio,
    c.hora_fin,
    c.estado,
    c.hora_ingreso,
    c.hora_salida,
    c.ciudadano_id,
    c.cancelado_por_ciudadano,
    c.motivo_cancelacion,
    c.motivo_reprogramacion,
    c.fecha_propuesta,
    c.hora_propuesta,
    c.created_at,
    c.updated_at
   FROM (((public.citas c
     JOIN public.ciudadanos ci ON ((ci.id_ciudadano = c.ciudadano_id)))
     JOIN public.funcionarios f ON ((f.id_funcionario = c.funcionario_id)))
     JOIN public.dependencias d ON ((d.id_dependencia = c.dependencia_id)))
UNION ALL
 SELECT 'espontanea'::text AS tipo_registro,
    ve.id_visita AS id,
    ci.nombres AS nombres_ciudadano,
    ci.apellidos AS apellidos_ciudadano,
    ci.tipo_identificacion,
    ci.numero_identificacion,
    ci.telefono,
    ci.email,
    f.id_funcionario,
    f.nombres AS nombres_funcionario,
    f.apellidos AS apellidos_funcionario,
    d.nombre AS dependencia,
    d.id_dependencia,
    ve.motivo,
    (ve.hora_ingreso)::date AS fecha_cita,
    NULL::time without time zone AS hora_inicio,
    NULL::time without time zone AS hora_fin,
    ve.estado,
    ve.hora_ingreso,
    ve.hora_salida,
    ci.id_ciudadano AS ciudadano_id,
    false AS cancelado_por_ciudadano,
    NULL::text AS motivo_cancelacion,
    NULL::text AS motivo_reprogramacion,
    NULL::date AS fecha_propuesta,
    NULL::time without time zone AS hora_propuesta,
    ve.created_at,
    ve.updated_at
   FROM (((public.visitas_espontaneas ve
     JOIN public.ciudadanos ci ON ((ci.id_ciudadano = ve.ciudadano_id)))
     JOIN public.funcionarios f ON ((f.id_funcionario = ve.funcionario_id)))
     JOIN public.dependencias d ON ((d.id_dependencia = ve.dependencia_id)));

--
-- Name: auditoria_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auditoria_logs ALTER COLUMN id SET DEFAULT nextval('public.auditoria_logs_id_seq'::regclass);

--
-- Name: citas id_cita; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.citas ALTER COLUMN id_cita SET DEFAULT nextval('public.citas_id_cita_seq'::regclass);

--
-- Name: ciudadanos id_ciudadano; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ciudadanos ALTER COLUMN id_ciudadano SET DEFAULT nextval('public.ciudadanos_id_ciudadano_seq'::regclass);

--
-- Name: configuracion_sistema id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracion_sistema ALTER COLUMN id SET DEFAULT nextval('public.configuracion_sistema_id_seq'::regclass);

--
-- Name: dependencias id_dependencia; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dependencias ALTER COLUMN id_dependencia SET DEFAULT nextval('public.dependencias_id_dependencia_seq'::regclass);

--
-- Name: dias_festivos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dias_festivos ALTER COLUMN id SET DEFAULT nextval('public.dias_festivos_id_seq'::regclass);

--
-- Name: funcionario_dependencia id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionario_dependencia ALTER COLUMN id SET DEFAULT nextval('public.funcionario_dependencia_id_seq'::regclass);

--
-- Name: funcionarios id_funcionario; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionarios ALTER COLUMN id_funcionario SET DEFAULT nextval('public.funcionarios_id_funcionario_seq'::regclass);

--
-- Name: horarios_bloqueados id_bloqueo; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios_bloqueados ALTER COLUMN id_bloqueo SET DEFAULT nextval('public.horarios_bloqueados_id_bloqueo_seq'::regclass);

--
-- Name: login_attempts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.login_attempts ALTER COLUMN id SET DEFAULT nextval('public.login_attempts_id_seq'::regclass);

--
-- Name: password_resets id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_resets ALTER COLUMN id SET DEFAULT nextval('public.password_resets_id_seq'::regclass);

--
-- Name: personal id_personal; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal ALTER COLUMN id_personal SET DEFAULT nextval('public.personal_id_personal_seq'::regclass);

--
-- Name: usuarios id_usuario; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id_usuario SET DEFAULT nextval('public.usuarios_id_usuario_seq'::regclass);

--
-- Name: valoraciones id_valoracion; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.valoraciones ALTER COLUMN id_valoracion SET DEFAULT nextval('public.valoraciones_id_valoracion_seq'::regclass);

--
-- Name: visitas_espontaneas id_visita; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas_espontaneas ALTER COLUMN id_visita SET DEFAULT nextval('public.visitas_espontaneas_id_visita_seq'::regclass);

--
-- Name: auditoria_logs auditoria_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auditoria_logs
    ADD CONSTRAINT auditoria_logs_pkey PRIMARY KEY (id);

--
-- Name: citas citas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.citas
    ADD CONSTRAINT citas_pkey PRIMARY KEY (id_cita);

--
-- Name: ciudadanos ciudadanos_numero_identificacion_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ciudadanos
    ADD CONSTRAINT ciudadanos_numero_identificacion_key UNIQUE (numero_identificacion);

--
-- Name: ciudadanos ciudadanos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ciudadanos
    ADD CONSTRAINT ciudadanos_pkey PRIMARY KEY (id_ciudadano);

--
-- Name: configuracion_sistema configuracion_sistema_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracion_sistema
    ADD CONSTRAINT configuracion_sistema_pkey PRIMARY KEY (id);

--
-- Name: dependencias dependencias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dependencias
    ADD CONSTRAINT dependencias_pkey PRIMARY KEY (id_dependencia);

--
-- Name: dias_festivos dias_festivos_fecha_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dias_festivos
    ADD CONSTRAINT dias_festivos_fecha_key UNIQUE (fecha);

--
-- Name: dias_festivos dias_festivos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.dias_festivos
    ADD CONSTRAINT dias_festivos_pkey PRIMARY KEY (id);

--
-- Name: funcionario_dependencia funcionario_dependencia_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionario_dependencia
    ADD CONSTRAINT funcionario_dependencia_pkey PRIMARY KEY (id);

--
-- Name: funcionarios funcionarios_numero_identificacion_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionarios
    ADD CONSTRAINT funcionarios_numero_identificacion_key UNIQUE (numero_identificacion);

--
-- Name: funcionarios funcionarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionarios
    ADD CONSTRAINT funcionarios_pkey PRIMARY KEY (id_funcionario);

--
-- Name: horarios_bloqueados horarios_bloqueados_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios_bloqueados
    ADD CONSTRAINT horarios_bloqueados_pkey PRIMARY KEY (id_bloqueo);

--
-- Name: login_attempts login_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.login_attempts
    ADD CONSTRAINT login_attempts_pkey PRIMARY KEY (id);

--
-- Name: password_resets password_resets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_pkey PRIMARY KEY (id);

--
-- Name: password_resets password_resets_token_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT password_resets_token_key UNIQUE (token);

--
-- Name: personal personal_numero_identificacion_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal
    ADD CONSTRAINT personal_numero_identificacion_key UNIQUE (numero_identificacion);

--
-- Name: personal personal_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal
    ADD CONSTRAINT personal_pkey PRIMARY KEY (id_personal);

--
-- Name: php_sessions php_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.php_sessions
    ADD CONSTRAINT php_sessions_pkey PRIMARY KEY (id);

--
-- Name: funcionario_dependencia uq_funcionario_dependencia; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionario_dependencia
    ADD CONSTRAINT uq_funcionario_dependencia UNIQUE (funcionario_id, dependencia_id);

--
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);

--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id_usuario);

--
-- Name: valoraciones valoraciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.valoraciones
    ADD CONSTRAINT valoraciones_pkey PRIMARY KEY (id_valoracion);

--
-- Name: valoraciones valoraciones_token_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.valoraciones
    ADD CONSTRAINT valoraciones_token_key UNIQUE (token);

--
-- Name: visitas_espontaneas visitas_espontaneas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas_espontaneas
    ADD CONSTRAINT visitas_espontaneas_pkey PRIMARY KEY (id_visita);

--
-- Name: idx_citas_ciudadano; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_citas_ciudadano ON public.citas USING btree (ciudadano_id);

--
-- Name: idx_citas_estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_citas_estado ON public.citas USING btree (estado);

--
-- Name: idx_citas_fecha_estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_citas_fecha_estado ON public.citas USING btree (fecha, estado);

--
-- Name: idx_citas_funcionario_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_citas_funcionario_fecha ON public.citas USING btree (funcionario_id, fecha);

--
-- Name: idx_citas_sin_solapamiento; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_citas_sin_solapamiento ON public.citas USING btree (funcionario_id, fecha, hora_inicio) WHERE ((estado)::text <> 'cancelada'::text);

--
-- Name: idx_ciudadanos_activo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_ciudadanos_activo ON public.ciudadanos USING btree (activo);

--
-- Name: idx_ciudadanos_numero_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_ciudadanos_numero_id ON public.ciudadanos USING btree (numero_identificacion);

--
-- Name: idx_ciudadanos_usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_ciudadanos_usuario_id ON public.ciudadanos USING btree (usuario_id);

--
-- Name: idx_dependencias_activo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dependencias_activo ON public.dependencias USING btree (activo);

--
-- Name: idx_dependencias_activo_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dependencias_activo_id ON public.dependencias USING btree (activo, id_dependencia, nombre);

--
-- Name: idx_funcionario_dependencia_dependencia_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_funcionario_dependencia_dependencia_id ON public.funcionario_dependencia USING btree (dependencia_id);

--
-- Name: idx_funcionario_dependencia_funcionario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_funcionario_dependencia_funcionario ON public.funcionario_dependencia USING btree (funcionario_id, dependencia_id);

--
-- Name: idx_funcionario_dependencia_funcionario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_funcionario_dependencia_funcionario_id ON public.funcionario_dependencia USING btree (funcionario_id);

--
-- Name: idx_funcionarios_activo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_funcionarios_activo ON public.funcionarios USING btree (activo);

--
-- Name: idx_funcionarios_activo_nombres; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_funcionarios_activo_nombres ON public.funcionarios USING btree (activo, nombres, apellidos);

--
-- Name: idx_funcionarios_usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_funcionarios_usuario_id ON public.funcionarios USING btree (usuario_id);

--
-- Name: idx_horarios_bloqueados_funcionario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_horarios_bloqueados_funcionario ON public.horarios_bloqueados USING btree (funcionario_id);

--
-- Name: idx_login_attempts_ip; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_login_attempts_ip ON public.login_attempts USING btree (ip, created_at);

--
-- Name: idx_password_resets_expires_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_password_resets_expires_at ON public.password_resets USING btree (expires_at);

--
-- Name: idx_password_resets_token; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_password_resets_token ON public.password_resets USING btree (token);

--
-- Name: idx_password_resets_used; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_password_resets_used ON public.password_resets USING btree (used);

--
-- Name: idx_password_resets_usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_password_resets_usuario_id ON public.password_resets USING btree (usuario_id);

--
-- Name: idx_php_sessions_expires; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_php_sessions_expires ON public.php_sessions USING btree (expires_at);

--
-- Name: idx_usuarios_activo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_activo ON public.usuarios USING btree (activo);

--
-- Name: idx_usuarios_email; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_email ON public.usuarios USING btree (email);

--
-- Name: idx_usuarios_rol; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_rol ON public.usuarios USING btree (rol);

--
-- Name: idx_visitas_atendido_por; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_visitas_atendido_por ON public.visitas_espontaneas USING btree (atendido_por);

--
-- Name: idx_visitas_ciudadano; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_visitas_ciudadano ON public.visitas_espontaneas USING btree (ciudadano_id);

--
-- Name: idx_visitas_estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_visitas_estado ON public.visitas_espontaneas USING btree (estado);

--
-- Name: idx_visitas_fecha; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_visitas_fecha ON public.visitas_espontaneas USING btree (hora_ingreso);

--
-- Name: citas trg_calcular_hora_fin; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_calcular_hora_fin BEFORE INSERT OR UPDATE ON public.citas FOR EACH ROW EXECUTE FUNCTION public.calcular_hora_fin();

--
-- Name: ciudadanos trg_ciudadanos_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_ciudadanos_updated_at BEFORE UPDATE ON public.ciudadanos FOR EACH ROW EXECUTE FUNCTION public.trg_ciudadanos_updated_at_fn();

--
-- Name: funcionarios trg_funcionarios_updated_at; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_funcionarios_updated_at BEFORE UPDATE ON public.funcionarios FOR EACH ROW EXECUTE FUNCTION public.actualizar_updated_at();

--
-- Name: citas trg_updated_at_citas; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_updated_at_citas BEFORE UPDATE ON public.citas FOR EACH ROW EXECUTE FUNCTION public.actualizar_updated_at();

--
-- Name: citas citas_ciudadano_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.citas
    ADD CONSTRAINT citas_ciudadano_id_fkey FOREIGN KEY (ciudadano_id) REFERENCES public.ciudadanos(id_ciudadano) ON DELETE RESTRICT;

--
-- Name: citas citas_dependencia_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.citas
    ADD CONSTRAINT citas_dependencia_id_fkey FOREIGN KEY (dependencia_id) REFERENCES public.dependencias(id_dependencia) ON DELETE RESTRICT;

--
-- Name: citas citas_funcionario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.citas
    ADD CONSTRAINT citas_funcionario_id_fkey FOREIGN KEY (funcionario_id) REFERENCES public.funcionarios(id_funcionario) ON DELETE RESTRICT;

--
-- Name: citas citas_gestionado_por_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.citas
    ADD CONSTRAINT citas_gestionado_por_fkey FOREIGN KEY (gestionado_por) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;

--
-- Name: ciudadanos ciudadanos_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ciudadanos
    ADD CONSTRAINT ciudadanos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;

--
-- Name: password_resets fk_password_resets_usuario; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_resets
    ADD CONSTRAINT fk_password_resets_usuario FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id_usuario) ON DELETE CASCADE;

--
-- Name: funcionario_dependencia funcionario_dependencia_dependencia_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionario_dependencia
    ADD CONSTRAINT funcionario_dependencia_dependencia_id_fkey FOREIGN KEY (dependencia_id) REFERENCES public.dependencias(id_dependencia) ON DELETE CASCADE;

--
-- Name: funcionario_dependencia funcionario_dependencia_funcionario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionario_dependencia
    ADD CONSTRAINT funcionario_dependencia_funcionario_id_fkey FOREIGN KEY (funcionario_id) REFERENCES public.funcionarios(id_funcionario) ON DELETE CASCADE;

--
-- Name: funcionarios funcionarios_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.funcionarios
    ADD CONSTRAINT funcionarios_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;

--
-- Name: horarios_bloqueados horarios_bloqueados_funcionario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios_bloqueados
    ADD CONSTRAINT horarios_bloqueados_funcionario_id_fkey FOREIGN KEY (funcionario_id) REFERENCES public.funcionarios(id_funcionario);

--
-- Name: personal personal_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal
    ADD CONSTRAINT personal_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL;

--
-- Name: visitas_espontaneas visitas_espontaneas_atendido_por_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas_espontaneas
    ADD CONSTRAINT visitas_espontaneas_atendido_por_fkey FOREIGN KEY (atendido_por) REFERENCES public.usuarios(id_usuario);

--
-- Name: visitas_espontaneas visitas_espontaneas_ciudadano_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas_espontaneas
    ADD CONSTRAINT visitas_espontaneas_ciudadano_id_fkey FOREIGN KEY (ciudadano_id) REFERENCES public.ciudadanos(id_ciudadano) ON DELETE RESTRICT;

--
-- Name: visitas_espontaneas visitas_espontaneas_dependencia_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas_espontaneas
    ADD CONSTRAINT visitas_espontaneas_dependencia_id_fkey FOREIGN KEY (dependencia_id) REFERENCES public.dependencias(id_dependencia);

--
-- Name: visitas_espontaneas visitas_espontaneas_funcionario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.visitas_espontaneas
    ADD CONSTRAINT visitas_espontaneas_funcionario_id_fkey FOREIGN KEY (funcionario_id) REFERENCES public.funcionarios(id_funcionario) ON DELETE SET NULL;

--
-- PostgreSQL database dump complete
--

