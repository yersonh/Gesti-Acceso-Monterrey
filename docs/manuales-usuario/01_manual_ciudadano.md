# Manual de Usuario — Ciudadano
Sistema de Control de Visitas (SCV) — Alcaldía Municipal de Monterrey, Casanare

---

## 1. Introducción

Este manual explica cómo un **ciudadano** utiliza el Sistema de Control de Visitas para crear su cuenta, agendar citas con funcionarios de la Alcaldía, hacer seguimiento a sus citas, responder a propuestas de reprogramación y calificar la atención recibida.

---

## 2. Crear una cuenta (registro)

1. Ingrese a la dirección del sistema y haga clic en **"¿No tiene cuenta? Regístrese aquí"** en la pantalla de inicio de sesión.

   > 📸 **CAPTURA 1:** Pantalla de login mostrando el enlace "Regístrese aquí".

2. Diligencie el formulario de registro:
   - Nombres, Apellidos
   - Tipo de identificación (Cédula de Ciudadanía, Tarjeta de Identidad, Cédula de Extranjería, Pasaporte, Registro Civil)
   - Número de identificación
   - Teléfono
   - Correo electrónico
   - WhatsApp (opcional)
   - Procedencia (opcional)
   - Dirección (opcional)
   - Contraseña y confirmación de contraseña
   - Marcar la casilla de **Términos y condiciones**

   > 📸 **CAPTURA 2:** Formulario de registro completo, con los campos vacíos.

3. Mientras escribe el número de identificación, el sistema valida automáticamente que no esté ya registrado.
4. Haga clic en **"Crear mi cuenta ciudadana"**.
5. El sistema muestra el mensaje **"¡Registro exitoso! Ya puede iniciar sesión."**, envía un correo de confirmación y lo redirige al login en unos segundos.

   > 📸 **CAPTURA 3:** Mensaje de registro exitoso.

---

## 3. Iniciar sesión

1. Ingrese su **correo electrónico** y **contraseña**.
2. Opcionalmente marque **"Recordarme"**.
3. Haga clic en **"Ingresar al Sistema"**.

   > 📸 **CAPTURA 4:** Formulario de login diligenciado.

**Nota de seguridad:** después de 5 intentos fallidos, el sistema bloquea temporalmente los intentos de ingreso y muestra un contador de tiempo restante.

### ¿Olvidó su contraseña?
1. Haga clic en **"¿Olvidó su contraseña?"**.
2. Ingrese su correo electrónico y haga clic en **Enviar**.
3. Revise su correo y siga el enlace para definir una nueva contraseña (mínimo 8 caracteres, con una mayúscula, un número y un carácter especial).

   > 📸 **CAPTURA 5:** Formulario de recuperación de contraseña.

---

## 4. Panel principal (Dashboard)

Al iniciar sesión, el ciudadano llega a su panel principal, donde encuentra:

- Saludo personalizado ("Hola, {nombre}")
- Tarjetas de resumen: **Total de citas**, **Pendientes**, **Confirmadas**, **Finalizadas**
- Botón **"Agendar nueva cita"**
- Sección **"Mis Citas"** con pestañas de filtro: Todas / Pendientes / Confirmadas / Finalizadas / Canceladas

> 📸 **CAPTURA 6:** Vista completa del dashboard del ciudadano, señalando las tarjetas de estadísticas, el botón de agendar y la lista de citas.

---

## 5. Agendar una nueva cita

1. Desde el dashboard, haga clic en **"Agendar nueva cita"**.

   > 📸 **CAPTURA 7:** Botón "Agendar nueva cita" resaltado.

2. **Paso 1 — Dependencia:** seleccione la dependencia municipal que desea visitar (ej. Secretaría de Salud, Secretaría de Gobierno, etc.).

   > 📸 **CAPTURA 8:** Paso 1 del asistente de agendamiento — selección de dependencia.

3. **Paso 2 — Funcionario:** seleccione el funcionario con quien desea la cita (la lista se habilita solo después de elegir la dependencia).

   > 📸 **CAPTURA 9:** Paso 2 — selección de funcionario.

