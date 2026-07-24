# Manual de Usuario — Administrador
Sistema de Control de Visitas (SCV) — Alcaldía Municipal de Monterrey, Casanare

---

## 1. Introducción

Este manual explica cómo el **Administrador** configura y mantiene el sistema: gestiona usuarios, personal de recepción, funcionarios, dependencias, horarios de atención, días festivos y cuentas de ciudadanos. El Administrador es responsable de la operación diaria del sistema, pero no accede a los reportes analíticos (eso corresponde al Superadmin).

---

## 2. Iniciar sesión

1. Ingrese su **correo electrónico** y **contraseña**.
2. Haga clic en **"Ingresar al Sistema"**.
3. El sistema lo redirige a **"Gestión de Usuarios"** (su panel principal).

> 📸 **CAPTURA 1:** Pantalla de login.

Para cambiar su propia contraseña, use **"Cambiar contraseña"** en el menú.

> 📸 **CAPTURA 2:** Formulario de cambio de contraseña.

---

## 3. Menú de navegación

El panel de Administrador tiene un menú lateral con las siguientes secciones: **Usuarios**, **Personal**, **Funcionarios**, **Dependencias**, **Funcionarios por Dependencia**, **Horarios de Atención**, **Días Festivos**, **Ciudadanos**.

> 📸 **CAPTURA 3:** Menú lateral completo del panel de Administrador.

---

## 4. Gestión de Usuarios

Vista general con indicadores: total de usuarios, activos, inactivos, funcionarios, administradores y recepcionistas.

> 📸 **CAPTURA 4:** Panel de "Usuarios" con los indicadores (KPIs) y la tabla de usuarios.

### 4.1 Crear un nuevo usuario (Administrador o Recepcionista)
1. Haga clic en **"Crear usuario"** (o botón equivalente "+ Nuevo").
2. Diligencie: Nombres, Apellidos, Tipo y Número de identificación, Correo, Teléfono, Cargo, y **Rol** (Administrador o Recepcionista).
3. Confirme la creación.
4. El sistema genera una **contraseña temporal válida por 24 horas** y la envía al correo del nuevo usuario. Mensaje: **"Usuario creado. Se enviaron las credenciales por correo."**

> 📸 **CAPTURA 5:** Formulario de creación de usuario.

### 4.2 Editar un usuario
1. Haga clic en el ícono/botón **"Editar"** de la fila correspondiente.
2. Modifique correo y/o rol.
3. Guarde los cambios.

> 📸 **CAPTURA 6:** Formulario de edición de usuario.

### 4.3 Cambiar el rol de un usuario
1. Use el selector de rol en la fila del usuario (Administrador / Recepcionista / Funcionario / Ciudadano).
2. Confirme el cambio.

> **Nota:** un usuario no puede cambiar su propio rol ni desactivar su propia cuenta.

### 4.4 Activar / Desactivar un usuario
Haga clic en el interruptor o botón **"Activar/Desactivar"** de la fila correspondiente.

> 📸 **CAPTURA 7:** Botón de activar/desactivar en la tabla de usuarios.

### 4.5 Restablecer contraseña de un usuario
Haga clic en **"Resetear contraseña"**: el sistema envía un enlace de restablecimiento al correo del usuario.

> 📸 **CAPTURA 8:** Botón "Resetear contraseña" señalado en la tabla.

---

## 5. Gestión de Personal

Sección para administrar el personal interno (recepcionistas).

1. **Crear:** haga clic en **"+ Nuevo"**, complete nombres, apellidos, identificación, teléfono, correo y cargo. Se crea automáticamente el usuario asociado con rol Recepcionista y contraseña temporal enviada por correo.
2. **Editar:** modifique los datos de un registro de personal existente.
3. **Activar/Desactivar:** use el interruptor correspondiente en cada fila.

> 📸 **CAPTURA 9:** Panel "Personal" con la tabla y el botón "+ Nuevo".
> 📸 **CAPTURA 10:** Formulario de creación/edición de personal.

---

## 6. Gestión de Funcionarios

Lista de funcionarios sincronizados en el sistema, junto con las dependencias que tienen asignadas.

### 6.1 Dar acceso al sistema a un funcionario
1. Ubique al funcionario que aún no tiene cuenta de acceso.
2. Haga clic en **"Dar acceso"**.
3. Ingrese el correo electrónico que usará para iniciar sesión.
4. Confirme. Se crea la cuenta con rol Funcionario y credenciales temporales enviadas por correo.

> 📸 **CAPTURA 11:** Panel "Funcionarios" con la lista y el botón "Dar acceso".

### 6.2 Editar datos de un funcionario
Modifique nombres, apellidos, identificación, teléfono, correo o cargo desde el botón **"Editar"**.

> 📸 **CAPTURA 12:** Formulario de edición de funcionario.

### 6.3 Activar / Desactivar un funcionario
Use el interruptor correspondiente en la fila del funcionario.

---

## 7. Dependencias

Gestión del catálogo de dependencias municipales (ej. Secretaría de Salud, Secretaría de Gobierno).

1. **Crear:** botón **"+ Nueva Dependencia"** → complete **Nombre** y **Descripción** → guardar.
2. **Editar:** modifique nombre/descripción de una dependencia existente.
3. **Activar/Desactivar:** interruptor en la fila correspondiente.

> 📸 **CAPTURA 13:** Panel "Dependencias" con la tabla y el formulario de creación.

---

## 8. Funcionarios por Dependencia

Asigna qué funcionarios pertenecen a cada dependencia (relación muchos-a-muchos).

1. **Asignar:** seleccione el **Funcionario** y la **Dependencia**, luego confirme.
2. **Desasignar:** haga clic en el botón de quitar junto a una asignación existente.

> 📸 **CAPTURA 14:** Panel "Funcionarios por Dependencia" mostrando el formulario de asignación y la lista de asignaciones activas.

---

## 9. Horarios de Atención

Configuración única (aplica a todo el sistema) de los horarios en que se pueden agendar citas.

1. Defina **Hora inicio / fin de la mañana** y **Hora inicio / fin de la tarde**.
2. Defina el **intervalo entre citas** en minutos (entre 5 y 120).
3. Marque los **días hábiles** (lunes a domingo, mediante casillas de verificación).
4. Haga clic en **"Guardar"**.

> 📸 **CAPTURA 15:** Formulario de "Horarios de Atención" completo.

---

## 10. Días Festivos

Gestión de fechas en las que no se permite agendar citas.

1. **Crear:** botón **"+ Nuevo Festivo"** → complete **Descripción**, **Fecha**, y marque **Recurrente** si el festivo se repite cada año en la misma fecha (independiente del año).
2. **Editar:** modifique un festivo existente.
3. **Activar/Desactivar:** interruptor en la fila correspondiente.

> 📸 **CAPTURA 16:** Panel "Días Festivos" con la tabla y el formulario de creación.

---

## 11. Ciudadanos

Vista de solo lectura de las cuentas de ciudadanos registrados, con la posibilidad de activar/desactivar una cuenta.

1. Consulte la lista de ciudadanos registrados.
2. Para bloquear el acceso de un ciudadano, use el interruptor **"Activar/Desactivar"** en su fila.

> 📸 **CAPTURA 17:** Panel "Ciudadanos" con la tabla y el interruptor de activar/desactivar.

---

## 12. Cerrar sesión

Haga clic en **"Salir"** en la cabecera del panel.

> 📸 **CAPTURA 18:** Ubicación del botón "Salir".
