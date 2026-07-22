CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;

CREATE FUNCTION public.actualizar_updated_at() RETURNS trigger
    LANGUAGE plpgsql AS $$
BEGIN
    NEW.updated_at := NOW();
    RETURN NEW;
END;
$$;

CREATE FUNCTION public.calcular_hora_fin() RETURNS trigger
    LANGUAGE plpgsql AS $$
DECLARE
    intervalo integer;
BEGIN
    SELECT intervalo_min INTO intervalo FROM configuracion_sistema LIMIT 1;
    NEW.hora_fin := NEW.hora_inicio + (intervalo || ' minutes')::INTERVAL;
    RETURN NEW;
END;
$$;

CREATE TABLE public.usuarios (
    id_usuario serial4 NOT NULL,
    email character varying(150) NOT NULL,
    password_hash character varying(255),
    rol character varying(20) DEFAULT 'Ciudadano'::character varying NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    fecha_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    password_temporal boolean DEFAULT false NOT NULL,
    password_temporal_expires_at timestamp with time zone,
    last_login_at timestamp with time zone,
    CONSTRAINT chk_rol CHECK (((rol)::text = ANY (ARRAY['Superadmin','Administrador','Recepcionista','Funcionario','Ciudadano']::text[]))),
    CONSTRAINT usuarios_email_key UNIQUE (email),
    CONSTRAINT usuarios_pkey PRIMARY KEY (id_usuario)
);
CREATE INDEX idx_usuarios_activo ON public.usuarios USING btree (activo);
CREATE INDEX idx_usuarios_email ON public.usuarios USING btree (email);
CREATE INDEX idx_usuarios_rol ON public.usuarios USING btree (rol);

CREATE TABLE public.personal (
    id_personal serial4 NOT NULL,
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
    CONSTRAINT chk_tipo_id_personal CHECK (((tipo_identificacion)::text = ANY (ARRAY['CC','TI','CE','PA','NIT','RC']::text[]))),
    CONSTRAINT personal_numero_identificacion_key UNIQUE (numero_identificacion),
    CONSTRAINT personal_pkey PRIMARY KEY (id_personal),
    CONSTRAINT personal_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL
);

CREATE TABLE public.dependencias_cache (
    id_dependencia integer NOT NULL,
    nombre character varying(150) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true NOT NULL,
    synced_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT dependencias_cache_pkey PRIMARY KEY (id_dependencia)
);
CREATE INDEX idx_dependencias_cache_activo ON public.dependencias_cache USING btree (activo);

CREATE TABLE public.funcionarios_cache (
    id_funcionario integer NOT NULL,
    nombres character varying(100) NOT NULL,
    apellidos character varying(100) NOT NULL,
    tipo_identificacion character varying(3) NOT NULL,
    numero_identificacion character varying(20) NOT NULL,
    telefono character varying(20),
    email character varying(150),
    cargo character varying(150),
    usuario_id integer,
    activo boolean DEFAULT true NOT NULL,
    synced_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT funcionarios_cache_pkey PRIMARY KEY (id_funcionario),
    CONSTRAINT funcionarios_cache_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL
);
CREATE INDEX idx_funcionarios_cache_activo ON public.funcionarios_cache USING btree (activo);
CREATE INDEX idx_funcionarios_cache_activo_nombres ON public.funcionarios_cache USING btree (activo, nombres, apellidos);

CREATE TABLE public.ciudadanos_cache (
    id_ciudadano integer NOT NULL,
    nombres character varying(100) NOT NULL,
    apellidos character varying(100) NOT NULL,
    tipo_identificacion character varying(3) NOT NULL,
    numero_identificacion character varying(20) NOT NULL,
    telefono character varying(20),
    email character varying(150),
    direccion character varying(250),
    proveniencia character varying(150),
    whatsapp character varying(20),
    usuario_id integer,
    activo boolean DEFAULT true NOT NULL,
    synced_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT ciudadanos_cache_pkey PRIMARY KEY (id_ciudadano),
    CONSTRAINT ciudadanos_cache_numero_identificacion_key UNIQUE (numero_identificacion),
    CONSTRAINT ciudadanos_cache_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL
);
CREATE INDEX idx_ciudadanos_cache_activo ON public.ciudadanos_cache USING btree (activo);
CREATE INDEX idx_ciudadanos_cache_usuario_id ON public.ciudadanos_cache USING btree (usuario_id);

CREATE TABLE public.configuracion_sistema (
    id serial4 NOT NULL,
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
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT configuracion_sistema_pkey PRIMARY KEY (id)
);