4. **Paso 3 — Fecha y hora:** elija una fecha (no se permiten días festivos ni no hábiles) y luego haga clic sobre uno de los horarios disponibles mostrados en bloques de "Mañana" y "Tarde".

   > 📸 **CAPTURA 10:** Paso 3 — calendario y horarios disponibles.

5. **Paso 4 — Motivo:** escriba el motivo de la cita (entre 10 y 1000 caracteres; el sistema muestra un contador).

   > 📸 **CAPTURA 11:** Paso 4 — campo de motivo con contador de caracteres.

6. Haga clic en **"Confirmar cita"**.
7. El sistema muestra el mensaje **"Solicitud enviada, pendiente de aprobación"**, envía un correo de confirmación y regresa al dashboard. La cita queda en estado **Pendiente** hasta que el funcionario la apruebe.

## 6. Consultar mis citas

En la sección **"Mis Citas"**, use las pestañas para filtrar por estado. Cada tarjeta de cita muestra: dependencia, funcionario, fecha y hora, motivo, estado (con color/insignia) y, si el funcionario dejó una nota, esta también se muestra.

> 📸 **CAPTURA 13:** Lista de citas filtrada por "Pendientes", mostrando una tarjeta de cita típica con todos sus datos.

---

## 7. Cancelar una cita pendiente

Solo es posible cancelar citas en estado **Pendiente**.

1. En la tarjeta de la cita, haga clic en **"Cancelar"**.
2. Se abre una ventana emergente; opcionalmente escriba el motivo de cancelación.
3. Haga clic en **"Sí, cancelar cita"**.
4. El sistema confirma con el mensaje **"Cita cancelada exitosamente."**

> 📸 **CAPTURA 14:** Ventana modal de cancelación de cita.

---

## 8. Responder a una propuesta de reprogramación

Cuando un funcionario no puede atender la cita en la fecha original, propone una nueva fecha/hora. El ciudadano verá un bloque destacado **"Nueva propuesta: fecha · hora"** en la tarjeta de la cita, con tres opciones:

> 📸 **CAPTURA 15:** Tarjeta de cita mostrando el bloque de "Nueva propuesta" con los botones de acción.

- **Aceptar:** confirma directamente la nueva fecha propuesta. Mensaje: **"¡Cita confirmada! La nueva fecha ha sido aceptada."**
- **Proponer otra fecha:** abre un formulario para elegir una fecha y hora alternativa (`contrapropuesta_fecha`, `contrapropuesta_hora`) y enviarla con el botón **"Enviar propuesta"**. Mensaje: **"Tu propuesta fue enviada. El funcionario la revisará..."**
- **Rechazar propuesta:** tras confirmar, la cita vuelve a estado Pendiente. Mensaje: **"Propuesta rechazada. El funcionario buscará una nueva alternativa."**

> 📸 **CAPTURA 16:** Modal con las tres opciones (Aceptar / Proponer otra fecha / Rechazar).
> 📸 **CAPTURA 17:** Formulario para proponer una fecha y hora alternativa.

---

## 9. Calificar la atención recibida (encuesta de valoración)

Después de que el funcionario registre la salida de una visita, el sistema envía automáticamente al correo del ciudadano un enlace de valoración.

1. Abra el correo recibido y haga clic en el enlace **"Califica tu visita"**.
2. Seleccione de 1 a 5 estrellas.
3. Responda si su motivo de visita fue solucionado (Sí / No).
4. Escriba un comentario opcional (máximo 500 caracteres).
5. Haga clic en **"Enviar"**.

   > 📸 **CAPTURA 18:** Formulario de encuesta de valoración con las estrellas y el campo de comentario.
   > 📸 **CAPTURA 19:** Pantalla de agradecimiento tras enviar la valoración.

> **Nota:** este enlace es de un solo uso y expira después de cierto tiempo. Si el enlace ya fue usado o venció, se mostrará una pantalla indicando que el enlace no es válido.

---

## 10. Cerrar sesión

Haga clic en **"Salir"** en la parte superior del panel para cerrar la sesión de la pestaña actual.

> 📸 **CAPTURA 20:** Ubicación del botón "Salir" en la cabecera.