CREATE TABLE public.dias_festivos (
    id serial4 NOT NULL,
    fecha date NOT NULL,
    descripcion character varying(100) NOT NULL,
    recurrente boolean DEFAULT false,
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    CONSTRAINT dias_festivos_fecha_key UNIQUE (fecha),
    CONSTRAINT dias_festivos_pkey PRIMARY KEY (id)
);

CREATE TABLE public.funcionario_dependencia (
    id serial4 NOT NULL,
    funcionario_id integer NOT NULL,
    dependencia_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT funcionario_dependencia_pkey PRIMARY KEY (id),
    CONSTRAINT uq_funcionario_dependencia UNIQUE (funcionario_id, dependencia_id),
    CONSTRAINT funcionario_dependencia_funcionario_id_fkey FOREIGN KEY (funcionario_id) REFERENCES public.funcionarios_cache(id_funcionario) ON DELETE CASCADE,
    CONSTRAINT funcionario_dependencia_dependencia_id_fkey FOREIGN KEY (dependencia_id) REFERENCES public.dependencias_cache(id_dependencia) ON DELETE CASCADE
);
CREATE INDEX idx_funcionario_dependencia_dependencia_id ON public.funcionario_dependencia USING btree (dependencia_id);
CREATE INDEX idx_funcionario_dependencia_funcionario_id ON public.funcionario_dependencia USING btree (funcionario_id);
CREATE INDEX idx_funcionario_dependencia_funcionario ON public.funcionario_dependencia USING btree (funcionario_id, dependencia_id);

CREATE TABLE public.horarios_bloqueados (
    id_bloqueo serial4 NOT NULL,
    funcionario_id integer NOT NULL,
    fecha date NOT NULL,
    hora_inicio time without time zone NOT NULL,
    hora_fin time without time zone NOT NULL,
    motivo text NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    CONSTRAINT horarios_bloqueados_pkey PRIMARY KEY (id_bloqueo),
    CONSTRAINT horarios_bloqueados_funcionario_id_fkey FOREIGN KEY (funcionario_id) REFERENCES public.funcionarios_cache(id_funcionario)
);
CREATE INDEX idx_horarios_bloqueados_funcionario ON public.horarios_bloqueados USING btree (funcionario_id);

CREATE TABLE public.citas (
    id_cita serial4 NOT NULL,
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
    resultado_visita text,
    token_expira timestamp without time zone,
    token_respuesta character varying(64),
    motivo_reprogramacion text,
    hora_propuesta time without time zone,
    fecha_propuesta date,
    CONSTRAINT chk_estado CHECK (((estado)::text = ANY (ARRAY['pendiente','confirmada','cancelada','completada','no_asistio','en_curso','finalizada','propuesta_reprogramacion','contrapropuesta_ciudadano']::text[]))),
    CONSTRAINT citas_pkey PRIMARY KEY (id_cita),
    CONSTRAINT citas_ciudadano_id_fkey FOREIGN KEY (ciudadano_id) REFERENCES public.ciudadanos_cache(id_ciudadano) ON DELETE RESTRICT,
    CONSTRAINT citas_dependencia_id_fkey FOREIGN KEY (dependencia_id) REFERENCES public.dependencias_cache(id_dependencia) ON DELETE RESTRICT,
    CONSTRAINT citas_funcionario_id_fkey FOREIGN KEY (funcionario_id) REFERENCES public.funcionarios_cache(id_funcionario) ON DELETE RESTRICT,
    CONSTRAINT citas_gestionado_por_fkey FOREIGN KEY (gestionado_por) REFERENCES public.usuarios(id_usuario) ON DELETE SET NULL
);
CREATE INDEX idx_citas_ciudadano ON public.citas USING btree (ciudadano_id);
CREATE INDEX idx_citas_estado ON public.citas USING btree (estado);
CREATE INDEX idx_citas_fecha_estado ON public.citas USING btree (fecha, estado);
CREATE INDEX idx_citas_funcionario_fecha ON public.citas USING btree (funcionario_id, fecha);
CREATE UNIQUE INDEX idx_citas_sin_solapamiento ON public.citas USING btree (funcionario_id, fecha, hora_inicio) WHERE ((estado)::text <> 'cancelada'::text);
CREATE TRIGGER trg_calcular_hora_fin BEFORE INSERT OR UPDATE ON public.citas FOR EACH ROW EXECUTE FUNCTION public.calcular_hora_fin();
CREATE TRIGGER trg_updated_at_citas BEFORE UPDATE ON public.citas FOR EACH ROW EXECUTE FUNCTION public.actualizar_updated_at();

CREATE TABLE public.visitas_espontaneas (
    id_visita serial4 NOT NULL,
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
    resultado_visita text,
    CONSTRAINT visitas_espontaneas_estado_check CHECK (((estado)::text = ANY (ARRAY['en_curso','finalizada']::text[]))),
    CONSTRAINT visitas_espontaneas_pkey PRIMARY KEY (id_visita),
    CONSTRAINT visitas_espontaneas_atendido_por_fkey FOREIGN KEY (atendido_por) REFERENCES public.usuarios(id_usuario),
    CONSTRAINT visitas_espontaneas_ciudadano_id_fkey FOREIGN KEY (ciudadano_id) REFERENCES public.ciudadanos_cache(id_ciudadano) ON DELETE RESTRICT,
    CONSTRAINT visitas_espontaneas_dependencia_id_fkey FOREIGN KEY (dependencia_id) REFERENCES public.dependencias_cache(id_dependencia),
    CONSTRAINT visitas_espontaneas_funcionario_id_fkey FOREIGN KEY (funcionario_id) REFERENCES public.funcionarios_cache(id_funcionario) ON DELETE SET NULL
);
CREATE INDEX idx_visitas_atendido_por ON public.visitas_espontaneas USING btree (atendido_por);
CREATE INDEX idx_visitas_ciudadano ON public.visitas_espontaneas USING btree (ciudadano_id);
CREATE INDEX idx_visitas_estado ON public.visitas_espontaneas USING btree (estado);
CREATE INDEX idx_visitas_fecha ON public.visitas_espontaneas USING btree (hora_ingreso);

CREATE TABLE public.valoraciones (
    id_valoracion serial4 NOT NULL,
    visita_id integer,
    tipo_visita character varying(10),
    token character varying(64) NOT NULL,
    estrellas smallint,
    solucionado boolean,
    comentario text,
    respondido boolean DEFAULT false NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT valoraciones_estrellas_check CHECK (((estrellas >= 1) AND (estrellas <= 5))),
    CONSTRAINT valoraciones_pkey PRIMARY KEY (id_valoracion),
    CONSTRAINT valoraciones_token_key UNIQUE (token)
);

CREATE TABLE public.auditoria_logs (
    id serial4 NOT NULL,
    usuario_id integer,
    usuario_nombre character varying(200),
    usuario_rol character varying(50),
    accion character varying(100) NOT NULL,
    descripcion text,
    tabla_afectada character varying(100),
    registro_id integer,
    ip character varying(45),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT auditoria_logs_pkey PRIMARY KEY (id)
);

CREATE TABLE public.login_attempts (
    id serial4 NOT NULL,
    ip character varying(45) NOT NULL,
    email character varying(255),
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT login_attempts_pkey PRIMARY KEY (id)
);
CREATE INDEX idx_login_attempts_ip ON public.login_attempts USING btree (ip, created_at);

CREATE TABLE public.password_resets (
    id serial4 NOT NULL,
    usuario_id integer NOT NULL,
    token character varying(255) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    used boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT password_resets_pkey PRIMARY KEY (id),
    CONSTRAINT password_resets_token_key UNIQUE (token),
    CONSTRAINT fk_password_resets_usuario FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id_usuario) ON DELETE CASCADE
);
CREATE INDEX idx_password_resets_expires_at ON public.password_resets USING btree (expires_at);
CREATE INDEX idx_password_resets_token ON public.password_resets USING btree (token);
CREATE INDEX idx_password_resets_used ON public.password_resets USING btree (used);
CREATE INDEX idx_password_resets_usuario_id ON public.password_resets USING btree (usuario_id);

CREATE TABLE public.php_sessions (
    id character varying(128) NOT NULL,
    data text DEFAULT ''::text NOT NULL,
    expires_at timestamp with time zone NOT NULL,
    CONSTRAINT php_sessions_pkey PRIMARY KEY (id)
);
CREATE INDEX idx_php_sessions_expires ON public.php_sessions USING btree (expires_at);

CREATE VIEW public.vista_registro_visitas AS
 SELECT 'cita'::text AS tipo_registro, c.id_cita AS id, ci.nombres AS nombres_ciudadano, ci.apellidos AS apellidos_ciudadano,
    ci.tipo_identificacion, ci.numero_identificacion, ci.telefono, ci.email,
    f.id_funcionario, f.nombres AS nombres_funcionario, f.apellidos AS apellidos_funcionario,
    d.nombre AS dependencia, c.motivo, c.fecha AS fecha_cita, c.hora_inicio, c.hora_fin, c.estado,
    c.hora_ingreso, c.hora_salida, NULL::integer AS atendido_por, NULL::text AS nombre_atendido_por,
    c.created_at, c.updated_at, (((c.fecha)::text || ' '::text) || (c.hora_inicio)::text) AS orden
   FROM ((public.citas c JOIN public.ciudadanos_cache ci ON ((ci.id_ciudadano = c.ciudadano_id)))
     JOIN public.funcionarios_cache f ON ((f.id_funcionario = c.funcionario_id)))
     JOIN public.dependencias_cache d ON ((d.id_dependencia = c.dependencia_id))
  WHERE (((c.estado)::text = ANY (ARRAY['pendiente','confirmada','en_curso','finalizada','no_asistio','propuesta_reprogramacion','contrapropuesta_ciudadano'])) AND (c.fecha = CURRENT_DATE))
UNION ALL
 SELECT 'espontanea'::text AS tipo_registro, ve.id_visita AS id, ci.nombres AS nombres_ciudadano, ci.apellidos AS apellidos_ciudadano,
    ci.tipo_identificacion, ci.numero_identificacion, ci.telefono, ci.email,
    ve.funcionario_id AS id_funcionario, f.nombres AS nombres_funcionario, f.apellidos AS apellidos_funcionario,
    d.nombre AS dependencia, ve.motivo, NULL::date AS fecha_cita, NULL::time without time zone AS hora_inicio,
    NULL::time without time zone AS hora_fin, ve.estado, ve.hora_ingreso, ve.hora_salida, ve.atendido_por,
    COALESCE((((p.nombres)::text || ' '::text) || (p.apellidos)::text), (u.email)::text) AS nombre_atendido_por,
    ve.created_at, ve.updated_at, (ve.hora_ingreso)::text AS orden
   FROM ((((public.visitas_espontaneas ve JOIN public.ciudadanos_cache ci ON ((ci.id_ciudadano = ve.ciudadano_id)))
     LEFT JOIN public.funcionarios_cache f ON ((f.id_funcionario = ve.funcionario_id)))
     JOIN public.dependencias_cache d ON ((d.id_dependencia = ve.dependencia_id)))
     LEFT JOIN public.usuarios u ON ((u.id_usuario = ve.atendido_por)))
     LEFT JOIN public.personal p ON ((p.usuario_id = ve.atendido_por))
  WHERE (date(ve.hora_ingreso) = CURRENT_DATE)
  ORDER BY 24;

CREATE VIEW public.vista_reportes_visitas AS
 SELECT 'cita'::text AS tipo_registro, c.id_cita AS id, ci.nombres AS nombres_ciudadano, ci.apellidos AS apellidos_ciudadano,
    ci.tipo_identificacion, ci.numero_identificacion, ci.telefono, ci.email,
    f.id_funcionario, f.nombres AS nombres_funcionario, f.apellidos AS apellidos_funcionario,
    d.nombre AS dependencia, d.id_dependencia, c.motivo, c.fecha AS fecha_cita, c.hora_inicio, c.hora_fin, c.estado,
    c.hora_ingreso, c.hora_salida, c.ciudadano_id, c.cancelado_por_ciudadano, c.motivo_cancelacion,
    c.motivo_reprogramacion, c.fecha_propuesta, c.hora_propuesta, c.created_at, c.updated_at
   FROM ((public.citas c JOIN public.ciudadanos_cache ci ON ((ci.id_ciudadano = c.ciudadano_id)))
     JOIN public.funcionarios_cache f ON ((f.id_funcionario = c.funcionario_id)))
     JOIN public.dependencias_cache d ON ((d.id_dependencia = c.dependencia_id))
UNION ALL
 SELECT 'espontanea'::text AS tipo_registro, ve.id_visita AS id, ci.nombres AS nombres_ciudadano, ci.apellidos AS apellidos_ciudadano,
    ci.tipo_identificacion, ci.numero_identificacion, ci.telefono, ci.email,
    f.id_funcionario, f.nombres AS nombres_funcionario, f.apellidos AS apellidos_funcionario,
    d.nombre AS dependencia, d.id_dependencia, ve.motivo, (ve.hora_ingreso)::date AS fecha_cita,
    NULL::time without time zone AS hora_inicio, NULL::time without time zone AS hora_fin, ve.estado,
    ve.hora_ingreso, ve.hora_salida, ci.id_ciudadano AS ciudadano_id, false AS cancelado_por_ciudadano,
    NULL::text AS motivo_cancelacion, NULL::text AS motivo_reprogramacion, NULL::date AS fecha_propuesta,
    NULL::time without time zone AS hora_propuesta, ve.created_at, ve.updated_at
   FROM ((public.visitas_espontaneas ve JOIN public.ciudadanos_cache ci ON ((ci.id_ciudadano = ve.ciudadano_id)))
     JOIN public.funcionarios_cache f ON ((f.id_funcionario = ve.funcionario_id)))
     JOIN public.dependencias_cache d ON ((d.id_dependencia = ve.dependencia_id));

-- Semilla obligatoria: sin esto, el trigger calcular_hora_fin() falla al crear cualquier cita
INSERT INTO public.configuracion_sistema DEFAULT VALUES;